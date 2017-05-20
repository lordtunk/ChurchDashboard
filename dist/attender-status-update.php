<?php
  session_start();
  include("utils/func.php");
  $f = new Func();
  
  if($f->doRedirect($_SESSION)) {
	header("Location: ".$f->getLoginUrl());
    die();
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

      <h1 id="attender-status-h">Attender Status Update</h1>

      <div class="app-content app-form attender-status-form">
        <div id="attender-status-container">
          <div class="attender-status-table-container" id="attender-status-table-container">
            <table class="table table-responsive table-striped attender-status-table" id="attender-status-table">
              <thead>
                  <tr>
					  <th></th>
                      <th>Name</th>
                      <th>Status</th>
                  </tr>
              </thead>
              <tbody>
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
	<script type="text/javascript">

	</script>
    <script src="scripts/login.js"></script>
    <script src="scripts/attender-status-update.js"></script>
  </body>
</html>
