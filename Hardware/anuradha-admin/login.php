<?php
session_start();
include('includes/db.php');

if(isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM admins WHERE username = '$username'");
    
    if(mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);
        if(password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Admin not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Anuradha Hardware</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-primary { background-color: #f57224; border-color: #f57224; }
        .btn-primary:hover { background-color: #e0611b; }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4">Admin Login</h3>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none text-muted">Back to Website</a>
        </div>
    </div>
</body>
</html>