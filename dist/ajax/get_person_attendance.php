<?php
    session_start();
    include("func.php");
    include("attendance.php");
    $f = new Func();
    $att = new Attendance();
    $id = $_GET['id'];
    $dict = array();
    $campus = $_GET['campus'];
    $label1 = $_GET['label1'];
    $label2 = $_GET['label2'];
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
            $dict['person'] = $att->getAttendanceByPersonId($id, $label1, $label2, $campus);
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