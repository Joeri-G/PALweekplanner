<?php
namespace joeri_g\palweekplanner\v2\act;
/**
 * Class with all class related actions.
 * Confusion imminent
 */
class Classes {
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
      $this->output = ["successful" => false, "error" => "No request object connection provided"];
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

  private function list() {
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
        $stmt = $this->conn->prepare("SELECT year, name, userCreate, created, GUID FROM classes");
      }
      else {
        $stmt = $this->conn->prepare("SELECT year, name, GUID FROM classes");
      }
    }
    else {
      //is user is admin return more data
      if ($_SESSION["userLVL"] >= 3) {
        $stmt = $this->conn->prepare("SELECT year, name, userCreate, created, GUID FROM classes WHERE GUID = :id LIMIT 1");
      }
      else {
        $stmt = $this->conn->prepare("SELECT year, name, GUID FROM classes WHERE GUID = :id LIMIT 1");
      }
      $stmt->bindParam("id", $this->selector);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    if (!$data) {
      //if the selector is a wildcard return an empty array, else return an empty object
      $this->output = ($this->selector === "*") ? ["successful" => true, "data" => []] : ["successful" => false, "error" => "GUID does not exist in this collection"];
      return true;
    }
    //if the selector is a wildcard return the array with the data, else return only the first item in the array
    $data = ["successful" => true, "data" => ($this->selector === "*") ? $data : $data[0]];
    $this->output = $data;
  }

  private function add() {
    $keys = ["name", "year"];
    if (!$this->request->POSTisset($keys)) {
      $this->output = ["successful" => false, "error" => "Please set all keys", "keys" => $keys];
      http_response_code(400);
      return false;
    }

    $name = $_POST["name"];
    $year = $_POST['year'];
    $userCreate = $_SESSION["GUID"];
    $GUID = $this->db->generateGUID();

    $stmt = $this->conn->prepare("INSERT INTO classes (name, year, userCreate, GUID) VALUES (:name, :year, :userCreate, :GUID)");
    $stmt->execute([
      "name" => $name,
      "year" => $year,
      "userCreate" => $userCreate,
      "GUID" => $GUID
    ]);

    $data = ["successful" => true, "data" => []];
    $data["data"]["name"] = $name;
    $data["data"]["year"] = $year;
    $data["data"]["userCreate"] = $userCreate;
    $data["data"]["GUID"] = $GUID;

    $this->output = $data;
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
      http_response_code(400);
      return false;
    }
    if ($this->selector == "*") {
      $stmt = $this->conn->prepare("TRUNCATE TABLE classes");
      $stmt->execute();
    }
    else {
      $stmt = $this->conn->prepare("DELETE FROM classes WHERE GUID = :GUID");
      $stmt->execute(["GUID" => $this->selector]);
    }
  }

  public function update() {
    parse_str(file_get_contents("php://input"), $_PUT);
    //because the data is provided via a PUT request we cannot acces the data in the body through the $_POST variable and we have to manually parse and store it
    $keys = ["year", "name"];
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
    $name = $_PUT["name"];
    $year = $_PUT["year"];
    $userCreate = $_SESSION["GUID"];
    $created = date('Y-m-d H:i:s');
    $GUID = $this->selector;
    $stmt = $this->conn->prepare("UPDATE classes SET name = :name, year = :year, userCreate = :userCreate, created = :created WHERE GUID = :GUID");
    $stmt->execute([
      "name" => $name,
      "year" => $year,
      "userCreate" => $userCreate,
      "created" => $created,
      "GUID" => $GUID
    ]);
    $data = ["successful" => true, "data" => []];
    $data["data"]["name"] = $name;
    $data["data"]["year"] = $year;
    $data["data"]["userCreate"] = $userCreate;
    $data["data"]["GUID"] = $GUID;

    $this->output = $data;
  }
}
