<?php
// Include database connection
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Check if the user is logged in as an admin
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Get the variant IDs from the query string
if (isset($_GET['variant_id'])) {
    $variantIds = explode(',', $_GET['variant_id']); // Convert the comma-separated string into an array

    // Sanitize and prepare the IDs for deletion
    $variantIds = array_map(function($id) use ($mysqli) {
        return (int)$id;
    }, $variantIds);

    // Delete the selected variants
    $variantIdsStr = implode(',', $variantIds);
    $deleteSql = "DELETE FROM variants WHERE variant_id IN ($variantIdsStr)";

    if ($mysqli->query($deleteSql)) {
        header('Location: /Kapelicious/frontend/admin/pages/manage-variant.php'); // Redirect back to the variant management page
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
} else {
    echo "No variants selected for deletion.";
}
?>