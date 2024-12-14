<?php
// Include database connection
$mysqli = require __DIR__ . '/backend/config/database.php';

// Fetch slideshow images from settings
$sql = "SELECT slideshow_images FROM settings WHERE id = 1";
$result = $mysqli->query($sql);
$settings = $result->fetch_assoc();

// Decode the JSON array of image paths
$slideshowImages = json_decode($settings['slideshow_images'] ?? '[]', true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapelicious</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.12.0/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    "light-gray": "#F5F5F5",
                    cream: "#F2EAD3",
                    beige: "#DFD7BF",
                    "dark-brown": "#3F2305",
                },
            }
        }
    }
    </script>
</head>

<body class="min-h-screen flex flex-col bg-light-gray">
    <?php
        // Include the header file
        require __DIR__ . '/frontend/includes/header.php';
    ?>

    <!-- Main Content -->
    <div class="flex-grow container mx-auto max-w-full">
        <!-- Full-Screen Slideshow -->
        <div class="hero-section max-w-full">
            <section class="hero relative h-screen w-full overflow-hidden">
                <div class="absolute inset-0">
                    <div x-data="{ slideIndex: 0 }"
                        x-init="setInterval(() => slideIndex = (slideIndex + 1) % <?= count($slideshowImages) ?>, 4000)"
                        class="relative w-full h-full transition duration-2000">
                        <?php foreach ($slideshowImages as $index => $imagePath): ?>
                        <div class="absolute inset-0 transition-opacity duration-1500 ease-in-out"
                            x-show="slideIndex === <?= $index ?>"
                            x-transition:enter="transition-opacity duration-1000 ease-in-out"
                            x-transition:leave="transition-opacity duration-1000 ease-in-out"
                            style="background-image: url('<?= htmlspecialchars($imagePath) ?>'); background-size: cover; background-position: center;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- About Us -->
    <div class="about-section py-20">
        <section class="about max-w-7xl mx-auto px-4 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center lg:gap-12">
                <img src="frontend/assets/Kapelicious-logo2.png" alt="About Us" class="w-full lg:w-1/2 ">
                <div class="mt-8 lg:mt-0 text-center lg:text-left">
                    <h3 class="text-5xl font-bold text-dark-brown mb-4">About Us</h3>
                    <p class="text-lg text-dark-brown leading-relaxed max-w-2xl mx-auto">
                        Kapelicious is more than just a platform. We are committed to ensuring timely and effective
                        rescue operations. Our mission is to simplify and manage rescues while offering tools for
                        seamless
                        operations. Join us in making a difference!
                    </p>
                </div>
            </div>
        </section>
    </div>

    <!-- Gallery -->
    <div class="gallery-section bg-dark-brown py-20">
        <section class="gallery max-w-7xl mx-auto px-4 lg:px-8">
            <h3 class="text-3xl font-semibold text-cream mb-8 text-center">Gallery</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <img src="frontend/assets/Kapelicious-logo2.png" alt="Gallery Image 1" class="rounded-lg">
                <img src="frontend/assets/Kapelicious-logo.png" alt="Gallery Image 2" class="rounded-lg ">
                <img src="frontend/assets/Kapelicious-logo2.png" alt="Gallery Image 3" class="rounded-lg">
                <img src="frontend/assets/Kapelicious-logo.png" alt="Gallery Image 4" class="rounded-lg">
                <img src="frontend/assets/Kapelicious-logo2.png" alt="Gallery Image 5" class="rounded-lg">
                <img src="frontend/assets/Kapelicious-logo.png" alt="Gallery Image 6" class="rounded-lg">
            </div>
        </section>
    </div>

    <!-- Popular Menus -->
    <div class="menus-section bg-white py-20">
        <section class="popular-menus max-w-7xl mx-auto px-4 lg:px-8">
            <h3 class="text-3xl font-semibold text-dark-brown mb-8 text-center">Popular Menus</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12">
                <div class="menu-item bg-white rounded-lg">
                    <img src="frontend/assets/Kapelicious-logo.png" alt="Coffee 1" class="w-full rounded-md mb-4">
                    <h4 class="text-xl font-bold text-dark-brown">Caramel Latte</h4>
                    <p class="text-gray-700">A perfect blend of caramel and espresso topped with creamy foam.</p>
                </div>
                <div class="menu-item bg-white rounded-lg ">
                    <img src="frontend/assets/Kapelicious-logo.png" alt="Pastry 1" class="w-full rounded-md mb-4">
                    <h4 class="text-xl font-bold text-dark-brown">Chocolate Croissant</h4>
                    <p class="text-gray-700">Flaky, buttery pastry filled with rich chocolate.</p>
                </div>
                <div class="menu-item bg-white rounded-lg ">
                    <img src="frontend/assets/Kapelicious-logo.png" alt="Coffee 2" class="w-full rounded-md mb-4">
                    <h4 class="text-xl font-bold text-dark-brown">Espresso Macchiato</h4>
                    <p class="text-gray-700">Strong espresso marked with a dollop of steamed milk.</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Services -->
    <div class="services-section bg-white py-20">
        <section class="services max-w-7xl mx-auto px-4 lg:px-8">
            <h3 class="text-3xl font-semibold text-dark-brown mb-8 text-center">Our Coffee Services</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12">
                <div class="service-item bg-white rounded-lg p-6 flex items-center space-x-6">
                    <div class="icon text-gray-700 text-4xl"><i class="fas fa-mug-hot"></i></div>
                    <div>
                        <h4 class="font-semibold text-dark-brown text-xl">Specialty Coffee</h4>
                        <p class="text-gray-700">Rich and flavorful coffee crafted with love and care.</p>
                    </div>
                </div>
                <div class="service-item bg-white rounded-lg p-6 flex items-center space-x-6">
                    <div class="icon text-gray-700 text-4xl"><i class="fas fa-bread-slice"></i></div>
                    <div>
                        <h4 class="font-semibold text-dark-brown text-xl">Fresh Baked Pastries</h4>
                        <p class="text-gray-700">Delicious treats baked fresh in-house every day.</p>
                    </div>
                </div>
                <div class="service-item bg-white rounded-lg p-6 flex items-center space-x-6">
                    <div class="icon text-gray-700 text-4xl"><i class="fas fa-coffee"></i></div>
                    <div>
                        <h4 class="font-semibold text-dark-brown text-xl">Coffee Blending</h4>
                        <p class="text-gray-700">Customize your own coffee blend with our expert roasters.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Include Footer -->
    <?php require __DIR__ . '/frontend/includes/footer.php'; ?>
</body>

</html>