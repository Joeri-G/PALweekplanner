<?php
require('funcLib.php');
if (!_GETIsset(['jaar', 'niveau'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET");
}
$jaar = $_GET['jaar'];
$niveau = $_GET['niveau'];

$out = new stdClass;

require('db-connect.php');
$stmt = $conn->prepare('SELECT
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
  WHERE klas1jaar = ?
  AND klas1niveau = ?'
);
$stmt->bind_param('ss', $jaar, $niveau);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resDaypart, $resKlas1Nummer, $resDocent1, $resDocent2, $resLokaal1, $resLokaal2, $resLaptop, $resProjectCode, $resNote, $resID);
while ($stmt->fetch()) {
  if (!isset($out->$resDaypart)) {
    $out->$resDaypart = array();
  }
  $obj = new stdClass;
  $klasObj = new stdClass;
  $klasObj->jaar = $jaar;
  $klasObj->niveau = $niveau;
  $klasObj->nummer = $resKlas1Nummer;

  $obj->klas = $klasObj;

  $obj->docent = [$resDocent1, $resDocent2];

  $obj->lokaal = [$resLokaal1, $resLokaal2];

  $obj->laptop = $resLaptop;
  $obj->projectCode = $resProjectCode;
  $obj->note = $resNote;
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
}
else {
  $json = json_encode($out);
}
//output JSON en stop execution
die($json);
?>