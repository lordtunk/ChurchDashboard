<?php
  session_start();
  include("func.php");
  $f = new Func();
  $people = json_decode($_POST['people']);
  $dict = array();
  $new_people = array();
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
      $f->useTransaction = FALSE;
      $f->beginTransaction();
      
//       if(count($people) > 0) {
//         $query = "DELETE FROM Attendance WHERE attendance_dt=STR_TO_DATE(:attendance_dt,'%m/%d/%Y')";
//         $f->executeAndReturnResult($query, array(":attendance_dt"=>$people[0]->attendanceDate));
//       }
      
      foreach($people as $key => $person) {
        // Translate TRUE/FALSE to 1/0 so that log statements
        // are easier to read since FALSE does not display
        $person->adult = $person->adult ? 1 : 0;
        $person->first = $person->first ? 1 : 0;
        $person->second = $person->second ? 1 : 0;

        // If the id is negative then we need to create the person
        if($person->id < 0) {
          if(isset($person->display)) {
            $pos = strpos($person->display, ",");

            // If a comma is found then assume the display is Last, First
            if($pos == FALSE) {
              $query = "INSERT INTO People (description, adult, last_modified_dt, modified_by, creation_dt, created_by) VALUES (:description, :adult, NOW(), :user_id, NOW(), :user_id)";
              $person->id = $f->queryLastInsertId($query, array(":description"=>$person->display, ":adult"=>$person->adult, ":user_id"=>$user_id));
            } else {
              $names = explode(",", $person->display);
              $person->last_name = trim($names[0]);
              $person->first_name = trim($names[1]);
              $query = "INSERT INTO People (last_name, first_name, adult, last_modified_dt, modified_by, creation_dt, created_by) VALUES (:last_name, :first_name, :adult, NOW(), :user_id, NOW(), :user_id)";
              $person->id = $f->queryLastInsertId($query, array(":last_name"=>$person->last_name, ":first_name"=>$person->first_name, ":adult"=>$person->adult, ":user_id"=>$user_id));
            }
            array_push($new_people, $person->id);
          }
        } else {
          $query = "UPDATE People SET active=true WHERE id=:id";
          $f->executeAndReturnResult($query, array(":id"=>$person->id));
          
          $query = "DELETE FROM Attendance WHERE attended_by=:id AND attendance_dt=STR_TO_DATE(:attendance_dt,'%m/%d/%Y')";
          $f->executeAndReturnResult($query, array(":id"=>$person->id, ":attendance_dt"=>$person->attendanceDate));
        }

        // Add an Attendance record if the person attended this service
        if($person->first || $person->second) {
          $query = "INSERT INTO Attendance (`attendance_dt`, `attended_by`, `first`, `second`) VALUES(STR_TO_DATE(:attendance_dt,'%m/%d/%Y'), :attended_by, :first, :second)";
          $results = $f->executeAndReturnResult($query, array(":attendance_dt"=>$person->attendanceDate, ":attended_by"=>$person->id, ":first"=>$person->first, ":second"=>$person->second));
        }
      }
      $f->commit();
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = FALSE;
      $dict['msg']= $e->getMessage();
      $f->rollback();
    }
    
    if($dict['success'] == TRUE && count($new_people) > 0) {
      try {
        // Should be safe to build query as no input from the client is used
        $idIn = "".$new_people[0];
        for($i=1; $i < count($new_people); $i++) {
          $idIn = $idIn.",".$new_people[$i];
        }
        $query = "SELECT
                    p.id,
                    p.first_name,
                    p.last_name,
                    p.description,
                    p.adult,
                    p.active,
                    DATE_FORMAT(a.attendance_dt,'%m/%d/%Y') attendance_dt,
                    a.first,
                    a.second
                  FROM
                    People p
                    LEFT OUTER JOIN Attendance a ON p.id=a.attended_by
                  WHERE
                    p.id IN ($idIn)
                  ORDER BY
                    p.last_name IS NOT NULL DESC,
                    p.description IS NOT NULL DESC,
                    p.last_name,
                    p.first_name,
                    p.description";
        $results = $f->fetchAndExecute($query);
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
        $dict['msg']= "The save was successful but there was a problem retrieving updated attendance information.";
      }
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>