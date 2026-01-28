<?php
    require "config/db.php";
    require "config/auth_check.php";

    $userID = (int)$_SESSION['user_id'];
    
    if ($_SESSION['role'] !== 'admin') {
        die("Access denied!");
    }

    $result = mysqli_query($conn, "select * from users order by role desc, full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h1 id="logo"><a href="dashboard.php">Klink</a></h1>

    <div class="subbar">
        <h2>Users</h2>
        <a href="users/add.php">+ Add user</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full name</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= e($user['username']) ?></td>
                    <td><?= e($user['full_name']) ?></td>
                    <td><?= e($user['department']) ?></td>
                    <td><?= e($user['role'])?></td>
                    <td>
                        <a href="tasks.php?user_id=<?= $user['id'] ?>">Tasks</a> | 
                        <a href="users/edit.php?id=<?= $user['id'] ?>">Edit</a> |
                        <?php if($user['id'] != $userID): ?>
                            <form method="post" action="users/delete.php" style="display: inline">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                                    <input type="submit" value="Delete" onclick="return confirm('Delete user?')">
                            </form>
                        <?php else: ?>
                            It's you!
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
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