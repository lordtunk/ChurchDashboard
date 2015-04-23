<?php
  session_start();
  include("func.php");
  $f = new Func();
  $dict = array();
  $followUpDate = $_POST['date'];
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
      $query = "SELECT
		  f.follow_up_to_person_id person_id,
		  p.first_name,
		  p.last_name,
		  p.description,
		  f.id follow_up_id,
		  f.type follow_up_type,
		  DATE_FORMAT(f.follow_up_date,'%m/%d/%Y') follow_up_date,
		  f.comments follow_up_comments,
		  v.follow_up_id visitor_follow_up_id,
		  v.person_id visitor_person_id,
		  fp.first_name follow_up_first_name,
		  fp.last_name follow_up_last_name,
		  fp.description follow_up_description
		FROM
		  FollowUps f
		  inner join People p on p.id=f.follow_up_to_person_id
		  left outer join FollowUpVisitors v on f.id=v.follow_up_id
		  left outer join People fp on fp.id=v.person_id
		WHERE
		  DATE_FORMAT(f.creation_dt,'%c/%e/%Y') = :date
		ORDER BY
		  f.follow_up_date";
      $results = $f->fetchAndExecute($query, array(":date"=>$followUpDate));
      $follow_ups = array();
      foreach($results as $key => $row) {
	$l = NULL;
	$fo = NULL;
	$foundFollowUp = FALSE;
	// Check to see if we have already added this follow up
	foreach($follow_ups as $m => $follow_up) {
	  if(!isset($follow_up['id'])) continue;
	  if($follow_up['id'] == $row['follow_up_id']) {
	    $l = $m;
	    $foundFollowUp = TRUE;
	    break;
	  }
	}
	// Set the person data if we have not encountered this person before
	if($foundFollowUp == FALSE) {
	  $fo = array();
	  $fo['id'] = $row['follow_up_id'];
	  $fo['personId'] = $row['person_id'];
	  $fo['name'] = getDisplayName($row);
	  $fo['typeCd'] = $row['follow_up_type'];
	  $fo['date'] = $row['follow_up_date'];
	  $fo['comments'] = $row['follow_up_comments'];
	  $fo['visitors'] = array();
	  $fo['visitorsIds'] = array();
	  
	  array_push($follow_ups, $fo);
	  $l = count($follow_ups) - 1;
	}
	
	array_push($follow_ups[$l]['visitors'], getDisplayName($row, "follow_up_"));
	array_push($follow_ups[$l]['visitorsIds'], $row['visitor_person_id']);
      }
      $dict['follow_ups'] = $follow_ups;
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = false;
      $dict['errorMsg'] = $e;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
  
  function getDisplayName($person, $prefix="") {
    if($person === null) return '';
    
    if($person[$prefix.'last_name'] && $person[$prefix.'first_name']) {
      return $person[$prefix.'first_name'] . " " . $person[$prefix.'last_name'];
    } else if($person[$prefix.'first_name']) {
      return $person[$prefix.'first_name'];
    } else if($person[$prefix.'last_name']) {
      return $person[$prefix.'last_name'];
    }
    return $person[$prefix.'description'];
  }
?>