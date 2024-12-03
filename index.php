<?php

// Start the session
session_start();

// Check if user is logged in
if (isset($_SESSION["user_id"])){

    // Connect to the database
    $mysqli = require __DIR__ . "/backend/config/database.php";

    // Query to get the user with the given user id
    $sql = "SELECT * FROM users WHERE id = {$_SESSION["user_id"]}";

    // Execute the query
    $result = $mysqli->query($sql);

    // Fetch the user
    $user = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="/Kapelicious/frontend/dist/style.css">

</head>

<body>
    <h1 class="text-5xl font-bold text-green-500">Home</h1>

    <?php 
    // Check if user is logged in
    if (isset($user)): ?>
    <p>Hello <?= htmlspecialchars($user["name"]) ?></p>

    <p><a href="./frontend/pages/php/logout.php">Log out</a></p>
    <?php 
    // If user is not logged in
    else:  ?>
    <p class="text-red-500"><a href="frontend/pages/php/login.php">Log in</a> or <a
            href="frontend/pages/html/signup.html">Sign up</a>
    </p>

    <?php endif; ?>
</body>

</html>