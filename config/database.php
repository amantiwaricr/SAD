<?php
// config/database.php

$host = 'localhost';
$db_name = 'hms_db';
$username = 'root'; // Change as necessary
$password = ''; // Change as necessary

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set charset
    $conn->exec("set names utf8");
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
    exit;
}
?>
