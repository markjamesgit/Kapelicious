<?php

// Check if the name is empty
if (empty($_POST["name"])) {
    die("Name is required");
}

// Validate the email
if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Validate email is required");
}

// Check if the password is at least 8 characters
if(strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

// Check if the password contains at least one letter
if (!preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

// Check if the password contains at least one number
if (!preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number");
}

// Check if the password matches the confirm password
if ($_POST["password"] !== $_POST["confirm_password"]) {
    die("Password must match");
}

// Hash the password
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

// Generate a random activation token
$activation_token = bin2hex(random_bytes(16));

// Hash the activation token
$activation_token_hash = hash("sha256", $activation_token);

// Get the database connection
$mysqli = require __DIR__ . "/../config/database.php";

// Check if the email already exists in the database
$email = $_POST["email"];
$sql = "SELECT COUNT(*) FROM users WHERE email = ?";
$stmt = $mysqli->stmt_init();

// Prepare the SQL statement
if (!$stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

// Bind the parameters
$stmt->bind_param("s", $email);

// Execute the statement
$stmt->execute();

// Bind the result
$stmt->bind_result($count);

// Fetch the result
$stmt->fetch();

// Check if the email already exists
if ($count > 0) {
    die("Email already taken");
}

// Insert the user into the database
$sql = "INSERT INTO users (name, email, password_hash, account_activation_hash) VALUES (?, ?, ?, ?)";
$stmt = $mysqli->stmt_init();

// Prepare the SQL statement
if (!$stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

// Bind the parameters
$stmt->bind_param("ssss", $_POST["name"], $email, $password_hash, $activation_token_hash);

// Execute the statement
if ($stmt->execute()) {

    // Send an activation email
    $mail = require __DIR__ . "/mailer.php";

    $mail->setFrom("kapeliciouscoffeeshop@gmail.com");
    $mail->addAddress($_POST["email"]);
    $mail->Subject = "Account Activation";
    $mail->Body = <<<END

    Click <a href="http://localhost/Kapelicious/frontend/pages/php/activate-account.php?token=$activation_token">here</a> 
    to activate your account.

    END;

    try {

        // Send the email
        $mail->send();

    } catch (Exception $e) {

        // Catch any errors
        echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
        exit;

    }

    // Redirect to the success page
    header("Location: ../../frontend/pages/html/signup-success.html");
    exit;

} else {
    // Catch any errors
    die($mysqli->error . " " . $mysqli->errno);
}