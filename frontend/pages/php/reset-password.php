<?php
$email = $_GET["email"];

$mysqli = require __DIR__ . "../../../../backend/config/database.php"; 

// Query to get the user with the given email
$sql = "SELECT * FROM users WHERE email = ?";

// Prepare and execute the query
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If user is not found, redirect or show an error
if ($user === null) {
    die("User not found.");
}

$old_password_hash = $user["password_hash"]; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>

<body>
    <h1>Reset Password</h1>

    <?php
    // Check if a session or GET variable is set for error messages (if the passwords match)
    if (isset($_GET['error']) && $_GET['error'] == 'password_match') {
        echo "<p style='color: red;'>New password cannot be the same as the old password.</p>";
    }
    ?>

    <form method="post" action="/Kapelicious/backend/functions/process-reset-password.php">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Submit</button>
    </form>
</body>

</html>