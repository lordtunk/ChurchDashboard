<?php
	session_start();
	include("utils/func.php");
	include("utils/user.php");
	$f = new Func();
	$u = new User($f);
  
	if($f->doRedirect($_SESSION)) {
		header("Location: ".$f->getLoginUrl());
		die();
	}

	$success = TRUE;
	$user = FALSE;
	$canPerformAdminFuncs = TRUE;
	try {
		$userId = $_SESSION['user_id'];
		$currentUser = $u->getUserPermissions($userId);
		$isUserAdmin = $currentUser['is_user_admin'] ? TRUE : FALSE;
		$isSiteAdmin = $currentUser['is_site_admin'] ? TRUE : FALSE;
		if(!$isUserAdmin && !$isSiteAdmin) {
		  header("Location: index.php");
		  die();
		} else if($isUserAdmin && !$isSiteAdmin) {
			$deleteUserPermissions = $u->getUserPermissions($_GET['id']);
			$deleteIsSiteAdmin = $deleteUserPermissions['is_site_admin'] ? TRUE : FALSE;
			if($deleteIsSiteAdmin) {
				$canPerformAdminFuncs = FALSE;
			}
		}
		if(isset($_GET['id'])) {
			$user = User::getUser($_GET['id'], $f);
			if($user['id'] == $userId) {
				$canPerformAdminFuncs = FALSE;
			}
		} else {
			$canPerformAdminFuncs = FALSE;
		}
		
    } catch (Exception $e) {
		$canPerformAdminFuncs = FALSE;
		$success = FALSE;
		$f->logMessage($e->getMessage());
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<?php include("head.php"); ?>
	<link rel="stylesheet" href="styles/main.min.css">
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <?php include("navbar.php"); ?>

    <div class="container">

	  <?php if($user == FALSE) { ?>
	  <h1 id="manage-user-h">Create User</h1>
	  <?php } else { ?>
	  <h1 id="manage-user-h">Manage User</h1>
	  <?php } ?>
      <div class="app-content app-form manage-user-form">
		<label for="username">Username:</label>
		<input type="text" id="username" />
		<br />
		<?php if($user == FALSE) { ?>
		<label for="password">Password:</label>
		<input type="password" id="password" />
		<br />
		<label for="confirm-password">Confirm Password:</label>
		<input type="password" id="confirm-password" />
		<br />
		<?php } ?>
		<label for="is-user-admin">User Admin?</label>
		<input type="checkbox" id="is-user-admin" />
		<?php if($isSiteAdmin) { ?>
		<br />
		<label for="is-site-admin">Site Admin?</label>
		<input type="checkbox" id="is-site-admin" />
		<p id="reset-password-text"></p>
		<?php } ?>
		<div class="form-bar background color--gray-keyline">
			<?php if($canPerformAdminFuncs) { ?>
			<button id="reset-password" type="button" class="btn btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>  <span class="btn-text">Reset Password</span></button>
			<?php } ?>
			<div class="spacer"></div>
			<button id="save" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span><span class="btn-text">Save</span></button>
			<?php if($canPerformAdminFuncs) { ?>
			<button id="delete" type="button" class="btn btn-danger"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>  <span class="btn-text">Delete</span></button>
			<?php } ?>
		</div>
      </div>

    </div><!-- /.container -->

    <div id="error-container" style="display: none;">
      <div id="error-msg"></div>
      <button id="email-error" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> <span class="btn-text">Email Error</span></button>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="scripts/main.min.js"></script>
	<script type="text/javascript">
<?php
	if($success) {
		echo "var usr = ".json_encode($user).";";
	} else {
		echo "$().toastmessage('showErrorToast', 'Error loading user');";
	}
?>
	</script>
    <script src="scripts/login.js"></script>
    <script src="scripts/manage-user.js"></script>
  </body>
</html>
