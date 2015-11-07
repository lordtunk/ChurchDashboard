<?php
    session_start();
    include("func.php");
    $params = isset($_POST['params']) ? json_decode($_POST['params']) : null;
    $email = $_POST['email'];
    $f = new Func();
    $dict = array();

    function isDate($txtDate, $allowBlank) {
        if($txtDate == "")
            return $allowBlank;
        $dt = date_parse($txtDate);
        if($dt === FALSE) return FALSE;
            return checkdate($dt['month'], $dt['day'], $dt['year']);
    }

    if($params != NULL && (!isDate($params->fromDate, true) || !isDate($params->toDate, true))) {
        $dict['success'] = FALSE;
        $f->logMessage('Invalid report parameters');
    } else {
        if(!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
            $dict['success'] = FALSE;
            $dict['error'] = 1;
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
            if(!$dict['success'])
                $dict['error'] = 1;
        }
    }
    if($dict['success'] == TRUE) {
        try {
            $results = $f->getFollowUpReport($params);
            foreach($results as $key => $row) {
                
            }
            $dict['success'] = true;
        } catch (Exception $e) {
          $dict['success'] = false;
          $f->logMessage($e);
        }
    }

    echo json_encode($dict);
?>
