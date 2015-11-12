<?php
    session_start();
    include("func.php");
    $f = new Func();
    $startingPointEmails = $_POST['startingPointEmails'];
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
        $dict['success'] = FALSE;
        try {
            
            $query = "
                UPDATE Settings SET 
                    starting_point_emails = :starting_point_emails";
            $results = $f->executeAndReturnResult($query, 
                array(":starting_point_emails"=>$startingPointEmails));
            }
            $dict['success'] = TRUE;
        } catch (Exception $e) {
          $dict['success'] = FALSE;
          $dict['exception']= $e->getMessage();
          $f->rollback();
        }
    } else {
        $dict['error'] = 1;
    }
    echo json_encode($dict);
?>