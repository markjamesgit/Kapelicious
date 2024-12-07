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

<body class="min-h-screen bg-gradient-to-b from-light-gray to-beige flex items-center justify-center">
    <div class="max-w-4xl w-full flex bg-dark-brown rounded-lg shadow-lg overflow-hidden">
        <!-- Left Side: Image -->
        <div class="hidden md:flex w-1/2 bg-cover bg-center"
            style="background-image: url('../../assets/login-bg.png');">
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full md:w-1/2 p-8">
            <!-- Back Button -->
            <button onclick="location.href='/Kapelicious/index.php'"
                class="flex items-center mb-6 text-light-gray text-sm font-medium hover:underline">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </button>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-light-gray mb-6">Welcome Back</h1>
            <p class="text-light-gray mb-6">Log in to access your account</p>

            <!-- Alerts -->
            <?php if ($is_invalid): ?>
            <p class="text-red-600 mb-4 text-center font-medium">Invalid email or password.</p>
            <?php endif; ?>
            <?php if ($activation_error): ?>
            <p class="text-red-600 mb-4 text-center font-medium">Your account has not been activated. Please check your
                email to verify your account.</p>
            <?php endif; ?>

            <!-- Form -->
            <form method="post" class="space-y-6">
                <!-- Email Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-envelope text-dark-brown absolute left-3"></i>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"
                        required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                        placeholder="Enter your email" />
                </div>

                <!-- Password Field -->
                <div class="relative">
                    <div class="flex items-center border border-beige rounded-md shadow-sm bg-white">
                        <i class="fas fa-lock text-dark-brown absolute left-3"></i>
                        <input type="password" name="password" id="password" required
                            class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                            placeholder="Enter your password" />
                    </div>
                    <!-- Forgot Password -->
                    <div class="mt-2">
                        <a href="forgot-password.php" class="text-sm text-beige hover:underline">Forgot
                            Password?</a>
                    </div>
                </div>

                <!-- Login Button -->
                <button
                    class="w-full bg-beige text-dark-brown py-3 rounded-full font-medium text-lg hover:bg-opacity-90 transition">
                    Log In
                </button>
            </form>

            <!-- Sign Up -->
            <p class="mt-6 text-center text-sm text-light-gray">
                Don’t have an account? <a href="/Kapelicious/frontend/pages/php/signup.php"
                    class="font-medium hover:underline">Sign up now</a>
            </p>
        </div>
    </div>
</body>

</html>