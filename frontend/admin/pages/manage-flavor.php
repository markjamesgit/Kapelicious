<?php
// Start session and check if the user is logged in as an admin
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Handle form submission for adding flavors
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
        $uploadDir = __DIR__ . '/../../../frontend/assets/flavors/';
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
            $image = '/Kapelicious/frontend/assets/flavors/' . $imageName;
        } else {
            echo "Failed to upload image.";
            exit;
        }
    }

    // Insert the new flavor into the database
    $insertSql = "INSERT INTO flavors (product_id, name, description, additional_price, quantity, status, image)
                  VALUES ('$product_id', '$name', '$description', '$additional_price', '$quantity', '$status', '$image')";

    if ($mysqli->query($insertSql)) {
        // Redirect after success
        header('Location: manage-flavor.php');
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

// Fetch flavors with search and product filter
$sql = "SELECT flavors.*, products.name AS product_name 
        FROM flavors 
        LEFT JOIN products ON flavors.product_id = products.product_id
        WHERE (flavors.name LIKE '%$search%' OR products.name LIKE '%$search%') 
        AND (flavors.product_id LIKE '%$product_filter%' OR '' = '$product_filter')
        LIMIT $limit OFFSET $offset";

$flavorResult = $mysqli->query($sql);

// Get total number of flavors for pagination
$totalSql = "SELECT COUNT(*) FROM flavors 
             WHERE name LIKE '%$search%' 
             AND (product_id LIKE '%$product_filter%' OR '' = '$product_filter')";
$totalResult = $mysqli->query($totalSql);
$totalFlavors = $totalResult->fetch_row()[0];
$totalPages = ceil($totalFlavors / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Flavors</title>
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
            <h1 class="text-3xl font-bold text-dark-brown">Manage Flavors</h1>
            <!-- Add Flavor Button -->
            <button id="addFlavorBtn" class="bg-green-500 text-white p-3 rounded-full flex items-center space-x-2">
                <span class="material-icons">Add Flavor</span>
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="flex items-center mb-6 space-x-4">
            <input type="text" id="searchInput" class="px-4 py-2 border rounded-l-md w-full md:w-80"
                placeholder="Search flavors..." value="<?= htmlspecialchars($search) ?>">
            <button onclick="searchFlavors()" class="bg-blue-500 text-white px-6 py-2 rounded-r-md">Search</button>
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
            <button onclick="deleteSelectedFlavors()" class="bg-red-500 text-white px-6 py-2 rounded-md">Delete
                Selected</button>
        </div>

        <!-- Flavor Management Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-beige rounded-t-lg">
                    <tr class="text-left">
                        <th class="px-6 py-3 text-lg font-semibold">
                            <input type="checkbox" id="selectAllFlavors" onclick="toggleSelectAllFlavors()" />
                        </th>
                        <th class="px-6 py-3 text-lg font-semibold">Flavor ID</th>
                        <th class="px-6 py-3 text-lg font-semibold">Product Name</th>
                        <th class="px-6 py-3 text-lg font-semibold">Name</th>
                        <th class="px-6 py-3 text-lg font-semibold">Description</th>
                        <th class="px-6 py-3 text-lg font-semibold">Price</th>
                        <th class="px-6 py-3 text-lg font-semibold">Quantity</th>
                        <th class="px-6 py-3 text-lg font-semibold">Image</th>
                        <th class="px-6 py-3 text-lg font-semibold">Status</th>
                        <th class="px-6 py-3 text-lg font-semibold rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($flavor = $flavorResult->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="px-6 py-3">
                            <input type="checkbox" class="flavor-checkbox" value="<?= $flavor['flavor_id'] ?>" />
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($flavor["flavor_id"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($flavor["product_name"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($flavor["name"]) ?></td>
                        <td class="px-6 py-3">
                            <?= htmlspecialchars(mb_strimwidth($flavor["description"], 0, 50, "...")) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($flavor["additional_price"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($flavor["quantity"]) ?></td>
                        <td class="px-6 py-3">
                            <?php if (!empty($flavor["image"])): ?>
                            <img src="<?= htmlspecialchars($flavor["image"]) ?>" alt="Flavor Image"
                                class="w-12 h-12 object-cover rounded-lg" />
                            <?php else: ?>
                            <span class="text-gray-500">No image</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($flavor["status"]) ?></td>
                        <td class="px-6 py-3 space-x-2">
                            <button onclick="editFlavor(<?= $flavor['flavor_id'] ?>)" class="text-yellow-500 p-2 "><i
                                    class="fas fa-edit"></i></button>
                            <button onclick="deleteFlavor(<?= $flavor['flavor_id'] ?>)" class="text-red-500 p-2"><i
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
                <a href="?page=<?= max(1, $page - 1) ?>&search=<?= htmlspecialchars($search) ?>&product_filter=<?= htmlspecialchars($product_filter) ?>"
                    class="px-4 py-2 bg-gray-300 rounded-md">Prev</a>
                <a href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= htmlspecialchars($search) ?>&product_filter=<?= htmlspecialchars($product_filter) ?>"
                    class="px-4 py-2 bg-gray-300 rounded-md">Next</a>
            </div>
        </div>
    </main>

    <div id="addFlavorPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-md w-10/12 md:w-1/3 shadow-md">
            <h2 class="text-2xl font-semibold text-dark-brown mb-4 text-center">Add a New Flavor</h2>
            <form action="manage-flavor.php" method="POST" enctype="multipart/form-data">
                <!-- Product Selection -->
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

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-dark-brown">Flavor Name</label>
                    <input type="text" name="name" id="name" class="w-full p-2 border rounded-md" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-dark-brown">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full p-2 border rounded-md"
                        required></textarea>
                </div>

                <div class="mb-4">
                    <label for="additional_price" class="block text-sm font-medium text-dark-brown">Additional
                        Price</label>
                    <input type="number" step="0.01" name="additional_price" id="additional_price"
                        class="w-full p-2 border rounded-md" required>
                </div>

                <div class="mb-4">
                    <label for="quantity" class="block text-sm font-medium text-dark-brown">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="w-full p-2 border rounded-md" required>
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-dark-brown">Status</label>
                    <select name="status" id="status" class="w-full p-2 border rounded-md" required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-dark-brown">Upload Image</label>
                    <input type="file" name="image" id="image" accept="image/*" class="w-full p-2 border rounded-md">
                </div>

                <div class="flex justify-between">
                    <button type="submit" class="bg-green-500 text-white py-2 px-6 rounded-md">Add Flavor</button>
                    <button type="button" id="closeAddFlavorBtn"
                        class="bg-gray-500 text-white py-2 px-6 rounded-md">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    <div id="deleteFlavorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-md w-10/12 md:w-1/3 shadow-md">
            <h2 class="text-2xl font-semibold text-dark-brown mb-4 text-center">Are you sure you want to delete this
                add-on?</h2>
            <div class="flex justify-between">
                <button id="confirmDeleteFlavorBtn" class="bg-red-500 text-white px-6 py-2 rounded-md">Yes,
                    Delete</button>
                <button id="closeDeleteFlavorModalBtn"
                    class="bg-gray-500 text-white py-2 px-6 rounded-md">Cancel</button>
            </div>
        </div>
    </div>

    <script>
    let flavorIdToDelete = null; // Store the flavor ID to delete

    // Show the delete modal when the delete button is clicked
    function deleteFlavor(flavorId) {
        flavorIdToDelete = flavorId; // Store the ID of the flavor to delete
        document.getElementById('deleteFlavorModal').classList.remove('hidden');
    }

    // Close the delete modal when the cancel button is clicked
    document.getElementById('closeDeleteFlavorModalBtn').addEventListener('click', function() {
        document.getElementById('deleteFlavorModal').classList.add('hidden');
    });

    // Confirm deletion and delete the flavor
    document.getElementById('confirmDeleteFlavorBtn').addEventListener('click', function() {
        if (flavorIdToDelete !== null) {
            // Now the delete action will work by calling delete-flavor.php with the correct flavor_id
            window.location.href =
                '/Kapelicious/frontend/admin/functions/delete-flavor.php?flavor_id=' + flavorIdToDelete;
        }
    });

    // Handle multiple flavor deletions
    function deleteSelectedFlavors() {
        const selectedFlavorIds = [];
        const checkboxes = document.querySelectorAll('.flavor-checkbox:checked');

        checkboxes.forEach(checkbox => {
            selectedFlavorIds.push(checkbox.value);
        });

        if (selectedFlavorIds.length > 0) {
            // Ask for confirmation before deleting
            const confirmDelete = confirm('Are you sure you want to delete the selected flavors?');
            if (confirmDelete) {
                // Pass the selected flavor IDs to the delete action
                window.location.href =
                    '/Kapelicious/frontend/admin/functions/delete-flavor.php?flavor_id=' + selectedFlavorIds.join(
                        ',');
            }
        } else {
            alert('Please select at least one flavor to delete.');
        }
    }

    // Show and hide the add flavor popup
    document.getElementById('addFlavorBtn').addEventListener('click', function() {
        document.getElementById('addFlavorPopup').classList.remove('hidden');
    });
    document.getElementById('closeAddFlavorBtn').addEventListener('click', function() {
        document.getElementById('addFlavorPopup').classList.add('hidden');
    });

    // Search flavors
    function searchFlavors() {
        let searchQuery = document.getElementById('searchInput').value;
        window.location.href =
            `?search=${searchQuery}&product_filter=${document.getElementById('productFilter').value}`;
    }

    // Filter by product (for flavors)
    function filterByProduct() {
        window.location.href =
            `?product_filter=${document.getElementById('productFilter').value}&search=${document.getElementById('searchInput').value}`;
    }

    // Edit flavor
    function editFlavor(flavorId) {
        // Redirect to the flavor edit page with the flavor ID
        window.location.href = `/Kapelicious/frontend/admin/pages/edit-flavor.php?flavor_id=${flavorId}`;
    }

    // Select all flavors for deletion
    function toggleSelectAllFlavors() {
        const selectAllCheckbox = document.getElementById('selectAllFlavors');
        const checkboxes = document.querySelectorAll('.flavor-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
    </script>

</body>


</html>