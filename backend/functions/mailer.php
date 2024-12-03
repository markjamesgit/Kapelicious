<?php
// Require the composer autoloader
require __DIR__ . "/../../vendor/autoload.php";

// Import the PHPMailer classes into the global namespace, so they can be used
// without fully qualifying them with their namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

// Set the SMTP debug mode to show the full SMTP communication on the page
// $mail->SMTPDebug = SMTP::DEBUG_SERVER;

// Set the mailer to use SMTP
$mail->isSMTP();

// Enable SMTP authentication
$mail->SMTPAuth = true;

// Set the SMTP host
$mail->Host = "smtp.gmail.com";

// Set the encryption type to use for the SMTP communication
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

// Set the SMTP port
$mail->Port = 587;

// Set the username to use for the SMTP authentication
$mail->Username = "kapeliciouscoffeeshop@gmail.com";

// Set the password to use for the SMTP authentication
$mail->Password = "hqkv tnvc esqx pzrw";

// Set the mailer to use HTML messages
$mail->isHtml(true);

// Return the mailer instance
return $mail;