<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Signup</title>
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
            style="background-image: url('../../assets/signup-bg.png');">
        </div>

        <!-- Right Side: Signup Form -->
        <div class="w-full md:w-1/2 p-8">
            <!-- Back Button -->
            <button onclick="location.href='/Kapelicious/frontend/pages/php/login.php'"
                class="flex items-center mb-6 text-light-gray text-sm font-medium hover:underline">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </button>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-light-gray mb-6">Create Your Account</h1>
            <p class="text-light-gray mb-6">Sign up to get started</p>

            <!-- Error Message -->
            <?php if (isset($_GET['error'])): ?>
            <p class="text-red-600 mb-4 text-center font-medium"><?= htmlspecialchars($_GET['error']) ?></p>
            <?php endif; ?>

            <!-- Form -->
            <form action="http://localhost/Kapelicious/backend/functions/signup-function.php" method="post"
                class="space-y-6">
                <!-- Name Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-user text-dark-brown absolute left-3"></i>
                    <input type="text" name="name" id="name" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                        placeholder="Enter your fullname" />
                </div>

                <!-- Username Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-user text-dark-brown absolute left-3"></i>
                    <input type="text" name="username" id="username" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                        placeholder="Enter your username" />
                </div>

                <!-- Address Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-map-marker-alt text-dark-brown absolute left-3"></i>
                    <input type="text" name="address" id="address" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                        placeholder="Enter your address" />
                </div>

                <!-- Email Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-envelope text-dark-brown absolute left-3"></i>
                    <input type="email" name="email" id="email" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                        placeholder="Enter your email" />
                </div>

                <!-- Password Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-lock text-dark-brown absolute left-3"></i>
                    <input type="password" name="password" id="password" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                        placeholder="Create a password" />
                </div>

                <!-- Confirm Password Field -->
                <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                    <i class="fas fa-check-circle text-dark-brown absolute left-3"></i>
                    <input type="password" name="confirm_password" id="confirm_password" required
                        class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-dark-brown focus:border-dark-brown rounded-md"
                        placeholder="Confirm your password" />
                </div>

                <!-- Signup Button -->
                <button
                    class="w-full bg-beige text-dark-brown py-3 rounded-full font-medium text-lg hover:bg-opacity-90 transition">
                    Sign Up
                </button>
            </form>

            <!-- Sign In -->
            <p class="mt-6 text-center text-sm text-light-gray">
                Already have an account? <a href="/Kapelicious/frontend/pages/php/login.php"
                    class="font-medium hover:underline">Log in here</a>
            </p>
        </div>
    </div>
</body>

</html>