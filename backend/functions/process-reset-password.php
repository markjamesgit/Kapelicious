<?php

// Get the token from the form
$token = $_POST["token"];

// Calculate the hash of the token
$token_hash = hash("sha256", $token);

// Connect to the database
$mysqli = require __DIR__ . "/../config/database.php";

// Query to get the user with the given token
$sql = "SELECT * FROM users
        WHERE reset_token_hash = ?";

// Prepare and execute the query
$stmt = $mysqli->prepare($sql);

// Bind the parameter to the query
$stmt->bind_param("s", $token_hash);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Get the user
$user = $result->fetch_assoc();

// If user is not found, die
if ($user === null) {
    die("token not found");
}

// If token has expired, die
if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("token has expired");
}

// Check if the old password and new password are the same
if (password_verify($_POST["new_password"], $user["password_hash"])) {

    // Redirect to the reset page with error message
    header("Location: ../../frontend/pages/php/reset-password.php?token=" . urlencode($token) . "&error=same_password");
    exit();
}

// Check if the new password is at least 8 characters
if(strlen($_POST["new_password"]) < 8) {
    die("Password must be at least 8 characters");
}

// Check if the new password contains at least one letter
if (!preg_match("/[a-z]/i", $_POST["new_password"])) {
    die("Password must contain at least one letter");
}

// Check if the new password contains at least one number
if (!preg_match("/[0-9]/", $_POST["new_password"])) {
    die("Password must contain at least one number");
}

// Check if the new password matches the confirm password
if ($_POST["new_password"] !== $_POST["confirm_password"]) {
    die("Password must match");
}


// Hash the new password
$password_hash = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

// Update the user with the new password hash and reset the token
$sql = "UPDATE users SET password_hash = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";

// Prepare and execute the query
$stmt = $mysqli->prepare($sql);

// Bind the parameter to the query
$stmt->bind_param("ss", $password_hash, $user["id"]);

// Execute the query
$stmt->execute();

// Redirect to the login page
header("Location: ../../frontend/pages/php/login.php");
?>