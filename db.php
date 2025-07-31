<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "anuradha_hardware";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
