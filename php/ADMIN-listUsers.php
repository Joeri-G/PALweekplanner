<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om array met alle users te sturen
  - select users uit database
  - sterilize
  - out naar JSON
  - return JSON
*/

$out = array();
require('db-connect.php');
$stmt = $conn->prepare('SELECT username, role, userLVL, userAvailability, ID FROM users');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resUsername, $resRole, $resUserLVL, $resUserAvailability, $resID);
while ($stmt->fetch()) {
    $obj = new stdClass;
    $obj->username = $resUsername;
    $obj->role = $resRole;
    $obj->userLVL = $resUserLVL;
    $obj->userAvailability = $resUserAvailability;
    $obj->ID = $resID;

    $out[] = $obj;
}

//set JSON header
header('Content-Type: application/json');
//als als input ?format is gezet doe dan prettyp print
//we doen dit niet meteen omdat het het bestand aanzienlijk groter maakt
if (isset($_GET['format']) && $_GET['format'] == 'true') {
    $json = json_encode($out, JSON_PRETTY_PRINT);
} else {
    $json = json_encode($out);
}
//output JSON en stop execution
die($json);
