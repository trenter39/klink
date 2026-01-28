<?php
    $env = parse_ini_file(__DIR__ . '/../.env');

    $host = $env['DB_HOST'];
    $user = $env['DB_USER'];
    $pass = $env['DB_PASS'];
    $db = $env['DB_NAME'];

    $conn = mysqli_connect($host, $user, $pass, $db);

    if(!$conn) {
        die("Database connection error");
    }

    mysqli_set_charset($conn, "utf8");
    
    session_start();
?>