<?php
// Initialize flags for login status and account activation status
$is_invalid = false; 
$activation_error = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mysqli = require __DIR__ . "../../../../backend/config/database.php"; 

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $_POST["email"]);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the user
    $user = $result->fetch_assoc();

    if ($user) {
        // Check if the account is verified
        if ($user["is_verified"] == 0) {
            $activation_error = true; // Account not activated
        } else {
            // Verify the password if the account is activated
            if (password_verify($_POST["password"], $user["password_hash"])) {

                // Start the session and regenerate the session ID
                session_start();
                session_regenerate_id();

                // Set the user ID in the session
                $_SESSION["user_id"] = $user["id"];

                // Redirect to the home page
                header("Location: /Kapelicious/index.php");
                exit;
            }
        }
    }

    // If login fails due to incorrect activation or password
    $is_invalid = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
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

<body class="flex items-center justify-center min-h-screen bg-light-gray">
    <div class="max-w-md w-full bg-cream p-8 rounded-lg shadow-lg">
        <!-- Back Button -->
        <button onclick="history.back()"
            class="flex items-center mb-6 text-dark-brown text-sm font-medium hover:underline">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </button>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-dark-brown mb-6 text-center">Welcome Back</h1>
        <p class="text-center text-beige mb-4">Log in to access your account</p>

        <!-- Alerts -->
        <?php if ($is_invalid): ?>
        <p class="text-red-600 mb-4 text-center font-medium">Invalid email or password.</p>
        <?php endif; ?>
        <?php if ($activation_error): ?>
        <p class="text-red-600 mb-4 text-center font-medium">Your account has not been activated. Please check your
            email
            to verify your account.</p>
        <?php endif; ?>

        <!-- Form -->
        <form method="post" class="space-y-6">
            <!-- Email Field -->
            <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                <i class="fas fa-envelope text-beige absolute left-3"></i>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                    required
                    class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-beige focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                    placeholder="Enter your email" />
            </div>

            <!-- Password Field -->
            <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                <i class="fas fa-lock text-beige absolute left-3"></i>
                <input type="password" name="password" id="password" required
                    class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-beige focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                    placeholder="Enter your password" />
            </div>

            <!-- Login Button -->
            <button
                class="w-full bg-dark-brown text-white py-3 rounded-md font-medium text-lg hover:bg-opacity-90 transition">
                Log In
            </button>
        </form>

        <!-- Links -->
        <p class="mt-6 text-center">
            <a href="forgot-password.php" class="text-sm text-dark-brown hover:underline">Forgot Password?</a>
            |
            <a href="/Kapelicious/frontend/pages/html/signup.html" class="text-sm text-dark-brown hover:underline">Sign
                up</a>
        </p>
    </div>
</body>

</html>