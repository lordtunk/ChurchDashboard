<?php
    session_start();
    include("../utils/func.php");
	include("../utils/user.php");
	$f = new Func();
	$u = new User($f);
    $userId = $_POST['id'];
    $dict = array();
    $dict['success'] = !$f->doRedirect($_SESSION);
	if($dict['success'] == TRUE) {
		$userPermissions = $u->getUserPermissions($_SESSION['user_id']);
		$isSiteAdmin = $userPermissions['is_site_admin'] ? TRUE : FALSE;
		if($isSiteAdmin == FALSE) {
			$dict['success'] = FALSE;
			$dict['error'] = 2;
		}
	} else {
		$dict['error'] = 1;
	}
    if($dict['success'] == TRUE) {
        $dict['success'] = FALSE;
        try {
            $dict['password'] = User::resetPassword($userId, $f);
            $dict['success'] = TRUE;
        } catch (Exception $e) {
          $dict['success'] = FALSE;
          $dict['exception']= $e->getMessage();
          $f->rollback();
        }
    }
    echo json_encode($dict);
?>