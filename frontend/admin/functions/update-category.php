<?php
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get category details from the POST request
$categoryId = $_POST['category_id'];
$name = $_POST['name'];
$description = $_POST['description'];
$status = $_POST['status'];
$image = $_FILES['image'] ?? null;

// Prepare the SQL query to update the category (excluding image for now)
$updateSql = "UPDATE categories SET 
                name = ?, 
                description = ?, 
                status = ? 
              WHERE category_id = ?";
$stmt = $mysqli->prepare($updateSql);
$stmt->bind_param("sssi", $name, $description, $status, $categoryId);

if ($stmt->execute()) {
    // Optionally, handle image upload if it's provided
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        // Define the directory for saving uploaded files on the server (file system path)
        $uploadDir = __DIR__ . '/../../../frontend/assets/categories/';
        $imageName = basename($image['name']);
        $targetFilePath = $uploadDir . $imageName;

        // Ensure the directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
            // Save the web-accessible path to the database (relative to the web root)
            $imagePath = '/Kapelicious/frontend/assets/categories/' . $imageName;

            // Update image path in the database
            $imageUpdateSql = "UPDATE categories SET image = ? WHERE category_id = ?";
            $stmt = $mysqli->prepare($imageUpdateSql);
            $stmt->bind_param("si", $imagePath, $categoryId);
            $stmt->execute();
        } else {
            echo "Failed to upload image.";
            exit;
        }
    }

    // Redirect to the manage categories page after successful update
    header("Location: /Kapelicious/frontend/admin/pages/manage-category.php");
    exit;
} else {
    echo "Error updating category.";
}
?>