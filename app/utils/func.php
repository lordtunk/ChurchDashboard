<?php
  class Func {
    private $db = NULL;
    private $env  = "dev";
    private $logFileNamePrefix = "log-";
    private $configFileName = "config.ini";
    private $dbHost = "";
    private $dbName = "";
    private $dbUser = "";
    private $dbPass = "";
	private $urlRoot = "";

    // INFO = 1
    // DEBUG = 2
    // WARNING = 3
    // ERROR = 4
    private $minSeverity = 1;

    public $useTransaction = TRUE;
    public function __construct() {
		try {
			$root = $this->getRootDirectory();
			$this->logFileNamePrefix = $root."logs\\".$this->logFileNamePrefix;
			$this->configFileName = $root.$this->configFileName;
			
			$this->readConfig();
			
			$this->db = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPass);
			if (!$this->db) {
			$this->logMessage('Could not connect to DB', 4);
			die('Could not connect: ' . mysql_error());
			}
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(Exception $e) {
        $this->logMessage($e->getMessage(), 4);
      }
    }

    function readConfig() {
      try {
        /*
         * Expected config file format:
            minSeverity = 1
            dbHost = "localhost"
            dbName= "dbName"
            dbUser = "user"
            dbPass = "password"
			urlRoot = "localhost"
         */
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
	
	function getUrl() {
		return $this->urlRoot;
	}
	
	function getLoginUrl() {
		return $this->urlRoot."login.php";
	}
        
    function getEnvironment() {
        return $this->env;
    }
	
	function getRootDirectory() {
		$root = "";
		// Convert slashes to all one type to account for different OS's
		$cwd = str_replace("\\", "/", getcwd());
		$arr = explode("/", $cwd);
		$ind = array_search("app", $arr);
		
		if($ind === FALSE) {
			$ind = array_search("dist", $arr);
		} 
		if($ind === FALSE) {
			return "$cwd/";
		}
		// Get the whole path up to either the app\ or dist\ directories
		$root = implode("/", array_slice($arr, 0, $ind));
		return "$root/";
	}
	
	function getLogPath() {
		return $this->getRootDirectory()."logs/";
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
      
    function sendEmail($email, $subject, $body) {
        $headers = "From: auto@gcb.my-tasks.info \r\n";
        $headers .= "Reply-To: auto@gcb.my-tasks.info \r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        mail(strip_tags($email), $subject, $body, $headers);
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
    /*
     * Use for inserts where you need the generated inserted Id
     */
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
    /*
     * Use for non queries
     */
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
    /*
     * Use for queries
     */
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
	
	function doRedirect($session) {
		if(!isset($session['user_id']) || !isset($session['session_id'])) {
			$this->logMessage('Session information missing');
		  } else {
			$session_id = $session['session_id'];
			$user_id = $session['user_id'];

			try {
			  if($this->isLoggedIn($session['user_id'], $session['session_id'])) {
				return FALSE;
			  }
			} catch (Exception $e) {
			  $this->logMessage($e->getMessage());
			}
		  }
		return TRUE;
	}
  }
?>
