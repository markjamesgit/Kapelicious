<?php

// Connect to the MySQL database
$mysqli = require "C:/xampp/htdocs/Kapelicious/backend/config/database.php";

// Fetch background color from settings table
$sql = "SELECT background_color, text_color FROM settings LIMIT 1"; 
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$backgroundColor = $settings['background_color'] ?? '#F2EAD3'; 
$textColor = $settings['text_color'] ?? '#3F2305';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <footer class="bg-dark-brown mt-auto" style="background-color: <?= htmlspecialchars($backgroundColor) ?>;">
        <div class="container mx-auto p-6">
            <!-- Footer Sections -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- About Section -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-cream">About Kapelicious</h3>
                    <p class="text-sm text-beige" style="color: <?= htmlspecialchars($textColor) ?>;">
                        Kapelicious offers the finest coffee experience with a blend of delicious pastries and warm
                        ambiance. Visit us for your perfect coffee moments!
                    </p>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-cream">Quick Links</h3>
                    <ul class="text-sm text-beige space-y-2">
                        <li><a href="index.php" class="hover:underline"
                                style="color: <?= htmlspecialchars($textColor) ?>;">Home</a></li>
                        <li><a href="frontend/pages/html/about.html" class="hover:underline"
                                style="color: <?= htmlspecialchars($textColor) ?>;">About</a></li>
                        <li><a href="frontend/pages/html/gallery.html" class="hover:underline"
                                style="color: <?= htmlspecialchars($textColor) ?>;">Gallery</a></li>
                        <li><a href="frontend/pages/html/menus.html" class="hover:underline"
                                style="color: <?= htmlspecialchars($textColor) ?>;">Menus</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-cream">Contact Us</h3>
                    <ul class="text-sm text-beige space-y-2">
                        <li style="color: <?= htmlspecialchars($textColor) ?>;">Email: <a
                                href="mailto:contact@kapelicious.com" class="hover:underline"
                                style="color: <?= htmlspecialchars($textColor) ?>;">kapeliciouscoffeeshop@gmail.com</a>
                        </li>
                        <li style="color: <?= htmlspecialchars($textColor) ?>;">Phone: 0927-866-3181</li>
                        <li style="color: <?= htmlspecialchars($textColor) ?>;">232 Baliwag, City, Philippines</li>
                    </ul>
                </div>
            </div>

            <!-- Social Media Links -->
            <div class="mt-6 text-center">
                <h3 class="text-lg font-bold text-cream">Follow Us</h3>
                <div class="flex justify-center text-beige space-x-4 mt-2">
                    <a href="#" class="hover:text-light-gray" style="color: <?= htmlspecialchars($textColor) ?>;"><i
                            class="fab fa-facebook"></i> Facebook</a>
                    <a href="#" class="hover:text-light-gray" style="color: <?= htmlspecialchars($textColor) ?>;"><i
                            class="fab fa-twitter"></i> Twitter</a>
                    <a href="#" class="hover:text-light-gray" style="color: <?= htmlspecialchars($textColor) ?>;"><i
                            class="fab fa-instagram"></i> Instagram</a>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="text-center mt-6 border-t border-cream pt-4 text-sm text-beige"
                style="color: <?= htmlspecialchars($textColor) ?>;">
                &copy; <?= date('Y'); ?> Kapelicious. All rights reserved.
            </div>
        </div>
    </footer>

</body>

</html>