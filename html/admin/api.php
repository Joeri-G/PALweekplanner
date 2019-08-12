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

 ?>
