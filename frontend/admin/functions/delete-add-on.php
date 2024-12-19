<?php
// Include database connection
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Check if the user is logged in as an admin
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Get the add-on IDs from the query string
if (isset($_GET['addon_id'])) {
    $addOnIds = explode(',', $_GET['addon_id']); // Convert the comma-separated string into an array

    // Sanitize and prepare the IDs for deletion
    $addOnIds = array_map(function($id) use ($mysqli) {
        return (int)$id;
    }, $addOnIds);

    // Delete the selected add-ons
    $addOnIdsStr = implode(',', $addOnIds);
    $deleteSql = "DELETE FROM addons WHERE addon_id IN ($addOnIdsStr)";

    if ($mysqli->query($deleteSql)) {
        header('Location: /Kapelicious/frontend/admin/pages/manage-add-ons.php'); // Redirect back to the add-on management page
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
} else {
    echo "No add-ons selected for deletion.";
}
?>