<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
//login check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['userLVL'] < 2) {
  header("location: /login?r=".$_SERVER['REQUEST_URI']);
  die('Not Logged In');
}

//insert script
if (isset($_GET['insert']) && $_GET['insert'] == 'true') {
  require('../php/insert.php');
}

//read script voor docent
//read rooster data van docent
if (isset($_GET['readDocent']) && $_GET['readDocent'] == 'true') {
  require('../php/readDocent.php');
}

//read script voor klas
if (isset($_GET['readKlas']) && $_GET['readKlas'] == 'true') {
  require('../php/readKlas.php');
}

//update script
//update alle vakken behalve ID en Daypart
if (isset($_GET['update']) && $_GET['update'] == 'true') {
  require('../php/update.php');
}

//script om afspraken te verwijderen
if (isset($_GET['delete']) && $_GET['delete'] == 'true') {
  require('../php/delete.php');
}

//als er om de config gevraagd wordt haal deze dan op en verstuur alleen de dagen en uren
if (isset($_GET['loadconfig']) && $_GET['loadconfig'] == 'true') {
  header('Content-Type: application/json');
  $data = file_get_contents('../conf/conf.json');
  $conf = json_decode($data);
  $out = new stdClass;
  $out->uren = $conf->uren;
  $out->dagen = $conf->dagen;
  $out->lestijden = $conf->lestijden;
  die(json_encode($out));
}

//maak een lijst met alle docenten en klassen
if (isset($_GET['listAll']) && $_GET['listAll'] =='true') {
  require('../php/listAll.php');
}

//listAvailable
//maak een lijst met alle docenten, klassen en lokalen die niet bezet zijn.
if (isset($_GET['listAvailable']) && $_GET['listAvailable'] == 'true') {
  require('../php/listAvailable.php');
}
 ?>
