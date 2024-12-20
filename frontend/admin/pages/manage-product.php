<?php 
// Start session and check if the user is logged in as an admin
session_start();

if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $name = mysqli_real_escape_string($mysqli, $_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $base_price = (float) $_POST['base_price'];  // Changed from price to base_price
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);
    $quantity = (int) $_POST['quantity'];
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);
    
    // Handle file upload
$image = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    // Define the directory for saving uploaded files on the server (file system path)
    $uploadDir = __DIR__ . '/../../../frontend/assets/products/'; // Going up 3 levels to reach frontend/assets/products/
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageName = basename($_FILES['image']['name']);
    $targetFilePath = $uploadDir . $imageName;

    // Ensure the directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($imageTmpName, $targetFilePath)) {
        // Save the web-accessible path to the database (relative to the web root)
        $image = '/Kapelicious/frontend/assets/products/' . $imageName;
    } else {
        echo "Failed to upload image.";
        exit;
    }
}

// Insert the new product into the database (handling both with and without image)
$insertSql = "INSERT INTO products (name, category_id, base_price, description, quantity, status, image)
              VALUES ('$name', '$category_id', '$base_price', '$description', '$quantity', '$status', '$image')";

if ($mysqli->query($insertSql)) {
    // Redirect after success
    header('Location: manage-product.php');
    exit;
} else {
    echo "Error: " . $mysqli->error;
}

}

// Handle search and category filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';

// Pagination setup
$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch categories for filtering
$categorySql = "SELECT * FROM categories";
$categoryResult = $mysqli->query($categorySql);

// Fetch products with search and category filter
$sql = "SELECT products.*, categories.name AS category_name 
        FROM products 
        LEFT JOIN categories ON products.category_id = categories.category_id
        WHERE products.name LIKE '%$search%' 
        AND (products.category_id LIKE '%$category_filter%' OR '' = '$category_filter')
        LIMIT $limit OFFSET $offset";

$productResult = $mysqli->query($sql);

// Get total number of products for pagination
$totalSql = "SELECT COUNT(*) FROM products 
             WHERE name LIKE '%$search%' 
             AND (category_id LIKE '%$category_filter%' OR '' = '$category_filter')";
