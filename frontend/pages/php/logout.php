<?php 

// start the session
session_start();

// destroy all of the data associated with the current session
session_destroy();

// redirect to the home page
header("Location: /Kapelicious/index.php");

// stop executing the script
exit;
?>