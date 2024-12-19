<?php
// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php"; 

// Get category_id from URL parameter
$categoryId = $_GET['category_id'] ?? 0;

// Fetch category details
$categorySql = "SELECT * FROM categories WHERE category_id = ?";
$stmt = $mysqli->prepare($categorySql);
$stmt->bind_param('i', $categoryId);
$stmt->execute();
$categoryResult = $stmt->get_result();
$category = $categoryResult->fetch_assoc();

// Fetch products for this category
$productSql = "SELECT * FROM products WHERE category_id = ? AND status = 'available'";
$stmt = $mysqli->prepare($productSql);
$stmt->bind_param('i', $categoryId);
$stmt->execute();
$productResult = $stmt->get_result();

// Fetch variants for each product
$variantSql = "SELECT * FROM variants";
$variantResult = $mysqli->query($variantSql);
$variantsByProduct = [];
while ($variant = $variantResult->fetch_assoc()) {
    $variantsByProduct[$variant['product_id']][] = $variant;
}

// Fetch add-ons for each product
$addonSql = "SELECT * FROM addons";
$addonResult = $mysqli->query($addonSql);
$addonsByProduct = [];
while ($addon = $addonResult->fetch_assoc()) {
    $addonsByProduct[$addon['product_id']][] = $addon;
}

// Fetch flavors for each product
$flavorSql = "SELECT * FROM flavors";
$flavorResult = $mysqli->query($flavorSql);
$flavorsByProduct = [];
while ($flavor = $flavorResult->fetch_assoc()) {
    $flavorsByProduct[$flavor['product_id']][] = $flavor;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>See More - <?= htmlspecialchars($category['name']) ?></title>
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

<body class="min-h-screen flex flex-col">
    <?php include '../../includes/header.php'; ?>

    <div class="container mx-auto max-w-screen-lg py-10">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="inline-block mb-6 text-blue-600 hover:text-blue-800 text-lg">
            &larr; Back to Categories
        </a>

        <!-- Category Title -->
        <h3 class="text-4xl font-semibold text-dark-brown mb-12 text-center">
            <?= htmlspecialchars($category['name']) ?> Menu
        </h3>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php while ($product = $productResult->fetch_assoc()): ?>
            <div class="menu-item bg-white rounded-lg shadow-md hover:shadow-lg overflow-hidden transition-all">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                    class="w-full h-56 object-cover mb-4">
                <div class="p-6">
                    <!-- Product Name -->
                    <h4 class="text-lg font-semibold text-dark-brown mb-2"><?= htmlspecialchars($product['name']) ?>
                    </h4>

                    <!-- Product Description -->
                    <p class="text-sm text-gray-600 mb-4"><?= htmlspecialchars($product['description']) ?></p>

                    <!-- Base Price -->
                    <p class="text-xl font-bold text-dark-brown mb-4">$<?= number_format($product['base_price'], 2) ?>
                    </p>

                    <!-- Variant Buttons -->
                    <div class="mb-4" x-data="{ selectedVariant: null, price: <?= $product['base_price'] ?> }">
                        <label class="text-sm font-bold text-dark-brown">Available Variants:</label>
                        <?php if (isset($variantsByProduct[$product['product_id']])): ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($variantsByProduct[$product['product_id']] as $variant): ?>
                            <button class="bg-beige text-dark-brown px-4 py-2 rounded-md text-sm"
                                @click="selectedVariant = '<?= htmlspecialchars($variant['value']) ?>'; price = <?= $variant['additional_price'] + $product['base_price'] ?>;">
                                <?= htmlspecialchars($variant['value']) ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <p class="mt-2 text-lg font-semibold">Price: $<span x-text="price.toFixed(2)"></span></p>
                    </div>
                    <!-- Addons Buttons -->
                    <div class="mt-4">
                        <label class="text-sm font-bold text-dark-brown">Available Add-ons:</label>
                        <?php if (isset($addonsByProduct[$product['product_id']])): ?>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach ($addonsByProduct[$product['product_id']] as $addon): ?>
                            <div class="addon-item bg-light-gray p-2 rounded-md flex items-center">
                                <img src="<?= htmlspecialchars($addon['image']) ?>"
                                    alt="<?= htmlspecialchars($addon['name']) ?>" class="w-10 h-10 rounded-full mr-2">
                                <div>
                                    <p class="text-sm font-semibold"><?= htmlspecialchars($addon['name']) ?></p>
                                    <p class="text-xs font-bold text-dark-brown">
                                        +$<?= number_format($addon['additional_price'], 2) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Flavors Buttons -->
                    <div class="mt-4">
                        <label class="text-sm font-bold text-dark-brown">Available Flavors:</label>
                        <?php if (isset($flavorsByProduct[$product['product_id']])): ?>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach ($flavorsByProduct[$product['product_id']] as $flavor): ?>
                            <div class="flavor-item bg-light-gray p-2 rounded-md flex items-center">
                                <img src="<?= htmlspecialchars($flavor['image']) ?>"
                                    alt="<?= htmlspecialchars($flavor['name']) ?>" class="w-10 h-10 rounded-full mr-2">
                                <p class="text-sm font-semibold"><?= htmlspecialchars($flavor['name']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Buttons -->
                    <div class="flex justify-between gap-4">
                        <button class="w-full bg-dark-brown text-white py-2 rounded-md hover:bg-brown-light transition">
                            Add to Cart
                        </button>
                        <button class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600 transition">Buy
                            Now</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>

</html>