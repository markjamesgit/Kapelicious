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
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);
    
    // Handle file upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imagePath = '/Kapelicious/frontend/assets/categories/' . basename($imageName);
        move_uploaded_file($imageTmpName, $imagePath);
        $image = $imagePath; // Store image path
    }

    // Insert the new category into the database
    $insertSql = "INSERT INTO categories (name, description, status, image)
                  VALUES ('$name', '$description', '$status', '$image')";

    if ($mysqli->query($insertSql)) {
        // Redirect after success
        header('Location: manage-category.php');
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination setup
$limit = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch categories with search filter
$sql = "SELECT * FROM categories 
        WHERE name LIKE '%$search%' 
        LIMIT $limit OFFSET $offset";

$categoryResult = $mysqli->query($sql);

// Get total number of categories for pagination
$totalSql = "SELECT COUNT(*) FROM categories 
             WHERE name LIKE '%$search%'";
$totalResult = $mysqli->query($totalSql);
$totalCategories = $totalResult->fetch_row()[0];
$totalPages = ceil($totalCategories / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Categories</title>
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
            <h1 class="text-3xl font-bold text-dark-brown">Manage Categories</h1>
            <!-- Add Category Button -->
            <button id="addCategoryBtn" class="bg-green-500 text-white p-3 rounded-full flex items-center space-x-2">
                <span class="material-icons">Add Category</span>
            </button>
        </div>

        <!-- Search -->
        <div class="flex items-center mb-6 space-x-4">
            <input type="text" id="searchInput" class="px-4 py-2 border rounded-l-md w-full md:w-80"
                placeholder="Search categories..." value="<?= htmlspecialchars($search) ?>">
            <button onclick="searchCategories()" class="bg-blue-500 text-white px-6 py-2 rounded-r-md">Search</button>
        </div>

        <!-- Add a Delete Selected Button -->
        <div class="flex justify-between items-center mb-6">
            <button onclick="deleteSelectedCategories()" class="bg-red-500 text-white p-3 rounded-full">
                Delete Selected
            </button>
        </div>

        <!-- Category Management Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-beige rounded-t-lg">
                    <tr class="text-left">
                        <th class="px-6 py-3 text-lg font-semibold rounded-tl-lg">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                        </th>
                        <th class="px-6 py-3 text-lg font-semibold">Category ID</th>
                        <th class="px-6 py-3 text-lg font-semibold">Category Name</th>
                        <th class="px-6 py-3 text-lg font-semibold">Description</th>
                        <th class="px-6 py-3 text-lg font-semibold">Status</th>
                        <th class="px-6 py-3 text-lg font-semibold">Image</th>
                        <th class="px-6 py-3 text-lg font-semibold rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = $categoryResult->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="px-6 py-3"><input type="checkbox" class="category-checkbox"
                                value="<?= $category['category_id'] ?>"></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($category["category_id"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($category["name"]) ?></td>
                        <td class="px-6 py-3">
                            <?= htmlspecialchars(mb_strimwidth($category["description"], 0, 50, "...")) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($category["status"]) ?></td>
                        <td class="px-6 py-3">
                            <?php if ($category['image']): ?>
                            <img src="<?= htmlspecialchars($category['image']) ?>" alt="Category Image"
                                class="w-12 h-12 object-cover rounded-full">
                            <?php else: ?>
                            <span>No Image</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3 space-x-2">
                            <button onclick="editCategory(<?= $category['category_id'] ?>)"
                                class="bg-yellow-500 text-white p-2 rounded-full">Edit</button>
                            <button onclick="deleteCategory(<?= $category['category_id'] ?>)"
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
                <a href="?page=<?= max(1, $page - 1) ?>&search=<?= htmlspecialchars($search) ?>"
                    class="px-4 py-2 bg-gray-300 rounded-md">Prev</a>
                <a href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= htmlspecialchars($search) ?>"
                    class="px-4 py-2 bg-gray-300 rounded-md">Next</a>
            </div>
        </div>
    </main>

    <!-- Add Category Form Popup -->
    <div id="addCategoryPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-md w-10/12 md:w-1/2 shadow-md">
            <h2 class="text-2xl font-semibold text-dark-brown mb-4 text-center">Add New Category</h2>
            <form action="manage-category.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="categoryName" class="text-lg font-medium text-dark-brown">Category Name:</label>
                    <input type="text" name="name" id="categoryName"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                </div>

                <div>
                    <label for="categoryDescription" class="text-lg font-medium text-dark-brown">Description:</label>
                    <textarea name="description" id="categoryDescription"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        rows="3"></textarea>
                </div>

                <div>
                    <label for="categoryStatus" class="text-lg font-medium text-dark-brown">Status:</label>
                    <select name="status" id="categoryStatus"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="categoryImage" class="text-lg font-medium text-dark-brown">Image:</label>
                    <input type="file" name="image" id="categoryImage"
                        class="w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div class="flex justify-between">
                    <button type="submit"
                        class="bg-green-500 text-white py-2 px-6 rounded-md hover:bg-green-600 transition duration-300">Save</button>
                    <button type="button" id="closeAddCategoryBtn"
                        class="bg-gray-500 text-white py-2 px-6 rounded-md hover:bg-gray-600 transition duration-300">Close</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div id="deleteCategoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg w-full md:w-96">
            <h2 class="text-2xl mb-4">Are you sure you want to delete this category?</h2>
            <div class="flex justify-between">
                <button id="confirmDeleteCategoryBtn" class="bg-red-500 text-white p-2 rounded-md">Delete</button>
                <button id="closeDeleteCategoryModalBtn" class="bg-gray-500 text-white p-2 rounded-md">Cancel</button>
            </div>
        </div>
    </div>

    <script>
    let categoryIdToDelete = null; // Store the category ID to delete

    // Show the delete modal when the delete button is clicked
    function deleteCategory(categoryId) {
        categoryIdToDelete = categoryId; // Store the ID of the category to delete
        document.getElementById('deleteCategoryModal').classList.remove('hidden');
    }

    // Close the delete modal when the cancel button is clicked
    document.getElementById('closeDeleteCategoryModalBtn').addEventListener('click', function() {
        document.getElementById('deleteCategoryModal').classList.add('hidden');
    });

    // Confirm deletion and delete the category
    document.getElementById('confirmDeleteCategoryBtn').addEventListener('click', function() {
        if (categoryIdToDelete !== null) {
            // Now the delete action will work by calling delete-category.php with the correct category_id
            window.location.href =
                `/Kapelicious/frontend/admin/pages/delete-category.php?category_id=${categoryIdToDelete}`;
        }
    });

    // Handle multiple category deletions
    function deleteSelectedCategories() {
        const selectedCategoryIds = [];
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');

        checkboxes.forEach(checkbox => {
            selectedCategoryIds.push(checkbox.value);
        });

        if (selectedCategoryIds.length > 0) {
            // Ask for confirmation before deleting
            const confirmDelete = confirm('Are you sure you want to delete the selected categories?');
            if (confirmDelete) {
                // Pass the selected category IDs to the delete action
                window.location.href =
                    `/Kapelicious/frontend/admin/pages/delete-category.php?category_ids=${selectedCategoryIds.join(',')}`;
            }
        } else {
            alert('Please select at least one category to delete.');
        }
    }

    // Show and hide the add category popup
    document.getElementById('addCategoryBtn').addEventListener('click', function() {
        document.getElementById('addCategoryPopup').classList.remove('hidden');
    });
    document.getElementById('closeAddCategoryBtn').addEventListener('click', function() {
        document.getElementById('addCategoryPopup').classList.add('hidden');
    });

    // Search categories
    function searchCategories() {
        let searchQuery = document.getElementById('searchInput').value;
        window.location.href =
            `?search=${searchQuery}`;
    }

    function editCategory(categoryId) {
        // Redirect to the category edit page with the category ID
        window.location.href = `/Kapelicious/frontend/admin/pages/edit-category.php?category_id=${categoryId}`;
    }

    // Select all categories for deletion
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
    </script>

</body>

</html>