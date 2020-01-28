<?php
require('funcLib.php');
if (!_GETIsset(['id'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET");
}

$id = $_GET['id'];

require("db-connect.php");
$stmt = $conn->prepare("DELETE FROM projecten WHERE ID=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->store_result();
if ($conn->error !== '') {
    $stmt->close();
    $conn->close();
    die("[DELETE] FAILED\n");
}
$stmt->close();
$conn->close();
die();
