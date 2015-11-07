<?php
    session_start();
    include("func.php");
    $type = $_POST['type'];
    $params = isset($_POST['params']) ? json_decode($_POST['params']) : null;
    $f = new Func();
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
                $where = "WHERE
                            (a.first = 1
                              OR a.second = 1)
                            AND p.active=1";
                if($params->fromDate != "") {
                    $queryParams[":fromDate"] = $params->fromDate;
                    $where .= "
                                AND a.attendance_dt >= STR_TO_DATE(:fromDate,'%m/%d/%Y')";
                }
                if($params->toDate != "") {
                    $queryParams[":toDate"] = $params->toDate;
                    $where .= "
                        AND a.attendance_dt <= STR_TO_DATE(:toDate,'%m/%d/%Y')";
                }
                $query = "SELECT
                            COUNT(1) Total_Attendance,
                            SUM(a.first) First_Service_Attendance,
                            SUM(a.second) Second_Service_Attendance,
                            SUM(CASE WHEN p.adult=1 AND a.first=1 THEN 1 ELSE 0 END) First_Service_Adult_Attendance,
                            SUM(CASE WHEN p.adult=1 AND a.second=1 THEN 1 ELSE 0 END) Second_Service_Adult_Attendance,
                            SUM(CASE WHEN p.adult=0 AND a.first=1 THEN 1 ELSE 0 END) First_Service_Kid_Attendance,
                            SUM(CASE WHEN p.adult=0 AND a.second=1 THEN 1 ELSE 0 END) Second_Service_Kid_Attendance,
                            DATE_FORMAT(attendance_dt,'%m/%d/%Y') Attendance_dt
                          FROM
                            People p
                            INNER JOIN Attendance a ON a.attended_by=p.id
                          $where
                          GROUP BY
                            a.attendance_dt
                          ORDER BY
                            a.attendance_dt DESC";
                $results = $f->fetchAndExecute($query, $queryParams);
                $dict['totals'] = $results;
                $query = "SELECT
                            AVG(totals.Total_Attendance) Avg_Total_Attendance,
                            AVG(totals.First_Service_Attendance) Avg_First_Service_Attendance,
                            AVG(totals.Second_Service_Attendance) Avg_Second_Service_Attendance,
                            AVG(totals.First_Service_Adult_Attendance) Avg_First_Service_Adult_Attendance,
                            AVG(totals.Second_Service_Adult_Attendance) Avg_Second_Service_Adult_Attendance,
                            AVG(totals.First_Service_Kid_Attendance) Avg_First_Service_Kid_Attendance,
                            AVG(totals.Second_Service_Kid_Attendance) Avg_Second_Service_Kid_Attendance,
                            MAX(totals.Total_Attendance) Max_Total_Attendance,
                            MAX(totals.First_Service_Attendance) Max_First_Service_Attendance,
                            MAX(totals.Second_Service_Attendance) Max_Second_Service_Attendance,
                            MAX(totals.First_Service_Adult_Attendance) Max_First_Service_Adult_Attendance,
                            MAX(totals.Second_Service_Adult_Attendance) Max_Second_Service_Adult_Attendance,
                            MAX(totals.First_Service_Kid_Attendance) Max_First_Service_Kid_Attendance,
                            MAX(totals.Second_Service_Kid_Attendance) Max_Second_Service_Kid_Attendance,
                            MIN(totals.Total_Attendance) Min_Total_Attendance,
                            MIN(totals.First_Service_Attendance) Min_First_Service_Attendance,
                            MIN(totals.Second_Service_Attendance) Min_Second_Service_Attendance,
                            MIN(totals.First_Service_Adult_Attendance) Min_First_Service_Adult_Attendance,
                            MIN(totals.Second_Service_Adult_Attendance) Min_Second_Service_Adult_Attendance,
                            MIN(totals.First_Service_Kid_Attendance) Min_First_Service_Kid_Attendance,
                            MIN(totals.Second_Service_Kid_Attendance) Min_Second_Service_Kid_Attendance
                          FROM (
                            SELECT
                              COUNT(1) Total_Attendance,
                              SUM(a.first) First_Service_Attendance,
                              SUM(a.second) Second_Service_Attendance,
                              SUM(CASE WHEN p.adult=1 AND a.first=1 THEN 1 ELSE 0 END) First_Service_Adult_Attendance,
                              SUM(CASE WHEN p.adult=1 AND a.second=1 THEN 1 ELSE 0 END) Second_Service_Adult_Attendance,
                              SUM(CASE WHEN p.adult=0 AND a.first=1 THEN 1 ELSE 0 END) First_Service_Kid_Attendance,
                              SUM(CASE WHEN p.adult=0 AND a.second=1 THEN 1 ELSE 0 END) Second_Service_Kid_Attendance
                            FROM
                              People p
                              INNER JOIN Attendance a ON a.attended_by=p.id
                            $where
                            GROUP BY
                              a.attendance_dt
                          ) totals";
                $dict['aggregates'] = $f->fetchAndExecute($query, $queryParams);
                break;
            case 2:
                $queryParams = array();
                $where = "WHERE
                            (a.first = 1
                              OR a.second = 1)
                            AND p.active=1";
                if($params->fromDate != "") {
                    $queryParams[":fromDate"] = $params->fromDate;
                    $where .= "
                        AND a.attendance_dt >= STR_TO_DATE(:fromDate,'%m/%d/%Y')";
                }
                if($params->toDate != "") {
                    $queryParams[":toDate"] = $params->toDate;
                    $where .= "
                        AND a.attendance_dt <= STR_TO_DATE(:toDate,'%m/%d/%Y')";
                }
                $query = "SELECT
                            p.id,
                            CASE WHEN p.adult = 1 THEN 'true' ELSE 'false' END adult,
                            p.first_name,
                            p.last_name,
                            p.description,
                            COUNT(1) Total_Attendance,
                            SUM(a.first) First_Service_Attendance,
                            SUM(a.second) Second_Service_Attendance
                          FROM
                            People p
                            INNER JOIN Attendance a ON a.attended_by=p.id
                          $where
                          GROUP BY
                            p.id
                          ORDER BY
                            Total_Attendance DESC,
                            p.last_name IS NOT NULL DESC,
                            p.description IS NOT NULL DESC,
                            p.last_name,
                            p.first_name,
                            p.description";
                $results = $f->fetchAndExecute($query, $queryParams);
                $dict['people'] = $results;
                break;
            case 3:
                $query = "SELECT DISTINCT
                            attendance_dt
                          FROM
                            Attendance AS a1
                          WHERE
                            (SELECT
                            COUNT(DISTINCT(attendance_dt))
                              FROM
                            Attendance AS a2
                              WHERE
                            DAYOFWEEK(a2.attendance_dt) = 1
                            AND DAYOFWEEK(a1.attendance_dt) = 1
                            AND a1.attendance_dt <= a2.attendance_dt) IN (1,2)";
                $results = $f->fetchAndExecute($query, array());
                if(count($results) > 0) {
                    $dates = array();
                    foreach($results as $key => $row) {
                        array_push($dates, $row['attendance_dt']);
                    }
                    $dateString = implode(",", $dates);
                    $query = "SELECT DISTINCT
                                  p.id,
                                  p.first_name,
                                  p.last_name,
                                  p.description,
                                  CASE WHEN p.adult = 1 THEN 'true' ELSE 'false' END adult
                                FROM
                                  People p
                                  LEFT OUTER JOIN Attendance a ON p.id=a.attended_by AND a.attendance_dt IN
                                ($dateString)
                                WHERE
                                  a.attendance_dt IS NULL
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
                $results = $f->getFollowUpReport($params);
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
