<?php
    session_start();

    $env = [];
    $envPath = dirname(__DIR__, 2) . '/.env';

    if(file_exists($envPath)) {
        $env = parse_ini_file($envPath);
    }

    $host = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? null);
    $user = getenv('DB_USER') ?: ($env['DB_USER'] ?? null);
    $pass = getenv('DB_PASS') ?: ($env['DB_PASS'] ?? null);
    $db = getenv('DB_NAME') ?: ($env['DB_NAME'] ?? null);

    $conn = mysqli_connect($host, $user, $pass, $db);

    if(!$conn) {
        die("Database connection error");
    }

    mysqli_set_charset($conn, "utf8");
?>