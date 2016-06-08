<?php
    require_once("func.php");
    class Attendance {
        private $f = NULL;
        public function __construct($f=null) {
            if($f != null) {
            	$this->f = $f;
            } else {
            	$this->f = new Func();
            }
        }
        
        public function getServiceOptions() {
            $query = "SELECT
                  campuses,
                  service_labels,
                  default_campus,
                  default_first_service_label,
                  default_second_service_label
                FROM
                  Settings";
            $results = $this->f->fetchAndExecute($query);
            $options = array();
            $options['campuses'] = $this->settingStringToObject($results[0]['campuses']);
            $options['service_labels'] = $this->settingStringToObject($results[0]['service_labels']);
            $options['default_campus'] = $results[0]['default_campus'];
            $options['default_first_service_label'] = $results[0]['default_first_service_label'];
            $options['default_second_service_label'] = $results[0]['default_second_service_label'];
            
            return $options;
        }
        
        public function getSetting($setting) {
            $query = "SELECT
                  $setting
                FROM
                  Settings";
            $results = $this->f->fetchAndExecute($query);
            return $this->settingStringToObject($results[0][$setting]);
        }
        
        private function settingStringToObject($str) {
            $arr = explode(',', $str);
            $obj = array();
            foreach($arr as $key => $setting) {
                $ind = strpos($setting, '|');
                if($ind === false) continue;
                $obj[trim(substr($setting, 0, $ind))] = trim(substr($setting, $ind+1));
            }
            return $obj;
        }
        
        public function getDefaultServiceOptions() {
            $query = "SELECT
                  default_campus,
                  default_first_service_label,
                  default_second_service_label
                FROM
                  Settings";
            $results = $this->f->fetchAndExecute($query);
            $options = array();
            $options['default_campus'] = $results[0]['default_campus'];
            $options['default_first_service_label'] = $results[0]['default_first_service_label'];
            $options['default_second_service_label'] = $results[0]['default_second_service_label'];
            
            return $options;
        }
        
        public function getAttendance($service_dt, $active, $adult, $campus, $label1, $label2, $ids) {
            $results = $this->getServiceIds($service_dt, $campus);
            $serviceId1 = -1;
            $serviceId2 = -1;
            $hasServiceId = FALSE;
            foreach($results as $key => $row) {
                if($row['label'] == $label1)
                    $serviceId1 = $row['id'];
                else if($row['label'] == $label2)
                    $serviceId2 = $row['id'];
            }
            $idsString = "";
            if($ids != NULL && $ids != "") {
                $idsString = "AND p.id IN($ids)";
            }
            if($serviceId2 !== -1) {
                $hasServiceId = TRUE;
                $query = "SELECT DISTINCT
                          p.id,
                          p.first_name,
                          p.last_name,
                          p.description,
                          CASE WHEN p.first_name IS NULL THEN
                                CASE WHEN p.last_name IS NULL THEN
                                    p.description
                                ELSE
                                    p.last_name
                                END
                            ELSE
                                CASE WHEN p.last_name IS NULL THEN
                                    p.first_name
                                ELSE
                                    CONCAT_WS(' ', p.first_name, p.last_name)
                                END
                            END display,
                          ".($active ? 1 : 0)." as active,
                          ".($adult ? 1 : 0)." as adult,
                          CASE WHEN a1.attended_by IS NULL THEN '' ELSE 1 END first,
                          CASE WHEN a2.attended_by IS NULL THEN '' ELSE 1 END second
                        FROM
                          People p
						  inner join PersonCampusAssociations pca on pca.person_id=p.id and pca.campus=:campus
                          LEFT OUTER JOIN (
                            SELECT
                              a.attended_by
                            FROM
                              Attendance a
                            WHERE
                              a.service_id=$serviceId1
                          ) a1 ON a1.attended_by=p.id
                          LEFT OUTER JOIN (
                            SELECT
                              a.attended_by
                            FROM
                              Attendance a
                            WHERE
                              a.service_id=$serviceId2
                          ) a2 ON a2.attended_by=p.id
                        WHERE
                          p.active = :active
                          AND p.adult = :adult
                          $idsString
                        ORDER BY
                          p.last_name IS NOT NULL DESC,
                          p.description IS NOT NULL DESC,
                          p.last_name,
                          p.first_name,
                          p.description";
            } else {
                $query = "SELECT DISTINCT
                          p.id,
                          p.first_name,
                          p.last_name,
                          p.description,
                          CASE WHEN p.first_name IS NULL THEN
                                CASE WHEN p.last_name IS NULL THEN
                                    p.description
                                ELSE
                                    p.last_name
                                END
                            ELSE
                                CASE WHEN p.last_name IS NULL THEN
                                    p.first_name
                                ELSE
                                    CONCAT_WS(' ', p.first_name, p.last_name)
                                END
                            END display,
                          ".($active ? 1 : 0)." as active,
                          ".($adult ? 1 : 0)." as adult,
                          CASE WHEN a1.attended_by IS NULL THEN '' ELSE 1 END first
                        FROM
                          People p
						  inner join PersonCampusAssociations pca on pca.person_id=p.id and pca.campus=:campus
                          LEFT OUTER JOIN (
                            SELECT
                              a.attended_by
                            FROM
                              Attendance a
                            WHERE
                              a.service_id=$serviceId1
                          ) a1 ON a1.attended_by=p.id
                        WHERE
                          p.active = :active
                          AND p.adult = :adult
                          $idsString
                        ORDER BY
                          p.last_name IS NOT NULL DESC,
                          p.description IS NOT NULL DESC,
                          p.last_name,
                          p.first_name,
                          p.description";
            }
            $results = $this->f->fetchAndExecute($query, array(":campus"=>$campus,":active"=>$active, ":adult"=>$adult));
            return $results;
        }
        
        public function getAttendanceTotals($service_dt, $adult, $campus, $label1, $label2) {
            $results = $this->getServiceIds($service_dt, $campus);
            $serviceId1 = -1;
            $serviceId2 = -1;
            $visitorsFirst = 0;
            $visitorsSecond = 0;
            $haveSecondService = FALSE;
            foreach($results as $key => $row) {
                if($row['label'] == $label1)
                    $serviceId1 = $row['id'];
                else if($row['label'] == $label2)
                    $serviceId2 = $row['id'];
            }

            if($serviceId2 === -1) {
                $visitorsFirst = $this->getVisitorCount($service_dt, $adult, $campus, $label1);
                $query = "
                        select
                            (SELECT count(*) FROM Attendance WHERE service_id = $serviceId1) total_first_count,

                            (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=1 AND a.service_id = $serviceId1) adult_first_count,

                            (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=0 AND a.service_id = $serviceId1) kid_first_count
                        from
                            dual";
            } else {
                $haveSecondService = TRUE;
                $query = "
                        select
                            (SELECT count(*) FROM Attendance WHERE service_id = $serviceId1) total_first_count,
                            (SELECT count(*) FROM Attendance WHERE service_id = $serviceId2) total_second_count,
                            (SELECT count(*) FROM Attendance WHERE (service_id = $serviceId1 OR service_id = $serviceId2)) total_total_count,

                            (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=1 AND a.service_id = $serviceId1) adult_first_count,
                            (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=1 AND a.service_id = $serviceId2) adult_second_count,
                            (SELECT count(*) FROM (SELECT count(*) cnt, attended_by FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=1 AND (a.service_id = $serviceId1 OR a.service_id = $serviceId2) group by attended_by) at1) adult_total_count,

                            (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=0 AND a.service_id = $serviceId1) kid_first_count,
                            (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=0 AND a.service_id = $serviceId2) kid_second_count,
                            (SELECT count(*) FROM (SELECT count(*) cnt, attended_by FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=0 AND (a.service_id = $serviceId1 OR a.service_id = $serviceId2) group by attended_by) at1) kid_total_count
                        from
                            dual";

            }
            $results = $this->f->fetchAndExecute($query);
            $totals = array();
            
            foreach($results as $key => $row) {
                $totals['total_first_count'] = $row['total_first_count'];
                if($haveSecondService == TRUE) {
                    $totals['total_second_count'] = $row['total_second_count'];
                    $totals['total_total_count'] = $row['total_total_count'];
                } else {
                    $totals['total_second_count'] = 0;
                    $totals['total_total_count'] = $row['total_first_count'];
                }
                
                $totals['adult_first_count'] = $row['adult_first_count'];
                if($haveSecondService == TRUE) {
                    $totals['adult_second_count'] = $row['adult_second_count'];
                    $totals['adult_total_count'] = $row['adult_total_count'];
                } else {
                    $totals['adult_second_count'] = 0;
                    $totals['adult_total_count'] = $row['adult_first_count'];
                }
                
                $totals['kid_first_count'] = $row['kid_first_count'];
                if($haveSecondService == TRUE) {
                    $totals['kid_second_count'] = $row['kid_second_count'];
                    $totals['kid_total_count'] = $row['kid_total_count'];
                } else {
                    $totals['kid_second_count'] = 0;
                    $totals['kid_total_count'] = $row['kid_first_count'];
                }
            }
            return $totals;
        }
        
        private function getServiceIds($service_dt, $campus) {
            $query = "SELECT id, label FROM Services WHERE service_dt = STR_TO_DATE(:date,'%c/%e/%Y') AND campus = :campus";
            return $this->f->fetchAndExecute($query, array(":date"=>$service_dt, ":campus"=>$campus));
        }
        
        public function getServiceId($service_dt, $campus, $service_label) {
            $query = "SELECT id FROM Services WHERE service_dt = STR_TO_DATE(:date,'%c/%e/%Y') AND campus = :campus AND label = :service_label";
            $results = $this->f->fetchAndExecute($query, array(":date"=>$service_dt, ":campus"=>$campus, ":service_label"=>$service_label));
            return count($results) > 0 ? $results[0]['id'] : -1;
        }
        
        public function createService($service_dt, $campus, $service_label) {
            $query = "INSERT INTO Services (service_dt, campus, label) VALUES 
                      (STR_TO_DATE(:date,'%c/%e/%Y'), :campus, :service_label)";
            return $this->f->queryLastInsertId($query, array(":date"=>$service_dt, ":campus"=>$campus, ":service_label"=>$service_label));
        }
        
        public function deleteAttendance($service_id, $person_id) {
            $query = "DELETE FROM Attendance WHERE attended_by=:id AND service_id=:service_id";
            $this->f->executeAndReturnResult($query, array(":id"=>$person_id, ":service_id"=>$service_id));
        }
        
        public function addAttendance($service_id, $person_id) {
            $query = "INSERT INTO Attendance (`attended_by`, `service_id`) VALUES(:attended_by, :service_id)";
            $results = $this->f->executeAndReturnResult($query, array(":attended_by"=>$person_id, ":service_id"=>$service_id));
        }
        
        public function getAttendanceByPersonId($person_id, $label1, $label2, $campus) {
            $queryParams = array();
            $queryParams[":id"] = $person_id;
            $queryParams[":campus"] = $campus;
            $queryParams[":label1"] = $label1;
            if($label2 != "") {
                $queryParams[":label2"] = $label2;
                $query = "
                    SELECT DISTINCT
                      p.id,
                      p.first_name,
                      p.last_name,
                      p.description,
                      COALESCE(a1.attendance_dt, a2.attendance_dt) service_dt,
                      DATE_FORMAT(COALESCE(a1.attendance_dt, a2.attendance_dt),'%m/%d/%Y') attendance_dt,
                      CASE WHEN a1.attended_by IS NULL THEN '' ELSE 1 END first,
                      CASE WHEN a2.attended_by IS NULL THEN '' ELSE 1 END second
                    FROM
                      People p
                      LEFT OUTER JOIN (
                        SELECT
                          a.attended_by,
                          s.service_dt attendance_dt
                        FROM
                          Attendance a
                          inner join Services s on a.service_id=s.id and s.label=:label1 and s.campus=:campus
                      ) a1 ON a1.attended_by=p.id
                      LEFT OUTER JOIN (
                        SELECT
                          a.attended_by,
                          s.service_dt attendance_dt
                        FROM
                          Attendance a
                          inner join Services s on a.service_id=s.id and s.label=:label2 and s.campus=:campus
                      ) a2 ON a2.attended_by=p.id and a2.attendance_dt=a1.attendance_dt
                    WHERE
                      p.id = :id
                    
                    UNION
                    
                    SELECT DISTINCT
                      p.id,
                      p.first_name,
                      p.last_name,
                      p.description,
                      COALESCE(a1.attendance_dt, a2.attendance_dt) service_dt,
                      DATE_FORMAT(COALESCE(a1.attendance_dt, a2.attendance_dt),'%m/%d/%Y') attendance_dt,
                      CASE WHEN a1.attended_by IS NULL THEN '' ELSE 1 END first,
                      CASE WHEN a2.attended_by IS NULL THEN '' ELSE 1 END second
                    FROM
                      People p
                      LEFT OUTER JOIN (
                        SELECT
                          a.attended_by,
                          s.service_dt attendance_dt
                        FROM
                          Attendance a
                          inner join Services s on a.service_id=s.id and s.label=:label2 and s.campus=:campus
                      ) a2 ON a2.attended_by=p.id
                      LEFT OUTER JOIN (
                        SELECT
                          a.attended_by,
                          s.service_dt attendance_dt
                        FROM
                          Attendance a
                          inner join Services s on a.service_id=s.id and s.label=:label1 and s.campus=:campus
                      ) a1 ON a1.attended_by=p.id and a2.attendance_dt=a1.attendance_dt
                    WHERE
                      p.id = :id
                    ORDER BY
                      service_dt DESC";
            } else {
                $query = "
                    SELECT DISTINCT
                      p.id,
                      p.first_name,
                      p.last_name,
                      p.description,
                      a1.attendance_dt service_dt,
                      DATE_FORMAT(a1.attendance_dt,'%m/%d/%Y') attendance_dt,
                      CASE WHEN a1.attended_by IS NULL THEN '' ELSE 1 END first
                    FROM
                      People p
                      LEFT OUTER JOIN (
                        SELECT
                          a.attended_by,
                          s.service_dt attendance_dt
                        FROM
                          Attendance a
                          inner join Services s on a.service_id=s.id and s.label=:label1 and s.campus=:campus
                      ) a1 ON a1.attended_by=p.id
                    WHERE
                      p.id = :id
                    ORDER BY
                      service_dt DESC";
            }
            
            $results = $this->f->fetchAndExecute($query, $queryParams);
            $person = array();
            $person['attendance'] = array();
            foreach($results as $key => $row) {
                $person['first_name'] = $row['first_name'];
                $person['last_name'] = $row['last_name'];
                $person['description'] = $row['description'];

                if(isset($row['attendance_dt'])) {
                  $att = array();
                  $att['date'] = $row['attendance_dt'];
                  $att['first'] = $row['first'] == "1" ? TRUE : FALSE;
                  if($label2 != "") {
                    $att['second'] = $row['second'] == "1" ? TRUE : FALSE;
                  }
                  array_push($person['attendance'], $att);
                }
            }
            return $person;
        }
        
        public function updateVisitorCount($service_id, $count, $adult) {
            if($adult)
                $query = "UPDATE Services SET adult_visitors=:count WHERE id=:service_id";
            else
                $query = "UPDATE Services SET kid_visitors=:count WHERE id=:service_id";
            $results = $this->f->executeAndReturnResult($query, array(":count"=>$count, ":service_id"=>$service_id));
        }
        
        public function getVisitorCount($service_dt, $adult, $campus, $label) {
            // if($adult)
                // $query = "SELECT COALESCE(adult_visitors, 0) visitors FROM Services WHERE service_dt = STR_TO_DATE(:date,'%c/%e/%Y') AND campus = :campus AND label=:label";
            // else
                // $query = "SELECT COALESCE(kid_visitors, 0) visitors FROM Services WHERE service_dt = STR_TO_DATE(:date,'%c/%e/%Y') AND campus = :campus AND label=:label";
            $query = "SELECT COALESCE(adult_visitors, 0) adult_visitors, COALESCE(kid_visitors, 0) kid_visitors FROM Services WHERE service_dt = STR_TO_DATE(:date,'%c/%e/%Y') AND campus = :campus AND label=:label";
            $results = $this->f->fetchAndExecute($query, array(":date"=>$service_dt, ":campus"=>$campus, ":label"=>$label));
            return count($results) > 0 ? $results[0] : array("adult_visitors"=>0, "kid_visitors"=>0);
        }
        
        public function getVisitorCounts($fromDate, $toDate, $campus, $label1, $label2) {
            $queryParams = array();
            $queryParams[':campus'] = $campus;
            $queryParams[':label1'] = $label1;
            $query = "
                SELECT
                    DATE_FORMAT(s.service_dt,'%m/%d/%Y') service_dt,
                    (COALESCE(s.adult_visitors, 0) + COALESCE(s.kid_visitors, 0)) visitors,
                    s.label
                FROM
                    Services s
                WHERE 
                    s.campus=:campus
                    AND s.label in (:label1";
            
            if($label2) {
                $query .= ", :label2";
                $queryParams[':label2'] = $label2;
            }
            $query .= ")";
            if($fromDate) {
                $queryParams[':fromDate'] = $fromDate;
                $query .= "
                    AND s.service_dt >= STR_TO_DATE(:fromDate,'%m/%d/%Y')";
            }
            if($toDate) {
                $queryParams[':toDate'] = $toDate;
                $query .= "
                    AND s.service_dt <= STR_TO_DATE(:toDate,'%m/%d/%Y')";
            }
            return $this->f->fetchAndExecute($query, $queryParams);
        }
    }
?>