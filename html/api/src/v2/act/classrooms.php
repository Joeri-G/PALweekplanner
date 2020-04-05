<?php
namespace joeri_g\palweekplanner\v2\act;

/**
 * Class with all classroom related actions.
 */
class classrooms {
  public $selector;
  public $action;
  private $db;
  private $conn;
  function __construct() {
  }

  public function act($db = null, $request = null) {
    //make sure the db and PDO objects are provided and add them
    if (is_null($db) || is_null($db->conn)) {
      http_response_code(500);
      echo "No db connection provided";
      return false;
    }
    $this->db = $db;
    $this->conn = $db->conn;


    if (is_null($request)) {
      http_response_code(500);
      echo "No request object connection provided";
      return false;
    }

    $this->request = $request;
    $this->action = $this->request->action;
    $this->selector = $this->request->selector;

    if (is_null($this->selector)) {
      http_response_code(405);
      echo "No selector provided";
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
        echo "Action could not be found";
        break;
    }
  }

  private function list() {
    //check selector for validity
    if (!$this->request->checkSelector()) {
      echo "INVALID SELECTOR";
      http_response_code(405);
      return false;
    }
    //statement depends on selector
    //if wildcard return all classrooms
    if ($this->selector === "*") {
      //is user is admin return more data
      if ($_SESSION["userLVL"] >= 3) {
        $stmt = $this->conn->prepare("SELECT classroom, userCreate, created, GUID FROM classrooms");
      }
      else {
        $stmt = $this->conn->prepare("SELECT classroom, GUID FROM classrooms");
      }
    }
    else {
      //is user is admin return more data
      if ($_SESSION["userLVL"] >= 3) {
        $stmt = $this->conn->prepare("SELECT classroom, userCreate, created, GUID FROM classrooms WHERE GUID = :id LIMIT 1");
      }
      else {
        $stmt = $this->conn->prepare("SELECT classroom, GUID FROM classrooms WHERE GUID = :id LIMIT 1");
      }
      $stmt->bindParam("id", $this->selector);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    if (!$data) {
      echo ($selector === "*") ? "[]" : "{}";
      return true;
    }
    echo json_encode(($this->selector === "*") ? $data : $data[0]);
  }

  private function add() {
    $keys = ["classroom"];
    if (!$this->request->POSTisset($keys)) {
      echo "Please set all keys";
      foreach ($keys as $key) {
        echo "<br><b>".htmlentities($key)."</b>";
      }
      http_response_code(405);
      return false;
    }

    $classroom = $_POST["classroom"];
    $userCreate = $_SESSION["GUID"];
    $GUID = $this->db->generateGUID();

    $stmt = $this->conn->prepare("INSERT INTO classrooms (classroom, userCreate, GUID) VALUES (:classroom, :userCreate, :GUID)");
    $stmt->execute([
      "classroom" => $classroom,
      "userCreate" => $userCreate,
      "GUID" => $GUID
    ]);

    $data = [];
    $data["classroom"] = $classroom;
    $data["userCreate"] = $userCreate;
    $data["GUID"] = $GUID;
    header('Content-Type: application/json');
    echo json_encode($data);
  }

  private function delete() {
    //check selector for validity
    if (!$this->request->checkSelector()) {
      echo "INVALID SELECTOR";
      http_response_code(405);
      return false;
    }
    //check if the user has sufficient permissions
    if ($_SESSION["userLVL"] < 3) {
      http_response_code(405);
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
  }

  public function update() {
    parse_str(file_get_contents("php://input"), $_PUT);
    //because the data is provided via a PUT request we cannot acces the data in the body through the $_POST variable and we have to manually parse and store it
    $keys = ["classroom"];
    if (!$this->request->PUTisset($keys)) {
      echo "Please set all keys";
      foreach ($keys as $key) {
        echo "<br><b>".htmlentities($key)."</b>";
      }
      http_response_code(405);
      return false;
    }
    //check selector for validity
    if (!$this->request->checkSelector()) {
      echo "Invalid selector";
      http_response_code(405);
      return false;
    }
    //check if the user has sufficient permissions
    //we cannot update every classroom so a wildcard is not permitted
    if ($_SESSION["userLVL"] < 3 || $this->selector === "*") {
      http_response_code(405);
      return false;
    }
    $classroom = $_PUT["classroom"];
    $userCreate = $_SESSION["GUID"];
    $created = date('Y-m-d H:i:s');
    $GUID = $this->selector;
    $stmt = $this->conn->prepare("UPDATE classrooms SET classroom = :classroom, userCreate = :userCreate, created = :created WHERE GUID = :GUID");
    $stmt->execute([
      "classroom" => $classroom,
      "userCreate" => $userCreate,
      "created" => $created,
      "GUID" => $GUID
    ]);
    $data = [];
    $data["classroom"] = $classroom;
    $data["userCreate"] = $userCreate;
    $data["GUID"] = $GUID;
    header('Content-Type: application/json');
    echo json_encode($data);
  }
}
