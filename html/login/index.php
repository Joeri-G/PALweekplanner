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
header("location: /");
die();
// </tmp>

//actual databse connection, hashes, salts, peppers etc. go here
 ?>
