<?php

$mysqli = require __DIR__ . "/../config/database.php";

// Error handling variables
$error_message = null;

// Validate the input fields
if (empty($_POST["name"])) {
    $error_message = "Name is required.";
} elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    $error_message = "A valid email is required.";
} elseif (strlen($_POST["password"]) < 8) {
    $error_message = "Password must be at least 8 characters.";
} elseif (!preg_match("/[a-z]/i", $_POST["password"])) {
    $error_message = "Password must contain at least one letter.";
} elseif (!preg_match("/[0-9]/", $_POST["password"])) {
    $error_message = "Password must contain at least one number.";
} elseif ($_POST["password"] !== $_POST["confirm_password"]) {
    $error_message = "Passwords do not match.";
}

// Redirect back with error if validation fails
if ($error_message) {
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message));
    exit;
}

// Check if email already exists
$sql = "SELECT COUNT(*) FROM users WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $_POST["email"]);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    $error_message = "This email is already registered.";
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message));
    exit;
}

// Generate credentials and insert user into the database
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
$verification_code = rand(100000, 999999);
$sql = "INSERT INTO users (name, email, password_hash, verification_code, is_verified) VALUES (?, ?, ?, ?, 0)";
$stmt = $mysqli->prepare($sql);

if (!$stmt->prepare($sql)) {
    $error_message = "Internal error occurred.";
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message));
    exit;
}

$stmt->bind_param("ssss", $_POST["name"], $_POST["email"], $password_hash, $verification_code);
$stmt->execute();

// Send verification email
$mail = require __DIR__ . "/mailer.php";
$mail->setFrom("kapeliciouscoffeeshop@gmail.com");
$mail->addAddress($_POST["email"]);
$mail->Subject = "Account Activation";
$mail->Body = <<<END
Thank you for signing up! Your verification code is: <b>$verification_code</b><br>
Please enter this code on the verification page to activate your account.
END;

try {
    $mail->send();
    header("Location: ../../frontend/pages/php/verify-account.php");
    exit;
} catch (Exception $e) {
    $error_message = "Failed to send verification email.";
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message));
    exit;
}
?>