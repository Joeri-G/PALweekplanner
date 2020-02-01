<?php
require('funcLib.php');
if (!_POSTIsset(['jaar', 'naam'])) {
    var_dump($_POST);
    var_dump($_GET);
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}

$jaar = $_POST['jaar'];
$naam = $_POST["naam"];

if (empty($jaar) || empty($naam)) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}

if ("$jaar" !== substr($naam, 0, strlen("$jaar"))) {
  echo "[WARNING] naam komt niet overeen met jaarlaag, dit kan problemen opleveren tijdens het zoeken";
}

//check of klas al bestaat
require('db-connect.php');
$stmt = $conn->prepare('SELECT ID FROM klassen WHERE klasNaam = ?');
$stmt->bind_param('s', $naam);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    die("[NAAM]\tBEZET\n");
}
// echo "[KLAS]\tOK\n";
$stmt->close();

// echo "INSERTING...\n";

$stmt = $conn->prepare('INSERT INTO klassen (jaar, klasNaam) VALUES (?, ?)');
$stmt->bind_param('ss', $jaar, $naam);
$stmt->execute();
$stmt->store_result();

if (!empty($conn->error)) {
    $stmt->close();
    $conn->close();
    die("ERROR\n");
}

// echo "[INSERT]\tOK\n";

$stmt->close();
$conn->close();
