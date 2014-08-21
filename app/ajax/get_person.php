<?php
  session_start();
  include("func.php");
  $id = $_GET['id'];
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
                  p.id,
                  p.first_name,
                  p.last_name,
                  p.description,
                  p.active,
                  p.adult
                FROM
                  People p
                WHERE
                  p.id=:id";
      $results = $f->fetchAndExecute($query, array(":id"=>$id));
      if(count($results) > 0) {
        $people = array();
        foreach($results as $key => $row) {
          $p = array();
          $p['id'] = $row['id'];
          $p['first_name'] = $row['first_name'];
          $p['last_name'] = $row['last_name'];
          $p['description'] = $row['description'];
          $p['adult'] = $row['adult'] ? TRUE : FALSE;
          $p['active'] = $row['active'] ? TRUE : FALSE;
          array_push($people, $p);
        }
        $dict['person'] = $people[0];
        $dict['success'] = TRUE;
        $_SESSION['scroll_to_id'] = $id;
      } else {
        $dict['success'] = FALSE;
      }
    } catch (Exception $e) {
      $dict['success'] = false;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>