<?php
//make sure user is loggedin
session_start();
if (!isset($_SESSION['loggedin'])) {
  header("location: /login");
  die();
}

if ($_SESSION['userLVL'] < 2) {
  die('Invalid permissions');
}


//generate options list, alsom make sure user has permission
if (isset($_GET['getList'])) {
  //tmp
  header('Content-Type: application/json');
  //load config file
  $jsonRaw = file_get_contents('../config.json');
  //decode config file to object to extract ONLY the variables we need
  $jsonObject = json_decode($jsonRaw);
  //create an object for the final json
  $json = new stdClass;
  $json->klas = $jsonObject->klassen;
  $json->docent = $jsonObject->docenten;
  //parese object into string
  $jsonText = json_encode($json);
  //output JSON and terminate execution
  die($jsonText);
}

//generate timetabel for docent or klas
if (isset($_GET['getDataOf']) && isset($_GET['mode'])) {
  //generate all the data of a teacher / class
  require('../php/getData.php');
  die();
}

//include submit script when user selects submit, also make sure user has permission
if (isset($_GET['submit']) && $_GET['submit'] == true) {
  require('../php/submit.php');
  die();
}

//just dump the _GET as a test or something
print_r($_GET);
 ?>
