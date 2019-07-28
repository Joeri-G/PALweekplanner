<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
require('funcLib.php');
if (!_GETIsset(['mode', 'selector'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET");
}
require('db-connect.php');

//de query hangt af van de "mode"
if ($_GET['mode'] === 'klas') {
  $klasJaar = (int) substr($_GET['selector'], 0, 1);
  $klasNiveau = substr($_GET['selector'], 1, -1);
  $klasNummer = substr($_GET['selector'], -1);
  $stmt = $conn->prepare('SELECT
      daypart,
      docent1,
      docent2,
      klas1jaar,
      klas1niveau,
      klas1nummer,
      klas2jaar,
      klas2niveau,
      klas2nummer,
      lokaal1,
      lokaal2,
      laptops,
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
    OR
      (
        klas2jaar = ?
        AND klas2niveau = ?
        AND klas2nummer = ?
      )
    ');
    var_dump($conn);
    $stmt->bind_param('isiisi', $klasJaar, $klasNiveau, $klasNummer, $klasJaar, $klasNiveau, $klasNummer);
}
else if ($_GET['mode'] === 'docent') {
  $stmt = $conn->prepare('SELECT daypart, docent1, docent2, klas1jaar, klas1niveau, klas1nummer, klas2jaar, klas2niveau, klas2nummer, lokaal1, lokaal2, laptops, notes, ID FROM week WHERE docent1 = ? OR docent2 = ?');
  //bind params
  $stmt->bind_param('ss', $_GET['selector'], $_GET['selector']);
}
else {
  die('[MODE] INVALID');
}


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
  $resKlas2jaar,
  $resKlas2niveau,
  $resKlas2nummer,
  $resLokaal1,
  $resLokaal2,
  $resLaptop,
  $resNote,
  $resID
);
while($stmt->fetch()) {
  //object daarin alle data en loop er door zolang er nog entries terug komen
  $data->$resDaypart = new ArrayObject;
  $data->$resDaypart['docent'] = array($resDocent1, $resDocent2);
  // $data->$resDaypart['klas'] = array(
  //   'jaar' => [$resKlas1jaar, $resKlas2jaar],
  //   'niveau' => [$resKlas1niveau, $resKlas2niveau],
  //   'nummer' => [$resKlas1nummer, $resKlas2nummer]
  // );
  $data->$resDaypart['klas'] = array(
    $resKlas1jaar.$resKlas1niveau.$resKlas1nummer,
    $resKlas2jaar.$resKlas2niveau.$resKlas2nummer
  );
  $data->$resDaypart['lokaal'] = array($resLokaal1, $resLokaal2);
  $data->$resDaypart['laptop'] = $resLaptop;
  $data->$resDaypart['note'] = $resNote;
  $data->$resDaypart['ID'] = $resID;
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
