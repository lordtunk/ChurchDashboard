<?php
  session_start();
  include("func.php");
  $f = new Func();
  $id = $_POST['id'];
  $personId = $_POST['personId'];
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
      $f->useTransaction = FALSE;
      $f->beginTransaction();
      
      $query = "DELETE FROM FollowUpVisitors WHERE follow_up_id=:id";
      $results = $f->executeAndReturnResult($query, array(":id"=>$id));
      $query = "DELETE FROM FollowUps WHERE id=:id";
      $results = $f->executeAndReturnResult($query, array(":id"=>$id));

      $f->commit();
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = FALSE;
      $dict['msg']= $e->getMessage();
    }
    if($dict['success'] == TRUE) {
        try {
            // Update the communication card options on the person
            $query = "update People set
                        commitment_baptism = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND commitment_baptism=1 AND follow_up_to_person_id = :person_id) > 0,
                        commitment_christ = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND commitment_christ=1 AND follow_up_to_person_id = :person_id) > 0,
                        commitment_ministry = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND commitment_ministry=1 AND follow_up_to_person_id = :person_id) > 0,
                        commitment_tithe = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND commitment_tithe=1 AND follow_up_to_person_id = :person_id) > 0,
                        info_ggroups = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND info_ggroups=1 AND follow_up_to_person_id = :person_id) > 0,
                        info_gkids = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND info_gkids=1 AND follow_up_to_person_id = :person_id) > 0,
                        info_gteams = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND info_gteams=1 AND follow_up_to_person_id = :person_id) > 0,
                        info_member = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND info_member=1 AND follow_up_to_person_id = :person_id) > 0,
                        info_next = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND info_next=1 AND follow_up_to_person_id = :person_id) > 0,
                        info_visit = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND info_visit=1 AND follow_up_to_person_id = :person_id) > 0
                    where
                        id = :person_id";
            $f->beginTransaction();
            $results = $f->executeAndReturnResult($query, 
                array(":person_id"=>$personId));
            $f->commit();
            
            
            $query = "
              SELECT
                  CASE WHEN p.commitment_christ = 1 THEN  'true' ELSE  'false' END commitment_christ,
                  CASE WHEN p.recommitment_christ = 1 THEN  'true' ELSE  'false' END recommitment_christ,
                  CASE WHEN p.commitment_tithe = 1 THEN  'true' ELSE  'false' END commitment_tithe,
                  CASE WHEN p.commitment_ministry = 1 THEN  'true' ELSE  'false' END commitment_ministry,
                  CASE WHEN p.commitment_baptism = 1 THEN  'true' ELSE  'false' END commitment_baptism,
                  CASE WHEN p.info_next = 1 THEN  'true' ELSE  'false' END info_next,
                  CASE WHEN p.info_gkids = 1 THEN  'true' ELSE  'false' END info_gkids,
                  CASE WHEN p.info_ggroups = 1 THEN  'true' ELSE  'false' END info_ggroups,
                  CASE WHEN p.info_gteams = 1 THEN  'true' ELSE  'false' END info_gteams,
                  CASE WHEN p.info_member = 1 THEN  'true' ELSE  'false' END info_member,
                  CASE WHEN p.info_visit = 1 THEN  'true' ELSE  'false' END info_visit
                FROM
                  People p
                WHERE
                  p.id=:id";
            $f->beginTransaction();
            $results = $f->fetchAndExecute($query, array(":id"=>$personId));
            $f->commit();
            $dict['communication_card_options'] = $results;

            $dict['success'] = TRUE;
        } catch (Exception $e) {
            $dict['success'] = FALSE;
            $dict['warning'] = 'The follow up was deleted but there was a problem updating the "Committing To" and "Interested In" information';
            $dict['msg']= $e->getMessage();
            $f->rollback();
        }
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>