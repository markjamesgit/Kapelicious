<?php

$token = $_GET["token"];

// Calculate the hash of the token
$token_hash = hash("sha256", $token);

$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Query to get the user with the given token
$sql = "SELECT * FROM users
        WHERE account_activation_hash = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Get the user
$user = $result->fetch_assoc();

// If user is not found, die
if ($user === null) {
    die("token not found");
}

// Update the user to set the account_activation_hash to NULL
$sql = "UPDATE users SET account_activation_hash = NULL WHERE id = ?";

// Prepare and execute the query
$stmt = $mysqli->prepare($sql);

// Bind the user ID to the query
$stmt->bind_param("s", $user["id"]);

// Execute the query to update the user
$stmt->execute();                      
?>
<!DOCTYPE html>
<html>

<head>
    <title>Account Activate</title>
    <meta charset="UTF-8">
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
    <script src="../../javascript/validation.js" defer></script>
</head>

<body>

    <h1>Account Activated</h1>

    <p>Account activated successfully. You can now <a href="login.php">log in</a>.</p>

</body>

</html>