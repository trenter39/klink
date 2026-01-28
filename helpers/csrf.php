<?php
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    function csrf_check(): void {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (
                empty($_POST['csrf_token']) ||
                empty($_SESSION['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
            ) {
                die('Invalid CSRF token');
            }
        }
    }
?>