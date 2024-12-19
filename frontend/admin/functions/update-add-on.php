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

// Get add-on details from the POST request
$addOnId = $_POST['add_on_id']; 
$name = $_POST['name'];
$description = $_POST['description'];
$additionalPrice = $_POST['price']; 
$status = $_POST['status'];
$image = $_FILES['image'] ?? null;


// Check if the addon_id exists
$checkSql = "SELECT * FROM addons WHERE addon_id = ?";
$checkStmt = $mysqli->prepare($checkSql);
$checkStmt->bind_param("i", $addOnId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    echo "Addon ID not found.";
    exit;
}

// Prepare the SQL query to update the add-on (excluding image for now)
$updateSql = "UPDATE addons SET 
                name = ?, 
                description = ?, 
                additional_price = ?, 
                status = ?, 
                updated_at = CURRENT_TIMESTAMP
              WHERE addon_id = ?";
$stmt = $mysqli->prepare($updateSql);
$stmt->bind_param("ssdsi", $name, $description, $additionalPrice, $status, $addOnId);

// Check if the update query executes successfully
if (!$stmt->execute()) {
    echo "Error updating add-on: " . $stmt->error;
    exit;
}

// Optionally, handle image upload if it's provided
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    // Define the directory for saving uploaded files on the server (file system path)
    $uploadDir = __DIR__ . '/../../../frontend/assets/addons/';
    $imageName = basename($image['name']);
    $targetFilePath = $uploadDir . $imageName;

    // Ensure the directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
        // Save the web-accessible path to the database (relative to the web root)
        $imagePath = '/Kapelicious/frontend/assets/addons/' . $imageName;

        // Update image path in the database
        $imageUpdateSql = "UPDATE addons SET image = ? WHERE addon_id = ?";
        $stmt = $mysqli->prepare($imageUpdateSql);
        $stmt->bind_param("si", $imagePath, $addOnId);
        if (!$stmt->execute()) {
            echo "Error updating image: " . $stmt->error;
            exit;
        }
    } else {
        echo "Failed to upload image.";
        exit;
    }
}

// Redirect to the manage add-ons page after successful update
header("Location: /Kapelicious/frontend/admin/pages/manage-add-ons.php");
exit;
?>