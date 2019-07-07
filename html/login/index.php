<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
  $_SESSION['loggedin'] = true;
  $_SESSION['username'] = 'testuser';
}
header("location: /");
 ?>
