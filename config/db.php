<?php
$host = 'localhost';
$db   = 'secure_lab_db'; // Change if your DB name is different
$user = 'root';
$pass = ''; // Default for XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
