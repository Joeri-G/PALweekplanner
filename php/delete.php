<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
de delete function verwijdert niet definitief rooster entries, hij verplaatst ze naar een nieuwe table, planner.deleted
op deze manier is het gemakkelijk voor de sysadmins om fouten terug te zetten en kan een gebruiker die alles verwijdert makkelijk geidentificeerdd worden

  - check voor input
  - laad alle data van die afspraak
  - input alle data in de deleted database
  - delete de afspraak uit de 'week' database
*/
require('funcLib.php');
if (!_GETIsset(['ID'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}
echo "[INPUT] OK\n";
//maak variabelen voor ID en Username
$ID = (string) $_GET['ID'];

//check of opgegeven ID bestaat
require('db-connect.php');
//select 1 omdat we alleen willen weten of de klas bestaat en geen aanvullende data nodig hebben
$stmt = $conn->prepare('SELECT daypart,
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
  USER
  FROM week WHERE ID = ?');
$stmt->bind_param('i', $ID);
$stmt->execute();
$stmt->store_result();
//als de query niets returned dan bestaat de afspraak niet
if ($stmt->num_rows !== 1) {
  $stmt->close();
  $conn->close();
  die("[ID] AFSPRAAK BESTAAT NIET\n");
}
echo "[ID] OK\n\nINSERTING...\n";

$stmt->bind_result(
  $resDaypart,
  $resDocent1,
  $resDocent2,
  $resKlas1jaar,
  $resKlas1niveau,
  $resKlas1nummer,
  /*$resKlas2jaar,
  $resKlas2niveau,
  $resKlas2nummer,*/
  $resLokaal1,
  $resLokaal2,
  $resLaptop,
  $resProjectCode,
  $resNote,
  $resUser
);
$stmt->fetch();
$stmt->close();
//maak nu een nieuwe query om alle data in de andere table te inserten
$stmt = $conn->prepare('INSERT INTO deleted (
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
  userCreate,
  userDelete,
  IP
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?/*, ?, ?, ?*/)');
echo "$conn->error";
$stmt->bind_param(
  //"sssssssssssssssss",
  "ssssssssssssss",
  $resDaypart,
  $resDocent1,
  $resDocent2,
  $resKlas1jaar,
  $resKlas1niveau,
  $resKlas1nummer,
  /*$resKlas2jaar,
  $resKlas2niveau,
  $resKlas2nummer,*/
  $resLokaal1,
  $resLokaal2,
  $resLaptop,
  $resProjectCode,
  $resNote,
  $resUser,
  $_SESSION['username'],
  $_SERVER["REMOTE_ADDR"]
);
$stmt->execute();

//if an error occurs
if ($conn->error !== '') {
  $stmt->close();
  $conn->close();
  die("[INSERT] FAILED\n");
}
echo "[INSERT] OK\n";
$stmt->close();
//nu de data in de nieuwe table staat kunnen we het uit de oude verweideren
echo "OUDE AFSPRAAK WORDT VERWIJDERD\n";
$stmt = $conn->prepare('DELETE FROM week WHERE ID=?');
$stmt->bind_param('i', $ID);
$stmt->execute();
$stmt->store_result();
if ($conn->error !== '') {
  $stmt->close();
  $conn->close();
  die("[DELETE] FAILED\n");
}
$stmt->close();
$conn->close();
die("[DELETE] OK\n");
 ?>
