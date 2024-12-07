<?php
// Create a new MySQLi connection with the specified database credentials
$mysqli = new mysqli("localhost", "markjames", "RKVDdUWRfRbRkVK-", "kapelicious_db");

// Check if the connection to the database was successful
if ($mysqli->connect_error) {
    // Terminate the script and display an error message if the connection failed
    die("Connection failed: " . $mysqli->connect_error);
}

// Define admin credentials
$name = "Admin"; // Admin name
$email = "admin@kapelicious.com"; // Admin email
$password = "admin123"; // Admin password
$user_type = "admin"; // User type set to admin
$is_verified = 1; // Set account as verified

// Hash the admin password for secure storage
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Prepare an SQL statement for inserting the admin user into the database
$sql = "INSERT INTO users (name, email, password_hash, user_type, is_verified)
        VALUES (?, ?, ?, ?, ?)";

// Prepare the SQL statement for execution
$stmt = $mysqli->prepare($sql);

// Bind the parameters to the SQL statement
$stmt->bind_param("ssssi", $name, $email, $password_hash, $user_type, $is_verified);

// Execute the statement and check if the admin account was created successfully
if ($stmt->execute()) {
    // Output success message if the account was created
    echo "Admin account created successfully.";
} else {
    // Output error message if there was an issue creating the account
    echo "Error creating admin account: " . $stmt->error;
}

// Close the prepared statement
$stmt->close();

// Close the database connection
$mysqli->close();
?>