<?php
    require '../config/db.php';

    if(!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([]);
        exit;
    }
    if($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([]);
        exit;
    }

    $q = trim($_GET['q'] ?? '');
    $search = "%{$q}%";
    if ($q === '') {
        echo json_encode([]);
        exit;
    }

    $stmt = mysqli_prepare($conn, "select id, full_name from users where full_name like ? order by full_name limit 10");
    mysqli_stmt_bind_param($stmt, "s", $search);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($users);
?>