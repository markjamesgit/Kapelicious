<?php

// Include the database configuration for connection
$mysqli = require __DIR__ . "/../config/database.php";

// Prepare SQL statement to select user by email
$sql = sprintf("SELECT * FROM users WHERE email = '%s'", $mysqli->real_escape_string($_GET["email"]));

// Execute the query
$result = $mysqli->query($sql);

// Check if the email is available (no rows returned)
$is_available = $result->num_rows === 0;

// Set the response content type to JSON
header("Content-Type: application/json");

// Output the availability status as JSON
echo json_encode(["available" => $is_available]);
?>