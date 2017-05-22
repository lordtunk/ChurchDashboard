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
		$isUserAdmin = $userPermissions['is_user_admin'] ? TRUE : FALSE;
		if(!$isSiteAdmin && !$isUserAdmin) {
			$dict['success'] = FALSE;
			$dict['error'] = 2;
		} else if($isUserAdmin && !$isSiteAdmin) {
			$deleteUserPermissions = $u->getUserPermissions($userId);
			$deleteIsSiteAdmin = $deleteUserPermissions['is_site_admin'] ? TRUE : FALSE
			if($deleteIsSiteAdmin) {
				$dict['success'] = FALSE;
				$dict['error'] = 3;
			}
		}
	} else {
		$dict['error'] = 1;
	}
	if($dict['success'] == TRUE) {
		$dict['success'] = FALSE;
		try {
			$f->useTransaction = FALSE;
			$f->beginTransaction();

			User::deleteUser($userId, $f);

			$f->commit();
			$dict['success'] = TRUE;
		} catch (Exception $e) {
			$dict['success'] = FALSE;
			$dict['msg']= $e->getMessage();
			$f->rollback();
		}
	}
	echo json_encode($dict);
?>