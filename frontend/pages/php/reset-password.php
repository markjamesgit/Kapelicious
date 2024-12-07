<?php
$email = $_GET["email"];

$mysqli = require __DIR__ . "../../../../backend/config/database.php"; 

// Query to get the user with the given email
$sql = "SELECT * FROM users WHERE email = ?";

// Prepare and execute the query
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If user is not found, redirect or show an error
if ($user === null) {
    die("User not found.");
}

$old_password_hash = $user["password_hash"]; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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

<body class="min-h-screen bg-gradient-to-b from-light-gray to-beige flex items-center justify-center">
    <div class="max-w-md w-full bg-dark-brown rounded-lg shadow-lg overflow-hidden p-6">
        <!-- Back Button -->
        <button onclick="window.history.back()"
            class="flex items-center mb-6 text-light-gray text-sm font-medium hover:underline">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </button>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-light-gray text-center mb-4">Reset Password</h1>
        <p class="text-light-gray text-center mb-6">Create a new password for your account.</p>

        <!-- Error Message -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'password_match') : ?>
        <p class="text-red-600 mb-4 text-center font-medium">New password cannot be the same as the old password.</p>
        <?php endif; ?>

        <!-- Reset Password Form -->
        <form method="post" action="/Kapelicious/backend/functions/process-reset-password.php" class="space-y-4">
            <!-- Hidden Email Field -->
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

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
                Submit
            </button>
        </form>
    </div>
</body>

</html>