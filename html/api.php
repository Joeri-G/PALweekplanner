<?php
if (isset($_GET['getList'])) {
  //get data from config file
  header('Content-Type: application/json');
  //decode the JSON to object
  die('{"klas": ["4v1", "4v2", "4v3"], "docent": ["Gast", "Bos", "Van den Berg"]}');
}

print_r($_GET);
 ?>
