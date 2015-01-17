<?php
  session_start();
  include("func.php");
  $f = new Func();
  $person = json_decode($_POST['person']);
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
      // Translate TRUE/FALSE to 1/0 so that log statements
      // are easier to read since FALSE does not display
      $person->adult = $person->adult ? 1 : 0;
      $person->active = $person->active ? 1 : 0;
      $person->baptized = $person->baptized ? 1 : 0;
      $person->saved = $person->saved ? 1 : 0;
      $person->member = $person->member ? 1 : 0;
      $person->visitor = $person->visitor ? 1 : 0;
      $person->first_name = trim($person->first_name);
      $person->last_name = trim($person->last_name);
      $person->description = trim($person->description);
      $person->street1 = trim($person->street1);
      $person->street2 = trim($person->street2);
      $person->city = trim($person->city);
      $person->state = trim($person->state);
      $person->zip = trim($person->zip);
      $person->email = trim($person->email);
      $person->primary_phone = trim($person->primary_phone);
      $person->secondary_phone = trim($person->secondary_phone);
      

      // Make sure their is a valid person id and
      // at least a first and last name or a description
      if($person->id < 0
        || $person->id == NULL
        || ($person->first_name == ""
          && $person->last_name == ""
          && $person->description == "")
        || ($person->description == ""
          && ($person->first_name == ""
            || $person->last_name == ""))) {
        throw new Exception('Person information missing');
      }
      
      if($person->first_name === ""){
        $person->first_name = NULL;
      }
      if($person->last_name === ""){
        $person->last_name = NULL;
      }
      $f->useTransaction = FALSE;
      $f->beginTransaction();
      // Add an Attendance record if a matching one does not exist, otherwise, update the existing
      $query = "UPDATE
                  People
                SET
                  first_name=:first_name,
                  last_name=:last_name,
                  description=:description,
                  adult=:adult,
                  active=:active,
                  baptized=:baptized,
                  saved=:saved,
                  member=:member,
                  visitor=:visitor,
                  street1=:street1,
                  street2=:street2,
                  city=:city,
                  state=:state,
                  zip=:zip,
                  email=:email,
                  primary_phone=:primary_phone,
                  secondary_phone=:secondary_phone,
                  last_modified_dt=NOW(),
                  modified_by=:modified_by
                WHERE
                  id=:id";
      $results = $f->executeAndReturnResult($query, 
	  array(":first_name"=>$person->first_name, 
		":last_name"=>$person->last_name, 
		":description"=>$person->description, 
		":adult"=>$person->adult, 
		":active"=>$person->active,
		":baptized"=>$person->baptized,
		":saved"=>$person->saved,
		":member"=>$person->member,
		":visitor"=>$person->visitor,
		":street1"=>$person->street1,
		":street2"=>$person->street2,
		":city"=>$person->city,
		":state"=>$person->state,
		":zip"=>$person->zip,
		":email"=>$person->email,
		":primary_phone"=>$person->primary_phone,
		":secondary_phone"=>$person->secondary_phone,
		":modified_by"=>$user_id, 
		":id"=>$person->id));
      
      foreach($person->follow_ups as $key => $follow_up) {
	if($follow_up->id < 0) {
	  $query = "INSERT INTO FollowUps 
		      (follow_up_to_person_id, type, follow_up_date, comments)
		    VALUES
		      (:person_id, :type, STR_TO_DATE(:follow_up_date,'%m/%d/%Y'), :comments)";
	  $follow_up->id = $f->queryLastInsertId($query, 
	      array(":person_id"=>$person->id,
		    ":type"=>$follow_up->typeCd,
		    ":follow_up_date"=>$follow_up->date,
		    ":comments"=>$follow_up->comments));
	} else {
	  $query = "UPDATE FollowUps SET 
		      follow_up_to_person_id = :person_id,
		      type = :type,
		      follow_up_date = STR_TO_DATE(:follow_up_date,'%m/%d/%Y'),
		      comments = :comments
		    WHERE
		      id=:follow_up_id";
	  $results = $f->executeAndReturnResult($query, 
	      array(":person_id"=>$person->id,
		    ":type"=>$follow_up->typeCd,
		    ":follow_up_date"=>$follow_up->date,
		    ":comments"=>$follow_up->comments,
		    ":follow_up_id"=>$follow_up->id));
		    
	  $query = "DELETE FROM FollowUpVisitors WHERE follow_up_id=:id";
	  $results = $f->executeAndReturnResult($query, array(":id"=>$follow_up->id));
	}
	$f->logMessage(implode(",", $follow_up->visitorsIds));
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
      }
      $f->commit();
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