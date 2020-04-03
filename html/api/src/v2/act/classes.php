<?php
namespace joeri_g\palweekplanner\v2\act;

/**
 * Class with all class related actions.
 * Confusion imminent
 */
class classes {
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
        $stmt = $this->conn->prepare("SELECT year, name, userCreate, created, GUID FROM classes WHERE GUID = :id");
      }
      else {
        $stmt = $this->conn->prepare("SELECT year, name, GUID FROM classes WHERE GUID = :id");
      }
      $stmt->bindParam("id", $this->selector);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    if (!$data) {
      echo "[]";
      return true;
    }
    echo json_encode($data);
  }

  private function add() {
    $keys = ["name", "year"];
    if (!$this->request->POSTisset($keys)) {
      echo "Please set all keys";
      foreach ($keys as $key) {
        echo "<br><b>".htmlentities($key)."</b>";
      }
      http_response_code(405);
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

    $data = [];
    $data[] = [];
    $data[0]["name"] = $name;
    $data[0]["year"] = $year;
    $data[0]["userCreate"] = $userCreate;
    $data[0]["GUID"] = $GUID;
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
      $stmt = $this->conn->prepare("TRUNCATE TABLE classes");
      $stmt->execute();
    }
    else {
      $stmt = $this->conn->prepare("DELETE FROM classes WHERE GUID = :GUID");
      $stmt->execute(["GUID" => $this->selector]);
    }
  }

  public function update() {
    //check selector for validity
    if (!$this->request->checkSelector()) {
      echo "Invalid selector";
      http_response_code(405);
      return false;
    }
    //check if the user has sufficient permissions
    //we cannot update every class so a wildcard is not permitted
    if ($_SESSION["userLVL"] < 3 && $this->selector == "*") {
      echo "Cannot update with a wildcard";
      http_response_code(405);
      return false;
    }
  }

}




 ?>
