<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om roosterdata in database te "inserten"
  - check of alle benodigde data gegeven is (een of meer docenten, klassen, lokalen en een dagdeel)
  - verwerk de data
    * check of de lokalen, docenten en klassen bestaan
    * check of de lokalen, docenten of klassen al bezet zijn
  - push naar database


!important
klas2 is uitgeschakeld omdat deze niet nodig was
als klas2 weer nodig is kan deze vrij gemakkelijk aangezet worden door te uncommenten
*/
require("funcLib.php");

//check of alle velder ingevuld zijn
//lege velden mogen niet, in plaats daar van moet er None worden ingevuld
if (!_GETIsset(["daypart", "lokaal1", "lokaal2", "klas1jaar", "klas1niveau", "klas1nummer", /*"klas2jaar", "klas2niveau", "klas2nummer",*/ "docent1", "docent2", "laptops"])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET");
}
$daypart = $_GET["daypart"];

$docent1 = $_GET["docent1"];
$docent2 = $_GET["docent2"];

$klas1 = new stdClass;
$klas1->jaar = $_GET["klas1jaar"];
$klas1->niveau = $_GET["klas1niveau"];
$klas1->nummer = $_GET["klas1nummer"];

// $klas2 = new stdClass;
// $klas2->jaar = $_GET["klas2jaar"];
// $klas2->niveau = $_GET["klas2niveau"];
// $klas2->nummer = $_GET["klas2nummer"];

$lokaal1 = $_GET["lokaal1"];
$lokaal2 = $_GET["lokaal2"];

$laptops = $_GET["laptops"];

//omdat sommige browsers geen leeg item in de url plaatsen worden notes en project codes zo gedaan
$note = "None";
$projectCode = "None";
if (isset($_GET["note"]) && !empty($_GET['note'])) {
    $note = $_GET["note"];
}
if (isset($_GET["projectCode"]) && !empty($_GET['projectCode'])) {
    $projectCode = $_GET['projectCode'];
}

$docentenGroep = array($docent1, $docent2);
$lokalenGroep = array($lokaal1, $lokaal2);

//zorg dat voor de klas of een hele klas gezet is of de hele klas None is
$klassenGroep = checkKlas(array($klas1/*, $klas2*/));

// //Op zijn minst een van de gegeven inputs moet niet none zijn
// if (!isPossible($docentenGroep)) {
//     die("[DOCENTEN] MINIMAAL EEN VAN DE GESELECTEERDE DOCENTEN MOET NIET NONE ZIJN");
// }
// //de klas is anders omdat dit al een object is
// if (!isPossibleKlas($klassenGroep)) {
//     die("[DOCENTEN] MINIMAAL EEN VAN DE GESELECTEERDE KLASSEN MOET NIET NONE ZIJN");
// }
// if (!isPossible($lokalenGroep)) {
//     die("[DOCENTEN] MINIMAAL EEN VAN DE GESELECTEERDE LOKALEN MOET NIET NONE ZIJN");
// }

//check of projectCode wel gezet is
if (!notNone($projectCode)) {
  die("[PROJECTEN] VUL PROJECT IN");
}


// echo "[INPUT]\tOK\n";

//check of het dagdeel wel mogelijk is
if (!daypartCheck($daypart)) {
    die("[DAGDEEL] BESTAAT NIET\n\nTERMINATING...");
}
// echo "[DAGDEEL] OK\n";

//connect met database
require("db-connect.php");

//check of docent wel bestaat en beschikbaar is op tijdstip
for ($i=0; $i < count($docentenGroep); $i++) {
    if (notNone($docentenGroep[$i])) {
        //maak een query om de userAvailability te selecteren waar de username matcht, de role docent is en de username niet none
        $stmt = $conn->prepare("SELECT userAvailability FROM docenten WHERE afkorting=?");
        $stmt->bind_param("s", $docentenGroep[$i]);
        $stmt->execute();
        $stmt->store_result();
        //als er geen / meer dan 1 hits zijn geef dan een error
        if ($stmt->num_rows !== 1) {
            $stmt->close();
            $conn->close();
            die("[DOCENT] BESTAAT NIET\n");
        }
        //nu we weten dat er een result is kunnen we gaan fetchen
        //res voor result
        $stmt->bind_result($resUserAvailability);
        $stmt->fetch();

        //format waarin de userAvailability komt is een json array {"DAG": BOOL}
        $userAvailability = json_decode($resUserAvailability);
        //haal de dag uit $daypart
        $dag = substr($daypart, 0, 2);
        //check of er een key is (zou er moeten zijn maar idk wat de sysadmin allemaal gaat doen)
        if (!isset($userAvailability->$dag)) {
            $stmt->close();
            $conn->close();
            die("[DOCENTEN] KEY ERROR. PLEASE CONTACT YOUR SYSADMIN");
        }
        if (!$userAvailability->$dag) {
            $stmt->close();
            $conn->close();
            die("[DOCENTEN] DOCENT IS NIET AANWEZIG OP OPGEGEVEN DAG");
        }
        $stmt->close();
    }
}

