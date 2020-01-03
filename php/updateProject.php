<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
/*
script om projecten toe te voegen
  - check of alle inputs gezet zijn
  - check of titel / afkorting al gezet zijn
  - check of verantwoordelijke bestaat
  - insert
*/
require('funcLib.php');
if (!_POSTIsset(['title', 'afkorting', 'verantwoordelijke', 'beschrijving', 'instructie', 'id'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET");
}
$title = $_POST['title'];
$afkorting = $_POST['afkorting'];
$verantwoordelijke = $_POST['verantwoordelijke'];
$beschrijving = $_POST['beschrijving'];
$instructie = $_POST['instructie'];
$id = toNum($_POST['id']);

//check of titel / afkorting al gezet is
require('db-connect.php');
$stmt = $conn->prepare('SELECT ID FROM projecten WHERE (projectTitle = ? OR projectCode = ?) AND ID != ?');
$stmt->bind_param('ssi', $title, $afkorting, $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    die("[NAME]\tTITEL OF AFKORTING BESTAAT AL\n");
}
$stmt->close();

//check of verantwoordelijke bestaat
$stmt = $conn->prepare('SELECT 1 FROM docenten WHERE afkorting = ?');
$stmt->bind_param('s', $verantwoordelijke);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    die("[VERANTWOORDELIJKE]\tVERANTWOORDELIJKE BESTAAT NIET\n");
}
$stmt->close();
//update stuff
$stmt = $conn->prepare(
  "UPDATE
    projecten
  SET
    projectTitle = ?,
    projectCode = ?,
    projectBeschrijving = ?,
    projectInstructie = ?,
    verantwoordelijke = ?,
    user = ?,
    `TIME` = ?,
    `IP` = ?
  WHERE
    ID = ?
");

$timestamp = date('Y-m-d H:i:s');

$stmt->bind_param(
    'ssssssssi',
    $title,
    $afkorting,
    $beschrijving,
    $instructie,
    $verantwoordelijke,
    $_SESSION['username'],
    $timestamp,
    $_SERVER['REMOTE_ADDR'],
    $id
);

$stmt->execute();

$stmt->close();
$conn->close();

die();
