<?php
  session_start();
  include("func.php");
  include("person.php");
  include("attendance.php");
  $f = new Func();
  $att = new Attendance();
  $people = json_decode($_POST['people']);
  $attendanceDate = $_POST['date'];
  $campus = $_POST['campus'];
  $label1 = $_POST['label1'];
  $label2 = $_POST['label2'];
  $visitors1 = $_POST['visitors1'];
  $visitors2 = $_POST['visitors2'];
  $adult = $_POST['adult'] == "true";
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
      $paramsSql = "";
      $paramsArr = array();
      $readyForStartingPoint = array();
      $serviceId1 = $att->getServiceId($attendanceDate, $campus, $label1);
      // Create the service if it does not exist
      if($serviceId1 == -1)
          $serviceId1 = $att->createService($attendanceDate, $campus, $label1);
      $att->updateVisitorCount($serviceId1, $visitors1, $adult);
      $serviceId2 = -1;
      if(isset($label2) && $label2 != '') {
        $serviceId2 = $att->getServiceId($attendanceDate, $campus, $label2);
        if($serviceId2 == -1)
          $serviceId2 = $att->createService($attendanceDate, $campus, $label2);
        $att->updateVisitorCount($serviceId2, $visitors2, $adult);
      }
      
      
      foreach($people as $key => $person) {
        $p = new Person($person, $f);

        // If the id is negative then we need to create the person
        if($p->id < 0) {
          $p->insert($user_id);
          array_push($new_people, $p->id);
        } else {
          $p->update();
          $att->deleteAttendance($serviceId1, $p->id);
          if($serviceId2 > 0)
              $att->deleteAttendance($serviceId2, $p->id);
        }

        // Add an Attendance record if the person attended this service
        if($p->first || $p->second) {
          if($p->first)
            $att->addAttendance($serviceId1, $p->id);
          if($p->second && $serviceId2 > 0)
              $att->addAttendance($serviceId2, $p->id);

          $paramCount = count($paramsArr)+1;
          $paramsArr[":id$paramCount"] = $p->id;
          if($paramCount > 1)
            $paramsSql = $paramsSql.",";
          $paramsSql = $paramsSql.":id".$paramCount;
        }
      }
      if(count($paramsArr) > 0) {
          $query = "SELECT 
                        p.id, p.first_name, p.last_name, p.description, p.primary_phone, count(*) attendance_count 
                    FROM 
                        Attendance a 
                        INNER JOIN People p ON p.id=a.attended_by
                    WHERE
                        a.attended_by IN ($paramsSql)
                        AND (a.first=1 OR a.second=1)
                        AND p.starting_point_notified = 0
                    GROUP BY 
                        p.id, p.first_name, p.last_name, p.description, p.primary_phone
                    HAVING
                        attendance_count=3";
          $results = $f->fetchAndExecute($query, $paramsArr);
          
          if(count($results) > 0) {
              $body = getEmailBody($results);
              $query = "SELECT
                      starting_point_emails
                    FROM
                      Settings";
              $results = $f->fetchAndExecute($query);
              if(count($results) > 0) {
                  $startingPointEmails = $results[0]['starting_point_emails'];
                  $f->sendEmail($startingPointEmails, "Ready for Starting Point", $body);
              }
              $query = "UPDATE People SET starting_point_notified=1 WHERE id IN ($paramsSql)";
              $f->executeAndReturnResult($query, $paramsArr);
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
        $people = $att->getAttendance($attendanceDate, true, $adult, $campus, $label1, $label2, $idIn);
        // $query = "SELECT
                    // p.id,
                    // p.first_name,
                    // p.last_name,
                    // p.description,
                    // p.adult,
                    // p.active,
                    // DATE_FORMAT(a.attendance_dt,'%m/%d/%Y') attendance_dt,
                    // a.first,
                    // a.second
                  // FROM
                    // People p
                    // LEFT OUTER JOIN Attendance a ON p.id=a.attended_by
                  // WHERE
                    // p.id IN ($idIn)
                  // ORDER BY
                    // p.last_name IS NOT NULL DESC,
                    // p.description IS NOT NULL DESC,
                    // p.last_name,
                    // p.first_name,
                    // p.description";
        // $results = $f->fetchAndExecute($query);
        // $people = array();
        // foreach($results as $key => $row) {
        // $p = NULL;
        // $j = NULL;
        // $foundPerson = FALSE;
        ////Check to see if we have already added the person
        // foreach($people as $k => $person) {
          // if(!isset($person['id'])) continue;
          // if($person['id'] == $row['id']) {
            // $j = $k;
            // $foundPerson = TRUE;
            // break;
          // }
        // }

        ////Set the person data if we have not encountered this person before
        // if($foundPerson == FALSE) {
          // $p = array();
          // $p['id'] = $row['id'];
          // $p['first_name'] = $row['first_name'];
          // $p['last_name'] = $row['last_name'];
          // $p['description'] = $row['description'];
          // $p['adult'] = $row['adult'] ? TRUE : FALSE;
          // $p['active'] = $row['active'] ? TRUE : FALSE;
          // $p['attendance'] = array();
          // array_push($people, $p);
          // $j = count($people) - 1;
        // }

        // if(isset($row['attendance_dt'])) {
          // $att = array();
          // $att['date'] = $row['attendance_dt'];
          // $att['first'] = $row['first'] ? TRUE : FALSE;
          // $att['second'] = $row['second'] ? TRUE : FALSE;
          // array_push($people[$j]['attendance'], $att);
        // }
      // }
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

  function getDisplayName($person, $prefix="") {
    if($person === null) return '';
    
    if($person[$prefix.'last_name'] && $person[$prefix.'first_name']) {
      return $person[$prefix.'first_name'] . " " . $person[$prefix.'last_name'];
    } else if($person[$prefix.'first_name']) {
      return $person[$prefix.'first_name'];
    } else if($person[$prefix.'last_name']) {
      return $person[$prefix.'last_name'];
    }
    return $person[$prefix.'description'];
  }
  function getEmailBody($people) {
      if(count($people) == 0) return;
      $body = "";
      foreach($people as $key => $person) {
        $body .= getListItem(getDisplayName($person)." ".$person['primary_phone']);
      }
      return "The following people are ready for Starting Point:<ul>$body</ul>";
  }
  function getListItem($text) {
      return "<li>$text</li>";
  }
?>
