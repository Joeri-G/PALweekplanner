<?php
require('funcLib.php');
if (!_GETIsset(['ID'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}
echo "[INPUT]\tOK\n";
require('db-connect.php');
echo "[DELETE]\tDELETING...\n";
if ($mode == 'user') {
    $stmt = $conn->prepare('DELETE FROM users WHERE ID = ?');
} elseif ($mode == 'klas') {
    $stmt = $conn->prepare('DELETE FROM klassen WHERE ID = ?');
} elseif ($mode == 'lokaal') {
    $stmt = $conn->prepare('DELETE FROM lokalen WHERE ID = ?');
} else {
    $conn->close();
    die('[MODE] INVALID');
}
$stmt->bind_param('i', $_GET['ID']);
$stmt->execute();
$stmt->store_result();
echo "[DELETE]\tDELETED";
