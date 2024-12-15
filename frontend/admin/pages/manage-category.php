<?php
// Start the session to use flash messages
session_start();

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Define the number of categories per page
$categoriesPerPage = 10;

// Get the current page from the query string, defaulting to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get the search query from the form and trim it
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calculate the OFFSET
$offset = ($page - 1) * $categoriesPerPage;

// Handle Add or Edit category
if (isset($_POST['add_category']) || isset($_POST['edit_category'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $status = $_POST['status'] ?? 'active'; // Default to active if not set

    // Handle Image Upload
    if (isset($_POST['edit_category'])) {
        // Edit category
        $id = $_POST['id'];
        
        // Check if a new image is uploaded
        if ($_FILES['image']['name'] != '') {
            // New image uploaded, move the image and update the path
            $image = $_FILES['image']['name'];
            $imagePath = "/Kapelicious/frontend/assets/categories/" . basename($image);
            move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imagePath);
        } else {
            // No new image uploaded, keep the existing image
            $stmt = $mysqli->prepare("SELECT image FROM categories WHERE category_id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existingImage = $result->fetch_assoc();
            $imagePath = $existingImage['image'];
        }

        // Prepare and execute the update query
        $stmt = $mysqli->prepare("UPDATE categories SET name=?, description=?, image=?, status=? WHERE category_id=?");
        $stmt->bind_param("ssssi", $name, $description, $imagePath, $status, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Category updated successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }

        $stmt->close();
    } else {
        // Add category
        // Handle image upload
        $image = $_FILES['image']['name'];
        $imagePath = "/Kapelicious/frontend/assets/categories/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imagePath);

        $stmt = $mysqli->prepare("INSERT INTO categories (name, description, image, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $description, $imagePath, $status);

        if ($stmt->execute()) {
            $_SESSION['message'] = "New category added successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }

        $stmt->close();
    }

    // Redirect to avoid form resubmission
    header("Location: manage-category.php");
    exit;
}

// Handle Delete category
if (isset($_GET['delete_id'])) {
$id = $_GET['delete_id'];

// Prepare the SQL query
$stmt = $mysqli->prepare("DELETE FROM categories WHERE category_id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
$_SESSION['message'] = "Category deleted successfully.";
$_SESSION['message_type'] = "success";
} else {
$_SESSION['message'] = "Error: " . $stmt->error;
$_SESSION['message_type'] = "error";
}

$stmt->close();

// Redirect to avoid URL manipulation
header("Location: manage-category.php");
exit;
}

// Handle multiple deletion
if (isset($_POST['delete_multiple'])) {
if (isset($_POST['delete_ids']) && !empty($_POST['delete_ids'])) {
$deleteIds = $_POST['delete_ids'];

// Prepare SQL query for deleting multiple categories
$stmt = $mysqli->prepare("DELETE FROM categories WHERE category_id IN (" . implode(",", array_fill(0, count($deleteIds),
"?")) . ")");
$types = str_repeat("i", count($deleteIds)); // Prepare the parameter type string
$stmt->bind_param($types, ...$deleteIds);

if ($stmt->execute()) {
$_SESSION['message'] = "Selected categories deleted successfully.";
$_SESSION['message_type'] = "success";
} else {
$_SESSION['message'] = "Error: " . $stmt->error;
$_SESSION['message_type'] = "error";
}

$stmt->close();
} else {
$_SESSION['message'] = "No categories selected for deletion.";
$_SESSION['message_type'] = "error";
}

// Redirect to the same page with the search query and pagination intact
header("Location: manage-category.php?search=" . urlencode($searchQuery) . "&page=" . $page);
exit;
}

// Fetch the categories for the current page with LIMIT and OFFSET, with optional search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $categoriesPerPage;

// Adjust the SQL query to include the search term
$sql = "SELECT * FROM categories WHERE name LIKE ? LIMIT ? OFFSET ?";
$searchTerm = "%" . $searchQuery . "%"; // Prepare the search term for LIKE query
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sii", $searchTerm, $categoriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get the total number of categories for pagination calculation
$totalCategoriesSql = "SELECT COUNT(*) FROM categories WHERE name LIKE ?";
$stmt = $mysqli->prepare($totalCategoriesSql);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$totalCategoriesResult = $stmt->get_result();
$totalCategories = $totalCategoriesResult->fetch_row()[0];
$totalPages = ceil($totalCategories / $categoriesPerPage);

// Fetch category data if editing
$category = null;
if (isset($_GET['id'])) {
$id = $_GET['id'];
$stmt = $mysqli->prepare("SELECT * FROM categories WHERE category_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$categoryResult = $stmt->get_result();
$category = $categoryResult->fetch_assoc();
$stmt->close();
} else {
$_POST['name'] = '';
$_POST['description'] = '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
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
            <?= isset($category) ? "Edit Category" : "Add New Category" ?>
        </h1>

        <!-- Display Flash Message -->
        <?php if (isset($_SESSION['message'])): ?>
        <div
            class="mb-4 p-4 text-white <?= ($_SESSION['message_type'] == 'success') ? 'bg-green-500' : 'bg-red-500' ?> rounded-lg">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Add/Edit Category Form -->
        <form method="POST" enctype="multipart/form-data" class="bg-white p-6">
            <?php if (isset($category)): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($category['category_id']) ?>">
            <?php endif; ?>

            <input type="text" name="name" placeholder="Category Name" required class="mb-4 p-2 border rounded w-full"
                value="<?= isset($category) ? htmlspecialchars($category['name']) : '' ?>">

            <textarea name="description" placeholder="Category Description" required
                class="mb-4 p-2 border rounded w-full"><?= isset($category) ? htmlspecialchars($category['description']) : '' ?></textarea>

            <label for="status" class="block mb-2">Status</label>
            <select name="status" class="mb-4 p-2 border rounded w-full">
                <option value="active" <?= (isset($category) && $category['status'] == 'active') ? 'selected' : '' ?>>
                    Active</option>
                <option value="inactive"
                    <?= (isset($category) && $category['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>

            <label for="image" class="block mb-2">Category Image</label>
            <input type="file" name="image" class="mb-4 p-2 border rounded w-full">

            <?php if (isset($category) && !empty($category['image'])): ?>
            <img src="<?= htmlspecialchars($category['image']) ?>" alt="Category Image" class="mt-4 max-w-xs">
            <?php endif; ?>

            <button type="submit" name="<?= isset($category) ? 'edit_category' : 'add_category' ?>"
                class="bg-dark-brown text-white rounded-full p-2">
                <?= isset($category) ? 'Update Category' : 'Add Category' ?>
            </button>
        </form>

        <h2 class="text-2xl font-semibold text-dark-brown mt-8">Categories List</h2>

        <form method="GET" action="manage-category.php" class="mb-6">
            <!-- Search Form -->
            <div class="flex items-center space-x-2">
                <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>"
                    placeholder="Search by Category Name" class="p-2 border rounded">
                <button type="submit" name="search_btn" class="bg-blue-500 text-white rounded-full p-2">Search</button>
            </div>
        </form>

        <form method="POST" action="manage-category.php">
            <!-- Delete Selected Form -->
            <div class="overflow-x-auto p-6">
                <div class="flex justify-between mt-4">
                    <!-- Delete Selected Button -->
                    <button type="submit" name="delete_multiple" class="bg-red-500 text-white rounded-full px-6 py-2">
                        Delete Selected
                    </button>
                </div>

                <!-- Categories Table -->
                <table class="min-w-full bg-white rounded-lg">
                    <thead class="bg-beige rounded-t-lg">
                        <tr class="text-left">
                            <th class="px-6 py-3 text-lg font-semibold rounded-tl-lg">
                                <input type="checkbox" id="select_all" onclick="selectAllCheckboxes()">
                            </th>
                            <th class="px-6 py-3 text-lg font-semibold">ID</th>
                            <th class="px-6 py-3 text-lg font-semibold">Name</th>
                            <th class="px-6 py-3 text-lg font-semibold">Description</th>
                            <th class="px-6 py-3 text-lg font-semibold">Status</th>
                            <th class="px-6 py-3 text-lg font-semibold">Image</th>
                            <th class="px-6 py-3 text-lg font-semibold rounded-tr-lg">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="px-6 py-3">
                                <input type="checkbox" name="delete_ids[]"
                                    value="<?= htmlspecialchars($row['category_id']) ?>" class="delete-checkbox">
                            </td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row["category_id"]) ?></td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row["name"]) ?></td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row["description"]) ?></td>
                            <td class="px-6 py-3"><?= htmlspecialchars($row["status"]) ?></td>
                            <td class="px-6 py-3">
                                <?php if ($row['image']): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" alt="Category Image"
                                    class="w-16 h-16">
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <a href="?id=<?= htmlspecialchars($row['category_id']) ?>"
                                    class="bg-blue-500 text-white rounded-full p-2">Edit</a>
                                <a href="?delete_id=<?= htmlspecialchars($row['category_id']) ?>"
                                    onclick="return confirm('Are you sure?')"
                                    class="bg-red-500 text-white rounded-full p-2">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-3 text-center">No categories found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div class="flex justify-between mt-6">
                <div>
                    <a href="?page=<?= max($page - 1, 1) ?>" class="px-4 py-2 bg-light-gray text-dark-brown rounded-lg">
                        <i class="fa fa-chevron-left"></i> Previous
                    </a>
                    <span class="px-4 py-2 text-dark-brown"><?= $page ?> / <?= $totalPages ?></span>
                    <a href="?page=<?= min($page + 1, $totalPages) ?>"
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