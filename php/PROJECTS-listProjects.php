<?php
//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om alle projecten te laden
*/
$out = array();
require('db-connect.php');
$stmt = $conn->prepare('SELECT DISTINCT projectTitle, projectCode, projectBeschrijving, projectInstructie, verantwoordelijke, ID FROM projecten');
$stmt->execute();
$stmt->bind_result($pT, $pC, $pB, $pI, $v, $ID);
while ($stmt->fetch()) {
    $obj = new stdClass;
    $obj->title = $pT;
    $obj->code = $pC;
    $obj->beschrijving = $pB;
    $obj->instructie = $pI;
    $obj->verantwoordelijke = $v;
    $obj->ID = $ID;

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
