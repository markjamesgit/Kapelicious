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
            <h2 class="text-3xl font-bold text-center text-light-gray mb-6">Change Profile Information</h2>

            <!-- Profile Information Form -->
            <form action="../../../backend/functions/change-profile-info-process.php" method="POST" class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-light-gray mb-2">
                        Full Name
                    </label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>"
                        class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown"
                        required placeholder="Enter your full name">
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-light-gray mb-2">
                        Username
                    </label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                        class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown"
                        required placeholder="Enter a new username">
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-semibold text-light-gray mb-2">
                        Address
                    </label>
                    <textarea id="address" name="address"
                        class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown"
                        rows="4" placeholder="Enter your address"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full bg-beige text-dark-brown font-semibold py-2 rounded-md hover:bg-opacity-90 transition duration-300">
                        Update Information
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>