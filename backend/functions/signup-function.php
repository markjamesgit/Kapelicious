<?php

// Connect to the database
$mysqli = require __DIR__ . "/../config/database.php"; 

// Initialize error message variable
$error_message = null; 

// Validate the input fields
if (empty($_POST["name"])) { 

    // Set error message for empty name
    $error_message = "Name is required."; 
} elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) { 

    // Set error message for invalid email
    $error_message = "A valid email is required."; 
} elseif (strlen($_POST["password"]) < 8) { 

    // Set error message for short password
    $error_message = "Password must be at least 8 characters."; 
} elseif (!preg_match("/[a-z]/i", $_POST["password"])) { 

    // Set error message for missing letter
    $error_message = "Password must contain at least one letter."; 
} elseif (!preg_match("/[0-9]/", $_POST["password"])) { 

     // Set error message for missing number
    $error_message = "Password must contain at least one number.";
} elseif ($_POST["password"] !== $_POST["confirm_password"]) { 

    // Set error message for mismatched passwords
    $error_message = "Passwords do not match."; 
}

// Redirect back with error if validation fails
if ($error_message) {
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message)); 
    exit;
}

// Check if email already exists

// SQL query to check existing email
$sql = "SELECT COUNT(*) FROM users WHERE email = ?"; 

// Prepare the SQL statement
$stmt = $mysqli->prepare($sql); 

// Bind the email parameter
$stmt->bind_param("s", $_POST["email"]); 

 // Execute the statement
$stmt->execute();

// Bind the result to $count
$stmt->bind_result($count); 

// Fetch the result
$stmt->fetch(); 

// Close the statement
$stmt->close(); 

if ($count > 0) { 

     // Set error message for existing email
    $error_message = "This email is already registered.";
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message)); 
    exit; 
}

// Generate credentials and insert user into the database

// Hash the password
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT); 

// Generate a random verification code
$verification_code = rand(100000, 999999); 

// SQL to insert user
$sql = "INSERT INTO users (name, username, address, email, password_hash, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)"; 

// Prepare the insert statement
$stmt = $mysqli->prepare($sql); 

if (!$stmt->prepare($sql)) { 

    // Set error message for internal error
    $error_message = "Internal error occurred."; 
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message)); 
    exit; 
}

$stmt->bind_param("ssssss", $_POST["name"], $_POST["username"], $_POST["address"], $_POST["email"], $password_hash, $verification_code); 

// Execute the insert statement
$stmt->execute(); 

// Send verification email

// Load mailer
$mail = require __DIR__ . "/mailer.php"; 

// Set sender email
$mail->setFrom("kapeliciouscoffeeshop@gmail.com"); 

// Add recipient email
$mail->addAddress($_POST["email"]); 

// Set email subject
$mail->Subject = "Account Activation"; 

// Set email body
$mail->Body = <<<END
Thank you for signing up! Your verification code is: <b>$verification_code</b><br>
Please enter this code on the verification page to activate your account.
END; 

try {
    // Attempt to send the email
    $mail->send(); 
    header("Location: ../../frontend/pages/php/verify-account.php"); 
    exit; 
} catch (Exception $e) { 

    // Set error message for email failure
    $error_message = "Failed to send verification email."; 
    header("Location: ../../frontend/pages/php/signup.php?error=" . urlencode($error_message)); 
    exit; 
}
?>