<?php
  require("autoload.php");
  use Vimeo\Vimeo;
  class Func {
    private $db = NULL;
    private $logFileName = "../logs/log.txt";
    private $logFileNamePrefix = "../logs/log-";
    private $configFileName = "../config.ini";
    private $dbHost = "";
    private $dbName = "";
    private $dbUser = "";
    private $dbPass = "";
    private $client_id = "";
    private $client_secret = "";
    private $access_token = "";

    // INFO = 1
    // DEBUG = 2
    // WARNING = 3
    // ERROR = 4
    private $minSeverity = 1;

    public $useTransaction = TRUE;
    public function __construct() {
      $this->readConfig();
      
      $this->db = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPass);
      if (!$this->db) {
        $this->logMessage('Could not connect to DB', 4);
        die('Could not connect: ' . mysql_error());
      }
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    function readConfig() {
      try {
        if(file_exists($this->configFileName)) {
          $contents = parse_ini_file($this->configFileName);
          foreach($contents as $prop => $value) {
            $this->$prop = $value;
          }
        }
      } catch(Exception $e) {
        $this->logMessage($e->getMessage(), 4);
      }
    }

    function beginTransaction() {
      $this->db->beginTransaction();
    }

    function commit() {
      $this->db->commit();
    }

    function rollback() {
      $this->db->rollback();
    }
      
    function getUser() {
        $lib = new Vimeo($this->client_id, $this->client_secret);
        if (!empty($this->access_token)) {
            $lib->setToken($this->access_token);
            $user = $lib->request('/me');
        } else {
            $user = $lib->request('/users/dashron');
        }
        if ($user['status'] != 200) {
            $this->logMessage('Could not locate the requested resource uri [/me]'.$user);
            throw new Exception('Could not locate the requested resource uri [/me]');
        }
        return $user;
    }
      
    function generateUploadTicket() {
        $lib = new Vimeo($this->client_id, $this->client_secret);
        $ticket = null;
        if (!empty($this->access_token)) {
            $lib->setToken($this->access_token);
            $ticket = $lib->request('/me/videos', array('type'=>'streaming'), 'POST');
        } else {
            throw new Exception('Must have access token');
        }
        return $ticket;
    }
      
    function getQuota() {
        $lib = new Vimeo($this->client_id, $this->client_secret);
        $resource = null;
        if (!empty($this->access_token)) {
            $lib->setToken($this->access_token);
            $resource = $lib->request('/me');
        } else {
            throw new Exception('Must have access token');
        }
        if ($resource['status'] != 200) {
            $this->logMessage('Could not locate the requested resource uri [/me]'.$resource);
            throw new Exception('Could not locate the requested resource uri [/me]');
        }
        if(empty($resource['body']['upload_quota']))
            throw new Exception('The resource loaded does not have the upload_quota');
        return $resource['body']['upload_quota'];
    }

    function CWD() {
      return getCWD();
    }

    function writableFile() {
      if (is_writable($this->logFileName)) {
        if (!$handle = fopen($this->logFileName, 'a'))
          return "Error opening";
        if (fwrite($handle, date("m-d-y g:i a").$msg."\n") === FALSE)
          return "Error writing";
        fclose($handle);
        return "Success";
      }
      return "Not writable";
    }

    function fileStatus() {
      $status = $this->CWD();

      if (file_exists($this->logFileName))
        $status = $status." file_exists: TRUE";
      else
        $status = $status." file_exists: FALSE";

      if (is_readable($this->logFileName))
        $status = $status." is_readable: TRUE";
      else
        $status = $status." is_readable: FALSE";

      if (is_writable($this->logFileName))
        $status = $status." is_writable: TRUE";
      else
        $status = $status." is_writable: FALSE";

      return $status;
    }

    function logMessage($msg, $severity=1) {
      if($severity < $this->minSeverity) return;
      $log_file_name = $this->logFileNamePrefix.date("y-m-d").".txt";
      
      if (!file_exists($log_file_name))
          fopen($log_file_name, 'w');
      if (is_writable($log_file_name)) {
        if (!$handle = fopen($log_file_name, 'a'))
          return;
        if (fwrite($handle, date("m-d-y g:i a")."\t$msg\n") === FALSE)
          return;
        fclose($handle);
      }
    }

    function queryLastInsertId($query, $params=array()) {
      $sth = $this->db->prepare($query);
      $this->logMessage($query);
      try {
        foreach($params as $param => $value) {
          $this->logMessage("$param => $value");
          if($value === NULL)
            $sth->bindValue($param, NULL, PDO::PARAM_NULL);
          else
            $sth->bindValue($param, $value);
        }

        if($this->useTransaction == TRUE)
          $this->db->beginTransaction();
          
        $sth->execute();
        $lastInsertId = $this->db->lastInsertId();
        
        if($this->useTransaction == TRUE)
          $this->db->commit();
          
        $this->logMessage("Success: $lastInsertId");
        return $lastInsertId;
      } catch(PDOExecption $e) {
        if($this->useTransaction == TRUE)
          $this->db->rollback();
          
        $this->logMessage("Error: ".$e, 4);
        return NULL;
      }
    }
    function executeAndReturnResult($query, $params=array()) {
      $sth = $this->db->prepare($query);
      $this->logMessage($query);
      try {
        if(count($params) == 0)
          $this->logMessage('No parameters');
        foreach($params as $param => $value) {
          $this->logMessage("$param => $value");
          if($value === NULL)
            $sth->bindValue($param, NULL, PDO::PARAM_NULL);
          else
            $sth->bindValue($param, $value);
        }

        if($this->useTransaction == TRUE)
          $this->db->beginTransaction();
          
        $bool = $sth->execute();
        
        if($this->useTransaction == TRUE)
          $this->db->commit();
          
        $this->logMessage("Success: ".($bool ? "TRUE" : "FALSE"));
        return $bool;
      } catch(PDOExecption $e) {
        $this->logMessage("Error: ".$e, 4);
        
        if($this->useTransaction == TRUE)
          $this->db->rollback();
        else
          throw $e;
          
        return FALSE;
      }
    }
    function fetchAndExecute($query, $params=array()) {
      $sth = $this->db->prepare($query);
      $this->logMessage($query);
      try {
        if(count($params) == 0)
          $this->logMessage('No parameters');
        foreach($params as $param => $value) {
          $this->logMessage("$param => $value");
          if($value === NULL)
            $sth->bindValue($param, NULL, PDO::PARAM_NULL);
          else
            $sth->bindValue($param, $value);
        }

        if($this->useTransaction == TRUE)
          $this->db->beginTransaction();
          
        $sth->execute();
        
        if($this->useTransaction == TRUE)
          $this->db->commit();
          
        $this->logMessage("Success");
        return $sth->fetchAll(PDO::FETCH_ASSOC);
      } catch(PDOExecption $e) {
        $this->logMessage("Error: ".$e, 4);
        
        if($this->useTransaction == TRUE)
          $this->db->rollback();
        else
          throw $e;
        return FALSE;
      }
    }

    function isLoggedIn($user_id, $session_id) {
      $query = "SELECT id FROM Users WHERE id=:user_id AND session_id=:session_id";
      $results = $this->executeAndReturnResult($query, array(":user_id"=>$user_id, ":session_id"=>$session_id));
      $this->logMessage(print_r($results, true));
      if(!$results) {
        return FALSE;
      } else {
        return TRUE;
      }
    }

    function logout($user_id) {
      $query = "UPDATE Users SET session_id = NULL WHERE id=:user_id";
      $results = $this->executeAndReturnResult($query, array(":user_id"=>$user_id));
      if(!$results) {
        return FALSE;
      } else {
        return TRUE;
      }
    }

    function numRows($sth) {
      $count = 0;
      while($row = fetch($sth)) {
        $count++;
      }
      return $count;
    }
    function dbMultiConn($type=0) {
      switch($type) {
      // Read, Write, Modify
      case 0:
        // Connect to the database
        $link = mysqli_connect('db371484997.db.1and1.com', 'dbo371484997', 'bobgnome');
        break;
      // Read only
      case 1:
        $link = mysqli_connect('db371484997.db.1and1.com', 'dbo371484997', 'bobgnome');
        break;
      // Read and Write
      case 2:
        $link = mysqli_connect('db371484997.db.1and1.com', 'dbo371484997', 'bobgnome');
        break;
      }
      /* check connection */
      if (mysqli_connect_errno()) {
          die("Connect failed: ".mysqli_connect_error());
      }
      // Select the database
      $db = mysqli_select_db($link, 'db371484997');
      return $link;
    }
    function close() {
      mysql_close();
    }
  }
?>
