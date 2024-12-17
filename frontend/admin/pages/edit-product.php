<?php
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get product ID from the URL
$productId = $_GET['product_id'] ?? null;

if ($productId) {
    // Fetch product data for the given product ID
    $productSql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $mysqli->prepare($productSql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        die("Product not found");
    }
} else {
    die("Invalid product ID");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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
            <h1 class="text-3xl font-bold text-dark-brown">Edit Product</h1>
        </div>

        <!-- Edit Product Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="/Kapelicious/frontend/admin/pages/update-product.php" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                <label for="productName" class="block text-sm font-semibold mb-2">Product Name:</label>
                <input type="text" name="name" id="productName" value="<?= htmlspecialchars($product['name']) ?>"
                    class="w-full p-3 border rounded-md mb-4" required>

                <label for="productCategory" class="block text-sm font-semibold mb-2">Category:</label>
                <select name="category_id" id="productCategory" class="w-full p-3 border rounded-md mb-4" required>
                    <!-- Populate categories as options -->
                    <?php
                    $categoryResult = $mysqli->query("SELECT * FROM categories");
                    while ($category = $categoryResult->fetch_assoc()) {
                        echo '<option value="' . $category['category_id'] . '" ' . ($category['category_id'] == $product['category_id'] ? 'selected' : '') . '>';
                        echo htmlspecialchars($category['name']);
                        echo '</option>';
                    }
                    ?>
                </select>

                <label for="productPrice" class="block text-sm font-semibold mb-2">Price:</label>
                <input type="number" name="price" id="productPrice" value="<?= htmlspecialchars($product['price']) ?>"
                    class="w-full p-3 border rounded-md mb-4" required>

                <label for="productDescription" class="block text-sm font-semibold mb-2">Description:</label>
                <textarea name="description" id="productDescription"
                    class="w-full p-3 border rounded-md mb-4"><?= htmlspecialchars($product['description']) ?></textarea>

                <label for="productQuantity" class="block text-sm font-semibold mb-2">Quantity:</label>
                <input type="number" name="quantity" id="productQuantity"
                    value="<?= htmlspecialchars($product['quantity']) ?>" class="w-full p-3 border rounded-md mb-4"
                    required>

                <label for="productStatus" class="block text-sm font-semibold mb-2">Status:</label>
                <select name="status" id="productStatus" class="w-full p-3 border rounded-md mb-4" required>
                    <option value="available" <?= $product['status'] == 'available' ? 'selected' : '' ?>>Available
                    </option>
                    <option value="unavailable" <?= $product['status'] == 'unavailable' ? 'selected' : '' ?>>Unavailable
                    </option>
                </select>

                <?php if ($product['image']): ?>
                <div class="mb-4">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image"
                        class="w-32 h-32 object-cover rounded-md">
                </div>
                <?php endif; ?>

                <label for="productImage" class="block text-sm font-semibold mb-2">Image:</label>
                <input type="file" name="image" id="productImage" class="w-full p-3 border rounded-md mb-4">

                <button type="submit" class="bg-green-500 text-white p-3 rounded-md w-full">Update Product</button>
            </form>
        </div>
    </main>
</body>

</html>