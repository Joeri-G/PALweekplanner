<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om alle afspraken voor alle klassen te lezen
  - select alle afspraken
    * als het dagdeel nog niet bestaat in het out object voeg deze dan toe als array
    * appen afspraak aan dagdeel array
  - encode out object naar JSON
  - output JSON
*/
$out = new stdClass;

require('db-connect.php');
$stmt = $conn->prepare(
    'SELECT
  daypart,
  klas1jaar,
  klas1niveau,
  klas1nummer,
  docent1,
  docent2,
  lokaal1,
  lokaal2,
  laptops,
  projectCode,
  notes,
  ID
  FROM week'
);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resDaypart, $resKlas1Jaar, $resKlas1Niveau, $resKlas1Nummer, $resDocent1, $resDocent2, $resLokaal1, $resLokaal2, $resLaptop, $resProjectCode, $resNote, $resID);
while ($stmt->fetch()) {
    if (!isset($out->$resDaypart)) {
        $out->$resDaypart = array();
    }
    $obj = new stdClass;
    $klasObj = new stdClass;
    $klasObj->j = $resKlas1Jaar;
    $klasObj->ni = $resKlas1Niveau;
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
