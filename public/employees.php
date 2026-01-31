<?php 
    require __DIR__ . "/config/db.php";
    require __DIR__ . "/config/auth_check.php";

    $userID = (int)$_SESSION['user_id'];

    if ($_SESSION['role'] === 'admin') {
        header('Location: users.php');
        exit;
    }

    $result = mysqli_query($conn, "select id, full_name, department from users where role='employee' order by full_name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <h1 id="logo"><a href="dashboard.php">Klink</a></h1>
    <h2>Employees</h2>

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
                <?php while ($employee = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= e($employee['full_name']) ?></td>
                        <td><?= e($employee['department']) ?></td>
                        <td><a href="tasks.php?user_id=<?= $employee['id'] ?>">View tasks</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
