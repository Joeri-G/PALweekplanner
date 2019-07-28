<?php
// BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
if (!isset($_SESSION['loggedin'])) {
  header("location: /login");
  die();
}
 ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <!-- icon -->
    <link rel="shortcut icon" href="/img/logo.png">
    <!-- css -->
    <link rel="stylesheet" href="/css/master.css">
    <link rel="stylesheet" media="(min-width: 992px)" href="/css/desktop.css">
    <link rel="stylesheet" media="(max-width: 991px)" href="/css/mobile.css">
    <!-- <link rel="stylesheet" href="/css/fork.css"> -->
    <!-- title -->
    <title>Rooster</title>
    <!-- CDN -->
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
  </head>
  <body>
  </body>
</html>
