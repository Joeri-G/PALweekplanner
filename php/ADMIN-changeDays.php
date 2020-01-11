<?php
require('funcLib.php');
if (!_GETIsset(['dag'])) {
  die("[INPUT]\tNOT ALL PARAMETERS SET");
}
//haal dagen uit _get
$dagen = $_GET['dag'];
//update conf
$conf = getConf();
$conf->dagen = $dagen;
$json = json_encode($conf, JSON_PRETTY_PRINT);
file_put_contents(__DIR__.'/../conf/conf.json', $json);
 ?>
