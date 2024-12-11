<?php
// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin") {
    // Redirect to login page if not authorized
    header("Location: /Kapelicious/frontend/pages/php/login.php");
    exit;
}

// Include the database connection if needed
$mysqli = require __DIR__ . "/../../../backend/config/database.php";
?>

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
</head>

<body class="min-h-screen bg-light-gray flex">
    <!-- Include Sidebar -->
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>


    <!-- Main Content Area -->
    <main class="flex-grow p-8">
        <h1 class="text-2xl font-bold text-dark-brown mb-6">Change Password</h1>


        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success! </strong>
            <span class="block sm:inline">Password updated successfully.</span>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error! </strong>
            <span class="block sm:inline">
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


        <form method="POST" action="/Kapelicious/frontend/admin/functions/change-password-process.php"
            class="max-w-md space-y-6 bg-white p-6 rounded-lg shadow-md">
            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-dark-brown mb-2">Current
                    Password</label>
                <input type="password" name="current_password" id="current_password" required
                    class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-dark-brown"
                    placeholder="Enter your current password">
            </div>

            <!-- New Password -->
            <div>
                <label for="new_password" class="block text-sm font-medium text-dark-brown mb-2">New Password</label>
                <input type="password" name="new_password" id="new_password" required
                    class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-dark-brown"
                    placeholder="Enter a new password">
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="confirm_new_password" class="block text-sm font-medium text-dark-brown mb-2">Confirm New
                    Password</label>
                <input type="password" name="confirm_new_password" id="confirm_new_password" required
                    class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-dark-brown"
                    placeholder="Confirm your new password">
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-beige text-dark-brown py-2 rounded-md font-medium hover:bg-opacity-90 transition">
                Update Password
            </button>
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