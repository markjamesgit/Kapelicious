<?php
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get product details from the POST request
$productId = $_POST['product_id'];
$name = $_POST['name'];
$category_id = $_POST['category_id'];
$price = $_POST['price'];
$description = $_POST['description'];
$quantity = $_POST['quantity'];
$status = $_POST['status'];
$image = $_FILES['image'] ?? null;

// Prepare the SQL query to update the product
$updateSql = "UPDATE products SET 
                name = ?, 
                category_id = ?, 
                price = ?, 
                description = ?, 
                quantity = ?, 
                status = ? 
              WHERE product_id = ?";
$stmt = $mysqli->prepare($updateSql);
$stmt->bind_param("siisssi", $name, $category_id, $price, $description, $quantity, $status, $productId);

if ($stmt->execute()) {
    // Optionally, handle image upload if it's provided
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $imagePath = '/Kapelicious/frontend/assets/products/' . basename($image['name']);
        move_uploaded_file($image['tmp_name'], $imagePath);

        // Update image path in the database
        $imageUpdateSql = "UPDATE products SET image = ? WHERE product_id = ?";
        $stmt = $mysqli->prepare($imageUpdateSql);
        $stmt->bind_param("si", $imagePath, $productId);
        $stmt->execute();
    }

    header("Location: /Kapelicious/frontend/admin/pages/manage-product.php"); // Redirect to the manage products page
    exit;
} else {
    echo "Error updating product.";
}