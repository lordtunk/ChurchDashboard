<?php
    require_once("func.php");
    class FollowUps {
        private $f = NULL;
        public function __construct($f=null) {
			if($f != null) {
				$this->f = $f;
			} else {
				$this->f = new Func();
			}
        }
		
		public function getFollowUpsByDate($date) {
			$query = "SELECT
					  f.follow_up_to_person_id person_id,
					  p.first_name,
					  p.last_name,
					  p.description,
					  f.id follow_up_id,
					  f.type follow_up_type,
					  DATE_FORMAT(f.follow_up_date,'%m/%d/%Y') follow_up_date,
					  f.attendance_frequency,
					  f.commitment_christ,
					  f.recommitment_christ,
					  f.commitment_tithe,
					  f.commitment_ministry,
					  f.commitment_baptism,
					  f.info_next,
					  f.info_gkids,
					  f.info_ggroups,
					  f.info_gteams,
					  f.info_member,
					  f.info_visit,
					  f.info_growth,
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
					  f.follow_up_date,
					  f.type,
					  p.last_name IS NOT NULL DESC,
					  p.description IS NOT NULL DESC,
					  p.last_name,
					  p.first_name,
					  p.description";
			$results = $this->f->fetchAndExecute($query, array(":date"=>$date));
			
            return $this->processFollowUpResults($results);
        }
		
		public function getFollowUpsFromDate($fromDate, $toDate) {
			$query = "SELECT
					  f.follow_up_to_person_id person_id,
					  p.first_name,
					  p.last_name,
					  p.description,
					  f.id follow_up_id,
					  f.type follow_up_type,
					  DATE_FORMAT(f.follow_up_date,'%m/%d/%Y') follow_up_date,
					  f.attendance_frequency,
					  f.commitment_christ,
					  f.recommitment_christ,
					  f.commitment_tithe,
					  f.commitment_ministry,
					  f.commitment_baptism,
					  f.info_next,
					  f.info_gkids,
					  f.info_ggroups,
					  f.info_gteams,
					  f.info_member,
					  f.info_visit,
					  f.info_growth,
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
					  DATE(f.creation_dt) >= STR_TO_DATE(:fromDate,'%c/%e/%Y')
					  and DATE(f.creation_dt) <= STR_TO_DATE(:toDate,'%c/%e/%Y')
					ORDER BY
					  f.follow_up_date";
			$results = $this->f->fetchAndExecute($query, array(":fromDate"=>$fromDate, ":toDate"=>$toDate));
			
            return $this->processFollowUpResults($results);
        }
		
		private function processFollowUpResults($results) {
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
					$fo['name'] = $this->getDisplayName($row);
					$fo['typeCd'] = $row['follow_up_type'];
					$fo['date'] = $row['follow_up_date'];
					$fo['comments'] = $row['follow_up_comments'];
					$fo['visitors'] = array();
					$fo['visitorsIds'] = array();

					$options = array();
					$options['frequency'] = $row['attendance_frequency'];
					$options['commitment_christ'] = $row['commitment_christ'] ? TRUE : FALSE;
					$options['recommitment_christ'] = $row['recommitment_christ'] ? TRUE : FALSE;
					$options['commitment_tithe'] = $row['commitment_tithe'] ? TRUE : FALSE;
					$options['commitment_ministry'] = $row['commitment_ministry'] ? TRUE : FALSE;
					$options['commitment_baptism'] = $row['commitment_baptism'] ? TRUE : FALSE;
					$options['info_next'] = $row['info_next'] ? TRUE : FALSE;
					$options['info_gkids'] = $row['info_gkids'] ? TRUE : FALSE;
					$options['info_ggroups'] = $row['info_ggroups'] ? TRUE : FALSE;
					$options['info_gteams'] = $row['info_gteams'] ? TRUE : FALSE;
					$options['info_member'] = $row['info_member'] ? TRUE : FALSE;
					$options['info_visit'] = $row['info_visit'] ? TRUE : FALSE;
					$options['info_growth'] = $row['info_growth'] ? TRUE : FALSE;

					$fo['communication_card_options'] = $options;

					array_push($follow_ups, $fo);
					$l = count($follow_ups) - 1;
				}
				
				array_push($follow_ups[$l]['visitors'], $this->getDisplayName($row, "follow_up_"));
				array_push($follow_ups[$l]['visitorsIds'], $row['visitor_person_id']);
			}
			return $follow_ups;
		}
		
		private function getDisplayName($person, $prefix="") {
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
                        p.info_growth,
                        p.assigned_agent,
                        p.commitment_christ,
                        p.recommitment_christ,
                        p.commitment_tithe,
                        p.commitment_ministry,
                        f.attendance_frequency,
                        CASE WHEN p.commitment_baptism = 1 THEN  'true' ELSE  'false' END commitment_baptism
                      FROM 
                        ";
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
                        ";
            }
			
			if(property_exists($params,'campus') && $params->campus != "") {
				$queryParams[":campus"] = $params->campus;
				$query .= "People p
						inner join PersonCampusAssociations pca on pca.person_id=p.id and pca.campus=:campus
                        LEFT OUTER JOIN FollowUps f ON f.follow_up_to_person_id = p.id AND f.type = 3
                        LEFT OUTER JOIN (SELECT COUNT(*) visit_count, follow_up_to_person_id FROM FollowUps WHERE TYPE = 2 GROUP BY follow_up_to_person_id) vc ON vc.follow_up_to_person_id = p.id
                        LEFT OUTER JOIN (SELECT COUNT(*) ty_card_sent_count, follow_up_to_person_id, follow_up_date FROM FollowUps WHERE TYPE =5 GROUP BY follow_up_to_person_id, follow_up_date) tyc ON tyc.follow_up_to_person_id = p.id";
			} else {
				$query .= "People p
                        LEFT OUTER JOIN FollowUps f ON f.follow_up_to_person_id = p.id AND f.type = 3
                        LEFT OUTER JOIN (SELECT COUNT(*) visit_count, follow_up_to_person_id FROM FollowUps WHERE TYPE = 2 GROUP BY follow_up_to_person_id) vc ON vc.follow_up_to_person_id = p.id
                        LEFT OUTER JOIN (SELECT COUNT(*) ty_card_sent_count, follow_up_to_person_id, follow_up_date FROM FollowUps WHERE TYPE =5 GROUP BY follow_up_to_person_id, follow_up_date) tyc ON tyc.follow_up_to_person_id = p.id";
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
            if($params->interested_in_growth) {
                array_push($optionsArr, "p.info_growth = 1");
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
