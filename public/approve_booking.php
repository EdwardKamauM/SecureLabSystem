<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SESSION['role'] !== 'admin') {
  header("Location: dashboard_user.php");
  exit();
}

if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Update booking status to approved
  $stmt = $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
  $stmt->execute([$id]);
}

header("Location: manage_bookings.php");
exit();
