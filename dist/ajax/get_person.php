<?php
    session_start();
    include("../utils/func.php");
	include("../utils/person.php");
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
			$p = Person::getPerson($id);
			if($p != NULL) {
				$_SESSION['scroll_to_id'] = $id;
			}
            $dict['person'] = $p;
			$dict['success'] = TRUE;
        } catch (Exception $e) {
            $dict['success'] = FALSE;
            $dict['errorMsg'] = $e;
        }
    } else {
        $dict['error'] = 1;
    }
    echo json_encode($dict);
?>
