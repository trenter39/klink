<?php
    require "config/db.php";
    require "config/auth_check.php";

    $userID = (int)$_SESSION['user_id'];
    $role = $_SESSION['role'];

    $viewUserID = $userID;
    $title = "My tasks";

    if (isset($_GET['user_id']) && $_GET['user_id'] !== $userID) {
        $viewUserID = (int)$_GET['user_id'];
    } elseif ($role === 'admin') {
        $title = 'All tasks';
    }
    // Fetch employee name if viewing specific employee's tasks
    $employeeName = null;
    if ($viewUserID !== $userID) {
        $nameQuery = "select full_name from users where id = $viewUserID";
        $nameResult = mysqli_query($conn, $nameQuery);
        if ($nameResult && mysqli_num_rows($nameResult) > 0) {
            $user = mysqli_fetch_assoc($nameResult);
            $employeeName = $user['full_name'];
            $title = "Employee's tasks - " . e($employeeName);
        }
    }
    if ($role === 'employee' && $viewUserID === $userID) {
        $sql = "select t.*, u.full_name
            from tasks t
            join users u on u.id = t.user_id
            where t.user_id = $userID
            order by t.start_date desc";
    } elseif ($viewUserID !== $userID) {
        $sql = "select t.*, u.full_name
            from tasks t
            join users u on u.id = t.user_id
            where t.user_id = $viewUserID
            order by t.start_date desc";
    } else {
        $sql = "select t.*, u.full_name
            from tasks t
            join users u on u.id = t.user_id
            order by t.start_date desc";
    }

    $result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <h1 id="logo"><a href="dashboard.php">Klink</a></h1>
    <div class="subbar">
        <h2><?= e($title) ?></h2>
        <?php if ($role === 'admin'): ?>
            <a href="tasks/add.php<?= $viewUserID ? '?user_id=' . $viewUserID : '' ?>">+ Add task</a>
        <?php elseif($viewUserID === $userID && $role === 'employee'): ?>
            <a href="tasks/add.php">+ Add task</a>
        <?php endif; ?>
    </div>


    <?php if(mysqli_num_rows($result) === 0): ?>
        <p class="empty-message">There is no tasks yet.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <?php if ($role === 'admin'): ?>
                            <th>Employee</th>
                        <?php endif; ?>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Start date</th>
                        <th>Status</th>
                        <?php if ($role === 'admin' || $userID === $viewUserID): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($task = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <?php if ($role === 'admin'): ?>
                            <td><?= e($task['full_name']) ?></td>
                        <?php endif; ?>
                        <td><?= e($task['title']) ?></td>
                        <td><?= e($task['description']) ?></td>
                        <td><?= e($task['start_date']) ?></td>
                        <td><span class="status status-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>"><?= e($task['status']) ?></span></td>
                        <?php if ($role === 'admin' || $userID === $viewUserID): ?>
                            <td>
                                <a href="tasks/edit.php?id=<?= $task['id'] ?>">Edit</a> | 
                                <form method="post" action="tasks/delete.php" style="display: inline">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
                                    <input type="submit" value="Delete" onclick="return confirm('Delete task?')">
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['toast_success'])): ?>
        <div class="toast toast-success" id="toast">
            <span><?= e($_SESSION['toast_success']) ?></span>
            <button onclick="closeToast()">OK</button>
        </div>
        <?php unset($_SESSION['toast_success']); ?>
    <?php endif; ?>
    <script src="/toast.js"></script>
</body>
</html>