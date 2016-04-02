<?php
    require_once("func.php");
    class FollowUps {
        private $f = NULL;
        public function __construct() {
            $this->f = new Func();
        }
        
        function getFollowUpReport($params, $queryFlags) {
            $where = "
              WHERE 
                p.adult = 1";
            $optionsArr = array();
            $orderBy = "
              ORDER BY
                p.last_name IS NOT NULL DESC,
                p.description IS NOT NULL DESC,
                p.last_name,
                p.first_name,
                p.description";
            $groupBy = "
              GROUP BY 
                p.id ";
            $having = "
              HAVING 
                ";
            $havingArr = array();
            $queryParams = array();
            if($queryFlags) {
                $query = "SELECT 
                        CASE WHEN vc.visit_count > 0 THEN  'true' ELSE  'false' END visited, 
                        CASE WHEN tyc.ty_card_sent_count > 0 THEN  'true' ELSE  'false' END ty_card_sent, 
                        DATE_FORMAT(f.follow_up_date, '%m/%d/%Y') communication_card_date, 
                        DATE_FORMAT(tyc.follow_up_date, '%m/%d/%Y') ty_card_date,
                        p.id, 
                        p.first_name, 
                        p.last_name, 
                        p.description,
                        p.primary_phone,
                        p.commitment_baptism,
                        p.baptized,
                        p.info_gkids,
                        p.info_next,
                        p.info_ggroups,
                        p.info_gteams,
                        p.info_member,
                        p.info_visit,
                        p.assigned_agent,
                        p.commitment_christ,
                        p.recommitment_christ,
                        p.commitment_tithe,
                        p.commitment_ministry,
                        f.attendance_frequency,
                        CASE WHEN p.commitment_baptism = 1 THEN  'true' ELSE  'false' END commitment_baptism
                      FROM 
                        People p
                        LEFT OUTER JOIN FollowUps f ON f.follow_up_to_person_id = p.id AND f.type = 3
                        LEFT OUTER JOIN (SELECT COUNT(*) visit_count, follow_up_to_person_id FROM FollowUps WHERE TYPE =2 GROUP BY follow_up_to_person_id)vc ON vc.follow_up_to_person_id = p.id
                        LEFT OUTER JOIN (SELECT COUNT(*) ty_card_sent_count, follow_up_to_person_id, follow_up_date FROM FollowUps WHERE TYPE =5 GROUP BY follow_up_to_person_id, follow_up_date)tyc ON tyc.follow_up_to_person_id = p.id";
            } else {
                $query = "SELECT 
                        CASE WHEN vc.visit_count > 0 THEN  'true' ELSE  'false' END visited, 
                        CASE WHEN tyc.ty_card_sent_count > 0 THEN  'true' ELSE  'false' END ty_card_sent, 
                        DATE_FORMAT(f.follow_up_date, '%m/%d/%Y') communication_card_date, 
                        DATE_FORMAT(tyc.follow_up_date, '%m/%d/%Y') ty_card_date,
                        p.id, 
                        p.first_name, 
                        p.last_name, 
                        p.description,
                        p.primary_phone,
                        p.street1,
                        p.street2,
                        p.city,
                        p.state,
                        p.zip,
                        CASE WHEN p.commitment_baptism = 1 THEN  'true' ELSE  'false' END commitment_baptism
                      FROM 
                        People p
                        LEFT OUTER JOIN FollowUps f ON f.follow_up_to_person_id = p.id AND f.type = 3
                        LEFT OUTER JOIN (SELECT COUNT(*) visit_count, follow_up_to_person_id FROM FollowUps WHERE type = 2 GROUP BY  follow_up_to_person_id)vc ON vc.follow_up_to_person_id = p.id
                        LEFT OUTER JOIN (SELECT COUNT(*) ty_card_sent_count, follow_up_to_person_id, follow_up_date FROM FollowUps WHERE type = 5 GROUP BY follow_up_to_person_id)tyc ON tyc.follow_up_to_person_id = p.id";
            }
                        

            if($params->active) {
                $where .= "
                    AND p.active = 1";
            }     
            if($params->fromDate != "") {
                $queryParams[":fromDate"] = $params->fromDate;
                $where .= "
                    AND f.follow_up_date >= STR_TO_DATE(:fromDate,'%m/%d/%Y')";
            }
            if($params->toDate != "") {
                $queryParams[":toDate"] = $params->toDate;
                $where .= "
                    AND f.follow_up_date <= STR_TO_DATE(:toDate,'%m/%d/%Y')";
            }
            if($params->signed_up_for_baptism) {
                array_push($optionsArr, "p.commitment_baptism = 1");
            }
            if($params->baptized && $queryFlags == FALSE) {
                array_push($optionsArr, "p.baptized = 1");
            }
            if($params->interested_in_gkids) {
                array_push($optionsArr, "p.info_gkids = 1");
            }
            if($params->interested_in_next) {
                array_push($optionsArr, "p.info_next = 1");
            }
            if($params->interested_in_ggroups) {
                array_push($optionsArr, "p.info_ggroups = 1");
            }
            if($params->interested_in_gteams) {
                array_push($optionsArr, "p.info_gteams = 1");
            }
            if($params->interested_in_joining) {
                array_push($optionsArr, "p.info_member = 1");
            }
            if($params->would_like_visit) {
                array_push($optionsArr, "p.info_visit = 1");
            }
            if($params->no_agent && $queryFlags == FALSE) {
                array_push($optionsArr, "p.assigned_agent = 0");
            }
            if($params->commitment_christ) {
                array_push($optionsArr, "p.commitment_christ = 1");
            }
            if($params->recommitment_christ) {
                array_push($optionsArr, "p.recommitment_christ = 1");
            }
            if($params->commitment_tithe) {
                array_push($optionsArr, "p.commitment_tithe = 1");
            }
            if($params->commitment_ministry) {
                array_push($optionsArr, "p.commitment_ministry = 1");
            }
            if($params->attendance_frequency) {
                array_push($optionsArr, "f.attendance_frequency = 1");
            }

            if(count($optionsArr) > 0) {
                $where .= " 
                    AND (";
                $where .= join(" 
                    OR ", $optionsArr);
                $where .= ")";
            }
            if($params->not_visited) {
                array_push($havingArr, "visited =  'false'");
            }
            if($params->ty_card_not_sent) {
                array_push($havingArr, "ty_card_sent =  'false'");
            }
            $query .= $where.$groupBy;

            if(count($havingArr) > 0) {
                $query .= $having;
                $query .= join(" AND ", $havingArr);
            }

            $query .= $orderBy;

            return $this->f->fetchAndExecute($query, $queryParams);
        }
    }
?>
