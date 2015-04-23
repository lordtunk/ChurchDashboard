<?php
  session_start();
  include("func.php");
  $f = new Func();
  $dict = array();
  $attendanceDate = $_POST['date'];
  $active = $_POST['active'] == "true";
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
                  p.active,
                  DATE_FORMAT(a.attendance_dt,'%c/%e/%Y') attendance_dt,
                  a.first,
                  a.second
                FROM
                  People p
                  LEFT OUTER JOIN Attendance a ON p.id=a.attended_by AND DATE_FORMAT(a.attendance_dt,'%c/%e/%Y') = :date
                WHERE
                  p.active = :active
                ORDER BY
                  p.last_name IS NOT NULL DESC,
                  p.description IS NOT NULL DESC,
                  p.last_name,
                  p.first_name,
                  p.description,
                  attendance_dt DESC";
      $results = $f->fetchAndExecute($query, array(":date"=>$attendanceDate, ":active"=>$active));
      $people = array();
      foreach($results as $key => $row) {
        $p = NULL;
        $j = NULL;
        $foundPerson = FALSE;
        // Check to see if we have already added the person
        foreach($people as $k => $person) {
          if(!isset($person['id'])) continue;
          if($person['id'] == $row['id']) {
            $j = $k;
            $foundPerson = TRUE;
            break;
          }
        }

        // Set the person data if we have not encountered this person before
        if($foundPerson == FALSE) {
          $p = array();
          $p['id'] = $row['id'];
          $p['first_name'] = $row['first_name'];
          $p['last_name'] = $row['last_name'];
          $p['description'] = $row['description'];
          $p['adult'] = $row['adult'] ? TRUE : FALSE;
          $p['active'] = $row['active'] ? TRUE : FALSE;
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
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = FALSE;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>