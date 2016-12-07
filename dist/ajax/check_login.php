<?php
  session_start();
  include("../utils/func.php");
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
  echo json_encode($dict);
?>