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
            $person->assigned_agent = $person->assigned_agent ? 1 : 0;
            $person->first_name = trim($person->first_name);
            $person->last_name = trim($person->last_name);
            $person->description = trim($person->description);
            $person->first_visit = trim($person->first_visit);
            $person->street1 = trim($person->street1);
            $person->street2 = trim($person->street2);
            $person->city = trim($person->city);
            $person->state = trim($person->state);
            $person->zip = trim($person->zip);
            $person->email = trim($person->email);
            $person->primary_phone = trim($person->primary_phone);
            $person->secondary_phone = trim($person->secondary_phone);
//            $person->commitment_christ = $person->commitment_christ ? 1 : 0;
//            $person->recommitment_christ = $person->recommitment_christ ? 1 : 0;
//            $person->commitment_tithe = $person->commitment_tithe ? 1 : 0;
//            $person->commitment_ministry = $person->commitment_ministry ? 1 : 0;
//            $person->commitment_baptism = $person->commitment_baptism ? 1 : 0;
//            $person->info_next = $person->info_next ? 1 : 0;
//            $person->info_gkids = $person->info_gkids ? 1 : 0;
//            $person->info_ggroups = $person->info_ggroups ? 1 : 0;
//            $person->info_gteams = $person->info_gteams ? 1 : 0;
//            $person->info_member = $person->info_member ? 1 : 0;
//            $person->info_visit = $person->info_visit ? 1 : 0;


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
            $msg = "";
            if(strlen($person->first_name) > 50)
                $msg .= 'First Name cannot exceed 50 characters<br />';
            if(strlen($person->last_name) > 50)
                $msg .= 'Last Name cannot exceed 50 characters<br />';
            if(strlen($person->description) > 250)
                $msg .= 'Description cannot exceed 250 characters<br />';
            if(strlen($person->email) > 100)
                $msg .= 'Email cannot exceed 100 characters<br />';
            if(strlen($person->primary_phone) > 15)
                $msg .= 'Primary Phone cannot exceed 15 characters<br />';
            if(strlen($person->secondary_phone) > 15)
                $msg .= 'Secondary Phone cannot exceed 15 characters<br />';
            if(strlen($person->street1) > 100)
                $msg .= 'Street 1 cannot exceed 100 characters<br />';
            if(strlen($person->street2) > 100)
                $msg .= 'Street 2 cannot exceed 100 characters<br />';
            if(strlen($person->city) > 100)
                $msg .= 'City cannot exceed 100 characters<br />';
            if(strlen($person->zip) > 5)
                $msg .= 'Zip Code cannot exceed 5 characters<br />';
            if(strlen($person->street1) > 100)
                $msg .= 'Street 1 cannot exceed 100 characters<br />';

            if(strlen($msg) > 0)
                throw new Exception($msg);

            if($person->first_name === ""){
                $person->first_name = NULL;
            }
            if($person->last_name === ""){
                $person->last_name = NULL;
            }
            if($person->first_visit === ""){
                $person->first_visit = NULL;
            }
            $f->useTransaction = FALSE;
            $f->beginTransaction();
            // Add an Attendance record if a matching one does not exist, otherwise, update the existing
//            $query = "UPDATE
//                          People
//                        SET
//                          first_name=:first_name,
//                          last_name=:last_name,
//                          description=:description,
//                          first_visit=:first_visit,
//                          adult=:adult,
//                          active=:active,
//                          baptized=:baptized,
//                          saved=:saved,
//                          member=:member,
//                          visitor=:visitor,
//                          assigned_agent=:assigned_agent,
//                          street1=:street1,
//                          street2=:street2,
//                          city=:city,
//                          state=:state,
//                          zip=:zip,
//                          email=:email,
//                          primary_phone=:primary_phone,
//                          secondary_phone=:secondary_phone,
//                          commitment_christ=:commitment_christ,
//                          recommitment_christ=:recommitment_christ,
//                          commitment_tithe=:commitment_tithe,
//                          commitment_ministry=:commitment_ministry,
//                          commitment_baptism=:commitment_baptism,
//                          info_next=:info_next,
//                          info_gkids=:info_gkids,
//                          info_ggroups=:info_ggroups,
//                          info_gteams=:info_gteams,
//                          info_member=:info_member,
//                          info_visit=:info_visit,
//                          last_modified_dt=NOW(),
//                          modified_by=:modified_by
//                        WHERE
//                          id=:id";
            $query = "UPDATE
                          People
                        SET
                          first_name=:first_name,
                          last_name=:last_name,
                          description=:description,
                          first_visit=:first_visit,
                          adult=:adult,
                          active=:active,
                          baptized=:baptized,
                          saved=:saved,
                          member=:member,
                          visitor=:visitor,
                          assigned_agent=:assigned_agent,
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
                    ":first_visit"=>$person->first_visit, 
                    ":adult"=>$person->adult, 
                    ":active"=>$person->active,
                    ":baptized"=>$person->baptized,
                    ":saved"=>$person->saved,
                    ":member"=>$person->member,
                    ":visitor"=>$person->visitor,
                    ":assigned_agent"=>$person->assigned_agent,
                    ":street1"=>$person->street1,
                    ":street2"=>$person->street2,
                    ":city"=>$person->city,
                    ":state"=>$person->state,
                    ":zip"=>$person->zip,
                    ":email"=>$person->email,
                    ":primary_phone"=>$person->primary_phone,
                    ":secondary_phone"=>$person->secondary_phone,
//                    ":commitment_christ"=>$person->commitment_christ,
//                    ":recommitment_christ"=>$person->recommitment_christ,
//                    ":commitment_tithe"=>$person->commitment_tithe,
//                    ":commitment_ministry"=>$person->commitment_ministry,
//                    ":commitment_baptism"=>$person->commitment_baptism,
//                    ":info_next"=>$person->info_next,
//                    ":info_gkids"=>$person->info_gkids,
//                    ":info_ggroups"=>$person->info_ggroups,
//                    ":info_gteams"=>$person->info_gteams,
//                    ":info_member"=>$person->info_member,
//                    ":info_visit"=>$person->info_visit,
                    ":modified_by"=>$user_id, 
                    ":id"=>$person->id));

            $f->commit();
            $dict['success'] = TRUE;
            $dict['follow_ups'] = $person->follow_ups;
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