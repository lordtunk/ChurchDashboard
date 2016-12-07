<?php
  session_start();
  include("../utils/func.php");
  $f = new Func();
  $id = $_POST['personId'];
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
      $query = "SELECT relation_id FROM  Relationships WHERE person_id=:id AND TYPE=1";
      $results = $f->fetchAndExecute($query, array(":id"=>$id));
      if(count($results) > 0) {
        $spouseId = $results[0]['relation_id'];
        $query = "
            UPDATE People p2, (SELECT * FROM People where id=:id) p1 SET 
                p2.street1 = p1.street1,
                p2.street2 = p1.street2,
                p2.city = p1.city,
                p2.state = p1.state,
                p2.zip = p1.zip
            WHERE p2.id=:spouse_id";
        $f->executeAndReturnResult($query, array(":id"=>$id, ":spouse_id"=>$spouseId));
        $dict['success'] = TRUE;
      } else {
        $dict['success'] = FALSE;
        $dict['msg']= "Person does not have a spouse";
      }
    } catch (Exception $e) {
      $dict['success'] = FALSE;
      $dict['msg']= $e->getMessage();
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>
