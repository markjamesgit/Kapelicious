<?php
// Start session and check if the user is logged in as an admin
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Handle form submission for adding add-ons
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int) $_POST['product_id'];
    $name = mysqli_real_escape_string($mysqli, $_POST['name']);
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);
    $additional_price = (float) $_POST['additional_price'];
    $quantity = (int) $_POST['quantity'];
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);
    
    // Handle file upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Define the directory for saving uploaded files
        $uploadDir = __DIR__ . '/../../../frontend/assets/addons/';
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
            $image = '/Kapelicious/frontend/assets/addons/' . $imageName;
        } else {
            echo "Failed to upload image.";
            exit;
        }
    }

    // Insert the new add-on into the database
    $insertSql = "INSERT INTO addons (product_id, name, description, additional_price, quantity, status, image)
                  VALUES ('$product_id', '$name', '$description', '$additional_price', '$quantity', '$status', '$image')";

    if ($mysqli->query($insertSql)) {
        // Redirect after success
        header('Location: manage-add-ons.php');
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

// Fetch add-ons with search and product filter
$sql = "SELECT addons.*, products.name AS product_name 
        FROM addons 
        LEFT JOIN products ON addons.product_id = products.product_id
        WHERE (addons.name LIKE '%$search%' OR products.name LIKE '%$search%') 
        AND (addons.product_id LIKE '%$product_filter%' OR '' = '$product_filter')
        LIMIT $limit OFFSET $offset";

$addonResult = $mysqli->query($sql);

// Get total number of add-ons for pagination
$totalSql = "SELECT COUNT(*) FROM addons 
             WHERE name LIKE '%$search%' 
             AND (product_id LIKE '%$product_filter%' OR '' = '$product_filter')";
$totalResult = $mysqli->query($totalSql);
$totalAddons = $totalResult->fetch_row()[0];
$totalPages = ceil($totalAddons / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Add-ons</title>
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
            <h1 class="text-3xl font-bold text-dark-brown">Manage Add-ons</h1>
            <!-- Add Add-on Button -->
            <button id="addAddonBtn" class="bg-green-500 text-white p-3 rounded-full flex items-center space-x-2">
                <span class="material-icons">Add Add-on</span>
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="flex items-center mb-6 space-x-4">
            <input type="text" id="searchInput" class="px-4 py-2 border rounded-l-md w-full md:w-80"
                placeholder="Search add-ons..." value="<?= htmlspecialchars($search) ?>">
            <button onclick="searchAddons()" class="bg-blue-500 text-white px-6 py-2 rounded-r-md">Search</button>
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
            <button onclick="deleteSelectedAddons()" class="bg-red-500 text-white px-6 py-2 rounded-md">Delete
                Selected</button>
        </div>

        <!-- Add-on Management Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-beige rounded-t-lg">
                    <tr class="text-left">
                        <th class="px-6 py-3 text-lg font-semibold">
                            <input type="checkbox" id="selectAllAddons" onclick="toggleSelectAllAddons()" />
                        </th>
                        <th class="px-6 py-3 text-lg font-semibold">Add-on ID</th>
                        <th class="px-6 py-3 text-lg font-semibold">Product Name</th>
                        <th class="px-6 py-3 text-lg font-semibold">Name</th>
                        <th class="px-6 py-3 text-lg font-semibold">Description</th>
                        <th class="px-6 py-3 text-lg font-semibold">Additional Price</th>
                        <th class="px-6 py-3 text-lg font-semibold">Quantity</th>
                        <th class="px-6 py-3 text-lg font-semibold">Image</th>
                        <th class="px-6 py-3 text-lg font-semibold">Status</th>
                        <th class="px-6 py-3 text-lg font-semibold rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($addon = $addonResult->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="px-6 py-3">
                            <input type="checkbox" class="addon-checkbox" value="<?= $addon['addon_id'] ?>" />
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($addon["addon_id"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($addon["product_name"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($addon["name"]) ?></td>
                        <td class="px-6 py-3">
                            <?= htmlspecialchars(mb_strimwidth($addon["description"], 0, 50, "...")) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($addon["additional_price"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($addon["quantity"]) ?></td>
                        <td class="px-6 py-3">
                            <?php if (!empty($addon["image"])): ?>
                            <img src="<?= htmlspecialchars($addon["image"]) ?>" alt="Addon Image"
                                class="w-16 h-16 object-cover rounded-lg" />
                            <?php else: ?>
                            <span class="text-gray-500">No image</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($addon["status"]) ?></td>
                        <td class="px-6 py-3 space-x-2">
                            <button onclick="editAddon(<?= $addon['addon_id'] ?>)"
                                class="bg-yellow-500 text-white p-2 rounded-full">Edit</button>
                            <button onclick="deleteAddon(<?= $addon['addon_id'] ?>)"
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

    <!-- Add Add-on Form Popup -->
    <div id="addAddonPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-md w-10/12 md:w-1/2 shadow-md">
            <h2 class="text-2xl font-semibold text-dark-brown mb-4 text-center">Add New Add-on</h2>
            <form action="manage-add-ons.php" method="POST" enctype="multipart/form-data" class="space-y-4">

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
                    <label for="name" class="text-lg font-medium text-dark-brown">Name:</label>
                    <input type="text" name="name" id="name" placeholder="Enter add-on name"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required />
                </div>
                <div>
                    <label for="description" class="text-lg font-medium text-dark-brown">Description:</label>
                    <input type="text" name="description" id="description" placeholder="Enter description"
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
                    <label for="image" class="text-lg font-medium text-dark-brown">Image:</label>
                    <input type="file" name="image" id="image" accept="image/*"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>

                <div class="flex justify-between">
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-md">Save</button>
                    <button id="closeAddAddonBtn" class="bg-gray-500 text-white py-2 px-6 rounded-md">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteAddonModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-md w-10/12 md:w-1/3 shadow-md">
            <h2 class="text-2xl font-semibold text-dark-brown mb-4 text-center">Are you sure you want to delete this
                add-on?</h2>
            <div class="flex justify-between">
                <button id="confirmDeleteAddonBtn" class="bg-red-500 text-white px-6 py-2 rounded-md">Yes,
                    Delete</button>
                <button id="closeDeleteAddonModalBtn"
                    class="bg-gray-500 text-white py-2 px-6 rounded-md">Cancel</button>
            </div>
        </div>
    </div>

    <script>
    let addonIdToDelete = null; // Store the add-on ID to delete

    // Show the delete modal when the delete button is clicked
    function deleteAddon(addonId) {
        addonIdToDelete = addonId; // Store the ID of the add-on to delete
        document.getElementById('deleteAddonModal').classList.remove('hidden');
    }

    // Close the delete modal when the cancel button is clicked
    document.getElementById('closeDeleteAddonModalBtn').addEventListener('click', function() {
        document.getElementById('deleteAddonModal').classList.add('hidden');
    });

    // Confirm deletion and delete the add-on
    document.getElementById('confirmDeleteAddonBtn').addEventListener('click', function() {
        if (addonIdToDelete !== null) {
            // Now the delete action will work by calling delete-addon.php with the correct addon_id
            window.location.href =
                '/Kapelicious/frontend/admin/functions/delete-add-on.php?addon_id=' + addonIdToDelete;
        }
    });

    // Handle multiple add-on deletions
    function deleteSelectedAddons() {
        const selectedAddonIds = [];
        const checkboxes = document.querySelectorAll('.addon-checkbox:checked');

        checkboxes.forEach(checkbox => {
            selectedAddonIds.push(checkbox.value);
        });

        if (selectedAddonIds.length > 0) {
            // Ask for confirmation before deleting
            const confirmDelete = confirm('Are you sure you want to delete the selected add-ons?');
            if (confirmDelete) {
                // Pass the selected add-on IDs to the delete action
                window.location.href =
                    '/Kapelicious/frontend/admin/functions/delete-add-on.php?addon_id=' + selectedAddonIds.join(',');
            }
        } else {
            alert('Please select at least one add-on to delete.');
        }
    }

    // Show and hide the add add-on popup
    document.getElementById('addAddonBtn').addEventListener('click', function() {
        document.getElementById('addAddonPopup').classList.remove('hidden');
    });
    document.getElementById('closeAddAddonBtn').addEventListener('click', function() {
        document.getElementById('addAddonPopup').classList.add('hidden');
    });

    // Search add-ons
    function searchAddons() {
        let searchQuery = document.getElementById('searchInput').value;
        window.location.href =
            `?search=${searchQuery}&product_filter=${document.getElementById('productFilter').value}`;
    }

    // Filter by product (for add-ons)
    function filterByProduct() {
        window.location.href =
            `?product_filter=${document.getElementById('productFilter').value}&search=${document.getElementById('searchInput').value}`;
    }

    // Edit add-on
    function editAddon(addonId) {
        // Redirect to the add-on edit page with the add-on ID
        window.location.href = `/Kapelicious/frontend/admin/pages/edit-add-ons.php?addon_id=${addonId}`;
    }

    // Select all add-ons for deletion
    function toggleSelectAllAddons() {
        const selectAllCheckbox = document.getElementById('selectAllAddons');
        const checkboxes = document.querySelectorAll('.addon-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
    </script>

</body>

</html>