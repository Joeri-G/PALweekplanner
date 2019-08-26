<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
we willen een lijst van alles wat vrij is op een gegeven tijdstip
  - maak een list met alle dagdelen
  - maak een list met alle klassen
  - maak een list met alle klassen die bezet zijn
  - maak list met alle projectCodes
  - loop door dagdelen
    * query alle docenten die niet bezet zijn op het huidige dagdeel
      + als de ook beschikbaar zijn, voeg ze toe aan het Available object onder het correcte dagdeel
    * query alle klassen die op dat dagdeel reregistreerd zijn
      + loop door de list met klassen
        ~ als de klas niet voor komt in de list met bezette klassen onder het huidige uur ($klassenBezet->[dagdeel]->[klasObject]) voeg klas dan toe aan output
    * query alle lokalen die niet bezet zijn op het huidige dagdeel
  - encode object naar JSON
  - output JSON
*/
require('funcLib.php');
//maak object voor output
$out = new stdClass;
$out->d = new stdClass;
$out->k = new stdClass;
$out->l = new stdClass;
$out->p = array();
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
$stmt = $conn->prepare('SELECT DISTINCT jaar, niveau, nummer FROM klassen;');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resJaar, $resNiveau, $resNummer);
while ($stmt->fetch()) {
    $klasObject = new stdClass;
    $klasObject->j = $resJaar;
    $klasObject->ni = $resNiveau;
    $klasObject->nu = $resNummer;
    $klassenAll[] = $klasObject;
}
$stmt->close();

//laad nu de rooster data
$klas1 = new stdClass;
// $klas2 = new stdClass;
$klassenBezet = new stdClass;
$stmt = $conn->prepare('SELECT DISTINCT
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
    $klas1->j,
    $klas1->ni,
    $klas1->nu,
  // $klas2->jaar,
  // $klas2->niveau,
  // $klas2->nummer,
  $resDaypart
);
while ($stmt->fetch()) {
    if (!isset($resDaypart)) {
        $klassenBezet->$resDaypart = array();
    }
    if (notNone($klas1->ni)) {
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
    $stmt = $conn->prepare("SELECT DISTINCT
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
    $out->d->$dagdeelTMP = array();
    $out->k->$dagdeelTMP = array();
    //loop door de results
    while ($stmt->fetch()) {
        //nu moeten we checken of de docent op dat tijdstip wel beschikbaar is
        $beschikbaar = json_decode($resUserAvailability);
        //haal de dag uit $daypart
        $dag = substr($dagdeelTMP, 0, 2);
        //check of er een key is (zou er moeten zijn maar idk wat de sysadmin allemaal gaat doen)
        if (isset($beschikbaar->$dag) && $beschikbaar->$dag == true) {
            //plaats ieder result in de docenten array in het $out object
            $out->d->$dagdeelTMP[] = $resDocent;
        }
    }
    $stmt->close();

    for ($i=0; $i < count($klassenAll); $i++) {
        if (!isset($klassenBezet->$dagdeelTMP) || !in_array($klassenAll[$i], $klassenBezet->$dagdeelTMP)) {
            if (!isset($out->klas->$dagdeelTMP)) {
                $out->k->$dagdeelTMP = array();
            }
            $out->k->$dagdeelTMP[] = $klassenAll[$i];
        }
    }


    //list alle vrije lokalen per dagdeel
    $stmt = $conn->prepare('SELECT DISTINCT
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
        $out->l->$dagdeelTMP[] = $resLokaal;
    }
    $stmt->close();
}

//selecteer projectCodes
$stmt = $conn->prepare('SELECT DISTINCT projectCode FROM projecten');
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resProjectCode);
while ($stmt->fetch()) {
    $out->p[] = $resProjectCode;
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
