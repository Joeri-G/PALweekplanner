<?php
/*
we willen een lijst van alles wat vrij is op een gegeven tijdstip
hiervoor moeten we JOINS gebruiken in SQL om tussen tables te vergelijken

de SQL gaat op iets dergelijks lijken
voor docent
SELECT
  username
FROM
  users
WHERE
  role = 'docent'
  AND username NOT IN (
    SELECT
      docent1
    FROM
      week
    WHERE
      daypart = 'MA2'
  )
  AND username NOT IN (
    SELECT
      docent2
    FROM
      week
    WHERE
      daypart = 'MA2'
  );


voor klassen
SELECT
  jaar,
  niveau,
  nummer
FROM
  klassen
WHERE
  jaar NOT IN (
    SELECT
      klas1jaar
    FROM
      week
    WHERE
      daypart = 'DI1'
  )
  AND jaar NOT IN (
    SELECT
      klas2jaar
    FROM
      week
    WHERE
      daypart = 'DI1'
  )
  AND jaar NOT IN (
    SELECT
      klas2niveau
    FROM
      week
    WHERE
      daypart = 'DI1'
  )
  AND jaar NOT IN (
    SELECT
      klas2niveau
    FROM
      week
    WHERE
      daypart = 'DI1'
  )
  AND nummer NOT IN (
    SELECT
      klas2nummer
    FROM
      week
    WHERE
      daypart = 'DI1'
  )
  AND nummer NOT IN (
    SELECT
      klas2nummer
    FROM
      week
    WHERE
      daypart = 'DI1'
  );
*/
require('funcLib.php');
//maak object voor output
$out = new stdClass;
$out->docent = new stdClass;
$out->klas = new stdClass;
$out->lokaal = new stdClass;
//maak list met alle dagdelen
$data = file_get_contents('../conf/conf.json');
$conf = json_decode($data);
$dagdelen = array();

for ($i=0; $i < count($conf->dagen); $i++) {
  for ($x=0; $x < $conf->uren; $x++) {
    $dagdelen[] = $conf->dagen[$i]."$x";
  }
}

require('db-connect.php');

//<TMP FIX>
$klassenAll = array();
//list alle vrije klassen per dagdeel
$stmt = $conn->prepare('SELECT jaar, niveau, nummer FROM klassen;');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resJaar, $resNiveau, $resNummer);
while ($stmt->fetch()) {
  $klasObject = new stdClass;
  $klasObject->jaar = $resJaar;
  $klasObject->niveau = $resNiveau;
  $klasObject->nummer = $resNummer;
  $klassenAll[] = $klasObject;
}
$stmt->close();

//laad nu de rooster data
$klas1 = new stdClass;
// $klas2 = new stdClass;
$klassenBezet = new stdClass;
$stmt = $conn->prepare('SELECT
  klas1jaar,
  klas1niveau,
  klas1nummer,
  /*klas2jaar,
  klas2niveau,
  klas2nummer,*/
  daypart
  FROM week');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result(
  $klas1->jaar,
  $klas1->niveau,
  $klas1->nummer,
  // $klas2->jaar,
  // $klas2->niveau,
  // $klas2->nummer,
  $resDaypart);
while ($stmt->fetch()) {
  if (!isset($resDaypart)) {
    $klassenBezet->$resDaypart = array();
  }
  if (notNone($klas1->niveau)) {
    $klassenBezet->$resDaypart[] = $klas1;
  }
  // if (notNone($klas2->niveau)) {
  //   $klassenBezet->$resDaypart[] = $klas2;
  // }
}
//</TMP FIX>

//loop door de dagdelen en query de database
for ($x=0; $x < count($dagdelen); $x++) {
  $dagdeelTMP = $dagdelen[$x];
  //list alle vrije docenten per dagdeel
  $stmt = $conn->prepare("SELECT
  username, userAvailability
FROM
  users
WHERE
  role = 'docent'
  AND username NOT IN (
    SELECT
      docent1
    FROM
      week
    WHERE
      daypart = ?
  )
  AND username NOT IN (
    SELECT
      docent2
    FROM
      week
    WHERE
      daypart = ?
  );");
  $stmt->bind_param('ss', $dagdeelTMP, $dagdeelTMP);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($resDocent, $resUserAvailability);
  //maak array voor resulsts
  $out->docent->$dagdeelTMP = array();
  $out->klas->$dagdeelTMP = array();
  //loop door de results
  while ($stmt->fetch()) {
    //nu moeten we checken of de docent op dat tijdstip wel beschikbaar is
    $beschikbaar = json_decode($resUserAvailability);
    //haal de dag uit $daypart
    $dag = substr($dagdeelTMP, 0, 2);
    //check of er een key is (zou er moeten zijn maar idk wat de sysadmin allemaal gaat doen)
    if (isset($beschikbaar->$dag) && $beschikbaar->$dag == true) {
      //plaats ieder result in de docenten array in het $out object
      $out->docent->$dagdeelTMP[] = $resDocent;
    }
  }
  $stmt->close();

  for ($i=0; $i < count($klassenAll); $i++) {
    if (!isset($klassenBezet->$dagdeelTMP) || !in_array($klassenAll[$i], $klassenBezet->$dagdeelTMP)) {
      if (!isset($out->klas->$dagdeelTMP)) {
        $out->klas->$dagdeelTMP = array();
      }
      $out->klas->$dagdeelTMP[] = $klassenAll[$i];
    }
  }


  //list alle vrije lokalen per dagdeel
  $stmt = $conn->prepare('SELECT
  lokaal
FROM
  lokalen
WHERE
  lokaal NOT IN (
    SELECT
      lokaal1
    FROM
      week
    WHERE
      daypart = ?
  )
  AND lokaal NOT IN (
    SELECT
      lokaal2
    FROM
      week
    WHERE
      daypart = ?
  );
');
  $stmt->bind_param('ss', $dagdeelTMP, $dagdeelTMP);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($resLokaal);
  while ($stmt->fetch()) {
    $out->lokaal->$dagdeelTMP[] = $resLokaal;
  }
  $stmt->close();
}
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
