<?php
    session_start();
    include("../utils/func.php");
	include("../utils/user.php");
    $f = new Func();
    $user = json_decode($_POST['user']);
    
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
			$f->useTransaction = FALSE;
            $f->beginTransaction();
			
			$user->id = $_SESSION['user_id'];
            User::updateUser($user, $f);
			
			$f->commit();
            $dict['success'] = TRUE;
        } catch (Exception $e) {
          $dict['success'] = FALSE;
          $dict['exception']= $e->getMessage();
          $f->rollback();
        }
    }
    echo json_encode($dict);
?>