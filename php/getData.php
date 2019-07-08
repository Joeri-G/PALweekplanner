<?php

require('db-connect.php');




//query depends on selected mode
if ($_GET['mode'] === 'klas') {
  $stmt = $conn->prepare('SELECT daypart, docent1, docent2, klas1, klas2, lokaal1, lokaal2, laptops, notes, ID FROM week WHERE klas1 = ? OR klas2 = ?');
}
else if ($_GET['mode'] === 'docent') {
  $stmt = $conn->prepare('SELECT daypart, docent1, docent2, klas1, klas2, lokaal1, lokaal2, laptops, notes, ID FROM week WHERE docent1 = ? OR docent2 = ?');
}
else {
  die('Invalid mode');
}

//bind params
$stmt->bind_param('ss', $_GET['getDataOf'], $_GET['getDataOf']);


$stmt->execute();


$stmt->store_result();
//res for result

//create empty object for data
$data = new stdClass;

$stmt->bind_result($resDaypart, $resDocent1, $resDocent2, $resKlas1, $resKlas2, $resLokaal1, $resLokaal2, $resLaptop, $resNote, $resID);
while($stmt->fetch()) {
  //create emty array object
  $data->$resDaypart = new ArrayObject;
  $data->$resDaypart['docent'] = array($resDocent1, $resDocent2);
  $data->$resDaypart['klas'] = array($resKlas1, $resKlas2);
  $data->$resDaypart['lokaal'] = array($resLokaal1, $resLokaal2);
  $data->$resDaypart['laptop'] = $resLaptop;
  $data->$resDaypart['note'] = $resNote;
  $data->$resDaypart['ID'] = $resID;
}
$stmt->close();


$conn->close();

//set header
header('Content-Type: application/json');
//parse json
$json = json_encode($data);
//output json and terminate execution
die($json);


 ?>
