<?php
  session_start();
  include("../utils/func.php");
  include("../utils/follow_ups.php");
  $f = new Func();
  $followUps = new FollowUps();
  $dict = array();
  $followUpDate = $_POST['date'];
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
      $dict['follow_ups'] = $followUps->getFollowUpsByDate($followUpDate);
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = false;
      $dict['errorMsg'] = $e;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>
