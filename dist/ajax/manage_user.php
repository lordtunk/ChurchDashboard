<?php
    session_start();
    include("../utils/func.php");
	include("../utils/user.php");
	$f = new Func();
	$u = new User($f);
    $user = json_decode($_POST['user']);
    $dict = array();
	
	$dict['success'] = !$f->doRedirect($_SESSION);
	if($dict['success'] == TRUE) {
		$userPermissions = $u->getUserPermissions($_SESSION['user_id']);
		$isUserAdmin = $userPermissions['is_user_admin'] ? TRUE : FALSE;
		$isSiteAdmin = $userPermissions['is_site_admin'] ? TRUE : FALSE;
		if($isUserAdmin == FALSE && $isSiteAdmin == FALSE) {
			$dict['success'] = FALSE;
			$dict['error'] = 2;
		}
	} else {
		$dict['error'] = 1;
	}
	
    if($dict['success'] == TRUE) {
        $dict['success'] = FALSE;
        try {
			if(isset($user->id)) {
				User::updateUser($user, $f);
			} else {
				User::createUser($user, $f);
			}
            $dict['success'] = TRUE;
        } catch (Exception $e) {
          $dict['success'] = FALSE;
          $dict['exception']= $e->getMessage();
        }
    }
    echo json_encode($dict);
?>