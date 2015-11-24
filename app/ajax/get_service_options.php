<?php
  session_start();
  include("func.php");
  include("attendance.php");
  $f = new Func();
  $att = new Attendance();
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
    if($dict['success'] == FALSE) {
        $dict['error'] = 1;
    } else {
        // Must be me to access this page
        if($user_id != "1") {
            $dict['success'] = FALSE;
            $dict['error'] = 2;
        }
    }
  }

  if($dict['success'] == TRUE) {
    try {
      $dict['options'] = $att->getServiceOptions();
      $dict['success'] = true;
    } catch (Exception $e) {
      $dict['success'] = false;
    }
  }
  echo json_encode($dict);
?>