<?php
$confFile = "../../conf/conf.json";
$json = file_get_contents($confFile);
//parse json back and forth to remove whitespaces
header('Content-Type: application/json');
die(json_encode(json_decode($json)));


 ?>
