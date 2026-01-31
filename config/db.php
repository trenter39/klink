<?php
    $env = parse_ini_file(__DIR__ . '/../.env');

    $host = getenv('DB_HOST') ?: $env['DB_HOST'];
    $user = getenv('DB_USER') ?: $env['DB_USER'];
    $pass = getenv('DB_PASS') ?: $env['DB_PASS'];
    $db = getenv('DB_NAME') ?: $env['DB_NAME'];

    $conn = mysqli_connect($host, $user, $pass, $db);

    if(!$conn) {
        die("Database connection error");
    }

    mysqli_set_charset($conn, "utf8");
    
    session_start();
?>