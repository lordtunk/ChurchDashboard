<?php
  session_start();
  include("../utils/func.php");
  $f = new Func();
  $text = trim($_GET['search']);
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
		  WHERE";
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
      $dict['people'] = $f->fetchAndExecute($query, $queryParams);
      
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = false;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>