<?php
  session_start();
  include("func.php");
  $id = $_GET['id'];
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
      $query = "SELECT
		  p.id,
		  p.first_name,
		  p.last_name,
		  p.description,
		  p.active,
		  p.adult,
		  p.saved,
		  p.baptized,
		  p.member,
		  p.visitor,
		  p.street1,
		  p.street2,
		  p.city,
		  p.state,
		  p.zip,
		  p.email,
		  p.primary_phone,
		  p.secondary_phone,
		  f.id follow_up_id,
		  f.type follow_up_type,
		  DATE_FORMAT(f.follow_up_date,'%m/%d/%Y') follow_up_date,
		  f.comments follow_up_comments,
		  v.follow_up_id visitor_follow_up_id,
		  v.person_id,
		  fp.first_name follow_up_first_name,
		  fp.last_name follow_up_last_name,
		  fp.description follow_up_description
		FROM
		  People p
		  left outer join FollowUps f on f.follow_up_to_person_id=p.id
		  left outer join FollowUpVisitors v on f.id=v.follow_up_id
		  left outer join People fp on fp.id=v.person_id
		WHERE
		  p.id=:id
		ORDER BY
		  f.follow_up_date";
      $results = $f->fetchAndExecute($query, array(":id"=>$id));
      if(count($results) > 0) {
        $people = array();
        foreach($results as $key => $row) {
	  $p = NULL;
	  $j = NULL;
	  $foundPerson = FALSE;
	  // Check to see if we have already added the person
	  foreach($people as $k => $person) {
	    if(!isset($person['id'])) continue;
	    if($person['id'] == $row['id']) {
	      $j = $k;
	      $foundPerson = TRUE;
	      break;
	    }
	  }

	  // Set the person data if we have not encountered this person before
	  if($foundPerson == FALSE) {
	    $p = array();
	    $p['id'] = $row['id'];
	    $p['first_name'] = $row['first_name'];
	    $p['last_name'] = $row['last_name'];
	    $p['description'] = $row['description'];
	    $p['adult'] = $row['adult'] ? TRUE : FALSE;
	    $p['active'] = $row['active'] ? TRUE : FALSE;
	    $p['saved'] = $row['saved'] ? TRUE : FALSE;
	    $p['baptized'] = $row['baptized'] ? TRUE : FALSE;
	    $p['member'] = $row['member'] ? TRUE : FALSE;
	    $p['visitor'] = $row['visitor'] ? TRUE : FALSE;
	    $p['street1'] = $row['street1'];
	    $p['street2'] = $row['street2'];
	    $p['city'] = $row['city'];
	    $p['state'] = $row['state'];
	    $p['zip'] = $row['zip'];
	    $p['email'] = $row['email'];
	    $p['primary_phone'] = $row['primary_phone'];
	    $p['secondary_phone'] = $row['secondary_phone'];
	    $p['follow_ups'] = array();
	    array_push($people, $p);
	    $j = count($people) - 1;
	  }
	  
	  
	  if($row['follow_up_id'] != "" && $row['follow_up_id'] != NULL) {
	    $l = NULL;
	    $fo = NULL;
	    $foundFollowUp = FALSE;
	    // Check to see if we have already added this follow up
	    foreach($people[$j]['follow_ups'] as $m => $follow_up) {
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
	      $fo['typeCd'] = $row['follow_up_type'];
	      $fo['date'] = $row['follow_up_date'];
	      $fo['comments'] = $row['follow_up_comments'];
	      $fo['visitors'] = array();
	      $fo['visitorsIds'] = array();
	      
	      array_push($people[$j]['follow_ups'], $fo);
	      $l = count($people[$j]['follow_ups']) - 1;
	    }
	    
	    array_push($people[$j]['follow_ups'][$l]['visitors'], getDisplayName($row, "follow_up_"));
	    array_push($people[$j]['follow_ups'][$l]['visitorsIds'], $row['person_id']);
	  }
        }
        $dict['person'] = $people[0];
        $dict['success'] = TRUE;
        $_SESSION['scroll_to_id'] = $id;
      } else {
        $dict['success'] = FALSE;
      }
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