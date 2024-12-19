<?php
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Check DB connection
if ($mysqli->connect_error) {
    echo "Database connection failed: " . $mysqli->connect_error;
    exit;
}

// Get flavor details from the POST request
$flavorId = $_POST['flavor_id']; 
$name = $_POST['name'];
$description = $_POST['description'];
$additionalPrice = $_POST['additional_price']; 
$quantity = $_POST['quantity'];
$status = $_POST['status'];
$image = $_FILES['image'] ?? null;

// Check if the flavor_id exists
$checkSql = "SELECT * FROM flavors WHERE flavor_id = ?";
$checkStmt = $mysqli->prepare($checkSql);
$checkStmt->bind_param("i", $flavorId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    echo "Flavor ID not found.";
    exit;
}

// Prepare the SQL query to update the flavor (excluding image for now)
$updateSql = "UPDATE flavors SET 
                name = ?, 
                description = ?, 
                additional_price = ?, 
                quantity = ?, 
                status = ?, 
                updated_at = CURRENT_TIMESTAMP
              WHERE flavor_id = ?";
$stmt = $mysqli->prepare($updateSql);
$stmt->bind_param("ssdisi", $name, $description, $additionalPrice, $quantity, $status, $flavorId);

// Check if the update query executes successfully
if (!$stmt->execute()) {
    echo "Error updating flavor: " . $stmt->error;
    exit;
}

// Optionally, handle image upload if it's provided
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    // Define the directory for saving uploaded files on the server (file system path)
    $uploadDir = __DIR__ . '/../../../frontend/assets/flavors/';
    $imageName = basename($image['name']);
    $targetFilePath = $uploadDir . $imageName;

    // Ensure the directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
        // Save the web-accessible path to the database (relative to the web root)
        $imagePath = '/Kapelicious/frontend/assets/flavors/' . $imageName;

        // Update image path in the database
        $imageUpdateSql = "UPDATE flavors SET image = ? WHERE flavor_id = ?";
        $stmt = $mysqli->prepare($imageUpdateSql);
        $stmt->bind_param("si", $imagePath, $flavorId);
        if (!$stmt->execute()) {
            echo "Error updating image: " . $stmt->error;
            exit;
        }
    } else {
        echo "Failed to upload image.";
        exit;
    }
}

// Redirect to the manage flavors page after successful update
header("Location: /Kapelicious/frontend/admin/pages/manage-flavor.php");
exit;
?>