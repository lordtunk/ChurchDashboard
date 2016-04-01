<?php
    session_start();
    include("func.php");
    $f = new Func();
    $follow_up = json_decode($_POST['follow_up']);
    $spouseFollowUpId = "";
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
            $options = $follow_up->communication_card_options;
            $f->useTransaction = FALSE;
            $f->beginTransaction();
            if($follow_up->id < 0) {
                if(isset($follow_up->communication_card_options)) {
                    $query = "INSERT INTO FollowUps 
                                (follow_up_to_person_id, type, follow_up_date, comments, last_modified_dt, modified_by, creation_dt, created_by, commitment_christ, recommitment_christ, commitment_tithe, commitment_ministry, commitment_baptism, info_next, info_gkids, info_ggroups, info_gteams, info_member, info_visit, attendance_frequency)
                              VALUES
                                (:person_id, :type, STR_TO_DATE(:follow_up_date,'%m/%d/%Y'), :comments, NOW(), :user_id, NOW(), :user_id, :commitment_christ, :recommitment_christ, :commitment_tithe, :commitment_ministry, :commitment_baptism, :info_next, :info_gkids, :info_ggroups, :info_gteams, :info_member, :info_visit, :attendance_frequency)";
                    $follow_up->id = $f->queryLastInsertId($query, 
                        array(":person_id"=>$follow_up->personId,
                              ":type"=>$follow_up->typeCd,
                              ":follow_up_date"=>$follow_up->date,
                              ":comments"=>$follow_up->comments,
                              ":user_id"=>$user_id,
                              ":attendance_frequency"=>$options->frequency == '' ? NULL : $options->frequency,
                              ":commitment_christ"=>$options->commitment_christ,
                              ":recommitment_christ"=>$options->recommitment_christ,
                              ":commitment_tithe"=>$options->commitment_tithe,
                              ":commitment_ministry"=>$options->commitment_ministry,
                              ":commitment_baptism"=>$options->commitment_baptism,
                              ":info_next"=>$options->info_next,
                              ":info_gkids"=>$options->info_gkids,
                              ":info_ggroups"=>$options->info_ggroups,
                              ":info_gteams"=>$options->info_gteams,
                              ":info_member"=>$options->info_member,
                              ":info_visit"=>$options->info_visit));
                    if($follow_up->spouseId !== "") {
                        $spouseFollowUpId = $f->queryLastInsertId($query, 
                            array(":person_id"=>$follow_up->spouseId,
                                  ":type"=>$follow_up->typeCd,
                                  ":follow_up_date"=>$follow_up->date,
                                  ":comments"=>$follow_up->comments,
                                  ":user_id"=>$user_id,
                                  ":attendance_frequency"=>$options->frequency == '' ? NULL : $options->frequency,
                                  ":commitment_christ"=>$options->commitment_christ,
                                  ":recommitment_christ"=>$options->recommitment_christ,
                                  ":commitment_tithe"=>$options->commitment_tithe,
                                  ":commitment_ministry"=>$options->commitment_ministry,
                                  ":commitment_baptism"=>$options->commitment_baptism,
                                  ":info_next"=>$options->info_next,
                                  ":info_gkids"=>$options->info_gkids,
                                  ":info_ggroups"=>$options->info_ggroups,
                                  ":info_gteams"=>$options->info_gteams,
                                  ":info_member"=>$options->info_member,
                                  ":info_visit"=>$options->info_visit));
                    }
                }
            } else {
                $query = "UPDATE FollowUps SET 
                            follow_up_to_person_id = :person_id,
                            type = :type,
                            follow_up_date = STR_TO_DATE(:follow_up_date,'%m/%d/%Y'),
                            comments = :comments,
                            attendance_frequency = :attendance_frequency,
                            commitment_christ=:commitment_christ,
                            recommitment_christ=:recommitment_christ,
                            commitment_tithe=:commitment_tithe,
                            commitment_ministry=:commitment_ministry,
                            commitment_baptism=:commitment_baptism,
                            info_next=:info_next,
                            info_gkids=:info_gkids,
                            info_ggroups=:info_ggroups,
                            info_gteams=:info_gteams,
                            info_member=:info_member,
                            info_visit=:info_visit,
                            last_modified_dt = NOW(),
                            modified_by = :user_id
                          WHERE
                            id=:follow_up_id";
                $results = $f->executeAndReturnResult($query, 
                    array(":person_id"=>$follow_up->personId,
                          ":type"=>$follow_up->typeCd,
                          ":follow_up_date"=>$follow_up->date,
                          ":comments"=>$follow_up->comments,
                          ":attendance_frequency"=>$options->frequency == '' ? NULL : $options->frequency,
                          ":commitment_christ"=>$options->commitment_christ,
                          ":recommitment_christ"=>$options->recommitment_christ,
                          ":commitment_tithe"=>$options->commitment_tithe,
                          ":commitment_ministry"=>$options->commitment_ministry,
                          ":commitment_baptism"=>$options->commitment_baptism,
                          ":info_next"=>$options->info_next,
                          ":info_gkids"=>$options->info_gkids,
                          ":info_ggroups"=>$options->info_ggroups,
                          ":info_gteams"=>$options->info_gteams,
                          ":info_member"=>$options->info_member,
                          ":info_visit"=>$options->info_visit,
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
                    
                    if($follow_up->spouseId !== "") {
                        $results = $f->executeAndReturnResult($query, 
                          array(":follow_up_id"=>$spouseFollowUpId,
                                ":person_id"=>$visitor_id));
                    }
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
        if($dict['success'] == TRUE) {
            try {
                // Update the communication card options on the person
                $query = "update People set
                            commitment_baptism = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND commitment_baptism=1 AND follow_up_to_person_id = :person_id) > 0,
                            commitment_christ = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND commitment_christ=1 AND follow_up_to_person_id = :person_id) > 0,
                            recommitment_christ = (SELECT COUNT(*) FROM FollowUps WHERE type = 3 AND recommitment_christ=1 AND follow_up_to_person_id = :person_id) > 0,
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
                    array(":person_id"=>$follow_up->personId));
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
                $results = $f->fetchAndExecute($query, array(":id"=>$follow_up->personId));
                $f->commit();
                $dict['communication_card_options'] = $results;
                $dict['success'] = TRUE;
            } catch (Exception $e) {
                $dict['success'] = FALSE;
                $dict['warning'] = 'The follow up was saved but there was a problem updating the "Committing To" and "Interested In" information';
                $dict['msg']= $e->getMessage();
                $f->rollback();
            }
        }
    } else {
    $dict['error'] = 1;
    }
    echo json_encode($dict);
?>
