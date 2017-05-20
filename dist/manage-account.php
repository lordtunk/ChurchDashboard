<?php
	session_start();
	include("utils/func.php");
	include("utils/user.php");
	$f = new Func();

	if($f->doRedirect($_SESSION)) {
		header("Location: ".$f->getLoginUrl());
		die();
	}
  
	$success = TRUE;
	try {
		$u = User::getUser($_SESSION['user_id'], $f);
	} catch (Exception $e) {
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

      <h1 id="manage-account-h">Manage Account</h1>

      <div class="app-content app-form manage-account-form">
        <label for="username">Username:</label>
        <input type="text" id="username"/><br />
		<label for="homepage">Homepage:</label>
        <select id="homepage">
			<option value="index.php" selected>Dashboard</option>
			<option value="attendance.php">Attendance</option>
			<option value="follow-ups.php">Follow Ups</option>
			<option value="reports.php">Reports</option>
		</select><br />
		
		<div class="row">
			<div class="col-sm-7 change-password-panel">
				<div class="panel panel-default">
					<div class="panel-heading">
					  <h3 class="panel-title">Change Password</h3>
					</div>
					<div class="panel-body">
						<label for="password">Old Password:</label>
						<input type="password" id="password"/><br />
						<label for="new-password">New Password:</label>
						<input type="password" id="new-password"/><br />
						<label for="confirm-password">Confirm Password:</label>
						<input type="password" id="confirm-password"/>
					</div>
				</div>
			</div>
		</div>
		
        <div class="form-bar background color--gray-keyline">
			<div class="spacer"></div>
			<button id="update" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>  <span class="btn-text">Save</span>
			</button>
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
	echo "var user=".json_encode($u).";";
?>		
	</script>
    <script src="scripts/login.js"></script>
    <script src="scripts/manage-account.js"></script>
  </body>
</html>
