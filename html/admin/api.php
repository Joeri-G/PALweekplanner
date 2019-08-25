<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
//login check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['userLVL'] < 4) {
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

if (isset($_GET['deleteUser']) && $_GET['deleteUser'] == 'true' ||
isset($_GET['deleteKlas']) && $_GET['deleteKlas'] == 'true' ||
isset($_GET['deleteLokaal']) && $_GET['deleteLokaal'] == 'true') {
  if (isset($_GET['deleteUser']) && $_GET['deleteUser'] == 'true') {
    $mode = 'user';
  }
  elseif (isset($_GET['deleteKlas']) && $_GET['deleteKlas'] == 'true') {
    $mode = 'klas';
  }
  elseif (isset($_GET['deleteLokaal']) && $_GET['deleteLokaal'] == 'true') {
    $mode = 'lokaal';
  }
  else {
    die('[MODE] INVALID');
  }
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

 ?>
