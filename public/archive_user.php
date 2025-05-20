<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SESSION['role'] !== 'admin') {
  header("Location: dashboard_user.php");
  exit();
}

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $pdo->prepare("UPDATE users SET status = 'archived' WHERE id = ?");
  $stmt->execute([$id]);
}

header("Location: manage_users.php");
exit();
