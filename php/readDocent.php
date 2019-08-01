<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
require('funcLib.php');
if (!_GETIsset(['docent'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET");
}
require('db-connect.php');

$stmt = $conn->prepare('SELECT
  daypart,
  docent1,
  docent2,
  klas1jaar,
  klas1niveau,
  klas1nummer,
  /*klas2jaar,
  klas2niveau,
  klas2nummer,*/
  lokaal1,
  lokaal2,
  laptops,
  projectCode,
  notes,
  ID
  FROM week
  WHERE docent1 = ?
  OR docent2 = ?');
//bind params
$stmt->bind_param('ss', $_GET['docent'], $_GET['docent']);

//execute query
$stmt->execute();


$stmt->store_result();
//res voor result

//maak leeg object
$data = new stdClass;

$stmt->bind_result(
  $resDaypart,
  $resDocent1,
  $resDocent2,
  $resKlas1jaar,
  $resKlas1niveau,
  $resKlas1nummer,
  // $resKlas2jaar,
  // $resKlas2niveau,
  // $resKlas2nummer,
  $resLokaal1,
  $resLokaal2,
  $resLaptop,
  $resProjectCode,
  $resNote,
  $resID
);

while($stmt->fetch()) {
  $klas1 = new stdClass;
  // $klas2 = new stdClass;
  //object daarin alle data en loop er door zolang er nog entries terug komen
  $data->$resDaypart = new stdClass;
  $data->$resDaypart->docent = array($resDocent1, $resDocent2);

  $klas1->jaar = $resKlas1jaar;
  $klas1->niveau = $resKlas1niveau;
  $klas1->nummer = $resKlas1nummer;

  // $klas2->jaar = $resKlas2jaar;
  // $klas2->niveau = $resKlas2niveau;
  // $klas2->nummer = $resKlas2nummer;

  $data->$resDaypart->klas = array(
    $klas1,
    // $klas2
  );
  $data->$resDaypart->lokaal = array($resLokaal1, $resLokaal2);
  $data->$resDaypart->laptop = $resLaptop;
  $data->$resDaypart->projectCode = $resProjectCode;
  $data->$resDaypart->note = $resNote;
  $data->$resDaypart->ID = $resID;
}
$stmt->close();

$conn->close();

//zet header
header('Content-Type: application/json');

//als als input ?format is gezet doe dan prettyp print
//we doen dit niet meteen omdat het het bestand aanzienlijk groter maakt
if (isset($_GET['format']) && $_GET['format'] == 'true') {
  $json = json_encode($data, JSON_PRETTY_PRINT);
}
else {
  $json = json_encode($data);
}
//output JSON en stop execution
die($json);
 ?>
