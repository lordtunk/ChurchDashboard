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
                  p.id,
                  p.first_name,
                  p.last_name,
                  p.description
                FROM
                  People p
                WHERE
                  p.visitor=true";
      $results = $f->fetchAndExecute($query);
      if(count($results) > 0) {
        $people = array();
        foreach($results as $key => $row) {
          $p = array();
          $p['id'] = $row['id'];
          $p['first_name'] = $row['first_name'];
          $p['last_name'] = $row['last_name'];
          $p['description'] = $row['description'];
          array_push($people, $p);
        }
        $dict['people'] = $people;
        $dict['success'] = TRUE;
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