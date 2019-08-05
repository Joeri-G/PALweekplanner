<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om afspraken van een klas te lezen
erg vergelijkbaar met readDocent
  - check of inputs gezet zijn (jaar, niveau, nummer)
  - doe SQL query en select alle afspraken waar jaar, niveau en nummer matchen
    * voeg afspraak toe aan dagdeel object
  - encode out object naar JSON
  - output JSON
*/
require('funcLib.php');
if (!_GETIsset(['jaar', 'niveau', 'nummer'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET");
}
require('db-connect.php');
$klasJaar = $_GET['jaar'];
$klasNiveau = $_GET['niveau'];
$klasNummer = $_GET['nummer'];
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
  FROM
    week
  WHERE
    (
      klas1jaar = ?
      AND klas1niveau = ?
      AND klas1nummer = ?
    )
  /*OR
    (
      klas2jaar = ?
      AND klas2niveau = ?
      AND klas2nummer = ?
    )*/
  ');
$stmt->bind_param(/*'isiisi'*/'isi', $klasJaar, $klasNiveau, $klasNummer/*, $klasJaar, $klasNiveau, $klasNummer*/);
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
