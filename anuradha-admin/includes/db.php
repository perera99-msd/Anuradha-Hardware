<?php
$host = "localhost";
$user = "root";
$password = ""; // your DB password
$database = "anuradha_hardware"; // your DB name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
