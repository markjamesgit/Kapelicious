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

// Prepare the SQL query to update the category
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
        $imagePath = '/Kapelicious/frontend/assets/categories/' . basename($image['name']);
        move_uploaded_file($image['tmp_name'], $imagePath);

        // Update image path in the database
        $imageUpdateSql = "UPDATE categories SET image = ? WHERE category_id = ?";
        $stmt = $mysqli->prepare($imageUpdateSql);
        $stmt->bind_param("si", $imagePath, $categoryId);
        $stmt->execute();
    }

    header("Location: /Kapelicious/frontend/admin/pages/manage-category.php"); // Redirect to the manage categories page
    exit;
} else {
    echo "Error updating category.";
}
?>