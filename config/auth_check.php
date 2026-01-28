<?php
    require_once __DIR__ . '/../helpers/security.php';
    require_once __DIR__ . '/../helpers/csrf.php';
    
    csrf_check();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }
?>