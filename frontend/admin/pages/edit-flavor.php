<?php
session_start();
if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get flavor ID from the URL
$flavorId = $_GET['flavor_id'] ?? null;

if ($flavorId) {
    // Fetch flavor data for the given flavor ID
    $flavorSql = "SELECT * FROM flavors WHERE flavor_id = ?";
    $stmt = $mysqli->prepare($flavorSql);
    $stmt->bind_param("i", $flavorId);
    $stmt->execute();
    $flavor = $stmt->get_result()->fetch_assoc();

    if (!$flavor) {
        die("Flavor not found");
    }
} else {
    die("Invalid flavor ID");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Flavor</title>
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
            <h1 class="text-3xl font-bold text-dark-brown">Edit Flavor</h1>
        </div>

        <!-- Edit Flavor Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <form action="/Kapelicious/frontend/admin/functions/update-flavor.php" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" name="flavor_id" value="<?= $flavor['flavor_id'] ?>">

                <!-- Flavor Name -->
                <label for="flavorName" class="block text-sm font-semibold mb-2">Flavor Name:</label>
                <input type="text" name="name" id="flavorName" value="<?= htmlspecialchars($flavor['name']) ?>"
                    class="w-full p-3 border rounded-md mb-4" required>

                <!-- Description -->
                <label for="flavorDescription" class="block text-sm font-semibold mb-2">Description:</label>
                <textarea name="description" id="flavorDescription" class="w-full p-3 border rounded-md mb-4"
                    required><?= htmlspecialchars($flavor['description']) ?></textarea>

                <!-- Additional Price -->
                <label for="additionalPrice" class="block text-sm font-semibold mb-2">Additional Price:</label>
                <input type="number" name="additional_price" id="additionalPrice"
                    value="<?= htmlspecialchars($flavor['additional_price']) ?>"
                    class="w-full p-3 border rounded-md mb-4" step="0.01" min="0" required>

                <!-- Quantity -->
                <label for="flavorQuantity" class="block text-sm font-semibold mb-2">Quantity:</label>
                <input type="number" name="quantity" id="flavorQuantity"
                    value="<?= htmlspecialchars($flavor['quantity']) ?>" class="w-full p-3 border rounded-md mb-4"
                    required>

                <!-- Status -->
                <label for="flavorStatus" class="block text-sm font-semibold mb-2">Status:</label>
                <select name="status" id="flavorStatus" class="w-full p-3 border rounded-md mb-4" required>
                    <option value="available" <?= $flavor['status'] == 'available' ? 'selected' : '' ?>>Available
                    </option>
                    <option value="'unavailable" <?= $flavor['status'] == 'unavailable' ? 'selected' : '' ?>>Unavailable
                    </option>
                </select>

                <!-- Associated Product -->
                <label for="product_id" class="block text-sm font-semibold mb-2">Associated Product:</label>
                <select name="product_id" id="product_id" class="w-full p-3 border rounded-md mb-4" required>
                    <!-- Populate products as options -->
                    <?php
                    $productResult = $mysqli->query("SELECT * FROM products");
                    while ($product = $productResult->fetch_assoc()) {
                        echo '<option value="' . $product['product_id'] . '" ' . ($product['product_id'] == $flavor['product_id'] ? 'selected' : '') . '>';
                        echo htmlspecialchars($product['name']);
                        echo '</option>';
                    }
                    ?>
                </select>
                <?php if ($flavor['image']): ?>
                <div class="mb-4">
                    <img src="<?= htmlspecialchars($flavor['image']) ?>" alt="Product Image"
                        class="w-32 h-32 object-cover rounded-md">
                </div>
                <?php endif; ?>
                <!-- Image Upload -->
                <label for="image" class="block text-sm font-semibold mb-2">Upload Image (Optional):</label>
                <input type="file" name="image" id="image" accept="image/*" class="w-full p-3 border rounded-md mb-4">

                <button type="submit" class="bg-green-500 text-white p-3 rounded-md w-full">Update Flavor</button>
            </form>
        </div>
    </main>
</body>

</html>