<?php
/*
require_once __DIR__ . '/database.php';

function getUsersDatabase(): PDO
{
    $database = connectDatabase();

    try {
        $database->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                email VARCHAR(254) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_users_username (username),
                UNIQUE KEY uq_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    } catch (PDOException $error) {
        error_log('Digi-Pet users table error: ' . $error->getMessage());
        throw new RuntimeException('Could not initialize the users table.', 0, $error);
    }

    return $database;
}

function getAuthCsrfToken(): string
{
    if (!isset($_SESSION['auth_csrf_token'])) {
        $_SESSION['auth_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['auth_csrf_token'];
}

function isValidAuthCsrfToken($token): bool
{
    return is_string($token)
        && isset($_SESSION['auth_csrf_token'])
        && hash_equals($_SESSION['auth_csrf_token'], $token);
}
*/
