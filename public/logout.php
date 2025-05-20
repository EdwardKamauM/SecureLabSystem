<?php
session_start();
session_unset();     // Remove all session variables
session_destroy();   // Destroy the session

// Optionally destroy the cookie
if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Redirect to login page
header("Location: index.php");
exit();
