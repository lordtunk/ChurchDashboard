<?php
  session_start();
  include("func.php");
  $f = new Func();
  $dict = array();
  $_SESSION['session_id'] = NULL;
  if(!isset($_SESSION['user_id'])) {
    $dict['success'] = FALSE;
    $f->logMessage('Session information missing');
  } else {
    $user_id = $_SESSION['user_id'];
    
    try {
      $dict['success'] = $f->logout($user_id);
    } catch (Exception $e) {
      $dict['success'] = FALSE;
      $f->logMessage($e->getMessage());
    }
  }

  echo json_encode($dict);
?>