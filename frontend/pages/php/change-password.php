<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
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
    <!-- Header included at the top -->
    <?php include("../../includes/header.php"); ?>

    <!-- Main Content Container -->
    <div class="flex-grow flex items-center justify-center py-12">
        <div class="max-w-md w-full bg-dark-brown rounded-lg shadow-lg overflow-hidden p-6">
            <!-- Back Button -->
            <button onclick="window.location.href='/Kapelicious/index.php'"
                class="flex items-center mb-6 text-light-gray text-sm font-medium hover:underline">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </button>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-light-gray text-center mb-4">Change Password</h1>
            <p class="text-light-gray text-center mb-6">Update your account password.</p>

            <!-- Display Success or Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <p class="text-green-600 mb-4 text-center font-medium">Password updated successfully.</p>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <p class="text-red-600 mb-4 text-center font-medium">
                <?php
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
            </p>
            <?php endif; ?>

            <!-- Change Password Form -->
            <form action="/Kapelicious/backend/functions/change-password-process.php" method="POST" class="space-y-4">
                <!-- Current Password Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-lock text-dark-brown absolute left-3"></i>
                    <input type="password" id="current_password" name="current_password" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown rounded-md"
                        placeholder="Enter current password">
                </div>

                <!-- New Password Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-lock text-dark-brown absolute left-3"></i>
                    <input type="password" id="new_password" name="new_password" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown rounded-md"
                        placeholder="Enter new password">
                </div>

                <!-- Confirm Password Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-check-circle text-dark-brown absolute left-3"></i>
                    <input type="password" id="confirm_password" name="confirm_password" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown rounded-md"
                        placeholder="Confirm new password">
                </div>

                <!-- Submit Button -->
                <button
                    class="w-full bg-beige text-dark-brown py-3 rounded-full font-medium text-lg hover:bg-opacity-90 transition">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</body>

</html>