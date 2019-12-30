
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
$stmt = $conn->prepare('SELECT afkorting, userAvailability, ID FROM docenten');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resAfkorting, $resUserAvailability, $resID);
while ($stmt->fetch()) {
    $obj = new stdClass;
    $obj->afkorting = $resAfkorting;
    $obj->userAvailability = json_decode($resUserAvailability);
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
