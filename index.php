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
                        seamless operations. Join us in making a difference!
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

                            <!-- Variant Radio Buttons -->
                            <div class="mt-4" x-data="{
                                selectedVariant: null, 
                                price: <?= $product['base_price'] ?>, 
                                selectedAddons: [], 
                                selectedFlavor: null,
                                updateTotalPrice() {
                                    let total = <?= $product['base_price'] ?>;
                                    if (this.selectedVariant) {
                                        total += parseFloat(this.selectedVariant.additional_price);
                                    }
                                    this.selectedAddons.forEach(addon => {
                                        total += parseFloat(addon.additional_price);
                                    });
                                    if (this.selectedFlavor) {
                                        total += parseFloat(this.selectedFlavor.additional_price);
                                    }
                                    this.price = total;
                                }
                            }">
                                <!-- Variant Radio Buttons -->
                                <label class="text-sm font-bold text-dark-brown">Available Variants:</label>
                                <?php if (isset($variantsByProduct[$product['product_id']])): ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php foreach ($variantsByProduct[$product['product_id']] as $variant): ?>
                                    <div class="variant-item bg-light-gray p-2 rounded-md flex items-center">
                                        <input type="radio" id="variant-<?= $variant['variant_id'] ?>"
                                            name="variant-<?= $product['product_id'] ?>" :value="{
                                                id: <?= $variant['variant_id'] ?>, 
                                                value: '<?= addslashes($variant['value']) ?>', 
                                                additional_price: <?= $variant['additional_price'] ?? 0 ?> 
                                            }" @change="selectedVariant = {
                                                id: <?= $variant['variant_id'] ?>, 
                                                value: '<?= addslashes($variant['value']) ?>', 
                                                additional_price: <?= $variant['additional_price'] ?? 0 ?>
                                            }; updateTotalPrice()" class="mr-2">
                                        <label for="variant-<?= $variant['variant_id'] ?>"
                                            class="text-sm font-semibold"><?= htmlspecialchars($variant['value']) ?></label>
                                    </div>

                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-sm text-gray-500">No available variant for this product.</p>
                                <?php endif; ?>

                                <!-- Add-ons -->
                                <label class="text-sm font-bold text-dark-brown mt-4">Available Add-ons:</label>
                                <?php if (isset($addonsByProduct[$product['product_id']])): ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php foreach ($addonsByProduct[$product['product_id']] as $addon): ?>
                                    <div class="addon-item bg-light-gray p-2 rounded-md flex items-center">
                                        <input type="checkbox" class="addon-checkbox" :value="{
                                            id: <?= $addon['addon_id'] ?>, 
                                            name: '<?= addslashes($addon['name']) ?>', 
                                            additional_price: <?= $addon['additional_price'] ?>
                                        }" @change="
                                            if (event.target.checked) {
                                                selectedAddons.push({
                                                    id: <?= $addon['addon_id'] ?>,
                                                    name: '<?= addslashes($addon['name']) ?>',
                                                    additional_price: <?= $addon['additional_price'] ?>
                                                });
                                            } else {
                                                selectedAddons = selectedAddons.filter(addon => addon.id !== <?= $addon['addon_id'] ?>);
                                            }
                                            updateTotalPrice()">
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

                                <!-- Flavors (Radio Buttons) -->
                                <label class="text-sm font-bold text-dark-brown mt-4">Available Flavors:</label>
                                <?php
            $flavorSql = "SELECT * FROM flavors WHERE product_id = {$product['product_id']} AND status = 'available'";
            $flavorResult = $mysqli->query($flavorSql);
            if ($flavorResult->num_rows > 0):
            ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php while ($flavor = $flavorResult->fetch_assoc()): ?>
                                    <div class="flavor-item bg-light-gray p-2 rounded-md flex items-center">
                                        <input type="radio" name="flavor" :value="{
                                            id: <?= $flavor['flavor_id'] ?>, 
                                            name: '<?= addslashes($flavor['name']) ?>', 
                                            additional_price: <?= $flavor['additional_price'] ?>
                                        }" @change="selectedFlavor = {
                                            id: <?= $flavor['flavor_id'] ?>, 
                                            name: '<?= addslashes($flavor['name']) ?>', 
                                            additional_price: <?= $flavor['additional_price'] ?>
                                        }; updateTotalPrice()">
                                        <img src="<?= htmlspecialchars($flavor['image']) ?>"
                                            alt="<?= htmlspecialchars($flavor['name']) ?>"
                                            class="w-10 h-10 rounded-full mr-2">
                                        <p class="text-sm font-semibold"><?= htmlspecialchars($flavor['name']) ?></p>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-sm text-gray-500">No available flavors for this product.</p>
                                <?php endif; ?>

                                <!-- Display the total price -->
                                <p class="mt-4 text-base font-semibold">Total Price: ₱<span
                                        x-text="price.toFixed(2)"></span></p>
                            </div>

                        </div>
                        <button
                            class="bg-beige text-dark-brown font-bold px-4 py-4 rounded-tl-none rounded-tr-none rounded-bl-none rounded-br-none flex-shrink-0 hover:bg-green-700">
                            Add to Cart
                        </button>
                    </div>


                    <?php endwhile; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </section>
    </div>

    <!-- Include Footer -->
    <?php require __DIR__ . '/frontend/includes/footer.php'; ?>
</body>

</html>