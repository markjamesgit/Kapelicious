<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    // Database connection
    $mysqli = require '../config/database.php';

    // Get the current user's ID from session
    $userId = $_SESSION['user_id'];

    // Validate the file
    $file = $_FILES['profile_picture'];
    $fileName = $file['name'];  // Original file name
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    // Allowed file extensions
    $allowedExtensions = ['image/jpeg', 'image/png', 'image/gif'];

    if (in_array($fileType, $allowedExtensions)) {
        // Keep the original file name
        $fileNewName = $fileName;

        // Destination folder
        $fileDestination = '../../frontend/assets/uploads/' . $fileNewName;

        // Move the uploaded file to the server folder
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            // Update the user's profile picture in the database
            $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('si', $fileDestination, $userId);

            if ($stmt->execute()) {
                // Redirect or show success message
                header('Location: /Kapelicious/frontend/pages/php/change-profile-picture.php?success=1');
                exit;
            } else {
                echo "Error updating profile picture.";
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
    }
} else {
    // If no file was uploaded, redirect back to the profile page
    header('Location: /Kapelicious/frontend/pages/php/change-profile-picture.php?error=1');
    exit;
}
?>