<?php 
    require __DIR__ . "/../config/db.php";
    require __DIR__ . "/../config/auth_check.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die('Invalid request method');
    }

    $taskID = (int)$_POST['id'];
    $userID = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    $stmt = mysqli_prepare($conn, "select user_id from tasks where id = ?");

    mysqli_stmt_bind_param($stmt, "i", $taskID);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $task = mysqli_fetch_assoc($result);

    if(!$task) {
        header('Location: ../tasks.php');
        exit;
    }

    if ($role !== 'admin' && $task['user_id'] !== $userID) {
        die("Access denied!");
    }

    $deletestmt = mysqli_prepare($conn, "delete from tasks where id = ?");
    
    mysqli_stmt_bind_param($deletestmt, "i", $taskID);
    mysqli_stmt_execute($deletestmt);

    $_SESSION['toast_success'] = 'Task has been successfully deleted';
    
    header("Location: ../tasks.php");
    exit;
?>