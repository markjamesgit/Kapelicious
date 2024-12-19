<?php
// Include database connection
$mysqli = require __DIR__ . '/backend/config/database.php';

// Fetch slideshow images from settings
$sql = "SELECT slideshow_images FROM settings WHERE id = 1";
$result = $mysqli->query($sql);
$settings = $result->fetch_assoc();

// Decode the JSON array of image paths
$slideshowImages = json_decode($settings['slideshow_images'] ?? '[]', true);

// Fetch categories
$categorySql = "SELECT * FROM categories WHERE status = 'available'";
$categoryResult = $mysqli->query($categorySql);

// Fetch products based on category
$productSql = "SELECT * FROM products WHERE status = 'available' LIMIT 6";
$productResult = $mysqli->query($productSql);

// Fetch variants for each product
$variantSql = "SELECT * FROM variants";
$variantResult = $mysqli->query($variantSql);

// Create an array to store variants by product_id
$variantsByProduct = [];
while ($variant = $variantResult->fetch_assoc()) {
    $variantsByProduct[$variant['product_id']][] = $variant;
}

// Fetch add-ons for each product
$addonSql = "SELECT * FROM addons WHERE status = 'available'";
$addonResult = $mysqli->query($addonSql);

// Create an array to store add-ons by product_id
$addonsByProduct = [];
while ($addon = $addonResult->fetch_assoc()) {
    $addonsByProduct[$addon['product_id']][] = $addon;
}
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

    <!-- Our Menus -->
    <div class="gallery-section bg-dark-brown py-20">
        <section class="gallery max-w-7xl mx-auto px-4 lg:px-8">
            <h3 class="text-4xl font-semibold text-white mb-12 text-center">Our Menus</h3>

            <!-- Categories -->
            <?php while ($category = $categoryResult->fetch_assoc()): ?>
            <div class="category-section mb-16">
                <h4 class="text-2xl font-semibold text-white mb-6"><?= htmlspecialchars($category['name']) ?></h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <?php
                    // Fetch products for this category (limit 6 items)
                    $productSql = "SELECT * FROM products WHERE category_id = {$category['category_id']} LIMIT 6";
                    $productResult = $mysqli->query($productSql);
                    while ($product = $productResult->fetch_assoc()):
                    ?>
                    <div class="menu-item bg-white rounded-lg overflow-hidden w-74 flex flex-col justify-between">
                        <img src="<?= htmlspecialchars($product['image']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-60 object-cover mb-4">
                        <div class="p-3 flex-grow">
                            <h4 class="text-3xl font-bold text-dark-brown"><?= htmlspecialchars($product['name']) ?>
                            </h4>
                            <p class="text-sm text-gray-700"><?= htmlspecialchars($product['description']) ?></p>
                            <p class="text-base font-semibold text-dark-brown mt-2">
                                ₱<?= number_format($product['base_price'], 2) ?>
                            </p>

                            <!-- Variant Buttons -->
                            <div class="mt-4" x-data="{ selectedVariant: null, price: <?= $product['base_price'] ?> }">
                                <label class="text-sm font-bold text-dark-brown">Available Variants:</label>
                                <?php if (isset($variantsByProduct[$product['product_id']])): ?>
                                <?php foreach ($variantsByProduct[$product['product_id']] as $variant): ?>
                                <button class="bg-light-gray text-dark-brown px-2 py-1 rounded-md mb-2 mr-2"
                                    @click="selectedVariant = '<?= htmlspecialchars($variant['value']) ?>'; price = <?= $variant['additional_price'] + $product['base_price'] ?>;">
                                    <?= htmlspecialchars($variant['value']) ?>
                                </button>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <p class="text-sm text-gray-500">No available variant for this product.</p>
                                <?php endif; ?>
                                <p class="mt-2 text-base font-semibold">Price: ₱<span x-text="price.toFixed(2)"></span>
                                </p>
                            </div>

                            <!-- Add-ons -->
                            <div class="mt-4">
                                <label class="text-sm font-bold text-dark-brown">Available Add-ons:</label>
                                <?php if (isset($addonsByProduct[$product['product_id']])): ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php foreach ($addonsByProduct[$product['product_id']] as $addon): ?>
                                    <div class="addon-item bg-light-gray p-2 rounded-md flex items-center">
                                        <img src="<?= htmlspecialchars($addon['image']) ?>"
                                            alt="<?= htmlspecialchars($addon['name']) ?>"
                                            class="w-10 h-10 rounded-full mr-2">
                                        <div>
                                            <p class="text-sm font-semibold"><?= htmlspecialchars($addon['name']) ?></p>
                                            <p class="text-xs font-bold text-dark-brown">
                                                +₱<?= number_format($addon['additional_price'], 2) ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-sm text-gray-500">No available add-ons for this product.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Flavors -->
                            <div class="mt-4">
                                <label class="text-sm font-bold text-dark-brown">Available Flavors:</label>
                                <?php
                                    // Fetch the available flavors for this product
                                    $flavorSql = "SELECT * FROM flavors WHERE product_id = {$product['product_id']} AND status = 'available'";
                                    $flavorResult = $mysqli->query($flavorSql);
                                    if ($flavorResult->num_rows > 0):
                                    ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php while ($flavor = $flavorResult->fetch_assoc()): ?>
                                    <div class="flavor-item bg-light-gray p-2 rounded-md flex items-center">
                                        <!-- Flavor Image -->
                                        <img src="<?= htmlspecialchars($flavor['image']) ?>"
                                            alt="<?= htmlspecialchars($flavor['name']) ?>"
                                            class="w-10 h-10 rounded-full mr-2">
                                        <!-- Flavor Name -->
                                        <p class="text-sm font-semibold"><?= htmlspecialchars($flavor['name']) ?></p>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-sm text-gray-500">No available flavors for this product.</p>
                                <?php endif; ?>
                            </div>

                        </div>
                        <button
                            class=" bg-beige text-dark-brown font-bold px-4 py-4 rounded-tl-none rounded-tr-none rounded-bl-none rounded-br-none flex-shrink-0 hover:bg-green-700">
                            Add to Cart
                        </button>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Check if there are more than 6 products and display the "See More" link -->
                <?php
                // Check if the total number of products in the category is more than 6
                $totalProductSql = "SELECT COUNT(*) AS total_products FROM products WHERE category_id = {$category['category_id']}";
                $totalProductResult = $mysqli->query($totalProductSql);
                $totalProduct = $totalProductResult->fetch_assoc();
                if ($totalProduct['total_products'] > 6):
                ?>
                <!-- See More Link -->
                <a href="/Kapelicious/frontend/pages/php/see-more-products.php?category_id=<?= $category['category_id'] ?>"
                    class="text-blue-500 mt-4 inline-block">See More</a>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
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