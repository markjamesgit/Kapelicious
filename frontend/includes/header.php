<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION["user_id"])) {
    // Connect to the database
    $mysqli = require "C:/xampp/htdocs/Kapelicious/backend/config/database.php";

    // Fetch user info from the database based on session user_id
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapelicious | Homepage</title>

</head>

<body>
    <!-- Header Section -->
    <header class="bg-beige shadow-md sticky top-0 z-10">
        <div class="container mx-auto p-6 flex justify-between items-center">
            <!-- Logo Section (Left) -->
            <div class="flex items-center space-x-4">
                <img src="frontend/assets/Kapelicious-logo.png" alt="Kapelicious Logo" class="w-12 h-12 rounded-full">
                <h1 class="text-3xl font-bold text-dark-brown">Kapelicious</h1>
            </div>

            <!-- Navigation Menu Section (Right) -->
            <nav class="flex items-center space-x-8 text-lg font-semibold text-dark-brown">
                <a href="index.php" class="hover:underline transition duration-300">Home</a>
                <a href="frontend/pages/html/about.html" class="hover:underline transition duration-300">About</a>
                <a href="frontend/pages/html/gallery.html" class="hover:underline transition duration-300">Gallery</a>
                <a href="frontend/pages/html/menus.html" class="hover:underline transition duration-300">Menus</a>

                <?php 
                // Check if user is logged in
                if (isset($user)): ?>
                <p class="text-md font-semibold text-blue-500"><?= htmlspecialchars($user["name"]) ?></p>
                <a href="frontend/pages/php/logout.php" class="text-md text-red-500 hover:text-red-700">Log out</a>
                <?php 
                // If user is not logged in
                else:  ?>
                <button onclick="window.location.href='frontend/pages/php/login.php'"
                    class="bg-dark-brown py-1 px-6 rounded-full text-cream hover:border-none cursor-pointer">Log
                    in</button>
                <?php endif; ?>
            </nav>
        </div>
    </header>
</body>

</html>