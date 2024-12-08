<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapelicious | Change Password</title>
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
    <?php include("../../includes/header.php"); ?>
    <!-- Main Container -->
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
            <!-- Page Title -->
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Change Password</h2>

            <!-- Display Success or Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Password updated successfully.
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php
                        // Map error codes to messages for security
                        $error_code = $_GET['error'];
                        $error_messages = [
                            'empty_fields' => 'Please fill in all fields.',
                            'password_mismatch' => 'New password and confirmation do not match.',
                            'weak_password' => 'Password must be at least 8 characters long.',
                            'The current password is incorrect.' => 'The current password you entered is incorrect.',
                            'User not found.' => 'User not found.',
                            'Database error: Unable to prepare statement.' => 'A server error occurred. Please try again later.',
                            'Database error: Unable to execute statement.' => 'A server error occurred. Please try again later.',
                            'The new password must be different from the current password.' => 'The new password must be different from the current password.',
                            'Error hashing the new password.' => 'A server error occurred. Please try again later.',
                            'Database error: Unable to prepare update statement.' => 'A server error occurred. Please try again later.',
                            'Database error: Unable to execute update.' => 'A server error occurred. Please try again later.',
                        ];

                        echo htmlspecialchars($error_messages[$error_code] ?? 'An unknown error occurred.');
                    ?>
            </div>
            <?php endif; ?>

            <!-- Change Password Form -->
            <form action="/Kapelicious/backend/functions/change-password-process.php" method="POST" class="space-y-4">
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-600 mb-1">
                        Current Password
                    </label>
                    <input type="password" id="current_password" name="current_password"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:ring-brown-200"
                        placeholder="Enter current password" required>
                </div>

                <!-- New Password -->
                <div>
                    <label for="new_password" class="block text-sm font-semibold text-gray-600 mb-1">
                        New Password
                    </label>
                    <input type="password" id="new_password" name="new_password"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:ring-brown-200"
                        placeholder="Enter new password" required>
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-gray-600 mb-1">
                        Confirm New Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:ring-brown-200"
                        placeholder="Confirm new password" required>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full bg-dark-brown text-white font-semibold py-2 rounded-md hover:bg-brown-700 transition duration-300">
                        Update Password
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