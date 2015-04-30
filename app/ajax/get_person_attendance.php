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
            $query = "
              SELECT
                    p.first_name,
                    p.last_name,
                    p.description,
                    DATE_FORMAT(a.attendance_dt,'%c/%e/%Y') attendance_dt,
                    a.first, 
                    a.second 
                FROM 
                    People p
                    left outer join Attendance a on a.attended_by=p.id
                WHERE 
                    p.id=:id
                ORDER BY
                    a.attendance_dt DESC";
            $results = $f->fetchAndExecute($query, array(":id"=>$id));
            $person = array();
            $person['attendance'] = array();
            foreach($results as $key => $row) {
                $person['first_name'] = $row['first_name'];
                $person['last_name'] = $row['last_name'];
                $person['description'] = $row['description'];

                if(isset($row['attendance_dt'])) {
                  $att = array();
                  $att['date'] = $row['attendance_dt'];
                  $att['first'] = $row['first'] ? TRUE : FALSE;
                  $att['second'] = $row['second'] ? TRUE : FALSE;
                  array_push($person['attendance'], $att);
                }
            }
            $dict['person'] = $person;
            $dict['success'] = TRUE;
        } catch (Exception $e) {
            $dict['success'] = false;
            $dict['errorMsg'] = $e;
        }
    } else {
        $dict['error'] = 1;
    }
    echo json_encode($dict);
?>