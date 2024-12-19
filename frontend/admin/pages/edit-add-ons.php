<?php
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get add-on ID from the URL
$addOnId = $_GET['addon_id'] ?? null;

if ($addOnId) {
    // Fetch add-on data for the given add-on ID
    $addOnSql = "SELECT * FROM addons WHERE addon_id = ?";
    $stmt = $mysqli->prepare($addOnSql);
    $stmt->bind_param("i", $addOnId);
    $stmt->execute();
    $addOn = $stmt->get_result()->fetch_assoc();

    if (!$addOn) {
        die("Add-On not found");
    }
} else {
    die("Invalid add-on ID");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Add-On</title>
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
            <h1 class="text-3xl font-bold text-dark-brown">Edit Add-On</h1>
        </div>

        <!-- Edit Add-On Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="/Kapelicious/frontend/admin/functions/update-add-on.php" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" name="add_on_id" value="<?= $addOn['addon_id'] ?>">

                <label for="addOnName" class="block text-sm font-semibold mb-2">Add-On Name:</label>
                <input type="text" name="name" id="addOnName" value="<?= htmlspecialchars($addOn['name']) ?>"
                    class="w-full p-3 border rounded-md mb-4" required>

                <label for="addOnDescription" class="block text-sm font-semibold mb-2">Description:</label>
                <textarea name="description" id="addOnDescription"
                    class="w-full p-3 border rounded-md mb-4"><?= htmlspecialchars($addOn['description']) ?></textarea>

                <label for="addOnPrice" class="block text-sm font-semibold mb-2">Quantity:</label>
                <input type="number" name="price" id="addOnQuantity" value="<?= htmlspecialchars($addOn['quantity']) ?>"
                    class="w-full p-3 border rounded-md mb-4" required step="0.01">

                <label for="addOnStatus" class="block text-sm font-semibold mb-2">Status:</label>
                <select name="status" id="addOnStatus" class="w-full p-3 border rounded-md mb-4" required>
                    <option value="available" <?= $addOn['status'] == 'available' ? 'selected' : '' ?>>Available
                    </option>
                    <option value="unavailable" <?= $addOn['status'] == 'unavailable' ? 'selected' : '' ?>>Unavailable
                    </option>
                </select>

                <?php if ($addOn['image']): ?>
                <div class="mb-4">
                    <img src="<?= htmlspecialchars($addOn['image']) ?>" alt="Add-On Image"
                        class="w-32 h-32 object-cover rounded-md">
                </div>
                <?php endif; ?>

                <label for="addOnImage" class="block text-sm font-semibold mb-2">Image:</label>
                <input type="file" name="image" id="addOnImage" class="w-full p-3 border rounded-md mb-4">

                <button type="submit" class="bg-green-500 text-white p-3 rounded-md w-full">Update Add-On</button>
            </form>
        </div>
    </main>
</body>

</html>