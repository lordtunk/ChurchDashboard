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
    public $access_token = "";

    // INFO = 1
    // DEBUG = 2
    // WARNING = 3
    // ERROR = 4
    private $minSeverity = 1;

    public $useTransaction = TRUE;
    public function __construct() {
		try {
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

    function sendEmail($email, $subject, $body) {
        $headers = "From: auto@gcb.my-tasks.info \r\n";
        $headers .= "Reply-To: auto@gcb.my-tasks.info \r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        mail(strip_tags($email), $subject, $body, $headers);
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

    function getFollowUpReport($params, $queryFlags) {
        $where = "
          WHERE 
            p.adult = 1";
        $optionsArr = array();
        $orderBy = "
          ORDER BY
            p.last_name IS NOT NULL DESC,
            p.description IS NOT NULL DESC,
            p.last_name,
            p.first_name,
            p.description";
        $groupBy = "
          GROUP BY 
            p.id ";
        $having = "
          HAVING 
            ";
        $havingArr = array();
        $queryParams = array();
        if($queryFlags) {
            $query = "SELECT 
                    CASE WHEN vc.visit_count > 0 THEN  'true' ELSE  'false' END visited, 
                    CASE WHEN tyc.ty_card_sent_count > 0 THEN  'true' ELSE  'false' END ty_card_sent, 
                    DATE_FORMAT(f.follow_up_date, '%m/%d/%Y') communication_card_date, 
                    DATE_FORMAT(tyc.follow_up_date, '%m/%d/%Y') ty_card_date,
                    p.id, 
                    p.first_name, 
                    p.last_name, 
                    p.description,
                    p.primary_phone,
                    p.commitment_baptism,
                    p.baptized,
                    p.info_gkids,
                    p.info_next,
                    p.info_ggroups,
					p.info_gteams,
					p.info_member,
					p.info_visit,
					p.assigned_agent,
					p.commitment_christ,
					p.recommitment_christ,
					p.commitment_tithe,
					p.commitment_ministry,
					f.attendance_frequency,
                    CASE WHEN p.commitment_baptism = 1 THEN  'true' ELSE  'false' END commitment_baptism
                  FROM 
                    People p
                    LEFT OUTER JOIN FollowUps f ON f.follow_up_to_person_id = p.id AND f.type = 3
                    LEFT OUTER JOIN (SELECT COUNT(*) visit_count, follow_up_to_person_id FROM FollowUps WHERE type = 2 GROUP BY  follow_up_to_person_id)vc ON vc.follow_up_to_person_id = p.id
                    LEFT OUTER JOIN (SELECT COUNT(*) ty_card_sent_count, follow_up_to_person_id, follow_up_date FROM FollowUps WHERE type = 5 GROUP BY follow_up_to_person_id)tyc ON tyc.follow_up_to_person_id = p.id";
        } else {
            $query = "SELECT 
                    CASE WHEN vc.visit_count > 0 THEN  'true' ELSE  'false' END visited, 
                    CASE WHEN tyc.ty_card_sent_count > 0 THEN  'true' ELSE  'false' END ty_card_sent, 
                    DATE_FORMAT(f.follow_up_date, '%m/%d/%Y') communication_card_date, 
                    DATE_FORMAT(tyc.follow_up_date, '%m/%d/%Y') ty_card_date,
                    p.id, 
                    p.first_name, 
                    p.last_name, 
                    p.description,
                    p.primary_phone,
                    p.street1,
                    p.street2,
                    p.city,
                    p.state,
                    p.zip,
                    CASE WHEN p.commitment_baptism = 1 THEN  'true' ELSE  'false' END commitment_baptism
                  FROM 
                    People p
                    LEFT OUTER JOIN FollowUps f ON f.follow_up_to_person_id = p.id AND f.type = 3
					LEFT OUTER JOIN (SELECT COUNT(*) visit_count, follow_up_to_person_id FROM FollowUps WHERE type = 2 GROUP BY  follow_up_to_person_id)vc ON vc.follow_up_to_person_id = p.id
                    LEFT OUTER JOIN (SELECT COUNT(*) ty_card_sent_count, follow_up_to_person_id, follow_up_date FROM FollowUps WHERE type = 5 GROUP BY follow_up_to_person_id)tyc ON tyc.follow_up_to_person_id = p.id";
        }
                    

        if($params->active) {
            $where .= "
                AND p.active = 1";
        }     
        if($params->fromDate != "") {
            $queryParams[":fromDate"] = $params->fromDate;
            $where .= "
                AND f.follow_up_date >= STR_TO_DATE(:fromDate,'%m/%d/%Y')";
        }
        if($params->toDate != "") {
            $queryParams[":toDate"] = $params->toDate;
            $where .= "
                AND f.follow_up_date <= STR_TO_DATE(:toDate,'%m/%d/%Y')";
        }
        if($params->signed_up_for_baptism) {
            array_push($optionsArr, "p.commitment_baptism = 1");
        }
        if($params->baptized && $queryFlags == FALSE) {
            array_push($optionsArr, "p.baptized = 1");
        }
        if($params->interested_in_gkids) {
            array_push($optionsArr, "p.info_gkids = 1");
        }
        if($params->interested_in_next) {
            array_push($optionsArr, "p.info_next = 1");
        }
        if($params->interested_in_ggroups) {
            array_push($optionsArr, "p.info_ggroups = 1");
        }
        if($params->interested_in_gteams) {
            array_push($optionsArr, "p.info_gteams = 1");
        }
        if($params->interested_in_joining) {
            array_push($optionsArr, "p.info_member = 1");
        }
        if($params->would_like_visit) {
            array_push($optionsArr, "p.info_visit = 1");
        }
        if($params->no_agent && $queryFlags == FALSE) {
            array_push($optionsArr, "p.assigned_agent = 0");
        }
        if($params->commitment_christ) {
            array_push($optionsArr, "p.commitment_christ = 1");
        }
        if($params->recommitment_christ) {
            array_push($optionsArr, "p.recommitment_christ = 1");
        }
        if($params->commitment_tithe) {
            array_push($optionsArr, "p.commitment_tithe = 1");
        }
        if($params->commitment_ministry) {
            array_push($optionsArr, "p.commitment_ministry = 1");
        }
        if($params->attendance_frequency) {
            array_push($optionsArr, "f.attendance_frequency = 1");
        }

        if(count($optionsArr) > 0) {
            $where .= " 
                AND (";
            $where .= join(" 
                OR ", $optionsArr);
            $where .= ")";
        }
        if($params->not_visited) {
            array_push($havingArr, "visited =  'false'");
        }
        if($params->ty_card_not_sent) {
            array_push($havingArr, "ty_card_sent =  'false'");
        }
        $query .= $where.$groupBy;

        if(count($havingArr) > 0) {
            $query .= $having;
            $query .= join(" AND ", $havingArr);
        }

        $query .= $orderBy;

        return $this->fetchAndExecute($query, $queryParams);
    }
  }
?>
