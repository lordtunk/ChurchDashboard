<?php
    session_start();
    include("func.php");
    $f = new Func();
    $dict = array();
    $attendanceDate = $_POST['date'];
    $active = $_POST['active'] == "true";
    $adult = $_POST['adult'] == "true";
    $isDefaultLoad = $_POST['isDefaultLoad'] == "true";
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
        if($isDefaultLoad == TRUE) {
            if(isset($_SESSION['attendance_dt']) && $_SESSION['attendance_dt'] != "") {
                $dict['attendance_dt'] = $_SESSION['attendance_dt'];
                $attendanceDate = $_SESSION['attendance_dt'];
            }
            if(isset($_SESSION['attendance_active']) && $_SESSION['attendance_active'] != "") {
                $dict['attendance_active'] = $_SESSION['attendance_active'];
                $active = $_SESSION['attendance_active'] == "true";
            }
            if(isset($_SESSION['attendance_adults']) && $_SESSION['attendance_adults'] != "") {
                $dict['attendance_adults'] = $_SESSION['attendance_adults'];
                $adult = $_SESSION['attendance_adults'] == "true";
            }
        } else {
            $_SESSION['attendance_dt'] = $attendanceDate;
            $_SESSION['attendance_active'] = $_POST['active'];
            $_SESSION['attendance_adults'] = $_POST['adult'];
        }
        try {
            $query = "SELECT
                      p.id,
                      p.first_name,
                      p.last_name,
                      p.description,
                      DATE_FORMAT(a.attendance_dt,'%c/%e/%Y') attendance_dt,
                      a.first,
                      a.second
                    FROM
                      People p
                      LEFT OUTER JOIN Attendance a ON p.id=a.attended_by AND DATE_FORMAT(a.attendance_dt,'%c/%e/%Y') = :date
                    WHERE
                      p.active = :active
                      AND p.adult = :adult
                    ORDER BY
                      p.last_name IS NOT NULL DESC,
                      p.description IS NOT NULL DESC,
                      p.last_name,
                      p.first_name,
                      p.description,
                      attendance_dt DESC";
            $results = $f->fetchAndExecute($query, array(":date"=>$attendanceDate, ":active"=>$active, ":adult"=>$adult));
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
                    $p['adult'] = $adult;
                    $p['active'] = $active;
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
            
            $query = "
                    select
                        (SELECT count(*) FROM Attendance WHERE first = 1 AND DATE_FORMAT(attendance_dt, '%c/%e/%Y') = :date) total_first_count,
                        (SELECT count(*) FROM Attendance WHERE second = 1 AND DATE_FORMAT(attendance_dt, '%c/%e/%Y') = :date) total_second_count,
                        (SELECT count(*) FROM Attendance WHERE (first = 1 OR second = 1) AND DATE_FORMAT(attendance_dt, '%c/%e/%Y') = :date) total_total_count,

                        (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=1 AND a.first = 1 AND DATE_FORMAT(a.attendance_dt, '%c/%e/%Y') = :date) adult_first_count,
                        (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=1 AND a.second = 1 AND DATE_FORMAT(a.attendance_dt, '%c/%e/%Y') = :date) adult_second_count,
                        (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=1 AND (a.first = 1 OR a.second = 1) AND DATE_FORMAT(a.attendance_dt, '%c/%e/%Y') = :date) adult_total_count,

                        (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=0 AND a.first = 1 AND DATE_FORMAT(a.attendance_dt, '%c/%e/%Y') = :date) kid_first_count,
                        (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=0 AND a.second = 1 AND DATE_FORMAT(a.attendance_dt, '%c/%e/%Y') = :date) kid_second_count,
                        (SELECT count(*) FROM Attendance a inner join People p on a.attended_by=p.id WHERE p.adult=0 AND (a.first = 1 OR a.second = 1) AND DATE_FORMAT(a.attendance_dt, '%c/%e/%Y') = :date) kid_total_count
                    from
                        dual";
            $results = $f->fetchAndExecute($query, array(":date"=>$attendanceDate));
            $totals = array();
            foreach($results as $key => $row) {
                $totals['total_first_count'] = $row['total_first_count'];
                $totals['total_second_count'] = $row['total_second_count'];
                $totals['total_total_count'] = $row['total_total_count'];
                
                $totals['adult_first_count'] = $row['adult_first_count'];
                $totals['adult_second_count'] = $row['adult_second_count'];
                $totals['adult_total_count'] = $row['adult_total_count'];
                
                $totals['kid_first_count'] = $row['kid_first_count'];
                $totals['kid_second_count'] = $row['kid_second_count'];
                $totals['kid_total_count'] = $row['kid_total_count'];
            }
            $dict['people'] = $people;
            $dict['totals'] = $totals;
            $dict['success'] = TRUE;
        } catch (Exception $e) {
            $dict['success'] = FALSE;
        }
    } else {
        $dict['error'] = 1;
    }
    echo json_encode($dict);
?>