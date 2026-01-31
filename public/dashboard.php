<?php
    require __DIR__ . "/config/db.php";
    require __DIR__ . "/config/auth_check.php";

    $role = $_SESSION['role'];
    $name = $_SESSION['name'];
    $userID = (int)$_SESSION['user_id'];

    if($role === 'admin') {
        $tasksQuery = "select t.*, u.full_name from tasks t join users u on u.id = t.user_id order by t.start_date desc limit 8";
    } else {
        $tasksQuery = "select t.*, u.full_name from tasks t join users u on u.id = t.user_id where t.user_id = $userID order by t.start_date desc limit 8";
    }

    $tasks = mysqli_query($conn, $tasksQuery);
    $users = mysqli_query($conn, "select * from users order by role desc, full_name limit 8");
    $employees = mysqli_query($conn, "select id, full_name, department from users where role='employee' order by full_name limit 8");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h1 id="logo"><a href="dashboard.php">Klink</a></h1>

    <!-- Admin panel -->
    <?php if ($role === 'admin'): ?>

        <!-- Users block -->
        <div class="subbar">
            <h2>Users</h2>
            <div>
                <a href="users/add.php">+ Add user</a>
                | <a href="users.php">All users</a>
            </div>
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
                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= e($user['username']) ?></td>
                            <td><?= e($user['full_name']) ?></td>
                            <td><?= e($user['department']) ?></td>
                            <td><?= e($user['role'])?></td>
                            <td>
                                <a href="tasks.php?user_id=<?= $user['id'] ?>">Tasks</a> | 
                                <a href="users/edit.php?id=<?= $user['id'] ?>">Edit</a>
                                <?php if($user['id'] != $userID): ?>
                                    | <form method="post" action="users/delete.php" style="display: inline">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                                            <input type="submit" value="Delete" onclick="return confirm('Delete user?')">
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Tasks block -->
        <div class="subbar">
            <h2>Tasks</h2>
            <div>
                <a href="tasks/add.php">+ Add task</a>
                | <a href="tasks.php">All tasks</a>
            </div>
        </div>

        <?php if(mysqli_num_rows($tasks) === 0): ?>
            <p class="empty-message">There is no tasks yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Start date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($task = mysqli_fetch_assoc($tasks)): ?>
                            <tr>
                                <td><?= e($task['full_name']) ?></td>
                                <td><?= e($task['title']) ?></td>
                                <td><?= e($task['description']) ?></td>
                                <td><?= e($task['start_date']) ?></td>
                                <td>
                                    <span class="status status-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>">
                                        <?= e($task['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="tasks/edit.php?id=<?= $task['id'] ?>">Edit</a> | 
                                    <form method="post" action="tasks/delete.php" style="display: inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
                                        <input type="submit" value="Delete" onclick="return confirm('Delete task?')">
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <!-- Employee panel -->
    <?php else: ?>

        <!-- Tasks block -->
        <div class="subbar">
            <h2>My tasks</h2>
            <div>
                <a href="tasks/add.php">+ Add task</a>
                | <a href="tasks.php">All tasks</a>
            </div>
        </div>

        <?php if(mysqli_num_rows($tasks) === 0): ?>
            <p class="empty-message">There is no tasks yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Start date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($task = mysqli_fetch_assoc($tasks)): ?>
                            <tr>
                                <td><?= e($task['title']) ?></td>
                                <td><?= e($task['description']) ?></td>
                                <td><?= e($task['start_date']) ?></td>
                                <td>
                                    <span class="status status-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>">
                                        <?= e($task['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="tasks/edit.php?id=<?= $task['id'] ?>">Edit</a> | 
                                    <form method="post" action="tasks/delete.php" style="display: inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
                                        <input type="submit" value="Delete" onclick="return confirm('Delete task?')">
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Employees block -->
        <div class="subbar">
            <h2>Employees</h2>
            <a href="employees.php">All employees</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Full name</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($employee = mysqli_fetch_assoc($employees)): ?>
                        <tr>
                            <td><?= e($employee['full_name']) ?></td>
                            <td><?= e($employee['department'])?></td>
                            <td><a href="tasks.php?user_id=<?= $employee['id'] ?>">View tasks</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

    <a class="button" href="auth/logout.php">Log out</a>
</body>
</html>