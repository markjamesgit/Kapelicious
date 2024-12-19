<?php
// Include database connection
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Check if the user is logged in as an admin
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Get the category IDs from the query string
if (isset($_GET['category_id'])) {
    $categoryIds = explode(',', $_GET['category_id']); // Convert the comma-separated string into an array

    // Sanitize and prepare the IDs for deletion
    $categoryIds = array_map(function($id) use ($mysqli) {
        return (int)$id;
    }, $categoryIds);

    // Delete the selected categories
    $categoryIdsStr = implode(',', $categoryIds);
    $deleteSql = "DELETE FROM categories WHERE category_id IN ($categoryIdsStr)";

    if ($mysqli->query($deleteSql)) {
        header('Location: /Kapelicious/frontend/admin/pages/manage-category.php'); // Redirect back to the category management page
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
} else {
    echo "No categories selected for deletion.";
}
?>