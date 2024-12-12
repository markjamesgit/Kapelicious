<?php 
// Start session and check if the user is logged in as an admin
session_start();

if ($_SESSION["user_type"] != "admin") {
    header("Location: /Kapelicious/frontend/admin/index.php"); // Redirect if not an admin
    exit;
}

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Fetch all customers
$sql = "SELECT * FROM users WHERE user_type = 'customer'";
$customerResult = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Accounts</title>
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
        <h1 class="text-3xl font-bold text-dark-brown mb-6">Manage Customer Accounts</h1>

        <!-- Account Management Table -->
        <div class="overflow-x-auto p-6">
            <table class="min-w-full bg-white rounded-lg">
                <thead class="bg-beige rounded-t-lg">
                    <tr class="text-left">
                        <th class="px-6 py-3 text-lg font-semibold rounded-tl-lg">ID</th>
                        <th class="px-6 py-3 text-lg font-semibold">Profile Picture</th>
                        <th class="px-6 py-3 text-lg font-semibold">Username</th>
                        <th class="px-6 py-3 text-lg font-semibold">Email</th>
                        <th class="px-6 py-3 text-lg font-semibold">Attempts</th>
                        <th class="px-6 py-3 text-lg font-semibold ">Status</th>
                        <th class="px-6 py-3 text-lg font-semibold rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $customerResult->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="px-6 py-3"><?= htmlspecialchars($user["id"]) ?></td>
                        <td class="px-6 py-3">
                            <?php if ($user["profile_picture"]): ?>
                            <img src="<?= htmlspecialchars('/Kapelicious/frontend/admin/assets/uploads/' . basename($user['profile_picture'] ?? '../assets/uploads/default-profile.jpg')) ?>"
                                alt="Profile Picture" class="w-12 h-12 rounded-full object-cover">
                            <?php else: ?>
                            <span>No image</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3"><?= htmlspecialchars($user["username"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($user["email"]) ?></td>
                        <td class="px-6 py-3"><?= htmlspecialchars($user["failed_attempts"]) ?></td>
                        <td class="px-6 py-3">
                            <?php if ($user["is_blocked"] == 1): ?>
                            <span class="text-red-500 font-semibold rounded-full p-2">Blocked</span>
                            <?php else: ?>
                            <span class="text-green-500 font-semibold rounded-full p-2">Unblocked</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3">
                            <?php if ($user["is_blocked"] == 1): ?>
                            <a href="/Kapelicious/frontend/admin/functions/unblock-account.php?id=<?= htmlspecialchars($user["id"]) ?>"
                                class="bg-green-500 text-white rounded-full p-2">Unblock</a>
                            <?php else: ?>
                            <span class="text-gray-400 font-semibold">Unblock option</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
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