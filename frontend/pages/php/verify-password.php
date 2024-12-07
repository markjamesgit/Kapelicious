<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get the email from the URL
$email = trim($_GET['email'] ?? '');

// Display the passed email
if (empty($email)) {
    die("No email provided in the URL. Debugging: email is empty.");
}

// Query to fetch the user with the given email
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Failed to prepare statement: " . $mysqli->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the user exists
if ($user === null) {
    die("No user found with that email. Debugging: email passed = " . htmlspecialchars($email));
}


// Check if the reset token is expired
if (isset($user["reset_token_expires_at"]) && strtotime($user["reset_token_expires_at"]) <= time()) {
    die("The reset token has expired. Debugging: expiry time = " . $user["reset_token_expires_at"]);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
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
    <div class="max-w-md w-full bg-dark-brown rounded-lg shadow-md overflow-hidden p-6">
        <!-- Back Button -->
        <button onclick="window.history.back()"
            class="flex items-center mb-6 text-light-gray text-sm font-medium hover:underline">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </button>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-light-gray text-center mb-4">Verify Your Code</h1>
        <p class="text-light-gray text-center mb-6">
            Please enter the verification code we sent to your email to proceed.
        </p>

        <!-- Verification Code Form -->
        <form action="/Kapelicious/backend/functions/process-verify-password.php" method="post" class="space-y-4">
            <!-- Hidden Email Field -->
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <!-- Verification Code Input -->
            <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                <i class="fas fa-key text-dark-brown absolute left-3"></i>
                <input type="text" name="verification_code" id="verification_code" required
                    class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown rounded-md"
                    placeholder="Enter your verification code">
            </div>

            <!-- Verify Button -->
            <button
                class="w-full bg-beige text-dark-brown py-3 rounded-full font-medium text-lg hover:bg-opacity-90 transition">
                Verify Code
            </button>
        </form>

        <!-- Additional Info -->
        <p class="mt-6 text-center text-sm text-light-gray">
            Didn’t receive the code? <a href="#" class="font-medium hover:underline">Resend Code</a>
        </p>
    </div>
</body>

</html>
>