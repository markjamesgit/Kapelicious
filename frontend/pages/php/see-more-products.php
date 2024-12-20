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

    <div class="container mx-auto max-w-screen-2xl py-10">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="inline-block mb-6 text-blue-600 hover:text-blue-800 text-lg">
            &larr; Back to Categories
        </a>

        <!-- Category Title -->
        <h3 class="text-4xl font-semibold text-dark-brown mb-12 text-center">
            <?= htmlspecialchars($category['name']) ?> Menu
        </h3>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($product = $productResult->fetch_assoc()): ?>
            <div class="menu-item bg-white rounded-lg overflow-hidden shadow-lg flex flex-col justify-between">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                    class="w-full h-60 object-cover mb-4">
                <div class="p-4 flex-grow">
                    <h4 class="text-3xl font-bold text-dark-brown mb-2">
                        <?= htmlspecialchars($product['name']) ?></h4>
                    <p class="text-sm text-gray-700 mb-2"><?= htmlspecialchars($product['description']) ?></p>
                    <p class="text-base font-semibold text-dark-brown mb-4">
                        ₱<?= number_format($product['base_price'], 2) ?>
                    </p>

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
                        <label class="text-sm font-bold text-dark-brown">Variants:</label>
                        <?php if (isset($variantsByProduct[$product['product_id']])): ?>
                        <div class="grid grid-cols-2 gap-2 mb-4">
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

                        <label class="text-sm font-bold text-dark-brown mt-4">Add-ons:</label>
                        <?php if (isset($addonsByProduct[$product['product_id']])): ?>
                        <div class="grid grid-cols-2 gap-2 mb-4">
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
                                    alt="<?= htmlspecialchars($addon['name']) ?>" class="w-10 h-10 rounded-full mr-2">
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

                        <label class="text-sm font-bold text-dark-brown mt-4">Flavors:</label>
                        <?php if (isset($flavorsByProduct[$product['product_id']])): ?>
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <?php foreach ($flavorsByProduct[$product['product_id']] as $flavor): ?>
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
                                    alt="<?= htmlspecialchars($flavor['name']) ?>" class="w-10 h-10 rounded-full mr-2">
                                <p class="text-sm font-semibold"><?= htmlspecialchars($flavor['name']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-500">No available flavors for this product.</p>
                        <?php endif; ?>

                        <p class="mt-4 text-base font-semibold">Total Price: ₱<span x-text="price.toFixed(2)"></span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-row justify-between">
                    <button
                        class="bg-beige text-dark-brown font-bold px-4 py-4 rounded-none hover:bg-green-700 transition w-full">
                        Add to Cart
                    </button>
                    <button
                        class="bg-green-700 text-white font-bold px-4 py-4 rounded-none hover:bg-green-900 transition w-full">
                        Buy Now
                    </button>
                </div>
            </div>

            <?php endwhile; ?>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>

</html>