<?php
    session_start();
    include("func.php");
    include("attendance.php");
    $type = $_POST['type'];
    $params = isset($_POST['params']) ? json_decode($_POST['params']) : null;
    $f = new Func();
    $att = new Attendance();
    $dict = array();

    function isDate($txtDate, $allowBlank) {
        if($txtDate == "")
            return $allowBlank;
        $dt = date_parse($txtDate);
        if($dt === FALSE) return FALSE;
            return checkdate($dt['month'], $dt['day'], $dt['year']);
    }

    if(!$type || (($type === 1 || $type === 2) && !$params->fromDate && !$params->toDate)) {
        $dict['success'] = FALSE;
        $f->logMessage('Report parameters missing');
    } else if($params != NULL && (!isDate($params->fromDate, true) || !isDate($params->toDate, true))) {
        $dict['success'] = FALSE;
        $f->logMessage('Invalid report parameters');
    } else {
        if(!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
            $dict['success'] = FALSE;
            $dict['error'] = 1;
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
            if(!$dict['success'])
                $dict['error'] = 1;
        }
    }
    if($dict['success'] == TRUE) {
        try {
            switch($type) {
            case 1:
                $queryParams = array();
                $totalsQuery = "";
                $aggQuery = "";
                $where = " and s.campus=:campus";
                $queryParams[":campus"] = $params->campus;
                $queryParams[":label1"] = $params->label1;
                if($params->fromDate != "") {
                    $queryParams[":fromDate"] = $params->fromDate;
                    $where .= " AND s.service_dt >= STR_TO_DATE(:fromDate,'%m/%d/%Y')";
                }
                if($params->toDate != "") {
                    $queryParams[":toDate"] = $params->toDate;
                    $where .= "AND s.service_dt <= STR_TO_DATE(:toDate,'%m/%d/%Y')";
                }
                if($params->label2 != "") {
                    $queryParams[":label2"] = $params->label2;
                    $totalsQuery = "
                        SELECT
                            DATE_FORMAT(summary.attendance_dt,'%m/%d/%Y') Attendance_dt,
                            COUNT(*) Total_Attendance,
                            SUM(summary.first) First_Service_Attendance,
                            SUM(summary.second) Second_Service_Attendance
                        FROM 
                            (
                                select distinct 
                                    COALESCE(u.attendance_dt1, u.attendance_dt2) attendance_dt,
                                    u.id,
                                    u.first,
                                    u.second
                                from (
                                    SELECT DISTINCT
                                      a1.attendance_dt attendance_dt1,
                                      a2.attendance_dt attendance_dt2,
                                      p.id,
                                      CASE WHEN a1.attended_by IS NULL THEN 0 ELSE 1 END first,
                                      CASE WHEN a2.attended_by IS NULL THEN 0 ELSE 1 END second
                                    FROM
                                      People p
                                      LEFT OUTER JOIN (
                                        SELECT
                                          s.service_dt attendance_dt,
                                          a.attended_by
                                        FROM
                                          attendance_test a
                                          inner join Services s on a.service_id=s.id and s.label=:label1 $where
                                      ) a1 ON a1.attended_by=p.id
                                      LEFT OUTER JOIN (
                                        SELECT
                                          s.service_dt attendance_dt,
                                          a.attended_by
                                        FROM
                                          attendance_test a
                                          inner join Services s on a.service_id=s.id and s.label=:label2 $where
                                      ) a2 ON a2.attended_by=p.id and a2.attendance_dt=a1.attendance_dt
                                      
                                    UNION
                                    
                                    SELECT DISTINCT
                                      a1.attendance_dt attendance_dt1,
                                      a2.attendance_dt attendance_dt2,
                                      p.id,
                                      CASE WHEN a1.attended_by IS NULL THEN 0 ELSE 1 END first,
                                      CASE WHEN a2.attended_by IS NULL THEN 0 ELSE 1 END second
                                    FROM
                                      People p
                                      LEFT OUTER JOIN (
                                        SELECT
                                          s.service_dt attendance_dt,
                                          a.attended_by
                                        FROM
                                          attendance_test a
                                          inner join Services s on a.service_id=s.id and s.label=:label2 $where
                                      ) a2 ON a2.attended_by=p.id
                                      LEFT OUTER JOIN (
                                        SELECT
                                          s.service_dt attendance_dt,
                                          a.attended_by
                                        FROM
                                          attendance_test a
                                          inner join Services s on a.service_id=s.id and s.label=:label1 $where
                                      ) a1 ON a1.attended_by=p.id and a2.attendance_dt=a1.attendance_dt
                                ) u
                            ) summary
                        WHERE
                            attendance_dt IS NOT NULL
                        GROUP BY
                            attendance_dt DESC";
                    
                    $results = $f->fetchAndExecute($totalsQuery, $queryParams);
                    $visitorResults = $att->getVisitorCounts($params->fromDate, $params->toDate, $params->campus, $params->label1, $params->label2);

                    $len = count($results);
                    $aggregates = array();
                    $aggregates['Avg_Total_Attendance'] = 0;
                    $aggregates['Avg_First_Service_Attendance'] = 0;
                    $aggregates['Avg_Second_Service_Attendance'] = 0;
                    $aggregates['Max_Total_Attendance'] = 0;
                    $aggregates['Max_First_Service_Attendance'] = 0;
                    $aggregates['Max_Second_Service_Attendance'] = 0;
                    
                    if($len > 0) {
                        $aggregates['Min_Total_Attendance'] = $results[0]['Total_Attendance'];
                        $aggregates['Min_First_Service_Attendance'] = $results[0]['First_Service_Attendance'];
                        $aggregates['Min_Second_Service_Attendance'] = $results[0]['Second_Service_Attendance'];
                    } else {
                        $aggregates['Min_Total_Attendance'] = 0;
                        $aggregates['Min_First_Service_Attendance'] = 0;
                        $aggregates['Min_Second_Service_Attendance'] = 0;
                    }
                    for($i=0; $i<$len; $i++) {
                        foreach($visitorResults as $k => $r) {                            
                            if($r['service_dt'] == $results[$i]['Attendance_dt']) {
                                $results[$i]['Total_Attendance'] += $r['visitors'];
                                
                                if($r['label'] == $params->label1) {
                                    $results[$i]['First_Service_Attendance'] += $r['visitors'];
                                } else if($r['label'] == $params->label2) {
                                    $results[$i]['Second_Service_Attendance'] += $r['visitors'];
                                }
                            }
                        }
                        $aggregates['Avg_Total_Attendance'] += $results[$i]['Total_Attendance'];
                        $aggregates['Avg_First_Service_Attendance'] += $results[$i]['First_Service_Attendance'];
                        $aggregates['Avg_Second_Service_Attendance'] += $results[$i]['Second_Service_Attendance'];
                        
                        if($results[$i]['Total_Attendance'] > $aggregates['Max_Total_Attendance'])
                            $aggregates['Max_Total_Attendance'] = $results[$i]['Total_Attendance'];
                        if($results[$i]['First_Service_Attendance'] > $aggregates['Max_First_Service_Attendance'])
                            $aggregates['Max_First_Service_Attendance'] = $results[$i]['First_Service_Attendance'];
                        if($results[$i]['Second_Service_Attendance'] > $aggregates['Max_Second_Service_Attendance'])
                            $aggregates['Max_Second_Service_Attendance'] = $results[$i]['Second_Service_Attendance'];
                        
                        if($results[$i]['Total_Attendance'] < $aggregates['Min_Total_Attendance'])
                            $aggregates['Min_Total_Attendance'] = $results[$i]['Total_Attendance'];
                        if($results[$i]['First_Service_Attendance'] < $aggregates['Min_First_Service_Attendance'])
                            $aggregates['Min_First_Service_Attendance'] = $results[$i]['First_Service_Attendance'];
                        if($results[$i]['Second_Service_Attendance'] < $aggregates['Min_Second_Service_Attendance'])
                            $aggregates['Min_Second_Service_Attendance'] = $results[$i]['Second_Service_Attendance'];
                    }
                    if($len > 0) {
                        $aggregates['Avg_Total_Attendance'] = $aggregates['Avg_Total_Attendance']/$len;
                        $aggregates['Avg_First_Service_Attendance'] = $aggregates['Avg_First_Service_Attendance']/$len;
                        $aggregates['Avg_Second_Service_Attendance'] = $aggregates['Avg_Second_Service_Attendance']/$len;
                    }
                } else {
                    $totalsQuery = "
                        SELECT
                            DATE_FORMAT(s.service_dt,'%m/%d/%Y') Attendance_dt,
                            COUNT(*) Total_Attendance
                        FROM 
                            attendance_test a
                            inner join Services s on a.service_id=s.id and s.label=:label1 $where
                        GROUP BY
                            s.service_dt DESC";
                    
                    $results = $f->fetchAndExecute($totalsQuery, $queryParams);
                    $visitorResults = $att->getVisitorCounts($params->fromDate, $params->toDate, $params->campus, $params->label1, $params->label2);
                    
                    $len = count($results);
                    $aggregates = array();
                    $aggregates['Avg_Total_Attendance'] = 0;
                    $aggregates['Max_Total_Attendance'] = 0;
                    
                    if($len > 0) {
                        $aggregates['Min_Total_Attendance'] = $results[0]['Total_Attendance'];
                    } else {
                        $aggregates['Min_Total_Attendance'] = 0;
                    }
                    for($i=0; $i<$len; $i++) {
                        foreach($visitorResults as $k => $r) {                            
                            if($r['service_dt'] == $results[$i]['Attendance_dt']) {
                                $results[$i]['Total_Attendance'] += $r['visitors'];
                            }
                        }
                        $aggregates['Avg_Total_Attendance'] += $results[$i]['Total_Attendance'];
                        
                        if($results[$i]['Total_Attendance'] > $aggregates['Max_Total_Attendance'])
                            $aggregates['Max_Total_Attendance'] = $results[$i]['Total_Attendance'];
                        
                        if($results[$i]['Total_Attendance'] < $aggregates['Min_Total_Attendance'])
                            $aggregates['Min_Total_Attendance'] = $results[$i]['Total_Attendance'];
                    }
                    if($len > 0) {
                        $aggregates['Avg_Total_Attendance'] = $aggregates['Avg_Total_Attendance']/$len;
                    }
                }
                
                $dict['totals'] = $results;
                $dict['aggregates'] = $aggregates;
                break;
            case 2:
                $queryParams = array();
                $where = " and s.campus=:campus";
                $queryParams[":campus"] = $params->campus;
                $queryParams[":label1"] = $params->label1;
                if($params->fromDate != "") {
                    $queryParams[":fromDate"] = $params->fromDate;
                    $where .= " AND s.service_dt >= STR_TO_DATE(:fromDate,'%m/%d/%Y')";
                }
                if($params->toDate != "") {
                    $queryParams[":toDate"] = $params->toDate;
                    $where .= "AND s.service_dt <= STR_TO_DATE(:toDate,'%m/%d/%Y')";
                }
                if($params->label2 != "") {
                    $queryParams[":label2"] = $params->label2;
                    $query = "
                        SELECT
                            summary.id,
                            CASE WHEN summary.first_name IS NULL THEN
                                CASE WHEN summary.last_name IS NULL THEN
                                    summary.description
                                ELSE
                                    summary.last_name
                                END
                            ELSE
                                CASE WHEN summary.last_name IS NULL THEN
                                    summary.first_name
                                ELSE
                                    CONCAT_WS(' ', summary.first_name, summary.last_name)
                                END
                            END display,
                            COUNT(*) Total_Attendance,
                            SUM(summary.first) First_Service_Attendance,
                            SUM(summary.second) Second_Service_Attendance
                        FROM 
                            (
                                select distinct 
                                    COALESCE(u.attendance_dt1, u.attendance_dt2) attendance_dt,
                                    u.id,
                                    u.first_name,
                                    u.last_name,
                                    u.description,
                                    u.first,
                                    u.second
                                from (
                                    SELECT DISTINCT
                                      p.id,
                                      p.first_name,
                                      p.last_name,
                                      p.description,
                                      a1.attendance_dt attendance_dt1,
                                      a2.attendance_dt attendance_dt2,
                                      CASE WHEN a1.attended_by IS NULL THEN 0 ELSE 1 END first,
                                      CASE WHEN a2.attended_by IS NULL THEN 0 ELSE 1 END second
                                    FROM
                                      People p
                                      LEFT OUTER JOIN (
                                        SELECT
                                          s.service_dt attendance_dt,
                                          a.attended_by
                                        FROM
                                          attendance_test a
                                          inner join Services s on a.service_id=s.id and s.label=:label1 $where
                                      ) a1 ON a1.attended_by=p.id
                                      LEFT OUTER JOIN (
                                        SELECT
                                          s.service_dt attendance_dt,
                                          a.attended_by
                                        FROM
                                          attendance_test a
                                          inner join Services s on a.service_id=s.id and s.label=:label2 $where
                                      ) a2 ON a2.attended_by=p.id and a2.attendance_dt=a1.attendance_dt
                                      
                                    UNION
                                    
                                    SELECT DISTINCT
                                      p.id,
                                      p.first_name,
                                      p.last_name,
                                      p.description,
                                      a1.attendance_dt attendance_dt1,
                                      a2.attendance_dt attendance_dt2,
                                      CASE WHEN a1.attended_by IS NULL THEN 0 ELSE 1 END first,
                                      CASE WHEN a2.attended_by IS NULL THEN 0 ELSE 1 END second
                                    FROM
                                      People p
                                      LEFT OUTER JOIN (
                                        SELECT
                                          s.service_dt attendance_dt,
                                          a.attended_by
                                        FROM
                                          attendance_test a
                                          inner join Services s on a.service_id=s.id and s.label=:label2 $where
                                      ) a2 ON a2.attended_by=p.id
                                      LEFT OUTER JOIN (
                                        SELECT
                                          s.service_dt attendance_dt,
                                          a.attended_by
                                        FROM
                                          attendance_test a
                                          inner join Services s on a.service_id=s.id and s.label=:label1 $where
                                      ) a1 ON a1.attended_by=p.id and a2.attendance_dt=a1.attendance_dt
                                ) u
                            ) summary
                        WHERE
                            summary.attendance_dt IS NOT NULL
                        GROUP BY
                            summary.id
                        ORDER BY
                            Total_Attendance DESC,
                            summary.last_name IS NOT NULL DESC,
                            summary.description IS NOT NULL DESC,
                            summary.last_name,
                            summary.first_name,
                            summary.description";
                } else {
                    $query = "
                        SELECT
                            p.id,
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
                            COUNT(*) Total_Attendance
                        FROM 
                            People p
                            LEFT OUTER JOIN (
                                SELECT
                                  s.service_dt attendance_dt,
                                  a.attended_by
                                FROM
                                  attendance_test a
                                  inner join Services s on a.service_id=s.id and s.label=:label1 $where
                              ) a1 ON a1.attended_by=p.id
                        WHERE
                            p.adult = 1
                            and a1.attended_by is not null
                        GROUP BY
                            p.id
                        ORDER BY
                            Total_Attendance DESC,
                            p.last_name IS NOT NULL DESC,
                            p.description IS NOT NULL DESC,
                            p.last_name,
                            p.first_name,
                            p.description";
                }
                $results = $f->fetchAndExecute($query, $queryParams);
                $dict['people'] = $results;
                break;
            case 3:
                $query = "SELECT DISTINCT
                                service_dt,
                                id
                            FROM
                                Services AS s1
                            WHERE
                                (SELECT
                                COUNT(DISTINCT(service_dt))
                                  FROM
                                Services AS s2
                                  WHERE
                                DAYOFWEEK(s2.service_dt) = 1
                                AND DAYOFWEEK(s1.service_dt) = 1
                                AND s1.service_dt <= s2.service_dt) IN (1,2)
                                
                                AND s1.campus=:campus";
                $results = $f->fetchAndExecute($query, array(":campus"=>$params->campus));
                if(count($results) > 0) {
                    $service_ids = array();
                    foreach($results as $key => $row) {
                        array_push($service_ids, $row['id']);
                    }
                    $idString = implode(",", $service_ids);
                    $query = "SELECT DISTINCT
                                  p.id,
                                  p.first_name,
                                  p.last_name,
                                  p.description
                                FROM
                                  People p
                                  LEFT OUTER JOIN attendance_test a ON p.id=a.attended_by AND a.service_id IN
                                ($idString)
                                WHERE
                                  a.attended_by IS NULL
                                  AND p.adult=1
                                  AND p.active=1
                                ORDER BY
                                  p.last_name IS NOT NULL DESC,
                                  p.description IS NOT NULL DESC,
                                  p.last_name,
                                  p.first_name,
                                  p.description";
                    $results = $f->fetchAndExecute($query);
                }
                $dict['people'] = $results;
                break;
            case 4:
                $results = $f->getFollowUpReport($params, false);
                $dict['people'] = $results;
                break;
            case 5:
                $query = "SELECT
                            p.id,
                            p.first_name,
                            p.last_name,
                            p.description,
                            p.attender_status,
                            'true' adult
                          FROM
                            People p
                          WHERE
                            p.adult = 1
                          ORDER BY
                            p.attender_status,
                            p.last_name IS NOT NULL DESC,
                            p.description IS NOT NULL DESC,
                            p.last_name,
                            p.first_name,
                            p.description";
                $results = $f->fetchAndExecute($query);
                $dict['people'] = $results;
                break;
            }
            $dict['success'] = true;
        } catch (Exception $e) {
          $dict['success'] = false;
          $f->logMessage($e);
        }
    }

    echo json_encode($dict);
?>
