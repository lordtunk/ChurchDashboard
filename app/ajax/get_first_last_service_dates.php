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
                  DATE_FORMAT(MAX(s.service_dt),'%m/%d/%Y') Last_Attendance_Dt,
                  DATE_FORMAT(MIN(s.service_dt),'%m/%d/%Y') First_Attendance_Dt
                FROM
                  Services s";
      $results = $f->fetchAndExecute($query);
      $dict['first_dt'] = $results[0]['First_Attendance_Dt'];
      $dict['last_dt'] = $results[0]['Last_Attendance_Dt'];
      $dict['success'] = true;
    } catch (Exception $e) {
      $dict['success'] = false;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>