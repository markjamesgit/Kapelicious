<?php
// Get the email and new passwords from the form
$email = $_POST["email"]; // Get the email from the form
$new_password = $_POST["new_password"]; // Get the new password from the form
$confirm_password = $_POST["confirm_password"]; // Get the confirm password from the form

// Validate inputs
if (empty($new_password) || empty($confirm_password)) {
    die("Both password fields are required."); // Exit if either field is empty
}

// Check if the passwords match
if ($new_password !== $confirm_password) {
    die("Passwords do not match."); // Exit if the passwords do not match
}

$mysqli = require __DIR__ . "/../config/database.php"; // Connect to the database

$sql = "SELECT password_hash FROM users WHERE email = ?"; // Query to get the current password hash
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email); // Bind the email to the query
$stmt->execute(); // Execute the query
$result = $stmt->get_result(); // Get the result
$user = $result->fetch_assoc(); // Fetch the user

if ($user === null) {
    die("User not found."); // Exit if the user is not found
}

$old_password_hash = $user["password_hash"]; // Current password hash

// Check if the new password is the same as the old password
if (password_verify($new_password, $old_password_hash)) {
    // If the new password is the same as the old, redirect with an error message
    header("Location: /Kapelicious/frontend/pages/php/reset-password.php?email=" . urlencode($email) . "&error=password_match");
    exit();
}

// Hash the new password
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT); // Hash the new password

// Update the user's password in the database
$sql = "UPDATE users SET password_hash = ? WHERE email = ?"; // Query to update the user's password
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $new_password_hash, $email); // Bind the new password hash and email to the query
$stmt->execute(); // Execute the query

header("Location: /Kapelicious/frontend/pages/php/login.php");
exit();
?>