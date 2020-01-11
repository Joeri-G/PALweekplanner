<?php

require('funcLib.php');
if (!_GETIsset(['laptops'])) {
    die("[INPUT]\tNOT ALL PARAMETERS SET");
}

$laptops = $_GET['laptops'];

$confFile = "../../conf/conf.json";


$conf = json_decode(file_get_contents($confFile));
$conf->laptops = $laptops;

$json = json_encode($conf, JSON_PRETTY_PRINT);

file_put_contents($confFile, $json);
die();
