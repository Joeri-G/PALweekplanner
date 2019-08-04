<?php
//list alle jaarlagen
$out = array();
require('db-connect.php');
$stmt = $conn->prepare('SELECT DISTINCT jaar, niveau FROM klassen');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resJaar, $resNiveau);
while($stmt->fetch()) {
  $tmpObj = new stdClass;
  $tmpObj->jaar = $resJaar;
  $tmpObj->niveau = $resNiveau;
  $out[] = $tmpObj;
}

//set JSON header
header('Content-Type: application/json');
//als als input ?format is gezet doe dan prettyp print
//we doen dit niet meteen omdat het het bestand aanzienlijk groter maakt
if (isset($_GET['format']) && $_GET['format'] == 'true') {
  $json = json_encode($out, JSON_PRETTY_PRINT);
}
else {
  $json = json_encode($out);
}
//output JSON en stop execution
die($json);
 ?>
