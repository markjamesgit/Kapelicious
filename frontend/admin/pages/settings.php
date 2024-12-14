<?php
session_start();
$mysqli = require '../../../backend/config/database.php';

// Fetch the current settings
$sql = "SELECT logo, background_color, text_color, slideshow_images FROM settings WHERE id = 1";
$result = $mysqli->query($sql);

if (!$result) {
    die("Database query failed: " . $mysqli->error);
}

$settings = $result->fetch_assoc();
$currentBackgroundColor = $settings['background_color'] ?? '#FFFFFF';
$currentTextColor = $settings['text_color'] ?? '#000000';
$slideshowImages = json_decode($settings['slideshow_images'] ?? '[]', true);

if (!is_array($slideshowImages)) {
    $slideshowImages = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '/Kapelicious/frontend/assets/settings/';
        $fullUploadPath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
        $logoPath = $uploadDir . basename($_FILES['logo']['name']);

        if (!is_dir($fullUploadPath)) {
            mkdir($fullUploadPath, 0755, true);
        }

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $fullUploadPath . basename($_FILES['logo']['name']))) {
            $sql = "UPDATE settings SET logo = ? WHERE id = 1";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("s", $logoPath);

            if ($stmt->execute()) {
                header("Location: settings.php?success=1");
                exit;
            } else {
                echo "Error updating logo: " . $stmt->error;
            }
        } else {
            echo "Error moving uploaded file.";
        }
    }

    // Handle background color update
    if (isset($_POST['background_color'])) {
        $backgroundColor = $_POST['background_color'];
        $sql = "UPDATE settings SET background_color = ? WHERE id = 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $backgroundColor);

        if ($stmt->execute()) {
            header("Location: settings.php?success=1");
            exit;
        } else {
            echo "Error updating background color: " . $stmt->error;
        }
    }

    // Handle text color update
    if (isset($_POST['text_color'])) {
        $textColor = $_POST['text_color'];
        $sql = "UPDATE settings SET text_color = ? WHERE id = 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $textColor);

        if ($stmt->execute()) {
            header("Location: settings.php?success=1");
            exit;
        } else {
            echo "Error updating text color: " . $stmt->error;
        }
    }

    // Handle slideshow image upload
    if (isset($_FILES['slideshow_image']) && $_FILES['slideshow_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '/Kapelicious/frontend/assets/slideshow/';
        $fullUploadPath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
        $imagePath = $uploadDir . basename($_FILES['slideshow_image']['name']);

        if (!is_dir($fullUploadPath)) {
            mkdir($fullUploadPath, 0755, true);
        }

        if (move_uploaded_file($_FILES['slideshow_image']['tmp_name'], $fullUploadPath . basename($_FILES['slideshow_image']['name']))) {
            $slideshowImages[] = $imagePath;
            $slideshowJson = json_encode($slideshowImages);

            $sql = "UPDATE settings SET slideshow_images = ? WHERE id = 1";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("s", $slideshowJson);

            if ($stmt->execute()) {
                header("Location: settings.php?success=1");
                exit;
            } else {
                echo "Error updating slideshow images: " . $stmt->error;
            }
        } else {
            echo "Error moving uploaded file.";
        }
    }

    // Handle slideshow image deletion
    if (isset($_POST['delete_slideshow_image'])) {
        $imagePathToDelete = $_POST['delete_slideshow_image'];

        $slideshowImages = array_filter($slideshowImages, function($path) use ($imagePathToDelete) {
            return $path !== $imagePathToDelete;
        });

        $slideshowJson = json_encode(array_values($slideshowImages));

        $sql = "UPDATE settings SET slideshow_images = ? WHERE id = 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $slideshowJson);

        if ($stmt->execute()) {
            header("Location: settings.php?success=1");
            exit;
        } else {
            echo "Error deleting slideshow image: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Kapelicious</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<body class="bg-gray-100 font-sans">
    <div class="container mx-auto py-10 px-6">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h1 class="text-4xl font-extrabold text-gray-700 mb-6 flex items-center space-x-2">
                <i class="fas fa-cog text-gray-600"></i> Settings
            </h1>

            <!-- Success and Error Messages -->
            <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                <span class="block sm:inline">Settings updated successfully!</span>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <span class="block sm:inline">An error occurred while updating the settings.</span>
            </div>
            <?php endif; ?>

            <!-- Logo Upload -->
            <div class="mb-8 bg-white p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center space-x-2">
                    <i class="fas fa-image text-gray-600"></i> Upload Logo
                </h2>
                <div class="flex items-center space-x-6">
                    <div class="flex flex-col items-center">
                        <h3 class="text-gray-600 text-sm mb-2">Current Logo</h3>
                        <img src="<?= htmlspecialchars($settings['logo'] ?? '/default-logo.png') ?>" alt="Current Logo"
                            class="w-24 h-24 object-cover rounded-full border border-gray-300">
                    </div>
                    <form action="settings.php" method="post" enctype="multipart/form-data" class="flex-grow space-y-4">
                        <label for="logo" class="block text-sm font-medium text-gray-700">Choose a New Logo</label>
                        <input type="file" name="logo" id="logo"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 file:bg-[#F2EAD3] file:text-[#3F2305]">
                        <button type="submit" class="w-full bg-[#F2EAD3] text-[#3F2305] py-2 px-4 rounded-lg">Upload New
                            Logo</button>
                    </form>
                </div>
            </div>

            <!-- Background Color Change Form -->
            <div class="mb-8 bg-white  p-8">
                <h2 class="text-3xl font-semibold text-gray-900 mb-6 flex items-center space-x-2">
                    <i class="fas fa-palette text-gray-600"></i> Change Background Color
                </h2>
                <form action="settings.php" method="post"
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-6">
                    <div class="flex items-center mb-4 sm:mb-0">
                        <label for="background_color" class="text-sm font-medium text-gray-700">Select Color</label>
                        <input type="color" name="background_color" id="background_color"
                            value="<?= htmlspecialchars($currentBackgroundColor) ?>"
                            class="w-16 h-16 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit"
                        class="w-full sm:w-auto bg-[#F2EAD3] text-[#3F2305] px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all duration-300">
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- Text Color Change Form -->
            <div class="mb-8 bg-white  p-8">
                <h2 class="text-3xl font-semibold text-gray-900 mb-6 flex items-center space-x-2">
                    <i class="fas fa-font text-gray-600"></i> Change Text Color
                </h2>
                <form action="settings.php" method="post"
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-6">
                    <div class="flex items-center mb-4 sm:mb-0">
                        <label for="text_color" class="text-sm font-medium text-gray-700">Select Color</label>
                        <input type="color" name="text_color" id="text_color"
                            value="<?= htmlspecialchars($currentTextColor) ?>"
                            class="w-16 h-16 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <button type="submit"
                        class="w-full sm:w-auto bg-[#F2EAD3] text-[#3F2305] px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-all duration-300">
                        Save Changes
                    </button>
                </form>
            </div>



            <!-- Slideshow Image Upload Form -->
            <div class="mb-8 bg-white  p-8">
                <h2 class="text-3xl font-semibold text-gray-900 mb-6 flex items-center space-x-2">
                    <i class="fas fa-images text-gray-600"></i> Add Slideshow Image
                </h2>
                <form action="settings.php" method="post" enctype="multipart/form-data"
                    class="flex items-center space-x-4">
                    <input type="file" name="slideshow_image"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 flex items-center space-x-2">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload</span>
                    </button>
                </form>
            </div>

            <!-- Display Slideshow Images -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($slideshowImages as $imagePath): ?>
                <div class="relative">
                    <img src="<?= htmlspecialchars($imagePath) ?>" alt="Slideshow Image"
                        class="w-full h-40 object-cover rounded-lg">
                    <button type="button"
                        class="absolute top-2 right-2 p-2 text-red-500 hover:text-red-600 transition-all duration-300"
                        onclick="this.parentElement.parentElement.querySelector('form').submit()">
                        <i class="fas fa-trash"></i>
                    </button>
                    <form action="settings.php" method="post" style="display: none;">
                        <input type="hidden" name="delete_slideshow_image" value="<?= htmlspecialchars($imagePath) ?>">
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>