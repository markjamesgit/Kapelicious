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
</head>

<body>
    <h1>Login</h1>

    <?php if ($is_invalid): ?>
    <p style="color: red;">Invalid email or password.</p>
    <?php endif; ?>
    <?php if ($activation_error): ?>
    <p style="color: red;">Your account has not been activated. Please check your email to verify your account.</p>
    <?php endif; ?>

    <form method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" required />

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required />

        <button>Log in</button>
    </form>

    <p>
        <a href="forgot-password.php">Forgot Password?</a> |
        <a href="/Kapelicious/frontend/pages/html/signup.html">Sign up</a>
    </p>
</body>

</html>