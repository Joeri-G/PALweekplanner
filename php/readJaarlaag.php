<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om alle afspraken van een jaarlaag in te lezen
erg vergelijkbaar met readAll maar deze neemt twee inputs, jaar en niveau
  - check of de inputs gezet zijn (jaar, niveau)
  - SQL query om alle afspraken te selecteren waar klas1 of klas2 gelijk is met de inputs
    * als dagdeel nog niet gezet is voeg dit array toe aan out object
      + voeg afspraak toe aan dagdeel
  - encode out object naar JSON
  - output JSON
*/
require('funcLib.php');
if (!_GETIsset(['jaar', 'niveau'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET");
}
$jaar = $_GET['jaar'];
$niveau = $_GET['niveau'];

$out = new stdClass;

require('db-connect.php');
$stmt = $conn->prepare(
    'SELECT
  daypart,
  klas1nummer,
  docent1,
  docent2,
  lokaal1,
  lokaal2,
  laptops,
  projectCode,
  notes,
  ID
  FROM week
  WHERE (klas1jaar = ?
  AND klas1niveau = ?)/* OR (klas2jaar=? AND klas2niveau = ?)*/'
);
$stmt->bind_param(/*'ssss'*/ 'ss', $jaar, $niveau/*, $jaar, $niveau*/);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resDaypart, $resKlas1Nummer, $resDocent1, $resDocent2, $resLokaal1, $resLokaal2, $resLaptop, $resProjectCode, $resNote, $resID);
while ($stmt->fetch()) {
    if (!isset($out->$resDaypart)) {
        $out->$resDaypart = array();
    }
    $obj = new stdClass;
    $klasObj = new stdClass;
    $klasObj->j = $jaar;
    $klasObj->ni = $niveau;
    $klasObj->nu = $resKlas1Nummer;

    $obj->k = [$klasObj];

    $obj->d = [$resDocent1, $resDocent2];

    $obj->l = [$resLokaal1, $resLokaal2];

    $obj->la = $resLaptop;
    $obj->p = $resProjectCode;
    $obj->no = $resNote;
    $obj->ID = $resID;

    $out->$resDaypart[] = $obj;
}

$stmt->close();
$conn->close();
//zet header
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
