<?php
// Start the session to use flash messages
session_start();

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Define the number of products per page
$productsPerPage = 10;

// Get the current page from the query string, defaulting to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get the search query from the form and trim it
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calculate the OFFSET
$offset = ($page - 1) * $productsPerPage;

// Add product
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $image = $_FILES['image']['name'];
    $status = $_POST['status'];

    // Handle image upload
    $imagePath = "/Kapelicious/frontend/assets/products/" . basename($image);
    if (move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imagePath)) {
        // Image uploaded successfully, proceed to insert into the database
        $stmt = $mysqli->prepare("INSERT INTO products (name, description, category_id, price, quantity, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiiss", $product_name, $product_description, $category, $price, $quantity, $imagePath, $status);

        if ($stmt->execute()) {
            $_SESSION['message'] = "New product added successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Error uploading image.";
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();
}

// Edit product
if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $status = $_POST['status'];

    // Check if a new image is uploaded
    if ($_FILES['image']['name'] != '') {
        // New image uploaded, move the image and update the path
        $image = $_FILES['image']['name'];
        $imagePath = "/Kapelicious/frontend/assets/products/" . basename($image);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imagePath)) {
            // Prepare and execute the update query
            $stmt = $mysqli->prepare("UPDATE products SET name=?, description=?, category_id=?, price=?, quantity=?, image=?, status=? WHERE product_id=?");
            $stmt->bind_param("ssdiissi", $product_name, $product_description, $category, $price, $quantity, $imagePath, $status, $id);
        } else {
            $_SESSION['message'] = "Error uploading image.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        // No new image uploaded, keep the existing image
        $stmt = $mysqli->prepare("SELECT image FROM products WHERE product_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingImage = $result->fetch_assoc();
        $imagePath = $existingImage['image'];

        // Prepare and execute the update query without changing the image
        $stmt = $mysqli->prepare("UPDATE products SET name=?, description=?, category_id=?, price=?, quantity=?, status=? WHERE product_id=?");
        $stmt->bind_param("ssdiisi", $product_name, $product_description, $category, $price, $quantity, $status, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product updated successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();
}

// Handle Delete product
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Prepare the SQL query
    $stmt = $mysqli->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();

    // Redirect to avoid URL manipulation
    header("Location: manage-product.php");
    exit;
}

// Handle multiple deletion
if (isset($_POST['delete_multiple'])) {
    if (isset($_POST['delete_ids']) && !empty($_POST['delete_ids'])) {
        $deleteIds = $_POST['delete_ids'];

        // Prepare SQL query for deleting multiple products
        $stmt = $mysqli->prepare("DELETE FROM products WHERE product_id IN (" . implode(",", array_fill(0, count($deleteIds), "?")) . ")");
        $types = str_repeat("i", count($deleteIds));  // Prepare the parameter type string
        $stmt->bind_param($types, ...$deleteIds);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Selected products deleted successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "No products selected for deletion.";
        $_SESSION['message_type'] = "error";
    }

    // Redirect to the same page with the search query and pagination intact
    header("Location: manage-product.php?search=" . urlencode($searchQuery) . "&page=" . $page);
    exit;
}

// Fetch the products for the current page with LIMIT and OFFSET, with optional search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $productsPerPage;

// Adjust the SQL query to include the search term
$sql = "SELECT products.product_id, products.name, products.description, products.price, products.quantity, products.image, products.status, products.created_at, products.updated_at, categories.name AS category_name 
        FROM products
        LEFT JOIN categories ON products.category_id = categories.category_id 
        WHERE products.name LIKE ? LIMIT ? OFFSET ?";
$searchTerm = "%" . $searchQuery . "%"; // Prepare the search term for LIKE query
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sii", $searchTerm, $productsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get the total number of products for pagination calculation
$totalProductsSql = "SELECT COUNT(*) FROM products WHERE name LIKE ?";
$stmt = $mysqli->prepare($totalProductsSql);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$totalProductsResult = $stmt->get_result();
$totalProducts = $totalProductsResult->fetch_row()[0];
$totalPages = ceil($totalProducts / $productsPerPage);

// Fetch category options for the dropdown
$categorySql = "SELECT * FROM categories WHERE status = 'active'";
$categoryResult = $mysqli->query($categorySql);

// Fetch product data if editing
$product = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $productResult = $stmt->get_result();
    $product = $productResult->fetch_assoc();
    $stmt->close();
} else {
    $_POST['product_name'] = '';
    $_POST['product_description'] = '';
    $_POST['category'] = '';
    $_POST['image'] = '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="min-h-screen bg-light-gray flex">
    <!-- Sidebar -->
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <!-- Main Content Area -->
    <main class="flex-grow p-8">
        <h1 class="text-3xl font-bold text-dark-brown mb-6">
            <?= isset($product) ? "Edit Product" : "Add New Product" ?>
        </h1>

        <!-- Display Flash Message -->
        <?php if (isset($_SESSION['message'])): ?>
        <div
            class="mb-4 p-4 text-white <?= ($_SESSION['message_type'] == 'success') ? 'bg-green-500' : 'bg-red-500' ?> rounded-lg">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Add/Edit Product Form -->
        <form method="POST" class="bg-white p-6" enctype="multipart/form-data">
            <?php if (isset($product)): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($product['product_id']) ?>">
            <?php endif; ?>

            <input type="text" name="product_name" placeholder="Product Name" required
                class="mb-4 p-2 border rounded w-full"
                value="<?= isset($product) ? htmlspecialchars($product['name']) : '' ?>">

            <textarea name="product_description" placeholder="Product Description" required
                class="mb-4 p-2 border rounded w-full"><?= isset($product) ? htmlspecialchars($product['description']) : '' ?></textarea>

            <input type="text" name="price" placeholder="Price" required class="mb-4 p-2 border rounded w-full"
                value="<?= isset($product) ? htmlspecialchars($product['price']) : '' ?>">

            <input type="text" name="quantity" placeholder="Quantity" required class="mb-4 p-2 border rounded w-full"
                value="<?= isset($product) ? htmlspecialchars($product['quantity']) : '' ?>">


            <!-- Category Dropdown (fetched dynamically) -->
            <select name="category" class="mb-4 p-2 border rounded w-full" required>
                <option value="">Select Category</option>
                <?php while ($category = $categoryResult->fetch_assoc()): ?>
                <option value="<?= $category['category_id'] ?>"
                    <?= (isset($product) && $product['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>

            <input type="file" name="image" class="mb-4 p-2 border rounded w-full">

            <?php if (isset($product) && !empty($product['image'])): ?>
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="Current image" class="w-32 h-32 mb-4">
            <?php endif; ?>

            <select name="status" class="mb-4 p-2 border rounded w-full">
                <option value="active" <?= isset($product) && $product['status'] == 'active' ? 'selected' : '' ?>>Active
                </option>
                <option value="inactive" <?= isset($product) && $product['status'] == 'inactive' ? 'selected' : '' ?>>
                    Inactive</option>
            </select>

            <button type="submit" name="<?= isset($product) ? 'edit_product' : 'add_product' ?>"
                class="bg-dark-brown text-white rounded-full p-2">
                <?= isset($product) ? 'Update Product' : 'Add Product' ?>
            </button>
        </form>

        <!-- Products Table and Pagination -->
        <h2 class="text-2xl font-semibold text-dark-brown mt-8">Products List</h2>

        <!-- Search Form -->
        <form method="GET" action="manage-product.php" class="mb-6">
            <div class="flex items-center space-x-2">
                <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>"
                    placeholder="Search by Product Name" class="p-2 border rounded">
            </div>
        </form>


        <form method="POST" action="manage-product.php">


            <!-- Delete Selected Form -->
            <div class="overflow-x-auto p-6">
                <div class="flex justify-between mt-4">
                    <!-- Delete Selected Button -->
                    <button type="submit" name="delete_multiple" class="bg-red-500 text-white rounded-full px-6 py-2">
                        Delete Selected
                    </button>
                </div>

                <table class="min-w-full bg-white border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th><input type="checkbox" id="select_all" class="mr-2"></th>
                            <th class="py-3 px-6 text-left text-gray-700">Product ID</th> <!-- Product ID Column -->
                            <th class="py-3 px-6 text-left text-gray-700">Product Name</th>
                            <th class="py-3 px-6 text-left text-gray-700">Category</th>
                            <th class="py-3 px-6 text-left text-gray-700">Description</th>
                            <th class="py-3 px-6 text-left text-gray-700">Price</th>
                            <th class="py-3 px-6 text-left text-gray-700">Quantity</th>
                            <th class="py-3 px-6 text-left text-gray-700">Status</th>
                            <th class="py-3 px-6 text-left text-gray-700">Image</th>
                            <th class="py-3 px-6 text-left text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4"><input type="checkbox" name="delete_ids[]"
                                    value="<?= $product['product_id'] ?>"></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($product['product_id']) ?></td>
                            <!-- Display Product ID -->
                            <td class="px-6 py-4"><?= htmlspecialchars($product['name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($product['category_name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($product['description']) ?></td>
                            <td class="px-6 py-4">₱<?= htmlspecialchars($product['price']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($product['quantity']) ?></td>
                            <td class="px-6 py-4"><?= ucfirst(htmlspecialchars($product['status'])) ?></td>
                            <td class="px-6 py-4">
                                <?php if (!empty($product['image'])): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image"
                                    class="w-32 h-32 object-cover">
                                <?php else: ?>
                                No Image
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 flex space-x-2">
                                <a href="manage-product.php?id=<?= $product['product_id'] ?>"
                                    class="bg-yellow-500 text-white px-4 py-2 rounded-full">Edit</a>
                                <a href="manage-product.php?delete_id=<?= $product['product_id'] ?>"
                                    class="bg-red-500 text-white px-4 py-2 rounded-full"
                                    onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination Controls -->
                <div class="flex justify-between mt-6">
                    <div>
                        <a href="manage-product.php?page=<?= max($page - 1, 1) ?>&search=<?= urlencode($searchQuery) ?>"
                            class="px-4 py-2 bg-light-gray text-dark-brown rounded-lg">
                            <i class="fa fa-chevron-left"></i> Previous
                        </a>
                        <span class="px-4 py-2 text-dark-brown"><?= $page ?> / <?= $totalPages ?></span>
                        <a href="manage-product.php?page=<?= min($page + 1, $totalPages) ?>&search=<?= urlencode($searchQuery) ?>"
                            class="px-4 py-2 bg-light-gray text-dark-brown rounded-lg">
                            Next <i class="fa fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
        </form>
    </main>

    <script>
    function selectAllCheckboxes() {
        const checkboxes = document.querySelectorAll('.delete-checkbox');
        const selectAllCheckbox = document.getElementById('select_all');

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
    </script>
</body>

</html>