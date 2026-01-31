<?php
    require __DIR__ . "/../config/db.php";
    require __DIR__ . "/../config/auth_check.php";

    $error = "";

    $taskID = (int)$_GET['id'];
    $userID = (int)$_SESSION['user_id'];
    $role = $_SESSION['role'];

    $stmt = mysqli_prepare($conn, "select t.*, u.full_name from tasks t join users u on u.id = t.user_id where t.id = ?");

    mysqli_stmt_bind_param($stmt, "i", $taskID);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $task = mysqli_fetch_assoc($result);

    if (!$task) {
        header('Location: ../tasks.php');
        exit;
    }

    if ($role !== 'admin' && $task['user_id'] != $userID) {
        die("Access denied!");
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $start_date = trim($_POST['start_date']);
        $status = str_replace('_', ' ', $_POST['status']);

        $stmt = mysqli_prepare($conn, "update tasks set title = ?, description = ?, start_date = ?, status = ? where id = ?");

        mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $start_date, $status, $taskID);
        mysqli_stmt_execute($stmt);

        $_SESSION['toast_success'] = 'Task has been successfully edited';

        header("Location: ../tasks.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit task - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h1 id="logo"><a href="../dashboard.php">Klink</a></h1>
    <h2>Edit task</h2>

    <div class="form-box">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title"
                       id="title" placeholder="Title" 
                       value="<?= e($task['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description"
                          placeholder="Description"><?= e($task['description']) ?></textarea>
            </div>

            <?php if($role === 'admin'): ?>
                <div class="form-group">
                    <label for="user_search">Employee</label>
                    <input type="text" name="full_name"
                           id="userSearch" placeholder="Type full name"
                           value="<?= e($task['full_name']) ?>" required>
                    <input type="hidden" name="user_id"
                           id="userId" required>
                    <ul id="userResults"></ul>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date"
                       id="start_date" value="<?= e($task['start_date']) ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status">
                    <option value="New" <?= $task['status'] === 'New' ? 'selected' : '' ?>>New</option>
                    <option value="In_Progress" <?= $task['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Completed" <?= $task['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>

            <button>Save</button> | <a href="../tasks.php">Discard</a>
        </form>
    </div>
</body>
</html>

