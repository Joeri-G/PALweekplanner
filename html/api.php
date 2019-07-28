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

//read script
//read rooster data van klas/docent
if (isset($_GET['read']) && $_GET['read'] == 'true') {
  require('../php/read.php');
}

echo "None";
 ?>
