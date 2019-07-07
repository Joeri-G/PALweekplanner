<?php
//functions
function _GETIsset($input) {
  //loop through list
  for ($i=0; $i < count($input); $i++) {
    //if the value is not set return false
    if (!isset($_GET[$input[$i]])) {
      return false;
    }
  }
  return true;
}

function isOverlap($arr1, $arr2) {
  //loop through first array
  for ($i=0; $i < count($arr1); $i++) {
    //loop through second array
    for ($x=0; $x < count($arr2); $x++) {
      //check every entry in the first array against every entry in the second array
      if ($arr1[$i] === $arr2[$x] && notNone($arr1[$i])) {
        //if there is overlap return true
        return true;
      }
    }
  }
  return false;;
}


//check if any other value than none occurs in array
function notNone($input) {
  if ($input === "none" || $input === "" || $input === "None") {
    return false;
  }
  return true;
}


//check if any other value than none occurs in array
function isPossible($input) {
  //set to false
  $hasValue = false;
  //loop through list
  for ($i=0; $i < count($input); $i++) {
    //if the input is None nor empty set to true
    if ($input[$i] != "none" && $input[$i] != "" && $input[$i] != "None") {
      $hasValue = true;
    }
  }
  return $hasValue;
}

//?mode=MODE&selected=SELECTION&daypart=DAYPART&kd=KLAS/DOCENT&lk=LOKAAL&kl=KLAS&&lk2=LOKAAL2&kl2=KLAS2&d2=DOCENT2&lp=LAPTOPS
//check for GET values
if (!_GETIsset(['daypart', 'lokaal1', 'lokaal2', 'klas1', 'klas2', 'docent1', 'docent2', 'laptops'])) {
  die('Not all parameters set');
}


//place everything in variables
$daypart = $_GET['daypart'];

$docent1 = $_GET['docent1'];
$docent2 = $_GET['docent2'];

$klas1 = $_GET['klas1'];
$klas2 = $_GET['klas2'];

$lokaal1 = $_GET['lokaal1'];
$lokaal2 = $_GET['lokaal2'];

$laptops = $_GET['laptops'];
//since some browsers will not palce an item in the url when its empty place notes like this
$note = '';
if (isset($_GET['note'])) {$note = $_GET['note'];}

$data = [$daypart, $lokaal1, $lokaal2, $klas1, $klas2, $docent1, $docent2, $laptops, $note];

$docentGroup = array($docent1, $docent2);
$klasGroup = array($klas1, $klas2);
$lokaalGroup = array($lokaal1, $lokaal2);

//make sure at least one entry is every array is not None
if (!isPossible($docentGroup)) {
  die('Minimaal een van de geselecteerde docenten moet niet None zijn');
  //at least one of the selected teachers should not be None
}
if (!isPossible($klasGroup)) {
  die('Minimaal een van de geselecteerde klassen moet niet None zijn');
  //at least one of the selected calsses should not be None
}
if (!isPossible($lokaalGroup)) {
  die('Minimaal een van de geselecteerde lokalen moet niet None zijn');
  //at least one of the selected classrooms should not be None
}
//connect with database
require('db-connect.php');

//load all data from selected daypart to test availability of selections
//use prepared statements to mitigate the risk of SQL injections
$stmt = $conn->prepare('SELECT docent1, docent2, klas1, klas2, lokaal1, lokaal2 FROM week WHERE `daypart` = ?');
$stmt->bind_param('s', $daypart);

//execute SQL query
$stmt->execute();

$stmt->store_result();

//res for result
$stmt->bind_result($resDocent1, $resDocent2, $resKlas1, $resKlas2, $resLokaal1, $resLokaal2);

while($stmt->fetch()) {
    //compare input to stored variables
    //place docent, klas and lokaal in groups since klas2 and klas2 will both have to be checked against resKlas1 and resKlas2
    $resDocentGroup = array($resDocent1, $resDocent2);

    $resKlasGroup = array($resKlas1, $resKlas2);

    $resLokaalGroup = array($resLokaal1, $resLokaal2);

    //check overlap for docenten
    if(isOverlap($docentGroup, $resDocentGroup)) {
      die('Een of meer van de geselecteerde docenten is bezet op het gekozen tijdstip');
      //one or more of the selected teachers is occupied in the selected timeframe
    }
    //check overlap for klassen
    if(isOverlap($klasGroup, $resKlasGroup)) {
      die('Een of meer van de geselecteerde klassen is bezet op het gekozen tijdstip');
      //one or more of the selected classes is occupied in the selected timeframe
    }
    //check overlap for lokalen
    if(isOverlap($lokaalGroup, $resLokaalGroup)) {
      die('Een of meer van de geselecteerde lokalen is bezet op het gekozen tijdstip');
      //one or more of the selected classrooms is occupied in the selected timeframe
    }
}
$stmt->close();

//now we know that the data is unique and we can add it to the database
//the entry to create will look something like this
//$daypart | $docent1 | $docent2 | $klas1 | $klas2 | $lokaal1 | $lokaal2 | $laptops | $notes | USER | TIMESTAMP | IP
//since the timestamp is generated by the MySQL server we will not generate it here
$stmt = $conn->prepare('INSERT INTO week (daypart, docent1, docent2, klas1, klas2, lokaal1, lokaal2, laptops, notes, USER, IP) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)');
//bind variables
//s stands for string
$stmt->bind_param('sssssssssss', $daypart, $docent1, $docent2, $klas1, $klas2, $lokaal1, $lokaal2, $laptops, $note, $_SESSION['username'], $_SERVER['REMOTE_ADDR']);
//executre query
$stmt->execute();
//close everything
$stmt->close();
$conn->close();

//give feedback
echo "Afspraak gecreeerd";
//appointment creaetd successfully
 ?>
