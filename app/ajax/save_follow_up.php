<?php
  session_start();
  include("func.php");
  $f = new Func();
  $follow_up = json_decode($_POST['follow_up']);
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
    $dict['success'] = FALSE;
    try {
      if($follow_up->date === ""){
	$follow_up->date = NULL;
      }
      $f->useTransaction = FALSE;
      $f->beginTransaction();
      if($follow_up->id < 0) {
	$query = "INSERT INTO FollowUps 
		    (follow_up_to_person_id, type, follow_up_date, comments, last_modified_dt, modified_by, creation_dt, created_by)
		  VALUES
		    (:person_id, :type, STR_TO_DATE(:follow_up_date,'%m/%d/%Y'), :comments, NOW(), :user_id, NOW(), :user_id)";
	$follow_up->id = $f->queryLastInsertId($query, 
	    array(":person_id"=>$follow_up->personId,
		  ":type"=>$follow_up->typeCd,
		  ":follow_up_date"=>$follow_up->date,
		  ":comments"=>$follow_up->comments,
		  ":user_id"=>$user_id));
      } else {
	$query = "UPDATE FollowUps SET 
		    follow_up_to_person_id = :person_id,
		    type = :type,
		    follow_up_date = STR_TO_DATE(:follow_up_date,'%m/%d/%Y'),
		    comments = :comments,
		    last_modified_dt = NOW(),
		    modified_by = :user_id
		  WHERE
		    id=:follow_up_id";
	$results = $f->executeAndReturnResult($query, 
	    array(":person_id"=>$follow_up->person_id,
		  ":type"=>$follow_up->typeCd,
		  ":follow_up_date"=>$follow_up->date,
		  ":comments"=>$follow_up->comments,
		  ":user_id"=>$user_id,
		  ":follow_up_id"=>$follow_up->id));
		  
	$query = "DELETE FROM FollowUpVisitors WHERE follow_up_id=:id";
	$results = $f->executeAndReturnResult($query, array(":id"=>$follow_up->id));
      }
      if(count($follow_up->visitorsIds) > 0) {
	foreach($follow_up->visitorsIds as $k => $visitor_id) {
	  $query = "INSERT INTO FollowUpVisitors 
		      (follow_up_id, person_id)
		    VALUES
		      (:follow_up_id, :person_id)";
	  $results = $f->executeAndReturnResult($query, 
	      array(":follow_up_id"=>$follow_up->id,
		    ":person_id"=>$visitor_id));
	}
      }
      $f->commit();
      
      $dict['follow_up_id'] = $follow_up->id;
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = FALSE;
      $dict['msg']= $e->getMessage();
      $f->rollback();
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>