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
$base_price = $_POST['base_price']; // Use base_price instead of price
$description = $_POST['description'];
$quantity = $_POST['quantity'];
$status = $_POST['status'];
$image = $_FILES['image'] ?? null;

// Prepare the SQL query to update the product (excluding image for now)
$updateSql = "UPDATE products SET 
                name = ?, 
                category_id = ?, 
                base_price = ?, 
                description = ?, 
                quantity = ?, 
                status = ? 
              WHERE product_id = ?";
$stmt = $mysqli->prepare($updateSql);
$stmt->bind_param("siisssi", $name, $category_id, $base_price, $description, $quantity, $status, $productId);

if ($stmt->execute()) {
    // Optionally, handle image upload if it's provided
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        // Define the directory for saving uploaded files on the server (file system path)
        $uploadDir = __DIR__ . '/../../../frontend/assets/products/';
        $imageName = basename($image['name']);
        $targetFilePath = $uploadDir . $imageName;

        // Ensure the directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
            // Save the web-accessible path to the database (relative to the web root)
            $imagePath = '/Kapelicious/frontend/assets/products/' . $imageName;

            // Update image path in the database
            $imageUpdateSql = "UPDATE products SET image = ? WHERE product_id = ?";
            $stmt = $mysqli->prepare($imageUpdateSql);
            $stmt->bind_param("si", $imagePath, $productId);
            $stmt->execute();
        } else {
            echo "Failed to upload image.";
            exit;
        }
    }

    // Redirect to the manage products page after successful update
    header("Location: /Kapelicious/frontend/admin/pages/manage-product.php");
    exit;
} else {
    echo "Error updating product.";
}
?>