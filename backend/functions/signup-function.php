<?php

$mysqli = require __DIR__ . "/../config/database.php"; // Connect to the database

$error_message = null; // Initialize error message variable

// Validate the input fields
if (empty($_POST["name"])) { // Check if name is empty
    $error_message = "Name is required."; // Set error message for empty name
} elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) { // Validate email format
    $error_message = "A valid email is required."; // Set error message for invalid email
} elseif (strlen($_POST["password"]) < 8) { // Check if password length is less than 8
    $error_message = "Password must be at least 8 characters."; // Set error message for short password
} elseif (!preg_match("/[a-z]/i", $_POST["password"])) { // Check for at least one letter in password
    $error_message = "Password must contain at least one letter."; // Set error message for missing letter
} elseif (!preg_match("/[0-9]/", $_POST["password"])) { // Check for at least one number in password
    $error_message = "Password must contain at least one number."; // Set error message for missing number
} elseif ($_POST["password"] !== $_POST["confirm_password"]) { // Compare password and confirm password
    $error_message = "Passwords do not match."; // Set error message for mismatched passwords
}

// Redirect back with error if validation fails
if ($error_message) {
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message)); // Redirect with error
    exit; // Exit script
}

// Check if email already exists
$sql = "SELECT COUNT(*) FROM users WHERE email = ?"; // SQL query to check existing email
$stmt = $mysqli->prepare($sql); // Prepare the SQL statement
$stmt->bind_param("s", $_POST["email"]); // Bind the email parameter
$stmt->execute(); // Execute the statement
$stmt->bind_result($count); // Bind the result to $count
$stmt->fetch(); // Fetch the result
$stmt->close(); // Close the statement

if ($count > 0) { // Check if email count is greater than 0
    $error_message = "This email is already registered."; // Set error message for existing email
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message)); // Redirect with error
    exit; // Exit script
}

// Generate credentials and insert user into the database
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password
$verification_code = rand(100000, 999999); // Generate a random verification code
$sql = "INSERT INTO users (name, username, address, email, password_hash, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)"; // SQL to insert user
$stmt = $mysqli->prepare($sql); // Prepare the insert statement

if (!$stmt->prepare($sql)) { // Check if statement preparation failed
    $error_message = "Internal error occurred."; // Set error message for internal error
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message)); // Redirect with error
    exit; // Exit script
}

$stmt->bind_param("ssssss", $_POST["name"], $_POST["username"], $_POST["address"], $_POST["email"], $password_hash, $verification_code); // Bind parameters
$stmt->execute(); // Execute the insert statement

// Send verification email
$mail = require __DIR__ . "/mailer.php"; // Load mailer
$mail->setFrom("kapeliciouscoffeeshop@gmail.com"); // Set sender email
$mail->addAddress($_POST["email"]); // Add recipient email
$mail->Subject = "Account Activation"; // Set email subject
$mail->Body = <<<END
Thank you for signing up! Your verification code is: <b>$verification_code</b><br>
Please enter this code on the verification page to activate your account.
END; // Set email body

try {
    $mail->send(); // Attempt to send the email
    header("Location: ../../frontend/pages/php/verify-account.php"); // Redirect to verification page
    exit; // Exit script
} catch (Exception $e) { // Catch exceptions during email sending
    $error_message = "Failed to send verification email."; // Set error message for email failure
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message)); // Redirect with error
    exit; // Exit script
}
?>