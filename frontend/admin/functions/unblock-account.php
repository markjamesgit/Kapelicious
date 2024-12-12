<?php
// Start session and check if the user is logged in as an admin
session_start();

if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/index.php"); // Redirect if not an admin
    exit;
}

// Get the user ID from the URL parameter
$user_id = $_GET["id"] ?? null;

if ($user_id) {
    // Connect to the database
    $mysqli = require __DIR__ . "../../../../backend/config/database.php";

    // Update the user's blocked status to 0 (unblocked)
    $sql = "UPDATE users SET is_blocked = 0, failed_attempts = 0 WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Redirect back to the manage accounts page
    header("Location: /Kapelicious/frontend/admin/pages/manage-accounts.php");
    exit;
} else {
    // If no user ID is provided, redirect to manage accounts page
    header("Location: /Kapelicious/frontend/admin/pages/manage-accounts.php");
    exit;
}
?>