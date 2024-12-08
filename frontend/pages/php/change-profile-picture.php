<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapelicious | Change Profile Picture</title>
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

<body class="bg-gray-100">
    <!-- Include Header -->
    <?php include '../../includes/header.php'; ?>

    <!-- Main Container -->
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white shadow-lg rounded-lg w-full max-w-lg p-8">
            <!-- Page Title -->
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Change Profile Picture</h2>

            <!-- Profile Picture Form -->
            <form action="../../../backend/functions/change-profile-picture-process.php" method="POST"
                enctype="multipart/form-data" class="space-y-6">
                <!-- Current Profile Picture -->
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Current Profile Picture</p>
                    <img src="<?= htmlspecialchars('/Kapelicious/frontend/assets/uploads/' . basename($user['profile_picture'] ?? '/frontend/assets/default-profile.jpg')) ?>"
                        alt="Current Profile Picture" class="w-32 h-32 rounded-full mx-auto border border-gray-300">
                </div>

                <!-- Upload New Picture -->
                <div>
                    <label for="profile_picture" class="block text-sm font-semibold text-gray-600 mb-2">
                        Upload New Profile Picture
                    </label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:ring-brown-200">
                </div>

                <!-- Preview New Picture -->
                <div id="previewContainer" class="text-center hidden">
                    <p class="text-sm text-gray-600 mb-2">Preview</p>
                    <img id="previewImage" src="#" alt="Preview"
                        class="w-32 h-32 rounded-full mx-auto border border-gray-300">
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full bg-dark-brown text-white font-semibold py-2 rounded-md hover:bg-brown-700 transition duration-300">
                        Update Profile Picture
                    </button>
                </div>
            </form>
            <!-- Back Link -->
            <div class="text-center mt-4">
                <a href="/Kapelicious/index.php" class="text-blue-500 hover:underline">Back to Profile</a>
            </div>
        </div>
    </div>
</body>

</html>