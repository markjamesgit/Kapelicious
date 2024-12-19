<?php
// Include database connection
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Check if the user is logged in as an admin
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Get the flavor IDs from the query string
if (isset($_GET['flavor_id'])) {
    $flavorIds = explode(',', $_GET['flavor_id']); // Convert the comma-separated string into an array

    // Sanitize and prepare the IDs for deletion
    $flavorIds = array_map(function($id) use ($mysqli) {
        return (int)$id;
    }, $flavorIds);

    // Delete the selected flavors
    $flavorIdsStr = implode(',', $flavorIds);
    $deleteSql = "DELETE FROM flavors WHERE flavor_id IN ($flavorIdsStr)";

    if ($mysqli->query($deleteSql)) {
        header('Location: /Kapelicious/frontend/admin/pages/manage-flavor.php'); // Redirect back to the flavor management page
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
} else {
    echo "No flavors selected for deletion.";
}
?>