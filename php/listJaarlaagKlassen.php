<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om alle klassen binnen een jaarlaag te listen
  - SQL query naar database om alle klassen te listen waar jaarlaag = jaarlaag en niveau = niveau
*/
require('funcLib.php');
if (!_GETIsset(['jaar', 'niveau'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET");
}
$out = new stdClass;
$out->k = array();
require('db-connect.php');
$stmt = $conn->prepare('SELECT DISTINCT nummer FROM klassen WHERE jaar = ? AND niveau = ?');
$stmt->bind_param('ss', $_GET['jaar'], $_GET['niveau']);
$stmt->execute();
$stmt->bind_result($resNummer);
while ($stmt->fetch()) {
    $tmpObj = new stdClass;
    $tmpObj->j = $_GET['jaar'];
    $tmpObj->ni = $_GET['niveau'];
    $tmpObj->nu = $resNummer;
    $out->k[] = $tmpObj;
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
