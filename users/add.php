<?php
    require "../config/db.php";
    require "../config/auth_check.php";

    if ($_SESSION['role'] !== 'admin') {
        header('Location: ../dashboard.php');
        exit;
    }

    $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST['username']);
        $fullName = trim($_POST['full_name']);
        $department = trim($_POST['department']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $checkstmt = mysqli_prepare($conn, "select id from users where username = ?");

        mysqli_stmt_bind_param($checkstmt, "s", $username);
        mysqli_stmt_execute($checkstmt);

        $checkResult = mysqli_stmt_get_result($checkstmt);
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "User with such username already exists!";
        } else {
            $stmt = mysqli_prepare($conn, "insert into users (username, password, role, full_name, department) values (?, ?, ?, ?, ?)");

            mysqli_stmt_bind_param($stmt, "sssss", $username, $password, $role, $fullName, $department);
            mysqli_stmt_execute($stmt);
            
            $_SESSION['toast_success'] = 'User has been successfully added';

            header("Location: ../users.php");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add user - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h1 id="logo"><a href="../dashboard.php">Klink</a></h1>
    <h2>Add user</h2>
    
    <div class="form-box">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div class="form-group">
                <label for="full_name">Full name</label>
                <input type="text" name="full_name"
                       id="full_name" placeholder="Full name"
                       required>
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" name="department"
                       id="department" placeholder="Department"
                       required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username"
                       id="username" placeholder="Username"
                       required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password"
                       id="password" placeholder="Password"
                       required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role">
                    <option value="admin">Admin</option>
                    <option value="employee" selected>Employee</option>
                </select>
            </div>

            <button>Create</button> | <a href="../users.php">Discard</a>
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