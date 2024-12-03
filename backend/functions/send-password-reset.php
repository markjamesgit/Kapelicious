<?php

// Get the email from the form
$email = $_POST["email"];

// Generate a random token
$token = bin2hex(random_bytes(16));

// Hash the token
$token_hash = hash("sha256", $token);

// Set the expiration time to 30 minutes from now
$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

// Connect to the database
$mysqli = require __DIR__ . "/../config/database.php";

// Prepare an SQL statement to update the user's record
$sql = "UPDATE users
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

// Prepare the statement
$stmt = $mysqli->prepare($sql);

// Bind the parameters to the statement
$stmt->bind_param("sss", $token_hash, $expiry, $email);

// Execute the statement
$stmt->execute();

// Check if any rows were affected
if ($mysqli->affected_rows) {

    // Create a new instance of the mailer class
    $mail = require __DIR__ . "/mailer.php";

    // Set the from address
    $mail->setFrom("kapeliciouscoffeeshop@gmail.com");

    // Set the recipient address
    $mail->addAddress($email);

    // Set the subject
    $mail->Subject = "Password Reset";

    // Set the body
    $mail->Body = <<<END

    Click <a href="http://localhost/Kapelicious/frontend/pages/php/reset-password.php?token=$token">here</a> 
    to reset your password.

    END;

    try {

        // Send the email
        $mail->send();

    } catch (Exception $e) {

        // Catch any errors and display them
        echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";

    }

}

// Display a success message
echo "Message sent, please check your inbox.";