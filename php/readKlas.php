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
if (!_GETIsset(['klas'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET");
}
require('db-connect.php');
$klas = $_GET['klas'];
$stmt = $conn->prepare('SELECT
    daypart,
    docent1,
    docent2,
    klas,
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
      klas = ?
    )
  ');
$stmt->bind_param('s', $klas);
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
    $resKlas,
    $resLokaal1,
    $resLokaal2,
    $resLaptop,
    $resProjectCode,
    $resNote,
    $resID
);
while ($stmt->fetch()) {
    $klas1 = new stdClass;
    //object daarin alle data en loop er door zolang er nog entries terug komen
    $data->$resDaypart = new stdClass;
    $data->$resDaypart->d = array($resDocent1, $resDocent2);
    $klas1->n = $resKlas;
    $data->$resDaypart->k = array($klas1);
    $data->$resDaypart->l = array($resLokaal1, $resLokaal2);
    $data->$resDaypart->la = $resLaptop;
    $data->$resDaypart->p = $resProjectCode;
    $data->$resDaypart->no = $resNote;
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
} else {
    $json = json_encode($data);
}
//output JSON en stop execution
die($json);
