<?php
    session_start();
    include("../utils/func.php");
    $f = new Func();
    $startingPointEmails = $_POST['startingPointEmails'];
    $campuses = $_POST['campuses'];
    $serviceLabels = $_POST['serviceLabels'];
    $defaultCampus = $_POST['defaultCampus'];
    $defaultFirstServiceLabel = $_POST['defaultFirstServiceLabel'];
    $defaultSecondServiceLabel = $_POST['defaultSecondServiceLabel'];
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
        if($dict['success'] == FALSE) {
            $dict['error'] = 1;
        } else {
            // Must be me to access this page
            if($user_id != "1") {
                $dict['success'] = FALSE;
                $dict['error'] = 2;
            }
        }
    }
    if($dict['success'] == TRUE) {
        $dict['success'] = FALSE;
        try {
            
            $query = "
                UPDATE Settings SET 
                    starting_point_emails = :starting_point_emails,
                    campuses = :campuses,
                    service_labels = :service_labels,
                    default_campus = :default_campus,
                    default_first_service_label = :default_first_service_label,
                    default_second_service_label = :default_second_service_label";
            $results = $f->executeAndReturnResult($query, 
                array(":starting_point_emails"=>$startingPointEmails,
                        ":campuses"=>$campuses,
                        ":service_labels"=>$serviceLabels,
                        ":default_campus"=>$defaultCampus,
                        ":default_first_service_label"=>$defaultFirstServiceLabel,
                        ":default_second_service_label"=>$defaultSecondServiceLabel));
            
            $dict['success'] = TRUE;
        } catch (Exception $e) {
          $dict['success'] = FALSE;
          $dict['exception']= $e->getMessage();
          $f->rollback();
        }
    }
    echo json_encode($dict);
?>