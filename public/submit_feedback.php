<?php
// submit_feedback.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));

    if (!$name || !$email || !$message) {
        die('Please fill in all fields with valid data.');
    }

    // Connect to DB (adjust with your DB config)
    require_once '../config/db.php'; // Assuming $pdo is your PDO connection

    // Insert feedback into database (create a feedback table first)
    $stmt = $pdo->prepare("INSERT INTO feedback (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt->execute([$name, $email, $message])) {
        header('Location: dashboard_user.php?feedback=success');
        exit;
    } else {
        echo "Error saving feedback. Please try again later.";
    }
} else {
    header('Location: dashboard_user.php');
    exit;
}
