<?php
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get variant ID from the URL
$variantId = $_GET['variant_id'] ?? null;

if ($variantId) {
    // Fetch variant data for the given variant ID
    $variantSql = "SELECT * FROM variants WHERE variant_id = ?";
    $stmt = $mysqli->prepare($variantSql);
    $stmt->bind_param("i", $variantId);
    $stmt->execute();
    $variant = $stmt->get_result()->fetch_assoc();

    if (!$variant) {
        die("Variant not found");
    }
} else {
    die("Invalid variant ID");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Variant</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-light-gray flex">
    <!-- Sidebar -->
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <!-- Main Content Area -->
    <main class="flex-grow p-8">

        <div class="flex items-center mb-6">
            <button onclick="window.history.back()" class="text-dark-brown hover:text-dark-brown/90">
                <i class="fas fa-arrow-left text-3xl"></i>
            </button>
            <h1 class="text-3xl font-bold text-dark-brown">Edit Variant</h1>
        </div>

        <!-- Edit Variant Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="/Kapelicious/frontend/admin/functions/update-variant.php" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" name="variant_id" value="<?= $variant['variant_id'] ?>">

                <label for="variantType" class="block text-sm font-semibold mb-2">Variant Type:</label>
                <input type="text" name="type" id="variantType" value="<?= htmlspecialchars($variant['type']) ?>"
                    class="w-full p-3 border rounded-md mb-4" required>

                <label for="variantValue" class="block text-sm font-semibold mb-2">Variant Value:</label>
                <input type="text" name="value" id="variantValue" value="<?= htmlspecialchars($variant['value']) ?>"
                    class="w-full p-3 border rounded-md mb-4" required>

                <label for="additionalPrice" class="block text-sm font-semibold mb-2">Additional Price:</label>
                <input type="number" name="additional_price" id="additionalPrice"
                    value="<?= htmlspecialchars($variant['additional_price']) ?>"
                    class="w-full p-3 border rounded-md mb-4" step="0.01" min="0" required>

                <label for="variantQuantity" class="block text-sm font-semibold mb-2">Quantity:</label>
                <input type="number" name="quantity" id="variantQuantity"
                    value="<?= htmlspecialchars($variant['quantity']) ?>" class="w-full p-3 border rounded-md mb-4"
                    required>

                <label for="variantStatus" class="block text-sm font-semibold mb-2">Status:</label>
                <select name="status" id="variantStatus" class="w-full p-3 border rounded-md mb-4" required>
                    <option value="available" <?= $variant['status'] == 'available' ? 'selected' : '' ?>>Available
                    </option>
                    <option value="unavailable" <?= $variant['status'] == 'unavailable' ? 'selected' : '' ?>>Unavailable
                    </option>
                </select>

                <label for="product_id" class="block text-sm font-semibold mb-2">Associated Product:</label>
                <select name="product_id" id="product_id" class="w-full p-3 border rounded-md mb-4" required>
                    <!-- Populate products as options -->
                    <?php
                    $productResult = $mysqli->query("SELECT * FROM products");
                    while ($product = $productResult->fetch_assoc()) {
                        echo '<option value="' . $product['product_id'] . '" ' . ($product['product_id'] == $variant['product_id'] ? 'selected' : '') . '>';
                        echo htmlspecialchars($product['name']);
                        echo '</option>';
                    }
                    ?>
                </select>
                <?php if ($variant['image']): ?>
                <div class="mb-4">
                    <img src="<?= htmlspecialchars($variant['image']) ?>" alt="Product Image"
                        class="w-32 h-32 object-cover rounded-md">
                </div>
                <?php endif; ?>
                <!-- Image Upload -->
                <label for="image" class="block text-sm font-semibold mb-2">Upload Image (Optional):</label>
                <input type="file" name="image" id="image" accept="image/*" class="w-full p-3 border rounded-md mb-4">
                <button type="submit" class="bg-green-500 text-white p-3 rounded-md w-full">Update Variant</button>
            </form>
        </div>
    </main>
</body>

</html>