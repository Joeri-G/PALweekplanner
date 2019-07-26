<?php
//tmp login
// <tmp>
session_start();
if (!isset($_SESSION['loggedin'])) {
  $_SESSION['loggedin'] = true;
  $_SESSION['username'] = 'testuser';
  //user 'level' (user, admin etc)
  $_SESSION['userLVL'] = 3;
}
if (isset($_GET['r'])) {
  header('location: '.$_GET['r']);
  die();
}
header("location: /");
die();
// </tmp>

//actual databse connection, hashes, salts, peppers etc. go here
 ?>
