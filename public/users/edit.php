<?php
    require __DIR__ . "/../config/db.php";
    require __DIR__ . "/../config/auth_check.php";

    if ($_SESSION['role'] !== 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header('Location: ../users.php');
        exit;
    }

    $userID = (int)$_GET['id'];
    $sessionUserID = (int)$_SESSION['user_id'];

    $stmt = mysqli_prepare($conn, "select id, username, full_name, department, role from users where id = ?");

    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        header('Location: ../users.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $fullName = trim($_POST['full_name']);
        $department = trim($_POST['department']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);

        if ($username === '') {
            $error = 'Username cannot be empty';
        } else {
            $checkstmt = mysqli_prepare($conn, "select id from users where username = ? and id != ?");

            mysqli_stmt_bind_param($checkstmt, "si", $username, $userID);
            mysqli_stmt_execute($checkstmt);

            $checkResult = mysqli_stmt_get_result($checkstmt);

            if(mysqli_num_rows($checkResult) > 0) {
                $error = 'Username already exists';
            } else {
                $updatestmt = mysqli_prepare($conn, "update users set username = ?, full_name = ?, department = ?, role = ? where id = ?");

                mysqli_stmt_bind_param($updatestmt, "ssssi", $username, $fullName, $department, $role, $userID);
                mysqli_stmt_execute($updatestmt);

                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $passstmt = mysqli_prepare($conn, "update users set password = ? where id = ?");
                    
                    mysqli_stmt_bind_param($passstmt, "si", $hashedPassword, $userID);
                    mysqli_stmt_execute($passstmt);
                }

                $_SESSION['toast_success'] = 'User has been successfully edited';

                header('Location: ../users.php');
                exit;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit user - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h1 id="logo"><a href="../dashboard.php">Klink</a></h1>
    <h2>Edit user</h2>

    <div class="form-box">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="full_name">Full name</label>
                <input type="text" name="full_name"
                       id="full_name" placeholder="Full name"
                       value="<?= e($user['full_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" name="department"
                       id="department" placeholder="Department"
                       value="<?= e($user['department']) ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username"
                       id="username" placeholder="Username"
                       value="<?= e($user['username']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password"
                       id="password" placeholder="Password (leave empty not to change)">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role">
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="employee" <?= $user['role'] == 'employee' ? 'selected' : '' ?>>Employee</option>
                </select>
            </div>

            <button>Save</button> | <a href="../users.php">Discard</a>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <div class="toast toast-error" id="toast">
            <span><?= e($error) ?></span>
            <button onclick="closeToast()">OK</button>
        </div>
    <?php endif; ?>
    <script src="/toast.js"></script>
</body>
</html>