<?php
// Include the database connection if needed
$mysqli = require __DIR__ . "/../../../backend/config/database.php";

// Fetch categories to display in the dropdown
$categories_result = $mysqli->query("SELECT id, name FROM categories");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get product details from the form
    $name = $_POST['product_name'];
    $description = $_POST['product_description'];
    $price = $_POST['product_price'];
    $category_id = $_POST['category_id'];

    // Insert into the products table
    $stmt = $mysqli->prepare("INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $name, $description, $price, $category_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to product management page after adding the product
    header("Location: manage-products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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

<body class="min-h-screen bg-light-gray flex">
    <!-- Sidebar -->
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <!-- Main Content Area -->
    <main class="flex-grow p-8">
        <div class="bg-white p-6 ">
            <h1 class="text-3xl font-bold text-dark-brown mb-6">Add New Product</h1>

            <!-- Product Form -->
            <form action="add-product.php" method="POST">
                <div class="mb-4">
                    <label for="product_name" class="block text-lg font-semibold text-dark-brown">Product Name:</label>
                    <input type="text" id="product_name" name="product_name" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-beige">
                </div>

                <div class="mb-4">
                    <label for="product_description"
                        class="block text-lg font-semibold text-dark-brown">Description:</label>
                    <textarea id="product_description" name="product_description"
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-beige"></textarea>
                </div>

                <div class="mb-4">
                    <label for="product_price" class="block text-lg font-semibold text-dark-brown">Price:</label>
                    <input type="number" id="product_price" name="product_price" step="0.01" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-beige">
                </div>

                <div class="mb-4">
                    <label for="category_id" class="block text-lg font-semibold text-dark-brown">Category:</label>
                    <select id="category_id" name="category_id" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-beige">
                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="bg-dark-brown text-white px-6 py-2 rounded-full hover:bg-beige transition duration-200">Add
                        Product</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Scripts -->
    <script>
    // Sidebar dropdown toggle (if any dropdown in sidebar)
    document.querySelector('.relative button').addEventListener('click', function() {
        const dropdown = this.nextElementSibling;
        dropdown.classList.toggle('hidden');
    });
    </script>
</body>

</html>