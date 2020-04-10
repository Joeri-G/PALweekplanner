<?php
namespace joeri_g\palweekplanner\v2\conf;
/**
 * Object with all the functions that are needed to directly interface with the database
 */
class Database {
  //DB Param
  private $host = "localhost";
  private $db_name = "planner_v2";
  private $username = "root";
  private $password = "";
  public $conn = null;

  public $tables = ["users"];

  public function connect($errmode = 0) {
    $this->conn = null;
    //wrap inside try catch because connection might fail
    try {
      $this->conn = new \PDO("mysql:host=$this->host;dbname=$this->db_name;",
                            $this->username, $this->password);
      // level of error erporting depends on given errormode (0 low -> 2 high)
      switch ($errmode) {
        case 0:
          $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
          break;
        case 1:
          $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
          break;
        case 2:
          $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
          break;
        default:
          echo json_encode(["succesful" => false, "error" => "Unknown ERRMODE; please eneter 0 for lowest and 2 for highest"]);
          break;
      }
      $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $error) {
      http_response_code(500);
      echo json_encode(["succesful" => false, "error" => "Connection Error:".$error->getMessage()]);
      return false;
    }
    return $this->conn;
  }

  public function generateGUID() {
    //generate a new and unique GUID
    do {
      $id = $this->GUIDv4();
    } while (!$this->checkGUID($id));
    return $id;
  }

  //function that checks if a guid already exists in the db
  public function checkGUID($guid = "00000000-0000-0000-0000-000000000000") {
    if ($this->conn === null) {
      $this->connect();
    }
    foreach ($this->tables as $table) {
      //make sure the table does not contain any illegal characters
      foreach ([" ", ";", "'", "\""] as $forbidden) {
        if (strpos($table, $forbidden)) {
          echo json_encode(["succesful" => false, "error" => "Forbidden table name".htmlentities($table)]);
          return false;
        }
      }
      //THIS IS BAD PRACTICE BUT RN I DONT KNOW OF ANY OTHER WAY TO DO IT
      //Check all tables for GUID
      $stmt = $this->conn->prepare("SELECT 1 FROM $table WHERE GUID = :GUID");
      $stmt->execute(["GUID" => $guid]);
      if ($stmt->rowCount() > 0) {
        return true;
      }
    }
    return true;
  }

  /**
  * https://www.php.net/manual/en/function.com-create-guid.php#119168
  * Returns a GUIDv4 string
  *
  * Uses the best cryptographically secure method
  * for all supported pltforms with fallback to an older,
  * less secure version.
  *
  * @param bool $trim
  * @return string
  */
  function GUIDv4 ($trim = true){
      // Windows
      if (function_exists('com_create_guid') === true) {
          if ($trim === true)
              return trim(com_create_guid(), '{}');
          else
              return com_create_guid();
      }

      // OSX/Linux
      if (function_exists('openssl_random_pseudo_bytes') === true) {
          $data = openssl_random_pseudo_bytes(16);
          $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
          $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
          return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
      }

      // Fallback (PHP 4.2+)
      mt_srand((double)microtime() * 10000);
      $charid = strtolower(md5(uniqid(rand(), true)));
      $hyphen = chr(45);                  // "-"
      $lbrace = $trim ? "" : chr(123);    // "{"
      $rbrace = $trim ? "" : chr(125);    // "}"
      $guidv4 = $lbrace.
                substr($charid,  0,  8).$hyphen.
                substr($charid,  8,  4).$hyphen.
                substr($charid, 12,  4).$hyphen.
                substr($charid, 16,  4).$hyphen.
                substr($charid, 20, 12).
                $rbrace;
      return $guidv4;
  }
}
