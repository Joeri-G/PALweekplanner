<?php
//functions
require('funcLib.php');

//update script
//very simmilar to submit script
//entryID will be used to select an entry
//everything can be changed except for timeframe

//make sure the selector is a valid integer
if (!ctype_digit($_GET['id'])) {
  die('Invalid ID');
}

//since we know $_GET['id'] only consists of integers we can now place it inside a variable as an integer
$entryID = (int) $_GET['id'];
//now do the same checks as with the submit script and update the correct entry

//check for GET values
if (!_GETIsset(['daypart', 'lokaal1', 'lokaal2', 'klas1', 'klas2', 'docent1', 'docent2', 'laptops'])) {
  die('Not all parameters set');
}

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

$data = [$lokaal1, $lokaal2, $klas1, $klas2, $docent1, $docent2, $laptops, $note];

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

//now test validity of selection

//connect with database
require('db-connect.php');

//load the daypart from the selected ID
$stmt = $conn->prepare('SELECT daypart FROM week WHERE `ID` = ?');
$stmt->bind_param('i', $entryID);

//execute SQL query
$stmt->execute();

$stmt->store_result();

if ($stmt->num_rows > 0) {
  $stmt->bind_result($daypart);
  $stmt->fetch();
  // $daypart = $resDaypart;
}
else {
  die('Invalid ID');
}

$stmt->close();

//load all data from selected daypart to test availability of selections
//use prepared statements to mitigate the risk of SQL injections
$stmt = $conn->prepare('SELECT docent1, docent2, klas1, klas2, lokaal1, lokaal2, ID FROM week WHERE `daypart` = ?');
$stmt->bind_param('s', $daypart);

//execute SQL query
$stmt->execute();

$stmt->store_result();

//res for result
$stmt->bind_result($resDocent1, $resDocent2, $resKlas1, $resKlas2, $resLokaal1, $resLokaal2, $resID);

while ($stmt->fetch()) {
    //compare input to stored variables
    //place docent, klas and lokaal in groups since klas2 and klas2 will both have to be checked against resKlas1 and resKlas2
    $resDocentGroup = array($resDocent1, $resDocent2);

    $resKlasGroup = array($resKlas1, $resKlas2);

    $resLokaalGroup = array($resLokaal1, $resLokaal2);

    //check overlap for docenten
    if(isOverlap($docentGroup, $resDocentGroup) && $resID !== $entryID) {
      die('Een of meer van de geselecteerde docenten is bezet op het gekozen tijdstip');
      //one or more of the selected teachers is occupied in the selected timeframe
    }
    //check overlap for klassen
    if(isOverlap($klasGroup, $resKlasGroup) && $resID !== $entryID) {
      die('Een of meer van de geselecteerde klassen is bezet op het gekozen tijdstip');
      //one or more of the selected classes is occupied in the selected timeframe
    }
    //check overlap for lokalen
    if(isOverlap($lokaalGroup, $resLokaalGroup) && $resID !== $entryID) {
      die('Een of meer van de geselecteerde lokalen is bezet op het gekozen tijdstip');
      //one or more of the selected classrooms is occupied in the selected timeframe
    }
}
$stmt->close();

//now we know that the data is unique and we can add it to the database
$stmt = $conn->prepare('UPDATE week SET docent1=?, docent2=?, klas1=?, klas2=?, lokaal1=?, lokaal2=?, laptops=?, notes=?, USER=?, IP=? WHERE ID=?');
//bind variables
//s stands for string
$stmt->bind_param('ssssssssssi', $docent1, $docent2, $klas1, $klas2, $lokaal1, $lokaal2, $laptops, $note, $_SESSION['username'], $_SERVER['REMOTE_ADDR'], $entryID);
//executre query
$stmt->execute();
//close everything
$stmt->close();
$conn->close();

//give feedback
echo "Afspraak gecreeerd";
//appointment created successfully


 ?>
