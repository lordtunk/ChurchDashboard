<?php
  session_start();
  include("func.php");
  $type = $_POST['type'];
  $params = json_decode($_POST['params']);
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
  } else if(!isDate($params->fromDate, true) || !isDate($params->toDate, true)) {
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
	$where = "
		  WHERE 
		    p.active = 1";
	$groupBy = "
		  GROUP BY 
		    p.id ";
	$having = "
		  HAVING 
		    ";
	$havingArr = array();
	$queryParams = array();
        $query = "SELECT 
		    CASE WHEN vc.visit_count >0 THEN  'true' ELSE  'false' END visited, 
		    CASE WHEN tyc.ty_card_sent_count >0 THEN  'true' ELSE  'false' END ty_card_sent, 
		    DATE_FORMAT(f.follow_up_date, '%m/%d/%Y') communication_card_date, 
		    DATE_FORMAT(tyc.follow_up_date, '%m/%d/%Y') ty_card_date, 
		    p.id, 
		    p.first_name, 
		    p.last_name, 
		    p.description,
		    p.primary_phone,
		    CASE WHEN p.commitment_baptism = 1 THEN  'true' ELSE  'false' END commitment_baptism
		  FROM 
		    People p
		    LEFT OUTER JOIN FollowUps f ON f.follow_up_to_person_id = p.id AND f.type = 3
		    LEFT OUTER JOIN (SELECT COUNT(*) visit_count, follow_up_to_person_id FROM FollowUps WHERE TYPE =2)vc ON vc.follow_up_to_person_id = p.id
		    LEFT OUTER JOIN (SELECT COUNT(*) ty_card_sent_count, follow_up_to_person_id, follow_up_date FROM FollowUps WHERE TYPE =5)tyc ON tyc.follow_up_to_person_id = p.id";
	
	if($params->signed_up_for_baptism) {
	  $where .= "
		    AND p.commitment_baptism = 1";
	}
	if($params->baptized) {
	  $where .= "
		    AND p.baptized = 1";
	}
	if($params->interested_in_gkids) {
	  $where .= "
		    AND p.info_gkids = 1";
	}
	if($params->interested_in_next) {
	  $where .= "
		    AND p.info_next = 1";
	}
	if($params->interested_in_ggroups) {
	  $where .= "
		    AND p.info_ggroups = 1";
	}
	if($params->interested_in_gteams) {
	  $where .= "
		    AND p.info_gteams = 1";
	}
	if($params->interested_in_joining) {
	  $where .= "
		    AND p.info_member = 1";
	}
	if($params->would_like_visit) {
	  $where .= "
		    AND p.info_visit = 1";
	}
	if($params->no_agent) {
	  $where .= "
		    AND p.assigned_agent = 0";
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
	if($params->not_visited) {
	  array_push($havingArr, "visited =  'false'");
	}
	if($params->ty_card_not_sent) {
	  array_push($havingArr, "ty_card_sent =  'false'");
	}
	$query .= $where;
	
	if(count($havingArr) > 0) {
	  $query .= $groupBy.$having;
	  $query .= join(" AND ", $havingArr);
	}
	
        $results = $f->fetchAndExecute($query, $queryParams);
        $dict['people'] = $results;
        break;
      }
      $dict['success'] = true;
    } catch (Exception $e) {
      $dict['success'] = false;
    }
    
  }
  
  echo json_encode($dict);
?>