<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Profile Picture</title>
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
        <h1 class="text-2xl font-bold text-dark-brown mb-6">Change Profile Picture</h1>

        <!-- Success or Error Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success! </strong>
            <span class="block sm:inline">Your profile picture has been updated successfully.</span>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error! </strong>
            <span class="block sm:inline">
                <?php
                    $error_code = $_GET['error'];
                    $error_messages = [
                        'empty_file' => 'Please select a file to upload.',
                        'invalid_file' => 'Invalid file type. Please upload an image.',
                        'upload_error' => 'An error occurred during the upload. Please try again later.',
                    ];
                    echo htmlspecialchars($error_messages[$error_code] ?? 'An unknown error occurred.');
                ?>
            </span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20">
                    <title>Close</title>
                    <path
                        d="M14.348 5.652a1 1 0 00-1.414 0L10 8.586 7.066 5.652a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 11.414l2.934 2.934a1 1 0 001.414-1.414L11.414 10l2.934-2.934a1 1 0 000-1.414z" />
                </svg>
            </span>
        </div>
        <?php endif; ?>

        <!-- Profile Picture Form -->
        <form action="../functions/change-profile-picture-process.php" method="POST" enctype="multipart/form-data"
            class="space-y-6">

            <!-- Current Profile Picture -->
            <div class="text-center">
                <p class="text-sm text-light-gray mb-2">Current Profile Picture</p>
                <img src="<?= htmlspecialchars('/Kapelicious/frontend/admin/assets/uploads/' . basename($user['profile_picture'] ?? '../assets/uploads/default-profile.jpg')) ?>"
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
    </main>
    <!-- Scripts -->
    <script>
    // Sidebar dropdown toggle (if any dropdown in sidebar)
    document.querySelector('.relative button').addEventListener('click', function() {
        const dropdown = this.nextElementSibling;
        dropdown.classList.toggle('hidden');
    });
    </script>
</body>

</html>