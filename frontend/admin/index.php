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
$mysqli = require __DIR__ . "../../../backend/config/database.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
    <?php include __DIR__ . "/includes/sidebar.php"; ?>

    <!-- Main Content Area -->
    <main class="flex-grow">
        <iframe name="contentFrame" src="dashboard-content.php" class="w-full h-screen" frameborder="0"></iframe>
    </main>

    <!-- Scripts -->
    <script>
    // Dropdown Toggle
    document.querySelector('.relative button').addEventListener('click', function() {
        const dropdown = this.nextElementSibling;
        dropdown.classList.toggle('hidden');
    });
    </script>
</body>

</html>