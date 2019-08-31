<?php
require('funcLib.php');
if (!_POSTIsset(['lokaal'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}

$lokaal = $_POST['lokaal'];

//check of er wel iets is ingevuld bij het lokaal
if (empty($lokaal)) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}

// echo "[INPUT]\tOK\n";

//check of het lokaal al bestaat
require('db-connect.php');
$stmt = $conn->prepare('SELECT ID FROM lokalen WHERE lokaal = ?');
$stmt->bind_param('s', $lokaal);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    die("[LOKAAL]\tTAKEN\n");
}
$stmt->close();

// echo "[LOKAAL]\tOK\nINSERTING...\n";

$stmt = $conn->prepare('INSERT INTO lokalen (lokaal) VALUES (?)');
$stmt->bind_param('s', $lokaal);
$stmt->execute();
$stmt->store_result();
if (!empty($conn->error)) {
    $stmt->close();
    $conn->close();
    die("ERROR\n");
}
$stmt->close();
$conn->close();
// die("[INSERT] OK\n");
die();
