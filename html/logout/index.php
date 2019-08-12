<?php
//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
//logout script
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('location: /login');
  die('Not logged in');
}
$_SESSION['loggedin'] = false;
$_SESSION['userLVL'] = 0;
$_SESSION['username'] = 'loggedout';

//unset alle session variabelen
$_SESSION = array();

// Verweider de session data
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// destroy session
session_destroy();
header('location: /login');
?>
