<?php
require('funcLib.php');
if (!_POSTIsset(['username', 'password', 'userLVL'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}
// echo "[INPUT]\tOK\n";

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$userLVL = $_POST['userLVL'];

//check of alles wel een lengte heeft
if (strlen($username) < 3) {
    die("[USERNAME]\tTOO SHORT\n");
}
// echo "[USERNAME]\tOK LENGTH\n";

if (strlen($_POST['password']) < 8) {
    die("[PASSWORD]\tTOO SHORT\n");
}
// echo "[PASSWORD]\tOK LENGTH\n";

//check of username en passwd wel gezet zijn
if (empty($username) || empty($_POST['password'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}

// echo "[INPUT]\tOK\n";

//check of username al bezet is
require('db-connect.php');
$stmt = $conn->prepare('SELECT ID FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    die("[USERNAME]\tTAKEN\n");
}

$stmt->close();
// echo "INSERTING...\n";
$stmt = $conn->prepare("INSERT INTO users (username, password, userLVL, lastLoginIP) VALUES (?, ?, ?, '0.0.0.0')");
$stmt->bind_param('ssi', $username, $password, $userLVL);
$stmt->execute();
$stmt->store_result();

if (!empty($conn->error)) {
    echo "ERROR\n$conn->error";
    $stmt->close();
    $conn->close();
    die();
}
$stmt->close();
$conn->close();
// die("[INSERT] OK\n");
die();
