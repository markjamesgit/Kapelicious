<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapelicious | Change Profile Information</title>
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
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Change Profile Information</h2>

            <!-- Profile Information Form -->
            <form action="change_profile_info_process.php" method="POST" class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-600 mb-2">
                        Full Name
                    </label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:ring-brown-200"
                        required placeholder="Enter your full name">
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-600 mb-2">
                        Username
                    </label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:ring-brown-200"
                        required placeholder="Enter a new username">
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-600 mb-2">
                        Address
                    </label>
                    <textarea id="address" name="address"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:ring-brown-200"
                        rows="4" placeholder="Enter your address"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full bg-dark-brown text-white font-semibold py-2 rounded-md hover:bg-brown-700 transition duration-300">
                        Update Information
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