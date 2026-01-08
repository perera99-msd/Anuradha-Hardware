<?php
$host = 'anuradhahardware.wuaze.com'; // REPLACE with your InfinityFree MySQL Hostname
$username = 'if0_40855726';         // REPLACE with your InfinityFree MySQL Username
$password = 'MeOnMyOwn9978';        // REPLACE with your InfinityFree Password
$dbname = 'if0_40855726_adnuradhahahardware';  // REPLACE with your InfinityFree Database Name

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error"); // Don't show full error details on live site for security
}
?>