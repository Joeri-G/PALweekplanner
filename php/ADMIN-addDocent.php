<?php
require('funcLib.php');
if (!_POSTIsset(['afkorting'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET\n");
}
// echo "[INPUT]\tOK\n";
$afkorting = $_POST['afkorting'];
//check length
if (strlen($afkorting) < 2) {
  die("[Afkorting] Te kort");
}
//maak userAvailability object
$confText = file_get_contents('../../conf/conf.json');
$conf = json_decode($confText);
$availabilityObj = new stdClass;
for ($i=0; $i < count($conf->dagen); $i++) {
    $dag = $conf->dagen[$i];
    if (isset($_POST["dag$dag"])) {
        if ($_POST["dag$dag"] == 'true') {
            $availabilityObj->$dag = true;
        } else {
            $availabilityObj->$dag = false;
        }
    }
}
$availability = json_encode($availabilityObj);
// echo "[INPUT]\tOK\n";

//check of username al bezet is
require('db-connect.php');
$stmt = $conn->prepare('SELECT ID FROM docenten WHERE afkorting = ?');
$stmt->bind_param('s', $afkorting);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    die("[DOCENT]\tBESTAAT AL\n");
}

$stmt->close();
// echo "INSERTING...\n";
$stmt = $conn->prepare("INSERT INTO docenten (afkorting, userAvailability) VALUES (?, ?)");
$stmt->bind_param('ss', $afkorting, $availability);
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
