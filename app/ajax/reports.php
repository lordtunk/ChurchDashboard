<?php
  session_start();
  include("func.php");
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
      $query = "SELECT
                  COUNT(1) Total_Attendance,
                  SUM(a.first) First_Service_Attendance,
                  SUM(a.second) Second_Service_Attendance,
                  SUM(CASE WHEN p.adult=1 AND a.first=1 THEN 1 ELSE 0 END) First_Service_Adult_Attendance,
                  SUM(CASE WHEN p.adult=1 AND a.second=1 THEN 1 ELSE 0 END) Second_Service_Adult_Attendance,
                  SUM(CASE WHEN p.adult=0 AND a.first=1 THEN 1 ELSE 0 END) First_Service_Kid_Attendance,
                  SUM(CASE WHEN p.adult=0 AND a.second=1 THEN 1 ELSE 0 END) Second_Service_Kid_Attendance,
                  attendance_dt Attendance_dt
                FROM
                  People p
                  INNER JOIN Attendance a ON a.attended_by=p.id
                WHERE
                  a.first = 1 OR a.second = 1
                GROUP BY
                  a.attendance_dt
                ORDER BY
                  a.attendance_dt DESC";
      $dict['totals'] = $f->fetchAndExecute($query);
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
                  MAX(totals.Second_Service_Kid_Attendance) Max_Second_Service_Kid_Attendance
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
                  WHERE
                    a.first = 1 OR a.second = 1
                  GROUP BY
                    a.attendance_dt
                ) totals";
      $dict['aggregates'] = $f->fetchAndExecute($query);
      $dict['success'] = true;
    } catch (Exception $e) {
      $dict['success'] = false;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>