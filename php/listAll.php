<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om een lijst te maken met alle klassen en docenten
  - klas
    * jaar
    * niveau
    * nummer
  - docent
    * username
    * availability

  - laad alle docenten met SQL query DISTICT staat voor geen duplicates
    * plaats in object
  - laad alle klassen op dezelfde manier
    * plaats in object

  - encode object naar JSON en stuur
*/
//library
require('funcLib.php');

//maak leeg out object en tijdenlijke objects voor docenten en klassen
$out = new stdClass;
$docenten = array();
$klassen = array();
//MySQL connection
require('db-connect.php');
//lees alle docenten
$stmt = $conn->prepare('SELECT DISTINCT username, userAvailability FROM users WHERE role=\'docent\'');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resUsername, $resUserAvailability);
while ($stmt->fetch()) {
  //tijdelijk object
  $docent = new stdClass;
  //plaats de username
  $docent->username = htmlentities($resUsername);
  //plaats de availability
  $docent->availability = json_decode($resUserAvailability);
  //plaats het in het object docent
  $docenten[] = $docent;
}
$stmt->close();
//plaats docenten in out;
$out->docent = $docenten;


$stmt = $conn->prepare('SELECT DISTINCT jaar, niveau, nummer FROM klassen');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resJaar, $resNiveau, $resNummer);
while ($stmt->fetch()) {
  //tijdelijk object
  $klas = new stdClass;
  //jaar
  $klas->jaar = $resJaar;
  //niveau
  $klas->niveau = htmlentities($resNiveau);
  //nummer
  $klas->nummer = $resNummer;
  //plaats in klassen
  $klassen[] = $klas;
}
$out->klas = $klassen;

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
