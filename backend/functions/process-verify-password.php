<?php

// Enable all errors for debugging
error_reporting(E_ALL);
// Display all errors for debugging
ini_set('display_errors', 1);

// Get the email from the form or an empty string if it does not exist
$email = trim($_POST["email"] ?? '');
// Get the verification code from the form or an empty string if it does not exist
$verification_code = trim($_POST["verification_code"] ?? '');

// Check if the email and verification code are valid
if (empty($email) || empty($verification_code)) {
    // If not, die with an error message
    die("Invalid request. Both email and verification code are required.");
}

// Connect to the database
$mysqli = require __DIR__ . "/../config/database.php";

// Prepare the query to verify the user with the given email and code
$sql = "SELECT * FROM users WHERE email = ? AND verification_code = ?";
$stmt = $mysqli->prepare($sql);

// Check if the query was successful
if (!$stmt) {
    // If not, die with an error message
    die("Failed to prepare statement: " . $mysqli->error);
}

// Bind the parameters to the query
$stmt->bind_param("ss", $email, $verification_code);
// Execute the query
$stmt->execute();
// Get the result of the query
$result = $stmt->get_result();
// Fetch the user from the result
$user = $result->fetch_assoc();

// Check if the user and code are valid
if ($user === null) {
    // If not, die with an error message
    die("Invalid verification code or email. Please try again.");
}

// Prepare the query to update the user's verification status
$sql = "UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = ?";
$stmt = $mysqli->prepare($sql);

// Check if the query was successful
if (!$stmt) {
    // If not, die with an error message
    die("Failed to prepare statement: " . $mysqli->error);
}

// Bind the parameter to the query
$stmt->bind_param("s", $email);
// Execute the query
$stmt->execute();

// Redirect to the reset password page with the email
header("Location: ../../frontend/pages/php/reset-password.php?email=" . urlencode($email));

// Exit the script
exit();
?>