<?php /*
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
*/ ?>

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
        <form onsubmit="return false;">
            <h1>Welcome back</h1>
            <p class="intro">Sign in to your Digi-Pet account.</p>
            <p class="form-message info" role="status">Sign-in is temporarily disabled.</p>

            <div>
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    maxlength="50"
                    autocomplete="username"
                    required
                >
            </div>

            <div>
                <label for="password">Password</label>
                <div class="password-input">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        class="password-toggle"
                        id="password-toggle"
                        type="button"
                        aria-label="Show password"
                        aria-controls="password"
                        aria-pressed="false"
                    >
                        <img
                            id="password-toggle-icon"
                            src="https://img.icons8.com/?size=100&id=85035&format=png&color=000000"
                            alt=""
                        >
                    </button>
                </div>
            </div>

            <button type="submit">Sign In</button>
            <p class="switch-link">Don't have an account? <a href="signup.php">Sign up</a></p>
        </form>
    </main>
    <script>
        <!-- JavaScript to toggle password visibility -->
        const passwordInput = document.getElementById("password");
        const passwordToggle = document.getElementById("password-toggle");
        const passwordToggleIcon = document.getElementById("password-toggle-icon");

        passwordToggle.addEventListener("click", () => {
            const isVisible = passwordInput.type === "text";
            passwordInput.type = isVisible ? "password" : "text";
            passwordToggle.setAttribute("aria-label", isVisible ? "Show password" : "Hide password");
            passwordToggle.setAttribute("aria-pressed", String(!isVisible));
            passwordToggleIcon.src = isVisible
                ? "https://img.icons8.com/?size=100&id=85035&format=png&color=000000"
                : "https://img.icons8.com/?size=100&id=85028&format=png&color=000000";
        });
    </script>
</body>
</html>
