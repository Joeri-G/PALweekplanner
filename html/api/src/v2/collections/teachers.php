<?php
namespace joeri_g\palweekplanner\v2\collections;
/**
 * Class with all teacher related actions
 */
class Teachers {
  public $selector;
  public $action;
  public $output;

  private $db;
  private $conn;

  public function act($db = null, $request = null) {
    //make sure the db and PDO objects are provided and add them
    if (is_null($db) || is_null($db->conn)) {
      http_response_code(500);
      $this->output = ["successful" => false, "error" => "No db connection provided"];
      return false;
    }
    $this->db = $db;
    $this->conn = $db->conn;


    if (is_null($request)) {
      http_response_code(500);
      $this->output = ["successful" => false, "error" => "No selector provided"];
      return false;
    }

    $this->request = $request;
    $this->action = $this->request->action;
    $this->selector = $this->request->selector;

    if (is_null($this->selector)) {
      http_response_code(400);
      $this->output = ["successful" => false, "error" => "No selector provided"];
      return false;
    }

    switch ($this->action) {
      case 'GET': //list all classes or select a specific one
        $this->list();
        break;

      case 'POST':  //add a class (admin)
        $this->add();
        break;

      case 'DELETE':  //delete one or all classes (admin)
        $this->delete();
        break;

      case 'PUT': //update a class (admin)
        $this->update();
        break;

      default:
        http_response_code(405);
        $this->output = ["successful" => false, "error" => "Action could not be found"];
        break;
    }
  }

