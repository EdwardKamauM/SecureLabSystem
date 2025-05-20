<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit;
}

// If logged in but not approved, log out and show message
if ($_SESSION['role'] !== 'admin') {
    require_once __DIR__ . '/../config/db.php';
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row && $row['status'] !== 'approved') {
        session_destroy();
        header("Location: ../public/index.php?error=pending");
        exit;
    }
}
?>
