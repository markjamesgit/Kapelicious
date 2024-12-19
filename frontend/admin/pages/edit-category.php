<?php
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get category ID from the URL
$categoryId = $_GET['category_id'] ?? null;

if ($categoryId) {
    // Fetch category data for the given category ID
    $categorySql = "SELECT * FROM categories WHERE category_id = ?";
    $stmt = $mysqli->prepare($categorySql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();

    if (!$category) {
        die("Category not found");
    }
} else {
    die("Invalid category ID");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
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
            <h1 class="text-3xl font-bold text-dark-brown">Edit Category</h1>
        </div>

        <!-- Edit Category Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="/Kapelicious/frontend/admin/functions/update-category.php" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">

                <label for="categoryName" class="block text-sm font-semibold mb-2">Category Name:</label>
                <input type="text" name="name" id="categoryName" value="<?= htmlspecialchars($category['name']) ?>"
                    class="w-full p-3 border rounded-md mb-4" required>

                <label for="categoryDescription" class="block text-sm font-semibold mb-2">Description:</label>
                <textarea name="description" id="categoryDescription"
                    class="w-full p-3 border rounded-md mb-4"><?= htmlspecialchars($category['description']) ?></textarea>

                <label for="categoryStatus" class="block text-sm font-semibold mb-2">Status:</label>
                <select name="status" id="categoryStatus" class="w-full p-3 border rounded-md mb-4" required>
                    <option value="available" <?= $category['status'] == 'available' ? 'selected' : '' ?>>Available
                    </option>
                    <option value="unavailable" <?= $category['status'] == 'unavailable' ? 'selected' : '' ?>>
                        Unavailable
                    </option>
                </select>

                <?php if ($category['image']): ?>
                <div class="mb-4">
                    <img src="<?= htmlspecialchars($category['image']) ?>" alt="Category Image"
                        class="w-32 h-32 object-cover rounded-md">
                </div>
                <?php endif; ?>

                <label for="categoryImage" class="block text-sm font-semibold mb-2">Image:</label>
                <input type="file" name="image" id="categoryImage" class="w-full p-3 border rounded-md mb-4">

                <button type="submit" class="bg-green-500 text-white p-3 rounded-md w-full">Update Category</button>
            </form>
        </div>
    </main>
</body>

</html>