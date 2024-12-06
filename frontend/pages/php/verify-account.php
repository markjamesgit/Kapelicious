<?php

$mysqli = require __DIR__ . "../../../../backend/config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['verification_code'];

    // Check if the verification code matches an unverified account
    $stmt = $mysqli->prepare("SELECT email FROM users WHERE verification_code = ? AND is_verified = 0");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    if ($email) {
        // Mark the account as verified
        $stmt = $mysqli->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE verification_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();

        header("Location: /Kapelicious/frontend/pages/html/signup-success.html");
        exit;
    } else {
        echo "<p style='color: red;'>Invalid verification code!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification</title>
</head>

<body>
    <h1>Verify Your Account</h1>
    <form method="POST">
        <label for="verification_code">Verification Code:</label>
        <input type="text" id="verification_code" name="verification_code" required>
        <br><br>
        <button type="submit">Verify</button>
    </form>
</body>

</html>