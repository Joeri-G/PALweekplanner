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
if (!_POSTIsset(['title', 'afkorting', 'verantwoordelijke', 'beschrijving', 'instructie'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET");
}
echo "[INPUT]\tOK\n";
$title = $_POST['title'];
$afkorting = $_POST['afkorting'];
$verantwoordelijke = $_POST['verantwoordelijke'];
$beschrijving = $_POST['beschrijving'];
$instructie = $_POST['instructie'];

//check of titel / afkorting al gezet is
require('db-connect.php');
$stmt = $conn->prepare('SELECT 1 FROM projecten WHERE projectTitle = ? OR projectCode = ?');
$stmt->bind_param('ss', $title, $afkorting);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    die("[NAME]\tTITEL OF AFKORTING BESTAAT AL\n");
}
echo "[NAME]\tOK\n";
//check of verantwoordelijke bestaat
$stmt->close();

$stmt = $conn->prepare('SELECT 1 FROM users WHERE username = ? AND role = "docent"');
$stmt->bind_param('s', $verantwoordelijke);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    die("[VERANTWOORDELIJKE]\tVERANTWOORDELIJKE BESTAAT NIET\n");
}
echo "[VERANTWOORDELIJKE]\tOK\n";
$stmt->close();
//insert in db
echo "INSETING...\n";
$stmt = $conn->prepare('INSERT INTO projecten (
  projectTitle,
  projectCode,
  projectBeschrijving,
  projectInstructie,
  verantwoordelijke,
  user
) VALUES (
  ?,
  ?,
  ?,
  ?,
  ?,
  ?
)');
$stmt->bind_param(
    'ssssss',
    $title,
    $afkorting,
    $beschrijving,
    $instructie,
    $verantwoordelijke,
    $_SESSION['username']
);

$stmt->execute();
$stmt->close();
$conn->close();
echo "[INSERT]\tOK\n";

die();
