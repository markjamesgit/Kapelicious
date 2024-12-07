<?php

// Enable all errors for debugging
error_reporting(E_ALL);
// Display all errors for debugging
ini_set('display_errors', 1);

// Get the email from the form
$email = trim($_POST["email"] ?? '');
// Validate that an email was provided
if (empty($email)) {
    // If not, die with an error message
    die("No email provided.");
}

// Generate a random verification code
$verification_code = rand(100000, 999999);
// Set the expiration time to 10 minutes from now
$expiry = date("Y-m-d H:i:s", time() + 60 * 10);

// Connect to the database
$mysqli = require __DIR__ . "/../config/database.php";

// Prepare the query to update the user with the given email
$sql = "UPDATE users
        SET verification_code = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

// Prepare the SQL statement for execution
$stmt = $mysqli->prepare($sql);

// Check if the query was successful
if (!$stmt) {
    // If not, die with an error message
    die("Failed to prepare statement: " . $mysqli->error);
}

// Bind the parameters to the query
$stmt->bind_param("sss", $verification_code, $expiry, $email);
// Execute the query
$stmt->execute();

// Check if any rows were affected (i.e., email exists in the database)
if ($stmt->affected_rows) {
    // Send the email using the mailer
    $mail = require __DIR__ . "/mailer.php";
    // Set the sender email address
    $mail->setFrom("kapeliciouscoffeeshop@gmail.com");
    // Set the recipient email address
    $mail->addAddress($email);
    // Set the email subject
    $mail->Subject = "Password Reset Code";
    // Set the email body
    $mail->Body = "Your password reset verification code is: <strong>$verification_code</strong>. This code will expire in 10 minutes.";

    try {
        // Send the email
        $mail->send();
        // Redirect to the verify password page with the email
        header("Location: ../../frontend/pages/php/verify-password.php?email=" . urlencode($email));
    } catch (Exception $e) {
        // If there is an error sending the email, die with an error message
        die("Error sending email: {$mail->ErrorInfo}");
    }
} else {
    // If the email is not found in the database, die with an error message
    die("Email not found. Please check and try again.");
}
?>