<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the email from the form
$email = trim($_POST["email"] ?? '');

// Validate that an email was provided
if (empty($email)) {
    die("No email provided.");
}

// Generate a random verification code
$verification_code = rand(100000, 999999);

// Set the expiration time to 10 minutes from now
$expiry = date("Y-m-d H:i:s", time() + 60 * 10);

$mysqli = require __DIR__ . "/../config/database.php";

$sql = "UPDATE users
        SET verification_code = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Failed to prepare statement: " . $mysqli->error);
}

// Bind the parameters and execute the query
$stmt->bind_param("sss", $verification_code, $expiry, $email);
$stmt->execute();

// Check if any rows were affected (i.e., email exists in the database)
if ($stmt->affected_rows) {
    // Send the email using the mailer
    $mail = require __DIR__ . "/mailer.php";
    $mail->setFrom("kapeliciouscoffeeshop@gmail.com");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset Code";
    $mail->Body = "Your password reset verification code is: <strong>$verification_code</strong>. This code will expire in 10 minutes.";

    try {
        $mail->send();
        header("Location: ../../frontend/pages/php/verify-code.php?email=" . urlencode($email));
    } catch (Exception $e) {
        die("Error sending email: {$mail->ErrorInfo}");
    }
} else {
    die("Email not found. Please check and try again.");
}
?>