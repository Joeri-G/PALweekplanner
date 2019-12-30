<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om te verbinden met een MySQL database via mysqli
*/
$dbServername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$db = "planner";

// Create connection
$conn = new mysqli($dbServername, $dbUsername, $dbPassword, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
