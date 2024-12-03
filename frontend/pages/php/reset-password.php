<?php

// Get the token from the URL
$token = $_GET["token"];

// Calculate the hash of the token
$token_hash = hash("sha256", $token);

// Connect to the database
$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Query to check if the user with the given token exists
$sql = "SELECT * FROM users
        WHERE reset_token_hash = ?";

// Prepare and execute the query
$stmt = $mysqli->prepare($sql);

// Bind the parameter to the query
$stmt->bind_param("s", $token_hash);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Get the user
$user = $result->fetch_assoc();

// If user is not found, die
if ($user === null) {
    die("token not found");
}

// If token has expired, die
if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("token has expired");
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
    <script src="../../javascript/validation.js" defer></script>
</head>

<body>

    <h1>Reset Password</h1>

    <!-- Add the error message if the old password and new password are the same -->
    <?php if (isset($_GET['error']) && $_GET['error'] == 'same_password'): ?>
    <p style="color: red;">New password cannot be the same as the old password.</p>
    <?php endif; ?>

    <form method="post" action="/Kapelicious/backend/functions/process-reset-password.php">

        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label for=" new_password">New password</label>
        <input type="password" id="new_password" name="new_password">


        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password">


        <button>Send</button>
    </form>

</body>

</html>