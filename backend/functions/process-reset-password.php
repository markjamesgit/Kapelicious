<?php
// Get the email and new passwords from the form
$email = $_POST["email"];
$new_password = $_POST["new_password"];
$confirm_password = $_POST["confirm_password"];

// Validate inputs
if (empty($new_password) || empty($confirm_password)) {
    die("Both password fields are required.");
}

// Check if the passwords match
if ($new_password !== $confirm_password) {
    die("Passwords do not match.");
}

$mysqli = require __DIR__ . "/../config/database.php";

$sql = "SELECT password_hash FROM users WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    die("User not found.");
}

$old_password_hash = $user["password_hash"]; // Current password hash

// Check if the new password is the same as the old password
if (password_verify($new_password, $old_password_hash)) {
    // If the new password is the same as the old, redirect with an error message
    header("Location: /Kapelicious/frontend/pages/php/reset-password.php?email=" . urlencode($email) . "&error=password_match");
    exit();
}

// Hash the new password
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update the user's password in the database
$sql = "UPDATE users SET password_hash = ? WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $new_password_hash, $email);
$stmt->execute();

header("Location: /Kapelicious/frontend/pages/php/login.php");
exit();
?>