<?php
require('funcLib.php');
if (!_GETIsset(['ID'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}
// echo "[INPUT]\tOK\n";
require('db-connect.php');
// echo "[DELETE]\tDELETING...\n";

switch ($mode) {
  case 'user':
    $stmt = $conn->prepare('DELETE FROM users WHERE ID = ?');
    break;
  case 'docent':
    $stmt = $conn->prepare('DELETE FROM docenten WHERE ID = ?');
    break;
  case 'klas':
    $stmt = $conn->prepare('DELETE FROM klassen WHERE ID = ?');
    break;
  case 'lokaal':
    $stmt = $conn->prepare('DELETE FROM lokalen WHERE ID = ?');
    break;
  default:
  $conn->close();
  die('[MODE] INVALID');
    break;
}

$stmt->bind_param('i', $_GET['ID']);
$stmt->execute();
$stmt->store_result();
// echo "[DELETE]\tDELETED";
if ($conn->error) {
  echo "[ERROR] $conn->error";
}
$stmt->close();
$conn->close();
die();
