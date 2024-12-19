<?php
// Start session and check if the user is logged in as an admin
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Handle form submission for adding variants
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int) $_POST['product_id'];
    $type = mysqli_real_escape_string($mysqli, $_POST['type']);
    $value = mysqli_real_escape_string($mysqli, $_POST['value']);
    $additional_price = (float) $_POST['additional_price'];
    $quantity = (int) $_POST['quantity'];
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);
    
    // Handle file upload for image
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Define the directory for saving uploaded files
        $uploadDir = __DIR__ . '/../../../frontend/assets/variants/';
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = time() . '_' . basename($_FILES['image']['name']); // Add a unique timestamp to prevent overwriting
        $targetFilePath = $uploadDir . $imageName;

        // Ensure the directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($imageTmpName, $targetFilePath)) {
            // Save the web-accessible path to the database (relative to the web root)
            $image = '/Kapelicious/frontend/assets/variants/' . $imageName;
        } else {
            echo "Failed to upload image.";
            exit;
        }
    }

    // Insert the new variant into the database with image path
    $insertSql = "INSERT INTO variants (product_id, type, value, additional_price, quantity, status, image)
                  VALUES ('$product_id', '$type', '$value', '$additional_price', '$quantity', '$status', '$image')";
    if ($mysqli->query($insertSql)) {
        header('Location: manage-variant.php');
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
}


$search = isset($_GET['search']) ? $_GET['search'] : '';
$product_filter = isset($_GET['product_filter']) ? $_GET['product_filter'] : '';


// Pagination setup
$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch products for filtering
$productSql = "SELECT * FROM products";
$productResult = $mysqli->query($productSql);

// Fetch variants with search and product filter
$sql = "SELECT variants.*, products.name AS product_name 
        FROM variants 
        LEFT JOIN products ON variants.product_id = products.product_id
        WHERE (variants.type LIKE '%$search%' OR products.name LIKE '%$search%') 
        AND (variants.product_id LIKE '%$product_filter%' OR '' = '$product_filter')
        LIMIT $limit OFFSET $offset";

$variantResult = $mysqli->query($sql);

// Get total number of variants for pagination
$totalSql = "SELECT COUNT(*) FROM variants 
             WHERE type LIKE '%$search%' 
             AND (product_id LIKE '%$product_filter%' OR '' = '$product_filter')";
