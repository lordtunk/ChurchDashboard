<?php
  session_start();
  include("../utils/func.php");
  $f = new Func();
  $dict = array();
  if(!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
    $dict['success'] = FALSE;
    $f->logMessage('Session information missing');
  } else {
    $session_id = $_SESSION['session_id'];
    $user_id = $_SESSION['user_id'];


    try {
      $dict['success'] = $f->isLoggedIn($user_id, $session_id);
    } catch (Exception $e) {
      $dict['success'] = FALSE;
      $f->logMessage($e->getMessage());
    }
  }
  if($dict['success'] == TRUE) {
    try {
      $queryParams = array();
	  $query = "SELECT
		    p.id,
		    p.first_name,
		    p.last_name,
		    p.description,
		    p.street1,
		    p.street2,
		    p.city,
		    p.state,
		    p.zip,
		    COALESCE(p.email, '') email,
            (select count(*) from Relationships r where r.person_id=p.id and r.type=1) > 0 as has_spouse
		  FROM
		    People p
		  WHERE
		  ";
		  
	  if(isset($_GET['search']) || isset($_POST['search'])) {
		  if(isset($_GET['search']))
			$text = trim($_GET['search']);
		  if(isset($_POST['search']))
			$text = trim($_POST['search']);
		  $arr = explode(" ", $text);
		  $first_name = "";
		  $last_name = "";
		  if(count($arr) > 1) {
			$first_name = trim($arr[0]);
			$last_name = trim($arr[1]);
		  } else {
			$arr = explode(",", $text);
			if(count($arr) > 1) {
			  $first_name = trim($arr[1]);
			  $last_name = trim($arr[0]);
			}
		  }
		  
		  if(strlen($first_name) > 0 && strlen($last_name) > 0) {
			$query = $query."
				first_name LIKE concat('%', :first_name, '%')
				AND last_name LIKE concat('%', :last_name, '%')";
			$queryParams["first_name"] = $first_name;
			$queryParams["last_name"] = $last_name;
		  } else {
			$query = $query."
					first_name LIKE concat('%', :text, '%') 
					OR last_name LIKE concat('%', :text, '%')";
			$queryParams[":text"] = $text;
		  }
	  } else if(isset($_POST['address'])) {
		  $whereClause = array();
		  $address = json_decode($_POST['address']);
		  $address->street1 = trim($address->street1);
		  $address->street2 = trim($address->street2);
		  $address->city = trim($address->city);
		  $address->state = trim($address->state);
		  $address->zip = trim($address->zip);
		  if($address->street1 == "" && $address->street2 == ""&& $address->city == ""&& $address->state == ""&& $address->zip == "")
			  throw new Exception();
		  if($address->street1 != "") {
			  array_push($whereClause, "street1 LIKE concat('%', :street1, '%')");
			  $queryParams[":street1"] = $address->street1;
		  }
		  if($address->street2 != "") {
			  array_push($whereClause, "street2 LIKE concat('%', :street2, '%')");
			  $queryParams[":street2"] = $address->street2;
		  }
		  if($address->city != "") {
			  array_push($whereClause, "city LIKE concat('%', :city, '%')");
			  $queryParams[":city"] = $address->city;
		  }
		  if($address->state != "") {
			  array_push($whereClause, "state LIKE concat('%', :state, '%')");
			  $queryParams[":state"] = $address->state;
		  }
		  if($address->zip != "") {
			  array_push($whereClause, "zip LIKE concat('%', :zip, '%')");
			  $queryParams[":zip"] = $address->zip;
		  }
		  $query = $query.join("
					AND ",$whereClause);
	  } else {
		  throw new Exception();
	  }
      $dict['people'] = $f->fetchAndExecute($query, $queryParams);
      
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = false;
	  $dict['msg']= $e->getMessage();
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>