$totalResult = $mysqli->query($totalSql);
$totalProducts = $totalResult->fetch_row()[0];
$totalPages = ceil($totalProducts / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Products</title>
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
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-dark-brown">Manage Products</h1>
            <!-- Add Product Button -->
            <button id="addProductBtn" class="bg-green-500 text-white p-3 rounded-full flex items-center space-x-2">
                <span class="material-icons">Add Product</span>
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="flex items-center mb-6 space-x-4">
            <input type="text" id="searchInput" class="px-4 py-2 border rounded-l-md w-full md:w-80"
                placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            <button onclick="searchProducts()" class="bg-blue-500 text-white px-6 py-2 rounded-r-md">Search</button>

            <select id="categoryFilter" class="p-2 border rounded-md w-full md:w-60" onchange="filterByCategory()">
                <option value="">All Categories</option>
                <?php while ($category = $categoryResult->fetch_assoc()): ?>
                <option value="<?= $category['category_id'] ?>"
                    <?= $category['category_id'] == $category_filter ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Add a Delete Selected Button -->
        <div class="flex justify-between items-center mb-6">
            <button onclick="deleteSelectedProducts()" class="bg-red-500 text-white px-6 py-2 rounded-md">
                Delete Selected
            </button>
        </div>


        <!-- Product Management Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-beige rounded-t-lg">
                    <tr class="text-left">
                        <th class="px-6 py-3 text-lg font-semibold rounded-tl-lg">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                        </th>
                        <th class="px-6 py-3 text-lg font-semibold">Product ID</th>
                        <th class="px-6 py-3 text-lg font-semibold">Product Name</th>
                        <th class="px-6 py-3 text-lg font-semibold">Description</th>
                        <th class="px-6 py-3 text-lg font-semibold">Category</th>
                        <th class="px-6 py-3 text-lg font-semibold">Price</th>
                        <th class="px-6 py-3 text-lg font-semibold">Quantity</th>
                        <th class="px-6 py-3 text-lg font-semibold">Status</th>
                        <th class="px-6 py-3 text-lg font-semibold">Image</th>
                        <th class="px-6 py-3 text-lg font-semibold rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $productResult->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="px-6 py-3"><input type="checkbox" class="product-checkbox"
                                value="<?= $product['product_id'] ?>"></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($product["product_id"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($product["name"]) ?></td>
                        <td class="px-6 py-3">
                            <?= htmlspecialchars(mb_strimwidth($product["description"], 0, 50, "...")) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($product["category_name"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($product["base_price"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($product["quantity"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($product["status"]) ?></td>
                        <td class="px-6 py-3">
                            <?php if ($product['image']): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image"
                                class="w-12 h-12 object-cover rounded-full">
                            <?php else: ?>
                            <span>No Image</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3 space-x-2">
                            <button onclick="editProduct(<?= $product['product_id'] ?>)" class="text-yellow-500 p-2 "><i
                                    class="fas fa-edit"></i></button>
                            <button onclick="deleteProduct(<?= $product['product_id'] ?>)" class="text-red-500 p-2"><i
                                    class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6">
            <div>
                <span class="text-sm text-dark-brown">Page <?= $page ?> of <?= $totalPages ?></span>
            </div>
            <div>
                <a href="?page=<?= max(1, $page - 1) ?>&search=<?= htmlspecialchars($search) ?>&category_filter=<?= htmlspecialchars($category_filter) ?>"
                    class="px-4 py-2 bg-gray-300 rounded-md">Prev</a>
                <a href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= htmlspecialchars($search) ?>&category_filter=<?= htmlspecialchars($category_filter) ?>"
                    class="px-4 py-2 bg-gray-300 rounded-md">Next</a>
            </div>
        </div>
    </main>

    <!-- Add Product Form Popup -->
    <div id="addProductPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-md w-10/12 md:w-1/2 shadow-md">
            <h2 class="text-2xl font-semibold text-dark-brown mb-4 text-center">Add New Product</h2>
            <form action="manage-product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="productName" class="text-lg font-medium text-dark-brown">Product Name:</label>
                    <input type="text" name="name" id="productName"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                </div>

                <div>
                    <label for="productCategory" class="text-lg font-medium text-dark-brown">Category:</label>
                    <select name="category_id" id="productCategory"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                        <option value="">Select a Category</option>
                        <?php 
            // Fetch categories for the Add Product Form
            $categorySqlForForm = "SELECT * FROM categories";
            $categoryResultForForm = $mysqli->query($categorySqlForForm);
            while ($category = $categoryResultForForm->fetch_assoc()): ?>
                        <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label for="productBasePrice" class="text-lg font-medium text-dark-brown">Base Price:</label>
                    <input type="number" name="base_price" id="productBasePrice" class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2
                    focus:ring-green-500" required>
                </div>

                <div>
                    <label for="productDescription" class="text-lg font-medium text-dark-brown">Description:</label>
                    <textarea name="description" id="productDescription"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        rows="3"></textarea>
                </div>

                <div>
                    <label for="productQuantity" class="text-lg font-medium text-dark-brown">Quantity:</label>
                    <input type="number" name="quantity" id="productQuantity"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                </div>

                <div>
                    <label for="productStatus" class="text-lg font-medium text-dark-brown">Status:</label>
                    <select name="status" id="productStatus"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>

                <div>
                    <label for="productImage" class="text-lg font-medium text-dark-brown">Image:</label>
                    <input type="file" name="image" id="productImage"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div class="flex justify-between">
                    <button type="submit"
                        class="bg-green-500 text-white py-2 px-6 rounded-md hover:bg-green-600 transition duration-300">Save</button>
                    <button type="button" id="closeAddProductBtn"
                        class="bg-gray-500 text-white py-2 px-6 rounded-md hover:bg-gray-600 transition duration-300">Close</button>
                </div>
            </form>

        </div>
    </div>

    <!-- Delete Product Modal -->
    <div id="deleteProductModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg w-full md:w-96">
            <h2 class="text-2xl mb-4">Are you sure you want to delete this product?</h2>
            <div class="flex justify-between">
                <button id="confirmDeleteBtn" class="bg-red-500 text-white p-2 rounded-md">Delete</button>
                <button id="closeDeleteModalBtn" class="bg-gray-500 text-white p-2 rounded-md">Cancel</button>
            </div>
        </div>
    </div>


    <script>
    let productIdToDelete = null; // Store the product ID to delete

    // Show the delete modal when the delete button is clicked
    function deleteProduct(productId) {
        productIdToDelete = productId; // Store the ID of the product to delete
        document.getElementById('deleteProductModal').classList.remove('hidden');
    }

    // Close the delete modal when the cancel button is clicked
    document.getElementById('closeDeleteModalBtn').addEventListener('click', function() {
        document.getElementById('deleteProductModal').classList.add('hidden');
    });

    // Confirm deletion and delete the product
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (productIdToDelete !== null) {
            // Now the delete action will work by calling delete-product.php with the correct product_id
            window.location.href =
                '/Kapelicious/frontend/admin/functions/delete-product.php?product_id=' + productIdToDelete;
        }
    });

    // Handle multiple product deletions
    function deleteSelectedProducts() {
        const selectedProductIds = [];
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');

        checkboxes.forEach(checkbox => {
            selectedProductIds.push(checkbox.value);
        });

        if (selectedProductIds.length > 0) {
            // Ask for confirmation before deleting
            const confirmDelete = confirm('Are you sure you want to delete the selected products?');
            if (confirmDelete) {
                // Pass the selected product IDs to the delete action
                window.location.href =
                    '/Kapelicious/frontend/admin/functions/delete-product.php?product_id=' + selectedProductIds.join(
                        ',');
            }
        } else {
            alert('Please select at least one product to delete.');
        }
    }

    // Show and hide the add product popup
    document.getElementById('addProductBtn').addEventListener('click', function() {
        document.getElementById('addProductPopup').classList.remove('hidden');
    });
    document.getElementById('closeAddProductBtn').addEventListener('click', function() {
        document.getElementById('addProductPopup').classList.add('hidden');
    });

    // Search products
    function searchProducts() {
        let searchQuery = document.getElementById('searchInput').value;
        window.location.href =
            `?search=${searchQuery}&category_filter=${document.getElementById('categoryFilter').value}`;
    }

    // Filter by category
    function filterByCategory() {
        window.location.href =
            `?category_filter=${document.getElementById('categoryFilter').value}&search=${document.getElementById('searchInput').value}`;
    }

    function editProduct(productId) {
        // Redirect to the product edit page with the product ID
        window.location.href = `/Kapelicious/frontend/admin/pages/edit-product.php?product_id=${productId}`;
    }

    // Select all products for deletion
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
    </script>
</body>

</html>