<?php
// Include database connection
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Check if the user is logged in as an admin
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Get the product IDs from the query string
if (isset($_GET['product_id'])) {
    $productIds = explode(',', $_GET['product_id']); // Convert the comma-separated string into an array

    // Sanitize and prepare the IDs for deletion
    $productIds = array_map(function($id) use ($mysqli) {
        return (int)$id;
    }, $productIds);

    // Delete the selected products
    $productIdsStr = implode(',', $productIds);
    $deleteSql = "DELETE FROM products WHERE product_id IN ($productIdsStr)";

    if ($mysqli->query($deleteSql)) {
        header('Location: /Kapelicious/frontend/admin/pages/manage-product.php'); // Redirect back to the product management page
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
} else {
    echo "No products selected for deletion.";
}
?>