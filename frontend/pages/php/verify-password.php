<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = require __DIR__ . "../../../../backend/config/database.php";

// Get the email from the URL
$email = trim($_GET['email'] ?? '');

// Display the passed email
if (empty($email)) {
    die("No email provided in the URL. Debugging: email is empty.");
}

// Query to fetch the user with the given email
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Failed to prepare statement: " . $mysqli->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the user exists
if ($user === null) {
    die("No user found with that email. Debugging: email passed = " . htmlspecialchars($email));
}


// Check if the reset token is expired
if (isset($user["reset_token_expires_at"]) && strtotime($user["reset_token_expires_at"]) <= time()) {
    die("The reset token has expired. Debugging: expiry time = " . $user["reset_token_expires_at"]);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
</head>

<body>
    <h1>Verify Your Code</h1>

    <form action="/Kapelicious/backend/functions/process-verify-password.php" method="post">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <label for="verification_code">Enter Verification Code</label>
        <input type="text" name="verification_code" id="verification_code" required>
        <button type="submit">Verify Code</button>
    </form>
</body>

</html>