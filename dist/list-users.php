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
	try {
		$currentUser = $u->getUserPermissions($_SESSION['user_id']);
		$isUserAdmin = $currentUser['is_user_admin'] ? TRUE : FALSE;
		$isSiteAdmin = $currentUser['is_site_admin'] ? TRUE : FALSE;
		if($isUserAdmin == FALSE && $isSiteAdmin == FALSE) {
		  header("Location: index.php");
		  die();
		}
		$users = User::getUsers($f);
		
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

      <h1 id="list-users-h">Users</h1>

      <div class="app-content app-form list-users-form">
		<div id="list-users-container">
          <div class="list-users-table-container" id="list-users-table-container">
            <table class="table table-responsive table-striped list-users-table" id="list-users-table">
              <thead>
                  <tr>
                      <th>Username</th>
                      <th>Site Admin?</th>
                      <th>User Admin?</th>
                  </tr>
              </thead>
              <tbody>
			  <?php for($i=0; $i<count($users); $i++) { ?>
				<tr>
					<td><a href="manage-user.php?id=<?php echo $users[$i]["id"]; ?>"><?php echo $users[$i]["username"]; ?></a></td>
					<td><?php echo $users[$i]["is_site_admin"] ? "Yes" : "No"; ?></td>
					<td><?php echo $users[$i]["is_user_admin"] ? "Yes" : "No"; ?></td>
				</tr>
			  <?php } ?>
              </tbody>
            </table>
          </div>
          <div class="navigation-links">
            <a href="javascript:void(0);" id="mia-top">Top</a>
            <a href="javascript:void(0);" id="mia-bottom">Bottom</a>
          </div>
          <div class="clear"></div>
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
    <script src="scripts/login.js"></script>
  </body>
</html>
