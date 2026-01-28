<?php 
    require "../config/db.php";
    require "../helpers/security.php";
    require "../helpers/csrf.php";

    csrf_check();

    $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = mysqli_prepare($conn, "select * from users where username = ?");

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['full_name'];
                
                header("Location: ../dashboard.php");
                exit;
            }
        }

        $error = "Wrong username or password!";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h1 id="logo"><a href="#">Klink</a></h1>
    <h2>Login</h2>

    <div class="form-box">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

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

            <button>Sign in</button>
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