<?php 
    require __DIR__ . "/../config/db.php";
    require __DIR__ . "/../config/auth_check.php";

    if ($_SESSION['role'] !== 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    $id = (int)$_POST['id'];

    if ($id == $_SESSION['user_id']) {
        die('Unable to delete the own account!');
    }

    $stmt = mysqli_prepare($conn, "select 1 from users where id = ?");

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if(!$user) {
        header('Location: ../users.php');
        exit;
    }

    $deletestmt = mysqli_prepare($conn, "delete from users where id = ?");

    mysqli_stmt_bind_param($deletestmt, "i", $id);
    mysqli_stmt_execute($deletestmt);

    $_SESSION['toast_success'] = 'User has been successfully deleted';

    header("Location: ../users.php");
    exit;
?>