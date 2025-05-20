<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Only admin allowed
if (!in_array($_SESSION['role'], ['admin'])) {
    header("Location: dashboard_user.php");
    exit();
}

// Process the rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['booking_id']) && isset($_POST['justification'])) {
        $booking_id = $_POST['booking_id'];
        $justification = trim($_POST['justification']);
        
        // Basic validation
        if (empty($justification)) {
            $_SESSION['error'] = "Rejection reason is required";
        } else {
            // Update the booking status and add the rejection reason
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'rejected', rejection_reason = ? WHERE id = ?");
            if ($stmt->execute([$justification, $booking_id])) {
                $_SESSION['success'] = "Booking has been rejected successfully";
            } else {
                $_SESSION['error'] = "Failed to reject booking";
            }
        }
    } else {
        $_SESSION['error'] = "Missing required information";
    }
}

// Redirect back to manage bookings
header("Location: manage_bookings.php");
exit();
?>