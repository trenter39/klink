<?php
    require __DIR__ . "/../config/db.php";
    require __DIR__ . "/../config/auth_check.php";

    $selectedUserID = null;
    $selectedUserFullName = '';
    $userID = (int)$_SESSION['user_id'];
    $role = $_SESSION['role'];

    if($role === 'admin' && isset($_GET['user_id']) && ctype_digit($_GET['user_id'])) {
        $selectedUserID = (int)$_GET['user_id'];

        $stmt = mysqli_prepare($conn, "select id, full_name from users where id = ? limit 1");
        
        mysqli_stmt_bind_param($stmt, "i", $selectedUserID);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $selectedUserFullName = $user['full_name'];
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $start_date = trim($_POST['start_date']);
        $status = str_replace('_', ' ', $_POST['status']);
        if ($role === 'admin') $userID = $_POST['user_id'];

        $stmt = mysqli_prepare($conn, "insert into tasks (title, description, start_date, status, user_id) values (?, ?, ?, ?, ?)");

        mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $start_date, $status, $userID);
        mysqli_stmt_execute($stmt);

        $_SESSION['toast_success'] = 'Task has been successfully added';

        header("Location: ../tasks.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add task - Klink</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h1 id="logo"><a href="../dashboard.php">Klink</a></h1>
    <h2>New task</h2>

    <div class="form-box">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title"
                       id="title" placeholder="Title"
                       required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Description"></textarea>
            </div>

            <?php if($role === 'admin'): ?>
                <div class="form-group">
                    <label for="user_search">Employee</label>
                    <input type="text" name="full_name"
                           id="userSearch" placeholder="Type full name"
                           value="<?= e($selectedUserFullName) ?>" required>
                    <input type="hidden" name="user_id"
                           id="userId" value="<?= $selectedUserID ?>"
                           required>
                    <ul id="userResults"></ul>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date"
                       id="start_date" value="<?= date('Y-m-d') ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="New">New</option>
                    <option value="In_Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            
            <button>Save</button> | <a href="../tasks.php">Discard</a>
        </form>
    </div>
</body>
<script>
    const searchInput = document.getElementById('userSearch');
    const resultsBox = document.getElementById('userResults');
    const userIdInput = document.getElementById('userId');

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();

        if(query.length < 2) {
            resultsBox.innerHTML = '';
            resultsBox.style.display = 'none';
            return;
        }

        fetch(`search_users.php?q=${encodeURIComponent(query)}`, { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                resultsBox.innerHTML = '';
                resultsBox.style.display = 'block';

                data.forEach(user => {
                    const li = document.createElement('li');
                    li.textContent = user.full_name;
                    li.addEventListener('mousedown', () => {
                        searchInput.value = user.full_name;
                        userIdInput.value = user.id;
                        resultsBox.innerHTML = '';
                        resultsBox.style.display = 'none';
                    });

                    resultsBox.appendChild(li);
                });
            });
    });

    searchInput.addEventListener('blur', () => {
        resultsBox.style.display = 'none';
    })
</script>
</html>