<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
    <div class="max-w-lg w-full bg-dark-brown rounded-lg shadow-md overflow-hidden p-6">
        <!-- Back Button -->
        <button onclick="window.location.href='/Kapelicious/index.php'"
            class="flex items-center mb-6 text-light-gray text-sm font-medium hover:underline">
            <i class="fas fa-arrow-left mr-2"></i> Back to Home
        </button>

        <!-- Page Title -->
        <h1 class="text-3xl font-bold text-light-gray text-center mb-4">Forgot Password</h1>
        <p class="text-light-gray text-center mb-6">
            Enter your registered email address, and we'll send you instructions to reset your password.
        </p>

        <!-- Forgot Password Form -->
        <form action="/Kapelicious/backend/functions/send-password-reset.php" method="post" class="space-y-4">
            <!-- Email Input -->
            <div class="relative flex items-center border border-beige rounded-md shadow-sm bg-white">
                <i class="fas fa-envelope text-dark-brown absolute left-3"></i>
                <input type="email" name="email" id="email" required
                    class="w-full pl-10 pr-3 py-2 bg-transparent text-dark-brown placeholder-dark-brown focus:outline-none focus:ring-2 focus:ring-beige focus:border-dark-brown rounded-md"
                    placeholder="Enter your email address" />
            </div>

            <!-- Submit Button -->
            <button
                class="w-full bg-beige text-dark-brown py-3 rounded-full font-medium text-lg hover:bg-opacity-90 transition">
                Send Reset Link
            </button>
        </form>

        <!-- Additional Help -->
        <p class="mt-6 text-center text-sm text-light-gray">
            Remembered your password? <a href="/Kapelicious/frontend/pages/php/login.php"
                class="font-medium hover:underline">Log in</a>
        </p>
    </div>
</body>

</html>