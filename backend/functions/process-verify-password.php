<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the email and verification code from the form
$email = trim($_POST["email"] ?? '');
$verification_code = trim($_POST["verification_code"] ?? '');

// Validate inputs
if (empty($email) || empty($verification_code)) {
    die("Invalid request. Both email and verification code are required.");
}

$mysqli = require __DIR__ . "/../config/database.php";

// Query to verify the user with the given email and code
$sql = "SELECT * FROM users WHERE email = ? AND verification_code = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Failed to prepare statement: " . $mysqli->error);
}

$stmt->bind_param("ss", $email, $verification_code);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the user and code are valid
if ($user === null) {
    die("Invalid verification code or email. Please try again.");
}

// Update the user's verification status
$sql = "UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Failed to prepare statement: " . $mysqli->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();

header("Location: ../../frontend/pages/php/reset-password.php?email=" . urlencode($email));

exit();
?>