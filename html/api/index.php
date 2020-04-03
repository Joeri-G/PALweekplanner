<?php
require_once __DIR__ . '/vendor/autoload.php';
use joeri_g\palweekplanner\v2 as v2;
define("acceptedMethods", ["GET", "POST", "PUT", "DELETE"]); //all the accepted HTTP Request Methods
define("versions", ["v2"]); //all supported api versions
define("allowedGroups", ["classes", "classrooms", "teachers", "projects", "laptops", "admin", "users", "config"]); //allowed groupings
define("tables", ["classes"/*, "classrooms", "users", "deleted", "lessons", "projects", "teachers", "users"*/]);
define("groupException", ["icon"]);





//test request on validity
$request = new v2\act\requestActions();
$request->allowedVersionPrefixes = versions;
$request->acceptedMethods = acceptedMethods;
$request->allowedGroups = allowedGroups;
$request->groupException = groupException;

$request->init();

if (!$request->verifyRequest()) {
  http_response_code(405);
  die("Invalid Request Structure");
}

$db = new v2\conf\Database();
$db->tables = tables;

if (!$db->connect($errmode = 2)) {
  die("Could not connect to database");
}

$auth = new v2\auth\authCheck();
if (!$auth->check(3, $db)) {
  http_response_code(401);
  header("WWW-Authenticate: Basic ream=\"Authentication is required to use this API\"");
  die("Please Authenticate Yourself Through Eiter The Login Page Or The Authentication Header.");
}


switch ($request->group) {
  case 'classes':
    $group = new v2\act\classes();
    break;

  default:
    die("Group could not be found");
    break;
}
$group->act($db, $request);
