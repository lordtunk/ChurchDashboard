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
            $query = "
              SELECT
                  DATE_FORMAT(at.attendance_dt,'%m/%d/%Y') first_attendance_dt,
                  p.id,
                  p.first_name,
                  p.last_name,
                  p.description,
                  DATE_FORMAT(p.first_visit,'%m/%d/%Y') first_visit,
                  p.attender_status,
                  p.active,
                  p.adult,
                  p.saved,
                  p.baptized,
                  p.member,
                  p.visitor,
                  p.assigned_agent,
                  p.street1,
                  p.street2,
                  p.city,
                  p.state,
                  p.zip,
                  p.email,
                  p.primary_phone,
                  p.primary_phone_type,
                  p.secondary_phone,
                  p.secondary_phone_type,
                  p.commitment_christ,
                  p.recommitment_christ,
                  p.commitment_tithe,
                  p.commitment_ministry,
                  p.commitment_baptism,
                  p.info_next,
                  p.info_gkids,
                  p.info_ggroups,
                  p.info_gteams,
                  p.info_member,
                  p.info_visit,
                  p.starting_point_notified,
                  f.id follow_up_id,
                  f.type follow_up_type,
                  DATE_FORMAT(f.follow_up_date,'%m/%d/%Y') follow_up_date,
                  f.comments follow_up_comments,
                  f.commitment_christ follow_up_commitment_christ,
                  f.recommitment_christ follow_up_recommitment_christ,
                  f.commitment_tithe follow_up_commitment_tithe,
                  f.commitment_ministry follow_up_commitment_ministry,
                  f.commitment_baptism follow_up_commitment_baptism,
                  f.info_next follow_up_info_next,
                  f.info_gkids follow_up_info_gkids,
                  f.info_ggroups follow_up_info_ggroups,
                  f.info_gteams follow_up_info_gteams,
                  f.info_member follow_up_info_member,
                  f.info_visit follow_up_info_visit,
                  f.attendance_frequency follow_up_attendance_frequency,
                  v.follow_up_id visitor_follow_up_id,
                  v.person_id,
                  fp.first_name follow_up_first_name,
                  fp.last_name follow_up_last_name,
                  fp.description follow_up_description,
                  r.id as relationship_id,
                  r.relation_id as relationship_relation_id,
                  rp.first_name relationship_first_name,
                  rp.last_name relationship_last_name,
                  rp.description relationship_description,
                  r.type relationship_type_cd
                FROM
                  (select min(service_dt) attendance_dt from Services where id in (select service_id from Attendance where attended_by=:id)) at,
                  People p
                  left outer join FollowUps f on f.follow_up_to_person_id=p.id
                  left outer join FollowUpVisitors v on f.id=v.follow_up_id
                  left outer join People fp on fp.id=v.person_id
                  left outer join Relationships r on r.person_id=p.id
                  left outer join People rp on rp.id=r.relation_id
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
                        $p['first_attendance_dt'] = $row['first_attendance_dt'];
                        $p['first_name'] = $row['first_name'];
                        $p['last_name'] = $row['last_name'];
                        $p['description'] = $row['description'];
                        $p['first_visit'] = $row['first_visit'];
                        $p['attender_status'] = $row['attender_status'];
                        $p['adult'] = $row['adult'] ? TRUE : FALSE;
                        $p['active'] = $row['active'] ? TRUE : FALSE;
                        $p['saved'] = $row['saved'] ? TRUE : FALSE;
                        $p['baptized'] = $row['baptized'] ? TRUE : FALSE;
                        $p['member'] = $row['member'] ? TRUE : FALSE;
                        $p['visitor'] = $row['visitor'] ? TRUE : FALSE;
                        $p['assigned_agent'] = $row['assigned_agent'] ? TRUE : FALSE;
                        $p['starting_point_notified'] = $row['starting_point_notified'] ? TRUE : FALSE;
                        $p['street1'] = $row['street1'];
                        $p['street2'] = $row['street2'];
                        $p['city'] = $row['city'];
                        $p['state'] = $row['state'];
                        $p['zip'] = $row['zip'];
                        $p['email'] = $row['email'];
                        $p['primary_phone'] = $row['primary_phone'];
                        $p['primary_phone_type'] = $row['primary_phone_type'];
                        $p['secondary_phone'] = $row['secondary_phone'];
                        $p['secondary_phone_type'] = $row['secondary_phone_type'];
                        $p['commitment_christ'] = $row['commitment_christ'] ? TRUE : FALSE;
                        $p['recommitment_christ'] = $row['recommitment_christ'] ? TRUE : FALSE;
                        $p['commitment_tithe'] = $row['commitment_tithe'] ? TRUE : FALSE;
                        $p['commitment_ministry'] = $row['commitment_ministry'] ? TRUE : FALSE;
                        $p['commitment_baptism'] = $row['commitment_baptism'] ? TRUE : FALSE;
                        $p['info_next'] = $row['info_next'] ? TRUE : FALSE;
                        $p['info_gkids'] = $row['info_gkids'] ? TRUE : FALSE;
                        $p['info_ggroups'] = $row['info_ggroups'] ? TRUE : FALSE;
                        $p['info_gteams'] = $row['info_gteams'] ? TRUE : FALSE;
                        $p['info_member'] = $row['info_member'] ? TRUE : FALSE;
                        $p['info_visit'] = $row['info_visit'] ? TRUE : FALSE;

                        $p['follow_ups'] = array();
                        $p['relationships'] = array();
                        array_push($people, $p);
                        $j = count($people) - 1;
                    }

                    if($row['follow_up_id'] != "" && $row['follow_up_id'] != NULL) {
                        $l = NULL;
                        $fo = NULL;
                        $foundFollowUp = FALSE;
                        $foundVisitorId = FALSE;
                        // Check to see if we have already added this follow up
                        foreach($people[$j]['follow_ups'] as $m => $follow_up) {
                            if(!isset($follow_up['id'])) continue;
                            if($follow_up['id'] == $row['follow_up_id']) {
                                $l = $m;
                                $foundFollowUp = TRUE;
                                foreach($follow_up['visitorsIds'] as $v => $visitorId) {
                                    if($visitorId == $row['person_id']) {
                                        $foundVisitorId = TRUE;
                                        break;
                                    }
                                }
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
                            
                            $options = array();
                            $options['frequency'] = $row['follow_up_attendance_frequency'];
                            $options['commitment_christ'] = $row['follow_up_commitment_christ'] ? TRUE : FALSE;
                            $options['recommitment_christ'] = $row['follow_up_recommitment_christ'] ? TRUE : FALSE;
                            $options['commitment_tithe'] = $row['follow_up_commitment_tithe'] ? TRUE : FALSE;
                            $options['commitment_ministry'] = $row['follow_up_commitment_ministry'] ? TRUE : FALSE;
                            $options['commitment_baptism'] = $row['follow_up_commitment_baptism'] ? TRUE : FALSE;
                            $options['info_next'] = $row['follow_up_info_next'] ? TRUE : FALSE;
                            $options['info_gkids'] = $row['follow_up_info_gkids'] ? TRUE : FALSE;
                            $options['info_ggroups'] = $row['follow_up_info_ggroups'] ? TRUE : FALSE;
                            $options['info_gteams'] = $row['follow_up_info_gteams'] ? TRUE : FALSE;
                            $options['info_member'] = $row['follow_up_info_member'] ? TRUE : FALSE;
                            $options['info_visit'] = $row['follow_up_info_visit'] ? TRUE : FALSE;

                            $fo['communication_card_options'] = $options;

                            array_push($people[$j]['follow_ups'], $fo);
                            $l = count($people[$j]['follow_ups']) - 1;
                        }
                        if($foundVisitorId == FALSE) {
                            array_push($people[$j]['follow_ups'][$l]['visitors'], getDisplayName($row, "follow_up_"));
                            array_push($people[$j]['follow_ups'][$l]['visitorsIds'], $row['person_id']);
                        }
                    }
                    if($row['relationship_id'] != "" && $row['relationship_id'] != NULL) {
                        $l = NULL;
                        $relationship = NULL;
                        $foundRelationship = FALSE;
                        // Check to see if we have already added this relationship
                        foreach($people[$j]['relationships'] as $m => $re) {
                            if(!isset($re['id'])) continue;
                            if($re['id'] == $row['relationship_id']) {
                                $l = $m;
                                $foundRelationship = TRUE;
                                break;
                            }
                        }
                        // Set the person data if we have not encountered this person before
                        if($foundRelationship == FALSE) {
                            $relationship = array();
                            $relationship['id'] = $row['relationship_id'];
                            $relationship['typeCd'] = $row['relationship_type_cd'];
                            $relationship['relation_id'] = $row['relationship_relation_id'];
                            $relationship['name'] = getDisplayName($row, "relationship_");
                            
                            array_push($people[$j]['relationships'], $relationship);
                            $l = count($people[$j]['relationships']) - 1;
                        }
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
