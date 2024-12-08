<?php
session_start();
$mysqli = require "C:/xampp/htdocs/Kapelicious/backend/config/database.php";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id']; // Assuming user is logged in
    $name = $_POST['name'];
    $username = $_POST['username'];
    $address = $_POST['address'];

    // Sanitize the inputs
    $name = htmlspecialchars($name);
    $username = htmlspecialchars($username);
    $address = htmlspecialchars($address);

    // Update user's information in the database
    $sql = "UPDATE users SET name = ?, username = ?, address = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sssi', $name, $username, $address, $userId);
    $stmt->execute();

    // Redirect or display a success message
    header('Location:  /Kapelicious/frontend/pages/php/change-profile-info.php?success=1');
    exit;
}