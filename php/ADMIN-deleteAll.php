<?php
//script om alle items naar de deleted table te verplaatsen
//check eerst of password klopt
require('funcLib.php');
if (!_POSTIsset(['password'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}
$pwd = $_POST['password'];
//connect met db
require('db-connect.php');

$stmt = $conn->prepare("SELECT password FROM users WHERE ID = ?");
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resPWD);
$stmt->fetch();

if (!password_verify($pwd, $resPWD)) {
  $stmt->close();
  $conn->close();
  die("[PASSWORD] INVALID");
}



$stmt->close();

$stmt = $conn->prepare('SELECT
  daypart,
  docent1,
  docent2,
  klas1jaar,
  klas1niveau,
  klas1nummer,
  lokaal1,
  lokaal2,
  laptops,
  projectCode,
  notes,
  USER
FROM
  week
');

$stmt->execute();
$stmt->store_result();
$stmt->bind_result(
    $daypart,
    $docent1,
    $docent2,
    $klas1jaar,
    $klas1niveau,
    $klas1nummer,
    $lokaal1,
    $lokaal2,
    $laptops,
    $projectCode,
    $notes,
    $USER
);
//insert statement
$stmt2 = $conn->prepare('INSERT INTO deleted (
  daypart,
  docent1,
  docent2,
  klas1jaar,
  klas1niveau,
  klas1nummer,
  lokaal1,
  lokaal2,
  laptops,
  projectCode,
  notes,
  userCreate,
  userDelete,
  IP
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
echo "$conn->error";
$stmt2->bind_param(
    "ssssssssssssss",
    $daypart,
    $docent1,
    $docent2,
    $klas1jaar,
    $klas1niveau,
    $klas1nummer,
    $lokaal1,
    $lokaal2,
    $laptops,
    $projectCode,
    $notes,
    $USER,
    $_SESSION['username'],
    $_SERVER["REMOTE_ADDR"]
);
echo "[DATA]\tINSERTING...\n";
while ($stmt->fetch()) {
    $stmt2->execute();
}
echo "[DATA]\tINSERTED\n";
$stmt->close();
$stmt2->close();

//als er iets mis is gegaan
if (!empty($conn->error)) {
    echo "[ERROR] $conn->error";
    $conn->close();
    die();
}
echo "REMOVING...\n";
//delete alles uit de active db
$stmt = $conn->prepare('TRUNCATE TABLE week');
$stmt->execute();
$stmt->close();
if (!empty($conn->error)) {
    echo "[ERROR] $conn->error";
    $conn->close();
    die();
}
$conn->close();
die("REMOVED\n");
