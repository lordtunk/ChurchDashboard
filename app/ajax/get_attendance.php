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
    if(isset($_SESSION['scroll_to_id']) && $_SESSION['scroll_to_id'] >= 0) {
      $dict['scroll_to_id'] = $_SESSION['scroll_to_id'];
      $_SESSION['scroll_to_id'] = -1;
    }
    try {
      $query = "SELECT
                  p.id,
                  p.first_name,
                  p.last_name,
                  p.description,
                  p.adult,
                  DATE_FORMAT(a.attendance_dt,'%m/%d/%Y') attendance_dt,
                  a.first,
                  a.second
                FROM
                  People p
                  LEFT OUTER JOIN Attendance a ON p.id=a.attended_by
                ORDER BY
                  p.description,
                  p.last_name,
                  p.first_name";
      $results = $f->fetchAndExecute($query);
      $people = array();
      foreach($results as $key => $row) {
        $p = NULL;
        $j = NULL;
        $foundPerson = false;
        // Check to see if we have already added the person
        foreach($people as $k => $person) {
          if(!isset($person['id'])) continue;
          if($person['id'] == $row['id']) {
            $j = $k;
            $foundPerson = true;
            break;
          }
        }

        // Set the person data if we have not encountered this person before
        if($foundPerson == false) {
          $p = array();
          $p['id'] = $row['id'];
          $p['first_name'] = $row['first_name'];
          $p['last_name'] = $row['last_name'];
          $p['description'] = $row['description'];
          $p['adult'] = $row['adult'] ? true : false;
          $p['attendance'] = array();
          array_push($people, $p);
          $j = count($people) - 1;
        }

        if(isset($row['attendance_dt'])) {
          $att = array();
          $att['date'] = $row['attendance_dt'];
          $att['first'] = $row['first'] ? TRUE : FALSE;
          $att['second'] = $row['second'] ? TRUE : FALSE;
          array_push($people[$j]['attendance'], $att);
        }
      }
      $dict['people'] = $people;
      $dict['success'] = true;
    } catch (Exception $e) {
      $dict['success'] = false;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>