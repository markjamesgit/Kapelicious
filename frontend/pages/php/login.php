<?php
// Initialize flags to check login status and account activation status
$is_invalid = false; 
$activation_error = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Connect to the database
    $mysqli = require __DIR__ . "../../../../backend/config/database.php";

    // Query to get the user with the given email
    $sql = sprintf("SELECT * FROM users WHERE email='%s'", $mysqli->real_escape_string($_POST["email"]));

    // Execute the query
    $result = $mysqli->query($sql);

    // Fetch the user
    $user = $result->fetch_assoc();

    // Check if account is activated
    if ($user) {
        if ($user["account_activation_hash"] !== null) {
            // Account is not activated
            $activation_error = true;
        } else {
            // Verify the password if the account is activated
            if (password_verify($_POST["password"], $user["password_hash"])) {
                // Start the session and regenerate the session id
                session_start();
                session_regenerate_id();

                // Set the user id in the session
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
    <p style="color: red;">Invalid login credentials</p>
    <?php endif; ?>
    <?php if ($activation_error): ?>
    <p style="color: red;">Your account has not been activated. Please check your email to activate your account.</p>
    <?php endif; ?>

    <form method="post">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" />

        <label for="password">Password</label>
        <input type="password" name="password" id="password" />

        <button>Log in</button>
    </form>
    <a href="forgot-password.php">Forgot Password?</a>
</body>

</html>