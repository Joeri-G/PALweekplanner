<?php
//BY JOERI GEUZINGE (https://www.joerigeuzinge.nl)
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    die('Already logged in');
}
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    die('Username or password not set');
}

$username = $_POST['username'];
$password = $_POST['password'];


require('../../php/db-connect.php');
$stmt = $conn->prepare('SELECT password, userLVL, ID FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($resPassword, $resUserLVL, $resID);
if ($stmt->num_rows !== 1) {
    $stmt->close();
    $conn->close();
    die("Incorrect username or password");
}
$stmt->fetch();
//check password
if (password_verify($password, $resPassword)) {
    //login user
    $stmt->close();
    $stmt = $conn->prepare('UPDATE users SET lastLoginTime = current_timestamp, lastLoginIP = ? WHERE ID = ?');
    $stmt->bind_param('si', $_SERVER['REMOTE_ADDR'], $resID);
    $stmt->execute();

    $_SESSION['loggedin'] = true;
    $_SESSION['userLVL'] = $resUserLVL;
    $_SESSION['username'] = $username;
    $_SESSION['id'] = $resID;
    //schrijf lastLoginIP naar server
    echo "OK";
} else {
    echo "Incorrect username or password";
}
$stmt->close();
$conn->close();
