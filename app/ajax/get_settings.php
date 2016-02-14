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
      $query = "SELECT
                  starting_point_emails,
                  campuses,
                  service_labels,
                  default_campus,
                  default_first_service_label,
                  default_second_service_label
                FROM
                  Settings";
      $results = $f->fetchAndExecute($query);
      $dict['starting_point_emails'] = $results[0]['starting_point_emails'];
      $dict['campuses'] = $results[0]['campuses'];
      $dict['service_labels'] = $results[0]['service_labels'];
      $dict['default_campus'] = $results[0]['default_campus'];
      $dict['default_first_service_label'] = $results[0]['default_first_service_label'];
      $dict['default_second_service_label'] = $results[0]['default_second_service_label'];
      $dict['success'] = true;
    } catch (Exception $e) {
      $dict['success'] = false;
    }
  }
  echo json_encode($dict);
?>