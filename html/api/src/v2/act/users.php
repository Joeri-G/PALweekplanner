<?php
namespace joeri_g\palweekplanner\v2\act;
/**
 * Class with all user related actions
 */
class Users {
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
        $stmt = $this->conn->prepare("SELECT username, userLVL, lastLoginIP, lastLoginTime, lastChanged, GUID FROM users");
      }
      else {
        $stmt = $this->conn->prepare("SELECT username, userLVL, GUID FROM users");
      }
    }
    else {
      //is user is admin return more data
      if ($_SESSION["userLVL"] >= 3) {
        $stmt = $this->conn->prepare("SELECT username, userLVL, lastLoginIP, lastLoginTime, lastChanged, GUID FROM users WHERE GUID = :id LIMIT 1");
      }
      else {
        $stmt = $this->conn->prepare("SELECT username, userLVL, GUID FROM users WHERE GUID = :id LIMIT 1");
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
    $this->output = $data;
  }

  public function add() {
    $keys = ["username", "password", "userLVL"];
    if (!$this->request->POSTisset($keys)) {
    $this->output = ["successful" => false, "error" => "Please set all keys", "keys" => $keys];
      http_response_code(400);
      return false;
    }

    if (!is_int((int) $_POST["userLVL"])) {
      http_response_code(400);
      return false;
    }

    $username = $_POST["username"];
    $userLVL = $_POST["userLVL"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $lastLoginIP = "127.0.0.1";
    $lastChanged = date('Y-m-d H:i:s');
    $GUID = $this->db->generateGUID();

    $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE username = :username");
    $stmt->execute(["username" => $username]);

    if ($stmt->rowCount() > 0) {
      $this->output = ["successful" => false, "error" => "Username already taken"];
      return false;
    }
    $stmt = null;

    $stmt = $this->conn->prepare("INSERT INTO users (username, password, userLVL, lastLoginIP, lastChanged, GUID)
    VALUES (:username, :password, :userLVL, :lastLoginIP, :GUID)");
    $data = [
      "username" => $username,
      "password" => $password,
      "userLVL" => $userLVL,
      "lastLoginIP" => $lastLoginIP,
      "lastChanged" => $lastChanged,
      "GUID" => $GUID
    ];
    $stmt->execute($data);
    $data = ["successful" => true, "data" => $data];

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

      $this->output = ["successful" => false, "error" => "Insufficient permissions"];
      http_response_code(400);
      return false;
    }
    if ($this->selector == "*") {
      $stmt = $this->conn->prepare("TRUNCATE TABLE users");
      $stmt->execute();
    }
    else {
      $stmt = $this->conn->prepare("DELETE FROM users WHERE GUID = :GUID");
      $stmt->execute(["GUID" => $this->selector]);
    }

    $this->output = ["successful" => true];
  }

  public function update() {
    parse_str(file_get_contents("php://input"), $_PUT);
    //because the data is provided via a PUT request we cannot acces the data in the body through the $_POST variable and we have to manually parse and store it
    $keys = ["username", "userLVL"];
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


    $username = $_PUT["username"];
    $userLVL = $_PUT["userLVL"];
    $GUID = $this->selector;
    $lastChanged = date('Y-m-d H:i:s');

    //make sure the username has not already been taken
    $stmt = $this->conn->prepare("SELECT 1 FROM users WHERE username = :username AND GUID != :GUID");
    $stmt->execute(["username" => $username, "GUID" => $GUID]);

    if ($stmt->rowCount() > 0) {
      $this->output = ["successful" => false, "error" => "Username already taken"];
      return false;
    }
    $stmt = null;

    //depending on wether or not the password has been set update all the userdata or the userdata minus the password
    $stmt = $this->conn->prepare("UPDATE users SET username = :username, userLVL = :userLVL WHERE GUID = :GUID");
    $data = [
      "username" => $username,
      "userLVL" => $userLVL,
      "lastChanged" => $lastChanged,
      "GUID" => $GUID
    ];
    if (isset($_PUT["password"])) {
      $stmt = null;
      $stmt = $this->conn->prepare("UPDATE users SET username = :username, password = :password, userLVL = :userLVL, lastChanged = :lastChanged WHERE GUID = :GUID");
      $password = password_hash($_PUT["password"], PASSWORD_DEFAULT);
      $data["password"] = $password;
    }
    $stmt->execute($data);

    $data = ["successful" => true, "data" => [
      "username" => $username,
      "userLVL" => $userLVL,
      "lastChanged" => $lastChanged,
      "GUID" => $GUID
      ]
    ];

    $this->output = $data;
  }

}
