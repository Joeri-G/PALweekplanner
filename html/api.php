<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
//login check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: /login");
    die('Not Logged In');
}




//read script voor docent
//read rooster data van docent
if (isset($_GET['readDocent']) && $_GET['readDocent'] == 'true') {
    require('../php/readDocent.php');
}

//maak een lijst met alle docenten en klassen
if (isset($_GET['listAll']) && $_GET['listAll'] =='true') {
    require('../php/listAll.php');
}

//read script voor klas
if (isset($_GET['readKlas']) && $_GET['readKlas'] == 'true') {
    require('../php/readKlas.php');
}

//als er om de config gevraagd wordt haal deze dan op en verstuur alleen de dagen en uren
if (isset($_GET['loadConfig']) && $_GET['loadConfig'] == 'true') {
    header('Content-Type: application/json');
    $data = file_get_contents('../conf/conf.json');
    $conf = json_decode($data);
    $out = new stdClass;
    $out->uren = $conf->uren;
    $out->dagen = $conf->dagen;
    $out->lestijden = $conf->lestijden;
    die(json_encode($out));
}
//listAvailable
//maak een lijst met alle docenten, klassen en lokalen die niet bezet zijn.
if (isset($_GET['listAvailable']) && $_GET['listAvailable'] == 'true') {
    require('../php/listAvailable.php');
}

//listJaarlaag
//maak een lijst net alle jaarlagen
if (isset($_GET['listJaarlaag']) && $_GET['listJaarlaag'] == 'true') {
    require('../php/listJaarlaag.php');
}

//read alle klassen in een jaarlaag
if (isset($_GET['readJaarlaag']) && $_GET['readJaarlaag'] == 'true') {
    require('../php/readJaarlaag.php');
}

//read alle klassem
if (isset($_GET['readAll']) && $_GET['readAll'] == 'true') {
    require('../php/readAll.php');
}

//list alle klassen in een jaarlaag
if (isset($_GET['listJaarlaagKlassen']) && $_GET['listJaarlaagKlassen'] == 'true') {
    require('../php/listJaarlaagKlassen.php');
}

//list alle projecten
if (isset($_GET['listProjects']) && $_GET['listProjects'] == 'true') {
    require('../php/PROJECTS-listProjects.php');
}

//list alle docenten
if (isset($_GET['listDocent']) && $_GET['listDocent'] == 'true') {
    require('../php/listDocent.php');
}

//PERMISSION SWITCH

//hier na alle scripts waarvoor write permission nodig is
if ($_SESSION['userLVL'] < 1) {
    die("Insufficient Permissions");
}

//script om afspraken te verwijderen
if (isset($_GET['delete']) && $_GET['delete'] == 'true') {
    require('../php/delete.php');
}

//insert script
//dit is write, dus userLVL > 3
if (isset($_GET['insert']) && $_GET['insert'] == 'true') {
  require('../php/insert.php');
}


//edit
if (isset($_GET['edit']) && $_GET['edit'] == 'true') {
    require('../php/edit.php');
}

//voeg project toe
if (isset($_GET['addProject']) && $_GET['addProject'] == 'true') {
    require('../php/addProject.php');
}

//export functie
if (isset($_GET['export']) && $_GET['export'] == 'true') {
  require('../php/csvParser.php');
}

//export functie
if (isset($_GET['somtodayExport']) && $_GET['somtodayExport'] == 'true') {
  require('../php/somtodayExport.php');
}

//edit script
if(isset($_GET['edit']) && $_GET['edit'] == 'true') {
  require('../php/edit.php');
}
