<?php
session_start();
require_once __DIR__ . '/auth.php';

$errorMessage = '';
$successMessage = '';
$username = '';

if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $successMessage = 'Your account was created. Sign in to continue.';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!isValidAuthCsrfToken($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Your session expired. Refresh the page and try again.';
    } else {
        $submittedUsername = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!is_string($submittedUsername) || !is_string($password)) {
            $errorMessage = 'Enter a valid username and password.';
        } else {
            $username = trim($submittedUsername);

            try {
                $database = getUsersDatabase();
                $statement = $database->prepare(
                    'SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1'
                );
                $statement->execute(['username' => $username]);
                $user = $statement->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: journal.php');
                    exit;
                }

                $errorMessage = 'Incorrect username or password.';
            } catch (RuntimeException $error) {
                $errorMessage = 'Sign-in is temporarily unavailable. Please try again later.';
            } catch (PDOException $error) {
                error_log('Digi-Pet sign-in database error: ' . $error->getMessage());
                $errorMessage = 'Sign-in is temporarily unavailable. Please try again later.';
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
    <title>Sign In | Digi-Pet</title>
    <link rel="stylesheet" href="form_style.css">
</head>
<body>
    <main id="Login">
        <form method="post" action="">
            <h1>Welcome back</h1>
            <p class="intro">Sign in to your Digi-Pet account.</p>
            <?php if ($successMessage !== ''): ?>
                <p class="form-message success" role="status"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($errorMessage !== ''): ?>
                <p class="form-message error" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div>
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    maxlength="50"
                    value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="username"
                    required
                >
            </div>

            <div>
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getAuthCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit">Sign In</button>
            <p class="switch-link">Don't have an account? <a href="signup.php">Sign up</a></p>
        </form>
    </main>
</body>
</html>
