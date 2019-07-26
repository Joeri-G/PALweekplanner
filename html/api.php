<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("location: /login?r=".$_SERVER['REQUEST_URI']);
  die('Not Logged In');
}


if (isset($_GET['insert']) && $_GET['insert'] == true) {
  require('../php/insert.php');
}





 ?>
