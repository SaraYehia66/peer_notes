<?php
// Database configuration
$server = 'localhost'; // The hostname or IP address of the database server. 
                       // 'localhost' refers to the local machine where the script is running.

$username = 'root';    // The username used to connect to the database.
$password = '';        // The password associated with the database username.
$database = 'person_db'; // The name of the database you want to connect to.

// Establish the connection
global $connection;    // Declare the $connection variable as global so it can be used outside this scope.
$connection = mysqli_connect($server, $username, $password, $database);
// This function attempts to establish a connection to the MySQL database using the provided server, username, 
// password, and database name. It returns a connection object on success.

// Check connection
if (!$connection) {    // This checks if the connection failed. If the connection returns `false`, it means there was an error.
    die('Database connection failed: ' . mysqli_connect_error());
    // If the connection fails, the script stops executing and outputs an error message along with the reason for failure.
    // `mysqli_connect_error()` provides details about what went wrong.
}
?>
