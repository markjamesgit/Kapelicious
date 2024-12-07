<?php

$mysqli = require __DIR__ . "../../../../backend/config/database.php";

$error_message = null; // To store error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['verification_code'];

    // Check if the verification code matches an unverified account
    $stmt = $mysqli->prepare("SELECT email FROM users WHERE verification_code = ? AND is_verified = 0");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    if ($email) {
        // Mark the account as verified
        $stmt = $mysqli->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE verification_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();

        header("Location: /Kapelicious/frontend/pages/html/signup-success.html");
        exit;
    } else {
        $error_message = "Invalid verification code!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification</title>
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
    <div class="max-w-md w-full bg-dark-brown rounded-lg shadow-lg p-6">
        <!-- Back Button -->
        <button onclick="window.history.back()"
            class="flex items-center mb-6 text-light-gray text-sm font-medium hover:underline">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </button>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-light-gray text-center mb-4">Account Verification</h1>
        <p class="text-light-gray text-center mb-6">Enter the verification code sent to your email.</p>

        <!-- Error Message -->
        <?php if (isset($error_message)) : ?>
        <p class="text-red-600 text-center font-medium mb-4"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <!-- Verification Form -->
        <form method="POST" class="space-y-4">
            <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                <i class="fas fa-key text-dark-brown absolute left-3"></i>
                <input type="text" id="verification_code" name="verification_code" required
                    class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown rounded-md"
                    placeholder="Enter verification code">
            </div>
            <button
                class="w-full bg-beige text-dark-brown py-3 rounded-full font-medium text-lg hover:bg-opacity-90 transition">
                Verify
            </button>
        </form>
    </div>
</body>

</html>