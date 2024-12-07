<?php
// Initialize flags for login status and account activation status
$is_invalid = false; 
$activation_error = false;

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Connect to the database
    $mysqli = require __DIR__ . "../../../../backend/config/database.php"; 

    // Prepare the SQL statement to select user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    // Bind the email parameter
    $stmt->bind_param("s", $_POST["email"]);
    // Execute the query
    $stmt->execute();
    // Get the result of the query
    $result = $stmt->get_result();

    // Fetch the user from the result
    $user = $result->fetch_assoc();

    // Check if user exists
    if ($user) {
        // Check if the account is verified
        if ($user["is_verified"] == 0) {
            // Set activation error flag
            $activation_error = true;
        } else {
            // Verify the password
            if (password_verify($_POST["password"], $user["password_hash"])) {

                // Start a new session
                session_start();
                // Regenerate session ID for security
                session_regenerate_id();

                // Store user ID in session
                $_SESSION["user_id"] = $user["id"];
                // Store user type in session
                $_SESSION["user_type"] = $user["user_type"];  

                // Redirect based on user type
                if ($user["user_type"] == "admin") {
                    header("Location: /Kapelicious/frontend/admin/dashboard.php");
                } else {
                    header("Location: /Kapelicious/index.php");
                }
                // Exit script after redirect
                exit;
            }
        }
    }
    // Set invalid flag if login fails
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