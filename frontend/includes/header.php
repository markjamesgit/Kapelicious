<?php
// Start the PHP session
session_start();

// Check if the user is logged in by checking if the session user_id is set
if (isset($_SESSION["user_id"])) {
    // Connect to the MySQL database
    $mysqli = require "C:/xampp/htdocs/Kapelicious/backend/config/database.php";

    // Prepare the SQL statement to fetch user info from the database based on session user_id
    $sql = "SELECT name, username, address, profile_picture FROM users WHERE id = ?";

    // Prepare the SQL statement for execution using the prepare method
    $stmt = $mysqli->prepare($sql);

    // Bind the parameter(s) to the query using the bind_param method
    $stmt->bind_param("i", $_SESSION["user_id"]);

    // Execute the query using the execute method
    $stmt->execute();

    // Get the result of the query using the get_result method
    $result = $stmt->get_result();

    // Fetch the user info from the result using the fetch_assoc method
    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapelicious | Homepage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script> <!-- Alpine.js for dropdown -->
    <style>
    [x-cloak] {
        display: none !important;
    }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header class="bg-beige shadow-md sticky top-0 z-10">
        <div class="container mx-auto p-6 flex justify-between items-center">
            <!-- Logo Section (Left) -->
            <div class="flex items-center space-x-4">
                <img src="/Kapelicious/frontend/assets/Kapelicious-logo.png" alt="Kapelicious Logo"
                    class="w-12 h-12 rounded-full">
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
                <div class="relative" x-data="{ open: false }" @keydown.escape="open = false" x-cloak>
                    <!-- Profile Picture and Name -->
                    <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                        <img src="<?= htmlspecialchars('/Kapelicious/frontend/assets/uploads/' . basename($user['profile_picture'] ?? '/frontend/assets/default-profile.jpg')) ?>"
                            alt="Profile Picture"
                            class="w-12 h-12 object-cover rounded-full border-2 border-dark-brown">
                        <p class="text-md font-semibold"><?= htmlspecialchars($user['username']) ?></p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-md py-2 z-20">
                        <a href="frontend/pages/php/change-password.php"
                            class="block px-4 py-2 text-dark-brown hover:bg-beige hover:underline">
                            Change Password
                        </a>
                        <a href="frontend/pages/php/change-profile-picture.php"
                            class="block px-4 py-2 text-dark-brown hover:bg-beige hover:underline">
                            Change Profile Picture
                        </a>
                        <a href="frontend/pages/php/change-profile-info.php"
                            class="block px-4 py-2 text-dark-brown hover:bg-beige hover:underline">
                            Change Profile Info
                        </a>
                        <a href="frontend/pages/php/logout.php"
                            class="block px-4 py-2 text-red-500 hover:bg-red-100 hover:underline">
                            Logout
                        </a>
                    </div>
                </div>
                <?php 
                // If user is not logged in
                else: ?>
                <button onclick="window.location.href='frontend/pages/php/login.php'"
                    class="bg-dark-brown py-1 px-6 rounded-full text-cream hover:border-none cursor-pointer">Log
                    in</button>
                <?php endif; ?>
            </nav>
        </div>
    </header>
</body>

</html>