<?php
require_once "../config/db.php";

$name = "Admin User";
$email = "admin@gmail.com";
$password = "Admin@123"; // plain password
$role = "admin";
$status = "approved";

// Hash the password securely
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "⚠️ Admin already exists!";
    exit;
}

// Insert admin into DB
$insert = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
$insert->execute([$name, $email, $hashed, $role, $status]);

echo "✅ Admin account created: $email / $password";
?>
