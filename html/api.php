<?php
//make sure user is loggedin
session_start();
if (!isset($_SESSION['loggedin'])) {
  header("location: /login");
  die();
}
//generate options list, alsom make sure user has permission
if (isset($_GET['getList']) && $_SESSION['userLVL'] >= 2) {
  header('Content-Type: application/json');
  die('{"klas": ["4v1", "4v2", "4v3"], "docent": ["Gast", "Bos", "Van den Berg"]}');
}

//include submit script when user selects submit, also make sure user has permission
if (isset($_GET['submit']) && $_GET['submit'] == true && $_SESSION['userLVL'] >= 2) {
  require('../php/submit.php');
  die();
}

//just dump the _GET as a test or something
print_r($_GET);
 ?>
