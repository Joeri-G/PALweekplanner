<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om te verbinden met een MySQL database via mysqli
*/
$servername = "localhost";
$username = "root";
$password = "";
$database = "planner";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}



 ?>
