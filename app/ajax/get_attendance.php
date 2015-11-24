<?php
    session_start();
    include("func.php");
    include("attendance.php");
    $f = new Func();
    $att = new Attendance();
    $dict = array();
    $attendanceDate = $_POST['date'];
    $campus = $_POST['campus'];
    $label1 = $_POST['label1'];
    $label2 = $_POST['label2'];
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
            
            $defaultServiceOptions = $att->getDefaultServiceOptions();
            if(isset($_SESSION['campus']) && $_SESSION['campus'] >= 0) {
                $dict['campus'] = $_SESSION['campus'];
                $campus = $_SESSION['campus'];
            } else {
                $campus = $defaultServiceOptions['default_campus'];
            }
            
            if(isset($_SESSION['label1']) && $_SESSION['label1'] >= 0) {
                $dict['label1'] = $_SESSION['label1'];
                $label1 = $_SESSION['label1'];
            } else {
                $label1 = $defaultServiceOptions['default_first_service_label'];
            }
            
            if(isset($_SESSION['label2']) && $_SESSION['label2'] >= 0) {
                $dict['label2'] = $_SESSION['label2'];
                $label2 = $_SESSION['label2'];
            } else {
                $label2 = $defaultServiceOptions['default_second_service_label'];
            }
        } else {
            $_SESSION['attendance_dt'] = $attendanceDate;
            $_SESSION['attendance_active'] = $_POST['active'];
            $_SESSION['attendance_adults'] = $_POST['adult'];
            $_SESSION['campus'] = $campus;
            $_SESSION['label1'] = $label1;
            $_SESSION['label2'] = $label2;
        }
        try {
            $dict['people'] = $att->getAttendance($attendanceDate, $active, $adult, $campus, $label1, $label2);
            $dict['totals'] = $att->getAttendanceTotals($attendanceDate, $adult, $campus, $label1, $label2);
            $dict['visitors1'] = $att->getVisitorCount($attendanceDate, $adult, $campus, $label1);
            if($label2)
                $dict['visitors2'] = $att->getVisitorCount($attendanceDate, $adult, $campus, $label2);
            $dict['success'] = TRUE;
        } catch (Exception $e) {
            $f->logMessage($e->getMessage());
            $dict['success'] = FALSE;
        }
    } else {
        $dict['error'] = 1;
    }
    echo json_encode($dict);
?>