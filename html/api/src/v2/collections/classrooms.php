<?php
namespace joeri_g\palweekplanner\v2\collections;
/**
 * Class with all classroom related actions.
 */
class Classrooms {
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
      $this->output = ["successful" => false, "error" => "No request object provided"];
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
      case 'GET': //list all classrooms or select a specific one
        $this->list();
        break;

      case 'POST':  //add a classroom (admin)
        $this->add();
        break;

      case 'DELETE':  //delete one or all classrooms (admin)
        $this->delete();
        break;

      case 'PUT': //update a classroom (admin)
        $this->update();
        break;

      default:
        http_response_code(405);
        $this->output = ["successful" => false, "error" => "Action could not be found"];
        break;
    }
  }

  private function list() {
    //check selector for validity
    if (!$this->request->checkSelector()) {
      $this->output = ["successful" => false, "error" => "Invalid selector"];
      http_response_code(400);
      return false;
    }
    //statement depends on selector
    //if wildcard return all classrooms
    if ($this->selector === "*") {
      //is user is admin return more data
      if ($_SESSION["userLVL"] >= 3) {
        $stmt = $this->conn->prepare("SELECT classroom, userCreate, lastChanged, GUID FROM classrooms");
      }
      else {
        $stmt = $this->conn->prepare("SELECT classroom, GUID FROM classrooms");
      }
    }
    else {
      //is user is admin return more data
      if ($_SESSION["userLVL"] >= 3) {
        $stmt = $this->conn->prepare("SELECT classroom, userCreate, lastChanged, GUID FROM classrooms WHERE GUID = :id LIMIT 1");
      }
      else {
        $stmt = $this->conn->prepare("SELECT classroom, GUID FROM classrooms WHERE GUID = :id LIMIT 1");
      }
      $stmt->bindParam("id", $this->selector);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);


    if (!$data) {
      $this->output = ($this->selector === "*") ? ["successful" => true, "data" => []] : ["successful" => false, "error" => "GUID does not exist in this collection"];
      return true;
    }
    $this->output = ["successful" => true, "data" => ($this->selector === "*") ? $data : $data[0]];
  }

  private function add() {
    $keys = ["classroom"];
    if (!$this->request->POSTisset($keys)) {
      $this->output = ["successful" => false, "error" => "Please set all keys", "keys" => $keys];
      http_response_code(400);
      return false;
    }

    $classroom = $_POST["classroom"];
    $userCreate = $_SESSION["GUID"];
    $GUID = $this->db->generateGUID();

    $stmt = $this->conn->prepare("INSERT INTO classrooms (classroom, userCreate, GUID) VALUES (:classroom, :userCreate, :GUID)");
    $data = [
      "classroom" => $classroom,
      "userCreate" => $userCreate,
      "GUID" => $GUID
    ];
    $stmt->execute($data);
    $data["lastChanged"] = date('Y-m-d H:i:s');
    $this->output = ["successful" => true, "data" => $data];
  }

  private function delete() {
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
      $stmt = $this->conn->prepare("TRUNCATE TABLE classrooms");
      $stmt->execute();
    }
    else {
      $stmt = $this->conn->prepare("DELETE FROM classrooms WHERE GUID = :GUID");
      $stmt->execute(["GUID" => $this->selector]);
    }
    $this->output = ["successful" => true];
  }

  public function update() {
    parse_str(file_get_contents("php://input"), $_PUT);
    //because the data is provided via a PUT request we cannot acces the data in the body through the $_POST variable and we have to manually parse and store it
    $keys = ["classroom"];
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
    //check if the user has sufficient permissions
    //we cannot update every classroom so a wildcard is not permitted
    if ($_SESSION["userLVL"] < 3 || $this->selector === "*") {
      $this->output = ["successful" => false, "error" => "Insufficient permissions"];
      http_response_code(400);
      return false;
    }
    $classroom = $_PUT["classroom"];
    $userCreate = $_SESSION["GUID"];
    $GUID = $this->selector;
    $stmt = $this->conn->prepare("UPDATE classrooms SET classroom = :classroom, userCreate = :userCreate, lastChanged = current_timestamp WHERE GUID = :GUID");
    $data = [
      "classroom" => $classroom,
      "userCreate" => $userCreate,
      "GUID" => $GUID
    ];
    $stmt->execute($data);
    $data["lastChanged"] = date('Y-m-d H:i:s');
    $this->output = ["successful" => true, "data" => $data];
  }
}
