<?php
session_start();
require_once __DIR__ . '/auth.php';

$errorMessage = '';
$username = '';
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!isValidAuthCsrfToken($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Your session expired. Refresh the page and try again.';
    } else {
        $submittedUsername = $_POST['username'] ?? null;
        $submittedEmail = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        $confirmPassword = $_POST['confirm_password'] ?? null;

        if (
            !is_string($submittedUsername)
            || !is_string($submittedEmail)
            || !is_string($password)
            || !is_string($confirmPassword)
        ) {
            $errorMessage = 'Enter valid information in all fields.';
        } else {
            $username = trim($submittedUsername);
            $email = trim($submittedEmail);

            if (!preg_match('/\A[A-Za-z0-9_.-]{3,50}\z/', $username)) {
                $errorMessage = 'Username must be 3-50 characters using letters, numbers, dots, dashes, or underscores.';
            } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = 'Enter a valid email address.';
            } elseif (strlen($password) < 8 || strlen($password) > 72) {
                $errorMessage = 'Password must be between 8 and 72 characters.';
            } elseif (!hash_equals($password, $confirmPassword)) {
                $errorMessage = 'The passwords do not match.';
            } else {
                try {
                    $database = getUsersDatabase();
                    $statement = $database->prepare(
                        'INSERT INTO users (username, email, password_hash)
                         VALUES (:username, :email, :password_hash)'
                    );
                    $statement->execute([
                        'username' => $username,
                        'email' => $email,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ]);

                    header('Location: sign_in.php?registered=1');
                    exit;
                } catch (RuntimeException $error) {
                    $errorMessage = 'Sign-up is temporarily unavailable. Please try again later.';
                } catch (PDOException $error) {
                    if ($error->getCode() === '23000') {
                        $errorMessage = 'That username or email is already registered.';
                    } else {
                        error_log('Digi-Pet sign-up database error: ' . $error->getMessage());
                        $errorMessage = 'Sign-up is temporarily unavailable. Please try again later.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Digi-Pet</title>
    <link rel="stylesheet" href="form_style.css">
</head>
<body>
    <main id="Login">
        <form method="post" action="">
            <h1>Create an account</h1>
            <p class="intro">Sign up for your Digi-Pet account.</p>
            <?php if ($errorMessage !== ''): ?>
                <p class="form-message error" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div>
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    minlength="3"
                    maxlength="50"
                    pattern="[A-Za-z0-9_.-]{3,50}"
                    value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="username"
                    required
                >
            </div>

            <div>
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="254"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="email"
                    required
                >
            </div>

            <div>
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div>
                <label for="confirm-password">Confirm password</label>
                <input
                    type="password"
                    id="confirm-password"
                    name="confirm_password"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >
            </div>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getAuthCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Sign Up</button>
            <p class="switch-link">Already have an account? <a href="sign_in.php">Sign in</a></p>
        </form>
    </main>
    <script>
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirm-password");

        function validatePasswordMatch() {
            confirmPassword.setCustomValidity(
                confirmPassword.value && confirmPassword.value !== password.value
                    ? "Passwords do not match."
                    : ""
            );
        }

        password.addEventListener("input", validatePasswordMatch);
        confirmPassword.addEventListener("input", validatePasswordMatch);
    </script>
    <!-- The script checks that the password fields match before submission. -->
</body>
</html>
