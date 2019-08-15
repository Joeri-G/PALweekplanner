<?php
require('funcLib.php');
if (!_POSTIsset(['jaar', 'niveau', 'nummer'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}

$jaar = $_POST['jaar'];
$niveau = $_POST['niveau'];
$nummer = $_POST['nummer'];

if (empty($jaar) || empty($niveau) || empty($nummer)) {
  die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}

echo "[INPUT]\tOK\n";

//check of klas al bestaat
require('db-connect.php');
$stmt = $conn->prepare('SELECT 1 FROM klassen WHERE jaar = ? AND niveau = ? AND nummer = ?');
$stmt->bind_param('sss', $jaar, $niveau, $nummer);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  $stmt->close();
  $conn->close();
  die("[KLAS]\tTAKEN\n");
}
echo "[KLAS]\tOK\n";
$stmt->close();

echo "INSERTING...\n";

$stmt = $conn->prepare('INSERT INTO klassen (jaar, niveau, nummer) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $jaar, $niveau, $nummer);
$stmt->execute();
$stmt->store_result();

if (!empty($conn->error)) {
  $stmt->close();
  $conn->close();
  die("ERROR\n");
}

echo "[INSERT]\tOK\n";

$stmt->close();
$conn->close();
 ?>