//check of klas bestaat
for ($i=0; $i < count($klassenGroep); $i++) {
    //plaats in array, checkKlas verwacht een array
    if (notNoneKlas($klassenGroep[$i])) {
        //SELECT ID omdat we alleen willen weten of de klas bestaat en geen aanvullende data nodig hebben
        $stmt = $conn->prepare('SELECT ID FROM klassen WHERE jaar=? AND niveau=? AND nummer=?');
        $stmt->bind_param("isi", $klassenGroep[$i]->jaar, $klassenGroep[$i]->niveau, $klassenGroep[$i]->nummer);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows !== 1) {
            $stmt->close();
            $conn->close();
            die("[KLASSEN] KLAS BESTAAT NIET");
        }
    }
}

//check of lokaal bestaat
for ($i=0; $i < count($lokalenGroep); $i++) {
    if (notNone($lokalenGroep[$i])) {
        //SELECT ID omdat we alleen willen weten of de klas bestaat en geen aanvullende data nodig hebben
        $stmt = $conn->prepare('SELECT ID FROM lokalen WHERE lokaal = ?');
        $stmt->bind_param("s", $lokalenGroep[$i]);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows !== 1) {
            $stmt->close();
            $conn->close();
            die("[LOKALEN] LOKAAL BESTAAT NIET");
        }
    }
}

//laad alle data van het geselecteerde dagdeel
//gebruik prepared statement om SQL injections te vermijden
$stmt = $conn->prepare("SELECT docent1, docent2, klas1jaar, klas1niveau, klas1nummer, /*klas2jaar, klas2niveau, klas2nummer,*/ lokaal1, lokaal2 FROM week WHERE `daypart` = ?");
$stmt->bind_param("s", $daypart);

//execute SQL query
$stmt->execute();

$stmt->store_result();

//res voor result
//maak legen objects voor result klassen
$resKlas1 = new stdClass;
// $resKlas2 = new stdClass;

//bind alle results aan variabelen
$stmt->bind_result(
    $resDocent1,
    $resDocent2,
    $resKlas1->jaar,
    $resKlas1->niveau,
    $resKlas1->nummer,
  // $resKlas2->jaar,
  // $resKlas2->niveau,
  // $resKlas2->nummer,
  $resLokaal1,
    $resLokaal2
);

while ($stmt->fetch()) {
    //vergelijk input met opgegeven variabelen
    //place docent, klas and lokaal in groups since klas2 and klas2 will both have to be checked against resKlas1 and resKlas2
    $resDocentenGroep = array($resDocent1, $resDocent2);

    $resKlassenGroep = array($resKlas1/*, $resKlas2*/);

    $resLokalenGroep = array($resLokaal1, $resLokaal2);

    if (isOverlap($docentenGroep, $resDocentenGroep)) {
        $stmt->close();
        $conn->close();
        die("[DOCENTEN]\tBEZET\n\nTERMINATING...");
    }
    if (isOverlapKlas($klassenGroep, $resKlassenGroep)) {
        $stmt->close();
        $conn->close();
        die("[KLASSEN]\tBEZET\n\nTERMINATING...");
    }
    if (isOverlap($lokalenGroep, $resLokalenGroep)) {
        $stmt->close();
        $conn->close();
        die("[LOKALEN]\tBEZET\n\nTERMINATING...");
    }
}
//feedback
// echo "[DOCENTEN]\tOK\n";
// echo "[KLASSEN]\tOK\n";
// echo "[LOKALEN]\tOK\n";

// echo "\nINSERTING...\n";

$stmt->close();
//nu we zeker weten dat er geen overlap is kunnen we de data in de database "inserten"
$stmt = $conn->prepare("INSERT INTO
  week (
    daypart,
    docent1,
    docent2,
    klas1jaar,
    klas1niveau,
    klas1nummer,/*
    klas2jaar,
    klas2niveau,
    klas2nummer,*/
    lokaal1,
    lokaal2,
    laptops,
    projectCode,
    notes,
    `USER`,
    `IP`
  )
VALUES
  (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?/*, ?, ?, ?*/)
");
//s voor string en i voor integer
echo "$conn->error";
$stmt->bind_param(
  //"ssssssssssssssss",
  "sssssssssssss",
    $daypart,
    $docent1,
    $docent2,
    $klas1->jaar,
    $klas1->niveau,
    $klas1->nummer,
  // $klas2->jaar,
  // $klas2->niveau,
  // $klas2->nummer,
  $lokaal1,
    $lokaal2,
    $laptops,
    $projectCode,
    $note,
    $_SESSION["username"],
    $_SERVER["REMOTE_ADDR"]
);

//execute query
$stmt->execute();
//sluit alles
$stmt->close();
$conn->close();

// die("[INSERT]\tOK");
die();
