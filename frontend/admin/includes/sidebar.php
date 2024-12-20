<?php
// Start the PHP session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in by checking if the session user_id is set
if (isset($_SESSION["user_id"])) {
   
    // Connect to the MySQL database
    $mysqli = require "C:/xampp/htdocs/Kapelicious/backend/config/database.php";

    // Prepare the SQL statement to fetch user info from the database based on session user_id
    $sql = "SELECT name, username, address, profile_picture FROM users WHERE id = ?";

    // Prepare the SQL statement for execution using the prepare method
    $stmt = $mysqli->prepare($sql);

    // Bind the parameter(s) to the query using the bind_param method
    $stmt->bind_param("i", $_SESSION["user_id"]);

    // Execute the query using the execute method
    $stmt->execute();

    // Get the result of the query using the get_result method
    $sidebarResult = $stmt->get_result();

    // Fetch the user info from the result using the fetch_assoc method
    $sidebarUser = $sidebarResult->fetch_assoc();
}
?>

<!-- Add FontAwesome CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<aside class="bg-dark-brown w-64 min-h-screen text-light-gray flex flex-col">
    <!-- Profile Section -->
    <div class="p-6 text-center border-b border-beige">
        <img src="<?= htmlspecialchars('/Kapelicious/frontend/admin/assets/uploads/' . basename($sidebarUser['profile_picture'] ?? '../assets/uploads/default-profile.jpg')) ?>"
            alt="Current Profile Picture"
            class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-light-gray">
        <h2 class="text-lg font-bold"><?= htmlspecialchars($sidebarUser['username']) ?></h2>
        <div class="relative mt-2">
            <button
                class="text-sm bg-beige text-dark-brown px-4 py-2 rounded-full hover:bg-opacity-90 w-full text-center">
                Profile Options
            </button>
            <div
                class="hidden absolute mt-2 bg-white text-dark-brown shadow-lg rounded-md w-full divide-y divide-beige">
                <a href="/Kapelicious/frontend/admin/pages/change-password.php"
                    class="block px-4 py-2 hover:bg-beige">Change Password</a>
                <a href="/Kapelicious/frontend/admin/pages/change-profile-info.php"
                    class="block px-4 py-2 hover:bg-beige">Change Profile Info</a>
                <a href="/Kapelicious/frontend/admin/pages/change-profile-picture.php"
                    class="block px-4 py-2 hover:bg-beige">Change Profile Picture</a>
                <a href="/Kapelicious/frontend/admin/pages/manage-accounts.php"
                    class="block px-4 py-2 hover:bg-beige">Manage Accounts</a>
            </div>
        </div>
    </div>

    <!-- Sidebar Links -->
    <nav class="flex-grow">
        <ul class="space-y-2 px-4 mt-4">
            <!-- Dashboard -->
            <li><a href="dashboard-content.php" target="contentFrame"
                    class="block px-4 py-2 rounded-md hover:bg-beige flex items-center">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>

            <!-- Manage Menu Dropdown -->
            <li class="relative">
                <button
                    class="block w-full px-4 py-2 rounded-md hover:bg-beige text-left flex justify-between items-center"
                    onclick="toggleDropdown('manage-menu-dropdown')">
                    <i class="fas fa-cogs mr-3"></i> Manage Menu
                    <span class="text-sm">&#9660;</span>
                </button>
                <ul id="manage-menu-dropdown" class="hidden space-y-2 bg-dark-brown pl-6">
                    <li><a href="/Kapelicious/frontend/admin/pages/manage-category.php"
                            class="block px-4 py-2 rounded-md hover:bg-beige"><i class="fas fa-list mr-3"></i> Category
                            List</a></li>
                    <li><a href="/Kapelicious/frontend/admin/pages/manage-product.php"
                            class="block px-4 py-2 rounded-md hover:bg-beige"><i class="fas fa-box mr-3"></i> Product
                            List</a></li>
                    <li><a href="/Kapelicious/frontend/admin/pages/manage-variant.php"
                            class="block px-4 py-2 rounded-md hover:bg-beige"><i class="fas fa-cogs mr-3"></i> Variant
                            List</a></li>
                    <li><a href="/Kapelicious/frontend/admin/pages/manage-add-ons.php"
                            class="block px-4 py-2 rounded-md hover:bg-beige"><i class="fas fa-plus-circle mr-3"></i>
                            Add Ons List</a></li>
                    <li><a href="/Kapelicious/frontend/admin/pages/manage-flavor.php"
                            class="block px-4 py-2 rounded-md hover:bg-beige"><i class="fas fa-palette mr-3"></i>
                            Flavors List</a></li>
                </ul>
            </li>

            <!-- Orders -->
            <li><a href="orders.php" target="contentFrame"
                    class="block px-4 py-2 rounded-md hover:bg-beige flex items-center">
                    <i class="fas fa-clipboard-list mr-3"></i> Orders
                </a>
            </li>

            <!-- Reports -->
            <li><a href="reports.php" target="contentFrame"
                    class="block px-4 py-2 rounded-md hover:bg-beige flex items-center">
                    <i class="fas fa-chart-line mr-3"></i> Reports
                </a>
            </li>

            <!-- Settings -->
            <li><a href="/Kapelicious/frontend/admin/pages/settings.php"
                    class="block px-4 py-2 rounded-md hover:bg-beige flex items-center">
                    <i class="fas fa-cogs mr-3"></i> Settings
                </a>
            </li>

            <!-- POS -->
            <li><a href="pos.php" target="contentFrame"
                    class="block px-4 py-2 rounded-md hover:bg-beige flex items-center">
                    <i class="fas fa-cash-register mr-3"></i> POS
                </a>
            </li>
        </ul>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-beige">
        <a href="/Kapelicious/frontend/pages/php/logout.php"
            class="block text-center bg-red-500 px-4 py-2 rounded-full hover:bg-red-600">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </div>
</aside>

<script>
// Function to toggle the visibility of the dropdown menu
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.classList.toggle('hidden');
}
</script>