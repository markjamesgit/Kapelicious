<?php

// define database host
$host = "localhost";

// define database name
$dbname = "kapelicious_db";

// define database username
$username = "markjames";

// define database password
$password = "RKVDdUWRfRbRkVK-";

// create an instance of mysqli
$mysqli = new mysqli(hostname: $host, username: $username, password: $password, database: $dbname);

// check if the connection is successful
if ($mysqli->connect_errno){
    // if not, kill the script with the error message
    die("Connection error: " . $mysqli->connect_error);
}

// return the instance of mysqli
return $mysqli;