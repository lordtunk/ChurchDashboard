<?php
  session_start();
  include("func.php");
  $f = new Func();
  $id = $_POST['id'];
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
    $dict['success'] = FALSE;
    try {
      $query = "DELETE FROM Relationships WHERE id=:id";
      $results = $f->executeAndReturnResult($query, array(":id"=>$id));

      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = FALSE;
      $dict['msg']= $e->getMessage();
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>