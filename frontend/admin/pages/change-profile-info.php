<?php
session_start();

// Include the database connection
$mysqli = require '../../../backend/config/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Kapelicious/frontend/pages/php/login.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the current profile information
$query = "SELECT name, username, address FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($name, $username, $address);

// Check if user data is found
if ($stmt->num_rows > 0) {
    $stmt->fetch();
    // Assign the data to the $user array
    $user = [
        'name' => $name,
        'username' => $username,
        'address' => $address
    ];
} else {
    // Handle the case where user data is not found
    $user = [
        'name' => '',
        'username' => '',
        'address' => ''
    ];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Profile Information</title>
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
        <h1 class="text-2xl font-bold text-dark-brown mb-6">Change Profile Information</h1>

        <!-- Display Success or Error Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success! </strong>
            <span class="block sm:inline">Your profile information has been updated successfully.</span>
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
                        'invalid_username' => 'Username contains invalid characters.',
                        'database_error' => 'An error occurred. Please try again later.',
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

        <!-- Profile Update Form -->
        <form method="POST" action="/Kapelicious/frontend/admin/functions/change-profile-info-process.php"
            class="max-w-md space-y-6 bg-white p-6 rounded-lg shadow-md">

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-dark-brown mb-2">Full Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                    class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-dark-brown"
                    placeholder="Enter your full name">
            </div>

            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-dark-brown mb-2">Username</label>
                <input type="text" name="username" id="username"
                    value="<?php echo htmlspecialchars($user['username']); ?>" required
                    class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-dark-brown"
                    placeholder="Enter your username">
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-sm font-medium text-dark-brown mb-2">Address</label>
                <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address']); ?>"
                    required
                    class="w-full px-4 py-2 border border-beige rounded-md focus:outline-none focus:ring-2 focus:ring-dark-brown"
                    placeholder="Enter your address">
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-beige text-dark-brown py-2 rounded-md font-medium hover:bg-opacity-90 transition">
                Update Profile
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