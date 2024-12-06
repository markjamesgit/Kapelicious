<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <footer class="bg-dark-brown mt-auto">
        <div class="container mx-auto p-6">
            <!-- Footer Sections -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- About Section -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-cream">About Kapelicious</h3>
                    <p class="text-sm text-beige">
                        Kapelicious offers the finest coffee experience with a blend of delicious pastries and warm
                        ambiance. Visit us for your perfect coffee moments!
                    </p>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-cream">Quick Links</h3>
                    <ul class="text-sm text-beige space-y-2">
                        <li><a href="index.php" class="hover:underline">Home</a></li>
                        <li><a href="frontend/pages/html/about.html" class="hover:underline">About</a></li>
                        <li><a href="frontend/pages/html/gallery.html" class="hover:underline">Gallery</a></li>
                        <li><a href="frontend/pages/html/menus.html" class="hover:underline">Menus</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-cream">Contact Us</h3>
                    <ul class="text-sm text-beige space-y-2">
                        <li>Email: <a href="mailto:contact@kapelicious.com"
                                class="hover:underline">kapeliciouscoffeeshop@gmail.com</a></li>
                        <li>Phone: 0927-866-3181</li>
                        <li>232 Baliwag, City, Philippines</li>
                    </ul>
                </div>
            </div>

            <!-- Social Media Links -->
            <div class="mt-6 text-center">
                <h3 class="text-lg font-bold text-cream">Follow Us</h3>
                <div class="flex justify-center text-beige space-x-4 mt-2">
                    <a href="#" class="hover:text-light-gray"><i class="fab fa-facebook"></i> Facebook</a>
                    <a href="#" class="hover:text-light-gray"><i class="fab fa-twitter"></i> Twitter</a>
                    <a href="#" class="hover:text-light-gray"><i class="fab fa-instagram"></i> Instagram</a>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="text-center mt-6 border-t border-cream pt-4 text-sm text-beige">
                &copy; <?= date('Y'); ?> Kapelicious. All rights reserved.
            </div>
        </div>
    </footer>

</body>

</html>