  public function list() {
    //check selector for validity
    if (!$this->request->checkSelector()) {
      $this->output = ["successful" => false, "error" => "Invalid selector"];
      http_response_code(400);
      return false;
    }
    //statement depends on selector
    //if wildcard return all classes
    if ($this->selector === "*") {
      //is user is admin return more data
      if ($_SESSION["userLVL"] >= 3) {
        $stmt = $this->conn->prepare("SELECT name, teacherAvailability, lastChanged, GUID FROM teachers");
      }
      else {
        $stmt = $this->conn->prepare("SELECT name, teacherAvailability, GUID FROM teachers");
      }
    }
    else {
      //is user is admin return more data
      if ($_SESSION["userLVL"] >= 3) {
        $stmt = $this->conn->prepare("SELECT name, teacherAvailability, lastChanged, GUID FROM teachers WHERE GUID = :id LIMIT 1");
      }
      else {
        $stmt = $this->conn->prepare("SELECT name, teacherAvailability, GUID FROM teachers WHERE GUID = :id LIMIT 1");
      }
      $stmt->bindParam("id", $this->selector);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    if (!$data) {
      //if the selector is a wildcard return an empty array, else return an error because the GUID does not exist
      $this->output = ($this->selector === "*") ? ["successful" => true, "data" => []] : ["successful" => false, "error" => "GUID does not exist in this collection"];
      return true;
    }
    //if the selector is a wildcard return the array with the data, else return only the first item in the array
    $data = ["successful" => true, "data" => ($this->selector === "*") ? $data : $data[0]];

    //since we want to make sure the teacherAvailability array is parsed to json and not stringified, loop through the array and parse it
    foreach ($data["data"] as $i => $teacher) {
      $data["data"][$i]["teacherAvailability"] = json_decode($teacher["teacherAvailability"]);
    }


    $this->output = $data;
  }

  public function add() {
    //make sure the neccesary data is provided
    $keys = ["name", "teacherAvailability"];
    if (!$this->request->POSTisset($keys)) {
    $this->output = ["successful" => false, "error" => "Please set all keys", "keys" => $keys];
      http_response_code(400);
      return false;
    }
    //check the teacherAvailability must be array of 7 booleans
    $teacherAvailability = $_POST["teacherAvailability"];

    if (gettype($teacherAvailability) !== "array" || sizeof($teacherAvailability) !== 7) {
      $this->output = ["successful" => false, "error" => "teacherAvailability must be an array of 7 booleans"];
      return false;
    }
    foreach ($teacherAvailability as $n => $day) {
      //HTTP makes everything a string so check if its a "1" or a "0"
      $teacherAvailability[$n] = ($day === "0" || $day === "1") ? true : false;
    }

    $teacherAvailability = json_encode($teacherAvailability);
    $name = $_POST["name"];
    $GUID = $this->db->generateGUID();


    $stmt = $this->conn->prepare("INSERT INTO teachers (name, teacherAvailability, GUID) VALUES (:name, :teacherAvailability, :GUID)");
    $data = [
      "name" => $name,
      "teacherAvailability" => $teacherAvailability,
      "GUID" => $GUID
    ];
    $stmt->execute($data);

    $data["lastChanged"] = date('Y-m-d H:i:s');
    $this->output = ["successful" => true, "data" => $data];

  }

  public function delete() {
    //check selector for validity
    if (!$this->request->checkSelector()) {
      $this->output = ["successful" => false, "error" => "Invalid selector"];
      http_response_code(400);
      return false;
    }
    //check if the user has sufficient permissions
    if ($_SESSION["userLVL"] < 3) {
      $this->output = ["successful" => false, "error" => "Insufficient permissions"];
      http_response_code(400);
      return false;
    }
    if ($this->selector == "*") {
      $stmt = $this->conn->prepare("TRUNCATE TABLE teachers");
      $stmt->execute();
    }
    else {
      $stmt = $this->conn->prepare("DELETE FROM teachers WHERE GUID = :GUID");
      $stmt->execute(["GUID" => $this->selector]);
    }
    $this->output = ["successful" => true];
  }

  public function update() {
    parse_str(file_get_contents("php://input"), $_PUT);
    //because the data is provided via a PUT request we cannot acces the data in the body through the $_POST variable and we have to manually parse and store it
    $keys = ["name", "teacherAvailability"];
    if (!$this->request->PUTisset($keys)) {
      $this->output = ["successful" => false, "error" => "Please set all keys", "keys" => $keys];
      http_response_code(400);
      return false;
    }
    //check selector for validity
    if (!$this->request->checkSelector()) {
      $this->output = ["successful" => false, "error" => "Invalid selector"];
      http_response_code(400);
      return false;
    }
    //check if the user has sufficient permissions
    //we cannot update every classroom so a wildcard is not permitted
    if ($_SESSION["userLVL"] < 3 || $this->selector === "*") {
      $this->output = ["successful" => false, "error" => "Insufficient permissions"];
      http_response_code(400);
      return false;
    }

    //make sure the teacherAvailability is valid
    $teacherAvailability = $_PUT["teacherAvailability"];

    if (gettype($teacherAvailability) !== "array" || sizeof($teacherAvailability) !== 7) {
      $this->output = ["successful" => false, "error" => "teacherAvailability must be an array of 7 booleans"];
      return false;
    }
    foreach ($teacherAvailability as $n => $day) {
      //HTTP makes everything a string so check if its a "1" or a "0"
      $teacherAvailability[$n] = ($day === "0" || $day === "1") ? true : false;
    }
    $teacherAvailability = json_encode($teacherAvailability);
    $name = $_PUT["name"];
    $GUID = $this->selector;

    $stmt = $this->conn->prepare("UPDATE teachers SET name = :name, teacherAvailability = :teacherAvailability, lastChanged = current_timestamp WHERE GUID = :GUID");
    $data = [
      "name" => $name,
      "teacherAvailability" => $teacherAvailability,
      "GUID" => $GUID,
    ];
    $stmt->execute($data);
    //parse the JSON back to an array
    $data["teacherAvailability"] = json_decode($data["teacherAvailability"]);
    $data["lastChanged"] = date('Y-m-d H:i:s');
    $this->output = ["successful" => true, "data" => $data];
  }
}
