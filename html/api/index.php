<?php
require_once __DIR__ . '/vendor/autoload.php';  //load all scripts in src/
use joeri_g\palweekplanner\v2 as v2;

//set constants
define("acceptedMethods", ["GET", "POST", "PUT", "DELETE"]); //all the accepted HTTP Request Methods
define("versions", ["v2"]); //all supported api versions
define("allowedCollections", ["classes", "classrooms", "teachers", "projects", "laptops", "admin", "users", "config"]); //allowed collections
define("tables", ["classes", "classrooms", "users", "deleted", "appointments", "projects", "teachers", "users"]);
define("collectionException", ["icon"]);
//set headers
header('Content-Type: application/json');

//test request on validity
$request = new v2\act\requestActions();
$request->allowedVersionPrefixes = versions;
$request->acceptedMethods = acceptedMethods;
$request->allowedCollections = allowedCollections;
$request->collectionException = collectionException;

$request->init();

if (!$request->verifyRequest()) {
  http_response_code(405);
  die(json_encode(["successful" => false, "error" => "Invalid request structure"]));
}

$db = new v2\conf\Database();
$db->tables = tables;

if (!$db->connect($errmode = 2)) {
  die();
}

$auth = new v2\auth\authCheck();
if (!$auth->check(3, $db)) {
  http_response_code(401);
  header("WWW-Authenticate: Basic ream=\"Authentication is required to use this API\"");
  die(json_encode(["successful" => false, "error" => "Please Authenticate Yourself Through Eiter The Login Page Or The Authentication Header."]));
}


switch ($request->collection) {
  case 'classes':
    $collection = new v2\act\Classes();
    break;
  case 'classrooms':
    $collection = new v2\act\Classrooms();
    break;
  case 'users':
    $collection = new v2\act\Users();
    break;
  case 'teachers':
    $collection = new v2\act\Teachers();
    break;
  case 'projects':
    $collection = new v2\act\Projects();
    break;


  default:
    die(json_encode(["successful" => false, "error" => "Collection could not be found"]));
    break;
}
$collection->act($db, $request);

echo json_encode($collection->output);
