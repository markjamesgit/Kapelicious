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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-b from-light-gray to-beige flex flex-col min-h-screen">
    <!-- Include Header -->
    <?php include '../../includes/header.php'; ?>

    <!-- Main Container -->
    <div class="flex-grow flex items-center justify-center py-12 px-4">
        <div class="bg-dark-brown rounded-lg shadow-lg w-full max-w-lg p-8">
            <!-- Back Button -->
            <button onclick="window.location.href='/Kapelicious/index.php'"
                class="flex items-center mb-6 text-light-gray text-sm font-medium hover:underline">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </button>
            <!-- Page Title -->
            <h2 class="text-3xl font-bold text-center text-light-gray mb-6">Change Profile Picture</h2>

            <!-- Profile Picture Form -->
            <form action="../../../backend/functions/change-profile-picture-process.php" method="POST"
                enctype="multipart/form-data" class="space-y-6">

                <!-- Current Profile Picture -->
                <div class="text-center">
                    <p class="text-sm text-light-gray mb-2">Current Profile Picture</p>
                    <img src="<?= htmlspecialchars('/Kapelicious/frontend/assets/uploads/' . basename($user['profile_picture'] ?? '/frontend/assets/default-profile.jpg')) ?>"
                        alt="Current Profile Picture"
                        class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-light-gray">
                </div>

                <!-- Upload New Picture -->
                <div>
                    <label for="profile_picture" class="block text-sm font-semibold text-light-gray mb-2">
                        Upload New Profile Picture
                    </label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required
                        class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown">
                </div>

                <!-- Preview New Picture -->
                <div id="previewContainer" class="text-center hidden">
                    <p class="text-sm text-light-gray mb-2">Preview</p>
                    <img id="previewImage" src="#" alt="Preview"
                        class="w-32 h-32 rounded-full mx-auto border-4 border-light-gray">
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full bg-beige text-dark-brown font-semibold py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                        Update Profile Picture
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>

</html>