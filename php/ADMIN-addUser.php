<?php
require('funcLib.php');
if (!_POSTIsset(['username', 'password', 'role', 'userLVL'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}
echo "[INPUT]\tOK\n";

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];
$userLVL = $_POST['userLVL'];

//check of alles wel een lengte heeft
if (strlen($username) < 3) {
  die("[USERNAME]\tTOO SHORT\n");
}
echo "[USERNAME]\tOK LENGTH\n";

if (strlen($_POST['password']) < 8) {
  die("[PASSWORD]\tTOO SHORT\n");
}
echo "[PASSWORD]\tOK LENGTH\n";

//check of username en passwd wel gezet zijn
if (empty($username) || empty($_POST['password'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}

echo "[INPUT]\tOK\n";

//check of username al bezet is
require('db-connect.php');
$stmt = $conn->prepare('SELECT 1 FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  $stmt->close();
  $conn->close();
  die("[USERNAME]\tTAKEN\n");
}
echo "[USERNAME]\tOK\n";
echo "PARSING...\n";
//maak userAvailability object
$confText = file_get_contents('../../conf/conf.json');
$conf = json_decode($confText);
$userAvailabilityObj = new stdClass;
for ($i=0; $i < count($conf->dagen); $i++) {
  $dag = $conf->dagen[$i];
  if (isset($_POST["dag$dag"])) {
    if ($_POST["dag$dag"] == 'true') {
      $userAvailabilityObj->$dag = true;
    }
    else {
      $userAvailabilityObj->$dag = false;
    }
  }
}

$userAvailability = json_encode($userAvailabilityObj);
echo "INSERTINGS...\n";
$stmt = $conn->prepare('INSERT INTO users (username, password, role, userLVL, userAvailability) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssis', $username, $password, $role, $userLVL, $userAvailability);
$stmt->execute();
$stmt->store_result();

if (!empty($conn->error)) {
  $stmt->close();
  $conn->close();
  die("ERROR\n");
}
$stmt->close();
$conn->close();
die("[INSERT] OK\n");
 ?>
