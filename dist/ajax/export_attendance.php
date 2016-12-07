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
  
  if($dict['success'] == TRUE) {
    try {
      // Get the service dates so that we can determine which services a person
      // has not attended.
      $query = "SELECT DISTINCT 
                  DATE_FORMAT(attendance_dt,'%m/%d/%Y') attendance_dt 
                FROM 
                  `Attendance` 
                ORDER BY 
                  attendance_dt";
      $results = $f->fetchAndExecute($query);
      $dates = array();
      $headers = array("Adult/Kid", "Active/Inactive", "Name");
      foreach($results as $key => $row) {
        array_push($dates, $row['attendance_dt']);
        array_push($headers, $row['attendance_dt']);
      }
      
      // Retrieve and build all of the information for each person
      $query = "SELECT
                  id,
                  first_name,
                  last_name,
                  description,
                  adult,
                  active
                FROM
                  People
                ORDER BY
                  last_name IS NOT NULL DESC,
                  description IS NOT NULL DESC,
                  last_name,
                  first_name,
                  description";
      $results = $f->fetchAndExecute($query);
      $people = array();
      $lastPerson = NULL;
      foreach($results as $key => $row) {
        $p = array();
        array_push($p, $row['adult'] ? 'Adult' : 'Kid');
        array_push($p, $row['active'] ? 'Active' : 'Inactive');
        array_push($p, getDisplayName($row));
        $lastPerson = $row['id'];
        $people[$row['id']] = $p;
      }
      $numDates = count($dates);
      if(count($people) > 0) {
        $numPersonFields = count($people[$lastPerson]);
      } else {
        $numPersonFields = 0;
      }

      $query = "SELECT
                  p.id,
                  DATE_FORMAT(a.attendance_dt,'%m/%d/%Y') attendance_dt,
                  a.first,
                  a.second
                FROM
                  People p
                  LEFT OUTER JOIN Attendance a ON p.id=a.attended_by
                ORDER BY
                  p.id,
                  attendance_dt";
      $results = $f->fetchAndExecute($query);
      
      $today = date("m-d-y");
      $fileName = "attendance as of $today.csv";
 
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header('Content-Description: File Transfer');
      header("Content-type: text/csv");
      header("Content-Disposition: attachment; filename=\"{$fileName}\"");
      header("Expires: 0");
      header("Pragma: public");
      
      $fh = @fopen( 'php://output', 'w' );
      fputcsv($fh, $headers);
      foreach($results as $key => $row) {
        $id = $row['id'];
        if(isset($row['attendance_dt']) && isset($people[$id])) {
          $startCount = count($people[$id]) - $numPersonFields;
          for($i=$startCount; $i<$numDates; $i++) {
            // If the dates match then take the data from the record. Otherwise,
            // add an empty slot and continue.
            if($dates[$i] === $row['attendance_dt']) {
              array_push($people[$id], getAttendanceString($row));
              
              // Do not continue looking through dates since we found the
              // current record's date.
              break;
            } else {
              // Since the dates are sorted in the same order, if the date from
              // the current row of data is not this date then the person did
              // not attend this service
              array_push($people[$id], "");
            }
          }
        }
      }
      
      foreach($people as $k => $p) {
        fputcsv($fh, $p);
      }
      
      //$dict['people'] = $people;
      //$dict['success'] = TRUE;
    } catch (Exception $e) {
      //$dict['success'] = FALSE;
    }
  } else {
    //$dict['error'] = 1;
  }
  //echo json_encode($dict);
  
  if(isset($fh)) {
    // Close the file
    fclose($fh);
  }
  // Make sure nothing else is sent, our file is done
  exit;
  
  function getDisplayName($person) {
    if($person === null) return '';
    
    if($person['last_name'] && $person['first_name']) {
      return $person['last_name'] . ", " . $person['first_name'];
    } else if($person['first_name']) {
      return $person['first_name'];
    } else if($person['last_name']) {
      return $person['last_name'];
    }
    return $person['description'];
  }
  function getAttendanceString($att) {
    if($att['first'] && $att['second'])
      return "1,2";
    else if($att['first'])
      return "1";
    else if($att['second'])
      return "2";
    return "";
  }
?>