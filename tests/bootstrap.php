<?php
    $host = getenv("DB_HOST");
    $user = getenv("DB_USER");
    $pass = getenv("DB_PASS");
    $db = getenv("DB_NAME");

    $conn = new mysqli($host, $user, $pass);

    if($conn->connect_error) {
        die("DB connection failed: " . $conn->connect_error);
    }

    $conn->query("create database if not exists $db");
    $conn->select_db($db);

    $conn->query("
        create table if not exists users (
            id int auto_increment primary key,
            username varchar(50),
            password varchar(255),
            role varchar(20)
        )");
?>