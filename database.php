<?php

function connectDatabase(): PDO
{
    $host = getenv('DIGIPET_DB_HOST');
    if ($host === false) {
        $host = getenv('DB_HOST');
    }
    if ($host === false || $host === '') {
        $host = '127.0.0.1';
    }

    $user = getenv('DIGIPET_DB_USER');
    if ($user === false) {
        $user = getenv('DB_USER');
    }
    if ($user === false || $user === '') {
        error_log('Digi-Pet database is not configured: DIGIPET_DB_USER is missing.');
        throw new RuntimeException('Database user is not configured.');
    }

    $password = getenv('DIGIPET_DB_PASSWORD');
    if ($password === false) {
        $password = getenv('DB_PASS');
    }
    if ($password === false) {
        $password = '';
    }

    $databaseName = getenv('DIGIPET_DB_NAME');
    if ($databaseName === false) {
        $databaseName = getenv('DB_NAME');
    }
    if ($databaseName === false || $databaseName === '') {
        $databaseName = 'block';
    }

    try {
        return new PDO(
            "mysql:host={$host};dbname={$databaseName};charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $error) {
        error_log('Digi-Pet database connection error: ' . $error->getMessage());
        throw new RuntimeException('Could not connect to the database.', 0, $error);
    }
}
