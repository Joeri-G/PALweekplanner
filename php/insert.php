<?php
//script om roosterdata in database te 'inserten'
//stap 1: check of alle benodigde data gegeven is (een of meer docenten, klassen, lokalen en een dagdeel)
//stap 2: verwerk de data
//        2.1 check of de lokalen, docenten en klassen bestaan
//        2.2 check of de lokalen, docenten of klassen al bezet zijn
//stap 3: push naar database
require('funcLib.php');

//check of alle velder ingevuld zijn
//lege velden mogen niet, in plaats daar van moet er None worden ingevuld
if (!_GETIsset(['daypart', 'lokaal1', 'lokaal2', 'klas1jaar', 'klas1niveau', 'klas1nummer', 'klas2jaar', 'klas2niveau', 'klas2nummer', 'docent1', 'docent2', 'laptops'])) {
  die('Not all parameters set');
}

//api.php?insert=true&daypart=MA0&lokaal1=101&lokaal2=None&klas1jaar=None&klas1niveau=V&klas1nummer=None&klas2jaar=None&klas2niveau=None&klas2nummer=None&docent1=None&docent2=BEG&laptops=2


$daypart = $_GET['daypart'];

$docent1 = $_GET['docent1'];
$docent2 = $_GET['docent2'];

$klas1 = new stdClass;
$klas1->jaar = $_GET['klas1jaar'];
$klas1->niveau = $_GET['klas1niveau'];
$klas1->nummer = $_GET['klas1nummer'];

$klas2 = new stdClass;
$klas2->jaar = $_GET['klas2jaar'];
$klas2->niveau = $_GET['klas2niveau'];
$klas2->nummer = $_GET['klas2nummer'];

$lokaal1 = $_GET['lokaal1'];
$lokaal2 = $_GET['lokaal2'];

$laptops = $_GET['laptops'];

//omdat sommige browsers geen leeg item in de url plaatsen worden notes zo gedaan
$note = '';
if (isset($_GET['note'])) {$note = $_GET['note'];}


$docentenGroep = array($docent1, $docent2);
$lokalenGroep = array($lokaal1, $lokaal2);

//zorg dat voor de klas of een hele klas gezet is of de hele klas None is
$klassenGroep = checkKlas(array($klas1, $klas2));

//Op zijn minst een van de gegeven inputs moet niet none zijn
if (!isPossible($docentenGroep)) {
  die('Minimaal een van de geselecteerde docenten moet niet None zijn');
}
//de klas is anders omdat dit al een object is
if (!isPossibleKlas($klassenGroep)) {
  die('Minimaal een van de geselecteerde klassen moet niet None zijn');
}
if (!isPossible($lokalenGroep)) {
  die('Minimaal een van de geselecteerde lokalen moet niet None zijn');
}
echo "[DATA] OK\n";


//connect met database
require('db-connect.php');

//laad alle data van het geselecteerde dagdeel
//gebruik prepared statement om SQL injections te vermijden
$stmt = $conn->prepare('SELECT docent1, docent2, klas1jaar, klas1niveau, klas1nummer, klas2jaar, klas2niveau, klas2nummer, lokaal1, lokaal2 FROM week WHERE `daypart` = ?');
$stmt->bind_param('s', $daypart);

//execute SQL query
$stmt->execute();

$stmt->store_result();

//res voor result
//maak legen objects voor result klassen
$resKlas1 = new stdClass;
$resKlas2 = new stdClass;

//bind alle results aan variabelen
$stmt->bind_result(
  $resDocent1,
  $resDocent2,
  $resKlas1->jaar,
  $resKlas1->niveau,
  $resKlas1->nummer,
  $resKlas2->jaar,
  $resKlas2->niveau,
  $resKlas2->nummer,
  $resLokaal1,
  $resLokaal2
);

while ($stmt->fetch()) {
  //vergelijk input met opgegeven variabelen
  //place docent, klas and lokaal in groups since klas2 and klas2 will both have to be checked against resKlas1 and resKlas2
  $resDocentenGroep = array($resDocent1, $resDocent2);

  $resKlassenGroep = array($resKlas1, $resKlas2);

  $resLokalenGroep = array($resLokaal1, $resLokaal2);

  if(isOverlap($docentenGroep, $resDocentenGroep)) {
    $stmt->close();
    $conn->close();
    die('Een of meer van de geselecteerde docenten is bezet op het gekozen tijdstip');
  }
  if(isOverlapKlas($klassenGroep, $resKlassenGroep)) {
    $stmt->close();
    $conn->close();
    die('Een of meer van de geselecteerde klassen is bezet op het gekozen tijdstip');
  }
  if(isOverlap($lokalenGroep, $resLokalenGroep)) {
    $stmt->close();
    $conn->close();
    die('Een of meer van de geselecteerde lokalen is bezet op het gekozen tijdstip');
  }
}
//nu we zeker weten dat er geen overlap is kunnen we de data in de database 'inserten'
$stmt = $conn->prepare('INSERT INTO week (daypart, docent1, docent2, klas1jaar, klas1niveau, klas1nummer, klas2jaar, klas2niveau, klas2nummer, lokaal1, lokaal2, laptops, notes, `USER`, `IP`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

//s voor string en i voor integer
$stmt->bind_param(
  'sssisiisissssss',
  $daypart,
  $docent1,
  $docent2,
  $klas1->jaar,
  $klas1->niveau,
  $klas1->nummer,
  $klas2->jaar,
  $klas2->niveau,
  $klas2->nummer,
  $lokaal1,
  $lokaal2,
  $laptops,
  $note,
  $_SESSION['username'],
  $_SERVER['REMOTE_ADDR']
);

//execute query
$stmt->execute();

//sluit alles
$stmt->close();
$conn->close();

echo "[INSERT] OK";

 ?>