$totalResult = $mysqli->query($totalSql);
$totalVariants = $totalResult->fetch_row()[0];
$totalPages = ceil($totalVariants / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Variants</title>
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
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-dark-brown">Manage Variants</h1>
            <!-- Add Variant Button -->
            <button id="addVariantBtn" class="bg-green-500 text-white p-3 rounded-full flex items-center space-x-2">
                <span class="material-icons">Add Variant</span>
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="flex items-center mb-6 space-x-4">
            <input type="text" id="searchInput" class="px-4 py-2 border rounded-l-md w-full md:w-80"
                placeholder="Search variants..." value="<?= htmlspecialchars($search) ?>">
            <button onclick="searchVariants()" class="bg-blue-500 text-white px-6 py-2 rounded-r-md">Search</button>
            <select id="productFilter" class="p-2 border rounded-md w-full md:w-60" onchange="filterByProduct()">
                <option value="">All Products</option>
                <?php while ($product = $productResult->fetch_assoc()): ?>
                <option value="<?= $product['product_id'] ?>"
                    <?= $product['product_id'] == $product_filter ? 'selected' : '' ?>>
                    <?= htmlspecialchars($product['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>

        </div>

        <!-- Add Delete Selected Button -->
        <div class="flex justify-start mb-6">
            <button onclick="deleteSelectedVariants()" class="bg-red-500 text-white px-6 py-2 rounded-md">Delete
                Selected</button>
        </div>

        <!-- Variant Management Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-beige rounded-t-lg">
                    <tr class="text-left">
                        <th class="px-6 py-3 text-lg font-semibold">
                            <input type="checkbox" id="selectAllVariants" onclick="toggleSelectAllVariants()" />
                        </th>
                        <th class="px-6 py-3 text-lg font-semibold">Variant ID</th>
                        <th class="px-6 py-3 text-lg font-semibold">Product Name</th>
                        <th class="px-6 py-3 text-lg font-semibold">Type</th>
                        <th class="px-6 py-3 text-lg font-semibold">Value</th>
                        <th class="px-6 py-3 text-lg font-semibold">Additional Price</th>
                        <th class="px-6 py-3 text-lg font-semibold">Quantity</th>
                        <th class="px-6 py-3 text-lg font-semibold">Image</th>
                        <th class="px-6 py-3 text-lg font-semibold">Status</th>
                        <th class="px-6 py-3 text-lg font-semibold rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($variant = $variantResult->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="px-6 py-3">
                            <input type="checkbox" class="variant-checkbox" value="<?= $variant['variant_id'] ?>" />
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($variant["variant_id"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($variant["product_name"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($variant["type"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($variant["value"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($variant["additional_price"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($variant["quantity"]) ?></td>
                        <td class="px-6 py-3">
                            <?php if (!empty($variant["image"])): ?>
                            <img src="<?= htmlspecialchars($variant["image"]) ?>" alt="Variant Image"
                                class="w-16 h-16 object-cover rounded-lg" />
                            <?php else: ?>
                            <span class="text-gray-500">No image</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($variant["status"]) ?></td>
                        <td class="px-6 py-3 space-x-2">
                            <button onclick="editVariant(<?= $variant['variant_id'] ?>)"
                                class="bg-yellow-500 text-white p-2 rounded-full">Edit</button>
                            <button onclick="deleteVariant(<?= $variant['variant_id'] ?>)"
                                class="bg-red-500 text-white p-2 rounded-full">Delete</button>
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
                <a href="?page=<?= max(1, $page - 1) ?>&search=<?= htmlspecialchars($search) ?>&product_filter=<?= htmlspecialchars($product_filter) ?>"
                    class="px-4 py-2 bg-gray-300 rounded-md">Prev</a>
                <a href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= htmlspecialchars($search) ?>&product_filter=<?= htmlspecialchars($product_filter) ?>"
                    class="px-4 py-2 bg-gray-300 rounded-md">Next</a>

            </div>
        </div>
    </main>

    <!-- Add Variant Form Popup -->
    <div id="addVariantPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-md w-10/12 md:w-1/2 shadow-md">
            <h2 class="text-2xl font-semibold text-dark-brown mb-4 text-center">Add New Variant</h2>
            <form action="manage-variant.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="productSelect" class="text-lg font-medium text-dark-brown">Product:</label>
                    <select name="product_id" id="productSelect"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                        <option value="">Select a Product</option>
                        <?php 
                        $categorySqlForForm = "SELECT * FROM products";
                        $categoryResultForForm = $mysqli->query($categorySqlForForm);
                        while ($productOption = $categoryResultForForm->fetch_assoc()): ?>
                        <option value="<?= $productOption['product_id'] ?>">
                            <?= htmlspecialchars($productOption['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="type" class="text-lg font-medium text-dark-brown">Variant Type:</label>
                    <input type="text" name="type" id="type" placeholder="Enter variant type"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required />
                </div>
                <div>
                    <label for="value" class="text-lg font-medium text-dark-brown">Variant Value:</label>
                    <input type="text" name="value" id="value" placeholder="Enter variant value"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required />
                </div>
                <div>
                    <label for="additional_price" class="text-lg font-medium text-dark-brown">Additional Price:</label>
                    <input type="number" name="additional_price" id="additional_price"
                        placeholder="Enter additional price"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required />
                </div>
                <div>
                    <label for="quantity" class="text-lg font-medium text-dark-brown">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" placeholder="Enter quantity"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required />
                </div>
                <div>
                    <label for="status" class="text-lg font-medium text-dark-brown">Status:</label>
                    <select name="status" id="status"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                <div>
                    <label for="image" class="text-lg font-medium text-dark-brown">Variant Image:</label>
                    <input type="file" name="image" id="image" accept="image/*"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>

                <div class="flex justify-between">
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-md">Save</button>
                    <button id="closeAddVariantBtn"
                        class="bg-gray-500 text-white py-2 px-6 rounded-md hover:bg-gray-600 transition duration-300">Close</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div id="deleteVariantModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg w-full md:w-96">
            <h2 class="text-2xl mb-4">Are you sure you want to delete this product?</h2>
            <div class="flex justify-between">
                <button id="confirmDeleteBtn" class="bg-red-500 text-white p-2 rounded-md">Delete</button>
                <button id="closeDeleteModalBtn" class="bg-gray-500 text-white p-2 rounded-md">Cancel</button>
            </div>
        </div>
    </div>
    <script>
    let variantIdToDelete = null; // Store the variant ID to delete

    // Show the delete modal when the delete button is clicked
    function deleteVariant(variantId) {
        variantIdToDelete = variantId; // Store the ID of the variant to delete
        document.getElementById('deleteVariantModal').classList.remove('hidden');
    }

    // Close the delete modal when the cancel button is clicked
    document.getElementById('closeDeleteModalBtn').addEventListener('click', function() {
        document.getElementById('deleteVariantModal').classList.add('hidden');
    });

    // Confirm deletion and delete the variant
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (variantIdToDelete !== null) {
            // Now the delete action will work by calling delete-variant.php with the correct variant_id
            window.location.href =
                `/Kapelicious/frontend/admin/functions/delete-variant.php?variant_id=${variantIdToDelete}`;
        }
    });

    // Handle multiple variant deletions
    function deleteSelectedVariants() {
        const selectedVariantIds = [];
        const checkboxes = document.querySelectorAll('.variant-checkbox:checked');

        checkboxes.forEach(checkbox => {
            selectedVariantIds.push(checkbox.value);
        });

        if (selectedVariantIds.length > 0) {
            // Ask for confirmation before deleting
            const confirmDelete = confirm('Are you sure you want to delete the selected variants?');
            if (confirmDelete) {
                // Pass the selected variant IDs to the delete action
                window.location.href =
                    `/Kapelicious/frontend/admin/functions/delete-variant.php?variant_id=${selectedVariantIds.join(',')}`;
            }
        } else {
            alert('Please select at least one variant to delete.');
        }
    }

    // Show and hide the add variant popup
    document.getElementById('addVariantBtn').addEventListener('click', function() {
        document.getElementById('addVariantPopup').classList.remove('hidden');
    });
    document.getElementById('closeAddVariantBtn').addEventListener('click', function() {
        document.getElementById('addVariantPopup').classList.add('hidden');
    });


    function searchVariants() {
        // Get the search query and product filter values
        let searchQuery = document.getElementById('searchInput').value;
        let productFilter = document.getElementById('productFilter').value;

        // Create the new URL with query parameters
        let url = new URL(window.location.href);
        url.searchParams.set('search', searchQuery); // Set search query in the URL
        url.searchParams.set('product_filter', productFilter); // Set product filter in the URL

        // Redirect to the updated URL
        window.location.href = url.toString();
    }


    // Filter variants by product function
    function filterByProduct() {
        let productFilter = document.getElementById('productFilter').value;
        let searchQuery = document.getElementById('searchInput').value;
        // Update the URL with the selected product filter and search query
        window.location.href = `?search=${searchQuery}&product_filter=${productFilter}`;
    }


    function editVariant(variantId) {
        // Redirect to the variant edit page with the variant ID
        window.location.href = `/Kapelicious/frontend/admin/pages/edit-variant.php?variant_id=${variantId}`;
    }

    // Select all variants for deletion
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.variant-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
    </script>

</body>

</html>