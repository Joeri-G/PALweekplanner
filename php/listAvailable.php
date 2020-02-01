<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
//functie om klassen te vergelijken
function compareKlas($k1, $k2)
{
    if ($k1->j === $k2->j && $k1->ni === $k2->ni && $k2->nu === $k2->nu) {
        return true;
    }
    return false;
}
require('funcLib.php');
//maak output variable
$out = new stdClass;
$out->d = new stdClass;   //docenten
$out->k = new stdClass;   //klassen
$out->l = new stdClass;   //lokalen
$out->p = [];             //projecten
//maak array met dagdelen
$dagen = getConf()->dagen;
$uren = getConf()->uren;
$dagdelen = [];
foreach ($dagen as $dag) {
    for ($uur=0; $uur < $uren; $uur++) {
        $dagdelen[] = $dag.$uur;
    }
}

require('db-connect.php');
//select alle vrije klassen per dagdeel
foreach ($dagdelen as $dagdeel) {
    //klassen
    $out->k->$dagdeel = [];   //alle klassen zijn beschikbaar, de bezette klassen worden er laten tussenuit gehaald
    $out->d->$dagdeel = [];
    $out->l->$dagdeel = [];
    $stmt = $conn->prepare(
      "SELECT
        DISTINCT jaar,
        klasNaam
      FROM
        klassen
      WHERE
        klasNaam NOT IN (
          SELECT
            klas
          FROM
            week
          WHERE
            daypart = ?
        )
        ");
    $stmt->bind_param('s', $dagdeel);
    $stmt->execute();
    $stmt->bind_result($resJaar, $resNaam);
    while ($stmt->fetch()) {
      $klas = new stdClass;
      $klas->j = $resJaar;
      $klas->n = $resNaam;

      $out->k->$dagdeel[] = $klas;
    }
    $stmt->close();
    //docenten
    //omdat een docent niet altijd beschikbaar is moet de "userAvailability" nu meegerekend worden
    //vind dag door index van dagdeel in dagdelen array te nemen, deze te delen door het aantal uren en dit naar beneden af te ronden
    $dagdeelIndex = array_search($dagdeel, $dagdelen);
    $offset = floor($dagdeelIndex / $uren);
    $dag = $dagen[$offset];

    $stmt = $conn->prepare(
        "SELECT DISTINCT afkorting,
                userAvailability
      FROM docenten
      WHERE afkorting NOT IN
          (SELECT docent1
           FROM week
           WHERE daypart = ? )
        AND afkorting NOT IN
          (SELECT docent2
           FROM week
           WHERE daypart = ? );"
    );
    $stmt->bind_param("ss", $dagdeel, $dagdeel);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($resDocent, $resUserAvailability);
    while ($stmt->fetch()) {
        $availability = json_decode($resUserAvailability);
        //check of de docent beschikbaar is op de dag
        if (isset($availability->$dag) && $availability->$dag == true) {
            $out->d->$dagdeel[] = $resDocent;
        }
    }
    $stmt->close();
    //list alle vrije lokalen per dagdeel
    $stmt = $conn->prepare(
        "SELECT DISTINCT
        lokaal
      FROM lokalen
      WHERE lokaal NOT IN (SELECT
        lokaal1
      FROM week
      WHERE daypart = ?)
      AND lokaal NOT IN (SELECT
        lokaal2
      FROM week
      WHERE daypart = ?);"
    );
    $stmt->bind_param('ss', $dagdeel, $dagdeel);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($resLokaal);
    while ($stmt->fetch()) {
        $out->l->$dagdeel[] = $resLokaal;
    }
    $stmt->close();
}
//selecteer projectCodes
//buiten loop omdat deze altijd beschikbaar zijn
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
