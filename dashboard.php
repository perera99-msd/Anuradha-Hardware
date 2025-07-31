<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.html?error=Please+login+first");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
</head>

<body>
    <h1>Welcome <?= $_SESSION['email'] ?>!</h1>
    <a href="logout.php">Logout</a>
</body>

</html>