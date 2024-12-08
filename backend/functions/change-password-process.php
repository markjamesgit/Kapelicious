<?php
// Start the session
session_start();

// Include the database connection
$mysqli = require '../config/database.php';

// Function to handle password change
function change_password($mysqli, $user_id, $current_password, $new_password) {
    // Prepare the SQL statement to fetch the current password hash
    $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE id = ?");
    if (!$stmt) {
        return "Database error: Unable to prepare statement.";
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        $stmt->close();
        return "Database error: Unable to execute statement.";
    }

    // Fetch the result
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        return "No user found with the given ID.";
    }

    // Get the hashed password
    $row = $result->fetch_assoc();
    $hashed_password = $row['password_hash']; // Correct column name
    $stmt->close();

    // Verify the current password
    if (!password_verify($current_password, $hashed_password)) {
        return "The current password is incorrect.";
    }

    // Check if the new password is different from the current password
    if (password_verify($new_password, $hashed_password)) {
        return "The new password must be different from the current password.";
    }

    // Hash the new password
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    if ($new_hashed_password === false) {
        return "Error hashing the new password.";
    }

    // Update the password in the database
    $update_stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    if (!$update_stmt) {
        return "Database error: Unable to prepare update statement.";
    }

    $update_stmt->bind_param("si", $new_hashed_password, $user_id);
    if (!$update_stmt->execute()) {
        $update_stmt->close();
        return "Database error: Unable to execute update.";
    }

    $update_stmt->close();
    return "Password updated successfully.";
}



// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not authenticated
    header("Location: /Kapelicious/frontend/pages/php/login.php?error=not_logged_in");
    exit;
}

// Process the form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize POST data
    $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    // Basic validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: /Kapelicious/frontend/pages/php/change-password.php?error=empty_fields");
        exit;
    }

    if ($new_password !== $confirm_password) {
        header("Location: /Kapelicious/frontend/pages/php/change-password.php?error=password_mismatch");
        exit;
    }

    // Optional: Add password strength validation here
    // Example: Minimum length, includes numbers/special characters, etc.
    if (strlen($new_password) < 8) {
        header("Location: /Kapelicious/frontend/pages/php/change-password.php?error=weak_password");
        exit;
    }

    // Get the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Attempt to change the password
    $result_message = change_password($mysqli, $user_id, $current_password, $new_password);

    // Redirect based on the result
    if ($result_message === "Password updated successfully.") {
        header("Location: /Kapelicious/frontend/pages/php/change-password.php?success=1");
        exit;
    } else {
        // For security, you might want to pass error codes instead of raw messages
        // Adjust as needed
        header("Location: /Kapelicious/frontend/pages/php/change-password.php?error=" . urlencode($result_message));
        exit;
    }
} else {
    // If accessed without POST data, redirect back to the change password form
    header("Location: /Kapelicious/frontend/pages/php/change-password.php");
    exit;
}
?>