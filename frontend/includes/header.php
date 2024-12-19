<?php
// Start the PHP session
session_start();

// Connect to the MySQL database
$mysqli = require "C:/xampp/htdocs/Kapelicious/backend/config/database.php";

// Fetch user info if logged in
$user = null;
if (isset($_SESSION["user_id"])) {
    $sql = "SELECT name, username, address, profile_picture FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Fetch logo, background color, and text color from settings table
$sql = "SELECT logo, background_color, text_color FROM settings LIMIT 1"; 
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();

$logo = $settings['logo'] ?? '/Kapelicious/frontend/assets/default-profile.jpg';
$backgroundColor = $settings['background_color'] ?? '#F2EAD3';
$textColor = $settings['text_color'] ?? '#3F2305';

// Ensure the logo path is correct
if (!filter_var($logo, FILTER_VALIDATE_URL)) {
    $logo = '/Kapelicious/frontend/assets/settings/' . basename($logo);
    $logo = 'http://localhost' . $logo;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapelicious | Homepage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    [x-cloak] {
        display: none !important;
    }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header class="shadow-md sticky top-0 z-10" style="background-color: <?= htmlspecialchars($backgroundColor) ?>;">
        <div class="container mx-auto p-6 flex justify-between items-center">
            <!-- Logo Section (Left) -->
            <div class="flex items-center space-x-4">
                <img src="<?= htmlspecialchars($logo) ?>" alt="Kapelicious Logo"
                    class="w-12 h-12 rounded-full object-cover">
                <h1 class="text-3xl font-bold" style="color: <?= htmlspecialchars($textColor) ?>;">Kapelicious</h1>
            </div>

            <!-- Navigation Menu Section (Right) -->
            <nav class="flex items-center space-x-8 text-lg font-semibold"
                style="color: <?= htmlspecialchars($textColor) ?>;">
                <a href="index.php" class="hover:underline transition duration-300" style="color: inherit;">Home</a>
                <a href="frontend/pages/html/about.html" class="hover:underline transition duration-300"
                    style="color: inherit;">About</a>
                <a href="frontend/pages/html/gallery.html" class="hover:underline transition duration-300"
                    style="color: inherit;">Gallery</a>
                <a href="frontend/pages/html/menus.html" class="hover:underline transition duration-300"
                    style="color: inherit;">Menus</a>

                <!-- Cart Icon with Quantity Badge -->
                <div class="relative" x-data="{ cartQuantity: 0 }">
                    <?php if (isset($user)): ?>
                    <!-- User Logged In: Redirect to Cart Page -->
                    <button onclick="window.location.href='frontend/pages/php/cart.php'"
                        class="relative flex items-center focus:outline-none">
                        <!-- FontAwesome Cart Icon -->
                        <i class="fa-solid fa-cart-shopping text-3xl text-dark-brown"
                            style="color: <?= htmlspecialchars($textColor) ?>;"></i>

                        <!-- Badge Circle -->
                        <span x-show="cartQuantity > 0" x-text="cartQuantity"
                            class="absolute top-0 right-0 w-5 h-5 bg-red-600 text-white text-xs font-bold flex items-center justify-center rounded-full -mt-2 -mr-2">
                        </span>
                    </button>
                    <?php else: ?>
                    <!-- User Not Logged In: Show Alert Message -->
                    <button onclick="alert('You need to log in to access the cart.');"
                        class="relative flex items-center focus:outline-none">
                        <!-- FontAwesome Cart Icon -->
                        <i class="fa-solid fa-cart-shopping text-3xl text-gray-400"
                            style="color: <?= htmlspecialchars($textColor) ?>;"></i>
                    </button>
                    <?php endif; ?>
                </div>

                <?php if (isset($user)): ?>
                <div class="relative" x-data="{ open: false }" @keydown.escape="open = false" x-cloak>
                    <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                        <img src="<?= htmlspecialchars('/Kapelicious/frontend/assets/uploads/' . basename($user['profile_picture'] ?? 'default-profile.jpg')) ?>"
                            alt="Profile Picture"
                            class="w-12 h-12 object-cover rounded-full border-2 border-dark-brown">
                        <p class="text-md font-semibold"><?= htmlspecialchars($user['username']) ?></p>
                        <i class="fa-solid fa-chevron-down text-dark-brown"
                            style="color: <?= htmlspecialchars($textColor) ?>;"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-md py-2 z-20">
                        <a href="frontend/pages/php/change-password.php"
                            class="block px-4 py-2 hover:bg-beige hover:underline"
                            style="color: <?= htmlspecialchars($textColor) ?>;">Change Password</a>
                        <a href="frontend/pages/php/change-profile-picture.php"
                            class="block px-4 py-2 hover:bg-beige hover:underline"
                            style="color: <?= htmlspecialchars($textColor) ?>;">Change Profile Picture</a>
                        <a href="frontend/pages/php/change-profile-info.php"
                            class="block px-4 py-2 hover:bg-beige hover:underline"
                            style="color: <?= htmlspecialchars($textColor) ?>;">Change Profile Info</a>
                        <a href="frontend/pages/php/logout.php"
                            class="block px-4 py-2 text-red-500 hover:bg-red-100 hover:underline">Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <button onclick="window.location.href='frontend/pages/php/login.php'"
                    class="bg-dark-brown py-1 px-6 rounded-full text-cream hover:border-none cursor-pointer">Log
                    in</button>
                <?php endif; ?>
            </nav>
        </div>
    </header>
</body>

</html>