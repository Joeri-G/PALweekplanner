<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
//login check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['userLVL'] < 2) {
  if (!isset($_SESSION['userLVL']) || $_SESSION['userLVL'] < 2) {
    header("location: /");
    die("Insufficient Permissions");
  }
    header("location: /login");
    die('Not Logged In');
}

if (isset($_GET['listUsers']) && $_GET['listUsers'] == 'true') {
    require('../../php/ADMIN-listUsers.php');
}

if (isset($_GET['listKlassen']) && $_GET['listKlassen'] == 'true') {
    require('../../php/ADMIN-listKlassen.php');
}

if (isset($_GET['listLokalen']) && $_GET['listLokalen'] == 'true') {
    require('../../php/ADMIN-listLokalen.php');
}

if (isset($_GET['listDocenten']) && $_GET['listDocenten'] == 'true') {
  require('../../php/ADMIN-listDocenten.php');
}

if (
  isset($_GET['deleteUser']) && $_GET['deleteUser'] == 'true' ||
  isset($_GET['deleteKlas']) && $_GET['deleteKlas'] == 'true' ||
  isset($_GET['deleteLokaal']) && $_GET['deleteLokaal'] == 'true'||
  isset($_GET['deleteDocent']) && $_GET['deleteDocent'] == 'true'
) {
    if (isset($_GET['deleteUser']) && $_GET['deleteUser'] == 'true')
        $mode = 'user';
    elseif (isset($_GET['deleteKlas']) && $_GET['deleteKlas'] == 'true')
        $mode = 'klas';
    elseif (isset($_GET['deleteLokaal']) && $_GET['deleteLokaal'] == 'true')
        $mode = 'lokaal';
    elseif (isset($_GET['deleteDocent']) && $_GET['deleteDocent'] == 'true')
        $mode = 'docent';
    else
        die('[MODE] INVALID');
    require('../../php/ADMIN-deleteEntry.php');
}

if (isset($_GET['addUser']) && $_GET['addUser'] == 'true') {
    require('../../php/ADMIN-addUser.php');
}

if (isset($_GET['addKlas']) && $_GET['addKlas'] == 'true') {
    require('../../php/ADMIN-addKlas.php');
}

if (isset($_GET['addLokaal']) && $_GET['addLokaal'] == 'true') {
    require('../../php/ADMIN-addLokaal.php');
}

if (isset($_GET['deleteAll']) && $_GET['deleteAll'] == 'true') {
    require('../../php/ADMIN-deleteAll.php');
}

if (isset($_GET['addDocent']) && $_GET['addDocent'] == 'true') {
  require('../../php/ADMIN-addDocent.php');
}

if (isset($_GET["updateLaptops"]) && $_GET["updateLaptops"] == "true") {
  require('../../php/ADMIN-updateLaptops.php');
}

if (isset($_GET["loadConf"]) && $_GET["loadConf"] == "true") {
  require('../../php/ADMIN-loadConf.php');
}

if (isset($_GET["changeDays"]) && $_GET["changeDays"] == "true") {
  require('../../php/ADMIN-changeDays.php');
}
