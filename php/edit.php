<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om afspraak te editen

check of alles mogelijk is, delete de oude afspraak en maak een nieuwe

 */

 require("funcLib.php");

 //check of alle velder ingevuld zijn
 //lege velden mogen niet, in plaats daar van moet er None worden ingevuld
 if (!_GETIsset(["daypart","lokaal1", "lokaal2", "klas1jaar", "klas1niveau", "klas1nummer", "docent1", "docent2", "laptops", "id", "projectCode"])) {
     die("[INPUT]\tNOT ALL PARAMETERS SET");
 }

 $id = toNum($_GET["id"]);
 $daypart = $_GET["daypart"];

 $docent1 = $_GET["docent1"];
 $docent2 = $_GET["docent2"];

 $klas1 = new stdClass;
 $klas1->jaar = $_GET["klas1jaar"];
 $klas1->niveau = $_GET["klas1niveau"];
 $klas1->nummer = $_GET["klas1nummer"];

 $lokaal1 = $_GET["lokaal1"];
 $lokaal2 = $_GET["lokaal2"];

 //zorg er voor dat laptops een int is
 $laptops = toNum($_GET["laptops"]);

 //omdat sommige browsers geen leeg item in de url plaatsen worden notes zo gedaan
 $note = "None";
 if (isset($_GET["note"]) && !empty($_GET['note'])) {
     $note = $_GET["note"];
 }

$projectCode = $_GET['projectCode'];


 $docentenGroep = array($docent1, $docent2);
 $lokalenGroep = array($lokaal1, $lokaal2);

 //zorg dat voor de klas of een hele klas gezet is of de hele klas None is
 $klassenGroep = checkKlas(array($klas1));

 if (!isPossibleKlas($klassenGroep)) {
     die("[DOCENTEN] MINIMAAL EEN VAN DE GESELECTEERDE KLASSEN MOET NIET NONE ZIJN");
 }
 //check of projectCode wel gezet is
 if (!notNone($projectCode)) {
     die("[PROJECTEN] VUL PROJECT IN");
 }
 //check of het dagdeel wel mogelijk is
 if (!daypartCheck($daypart)) {
     die("[DAGDEEL] BESTAAT NIET\n\nTERMINATING...");
 }

 //connect met database
 require("db-connect.php");
 //check of ID wel bestaat
 $stmt = $conn->prepare("SELECT ID FROM week WHERE ID = ?");
 $stmt->bind_param("i", $id);
 $stmt->execute();
 $stmt->store_result();
 $stmt->bind_result($resID);
 $stmt->fetch();
 if ($conn->affected_rows < 1) {
     //sluit alles
     $stmt->close();
     $conn->close();
     die("[ID] INVALID");
 }
 $stmt->close();
 //check of er wel genoeg laptops beschikbaar zijn
 $totalLaptops = getConf()->laptops;

 $takenLaptops = 0;

 $stmt = $conn->prepare("SELECT laptops FROM week WHERE daypart = ? AND ID != ?");
 $stmt->bind_param("si", $daypart, $id);
 $stmt->execute();
 $stmt->store_result();
 $stmt->bind_result($l);
 while ($stmt->fetch()) {
     //loop door bestaande afspraken en voeg ze toe aan het aantal bezette laptops
     $takenLaptops += $l;
 }
 $stmt->close();

 if ($laptops > $totalLaptops - $takenLaptops) {
     $conn->close();
     $limit = $totalLaptops - $takenLaptops;
     if ($limit < 0) {
         $limit = 0;
     }
     die("[LAPTOPS] NOT ENOUGH LAPTOPS AVAILABLE, $limit OUT OF $totalLaptops LEFT");
 }


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
 //exclude de huidige ID
 //gebruik prepared statement om SQL injections te vermijden
 $stmt = $conn->prepare("SELECT docent1, docent2, klas1jaar, klas1niveau, klas1nummer, lokaal1, lokaal2, id FROM week WHERE `daypart` = ? AND ID != ?");
 $stmt->bind_param("si", $daypart, $id);

 //execute SQL query
 $stmt->execute();

 $stmt->store_result();

 //res voor result
 //maak legen objects voor result klassen
 $resKlas1 = new stdClass;

 //bind alle results aan variabelen
 $stmt->bind_result(
     $resDocent1,
     $resDocent2,
     $resKlas1->jaar,
     $resKlas1->niveau,
     $resKlas1->nummer,
     // $resKlas2->jaar,
     // $resKlas2->niveau,
     // $resKlas2->nummer,num
     $resLokaal1,
     $resLokaal2,
     $resID
 );

 while ($stmt->fetch()) {
     //vergelijk input met opgegeven variabelen
     //place docent, klas and lokaal in groups since klas2 and klas2 will both have to be checked against resKlas1 and resKlas2
     $resDocentenGroep = array($resDocent1, $resDocent2);

     $resKlassenGroep = array($resKlas1);

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

 $stmt->close();

 //update record
 $stmt = $conn->prepare("UPDATE
  week
SET
  daypart = ?,
  docent1 = ?,
  docent2 = ?,
  klas1jaar = ?,
  klas1niveau = ?,
  klas1nummer = ?,
  lokaal1 = ?,
  lokaal2 = ?,
  laptops = ?,
  projectCode = ?,
  notes = ?,
  `USER` = ?,
  `TIME` = ?,
  `IP` = ?
WHERE
  ID = ?
");

$timestamp = date('Y-m-d H:i:s');


$stmt->bind_param(
  //"ssssssssssssssss",
  "sssssssssssssss",
    $daypart,
    $docent1,
    $docent2,
    $klas1->jaar,
    $klas1->niveau,
    $klas1->nummer,
    $lokaal1,
    $lokaal2,
    $laptops,
    $projectCode,
    $note,
    $_SESSION["username"],
    $timestamp,
    $_SERVER["REMOTE_ADDR"], //client IP
    $id
);

$stmt->execute();

if ($conn->error !== "") {
    echo "[ERROR] $conn->error";
}

 //sluit alles
 $stmt->close();
 $conn->close();

 die();
