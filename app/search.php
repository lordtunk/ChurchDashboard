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
	<!-- build:css styles/main.min.css -->
    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/jquery-ui.css">
    <link rel="stylesheet" href="styles/jquery.toastmessage.css">
    <link rel="stylesheet" href="styles/jquery.loadmask.css">
    <!-- endbuild -->
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <?php include("navbar.php"); ?>

    <div class="container">

      <h1 id="search-h">Person Search</h1>

      <div class="app-content app-form search-form">
		<label for="search-by">Search By:</label>
		<select id="search-by">
		</select>
		<br />
		<div id="search-by-address" class="address-panel" style="display: none;">
			<label for="street1">Street 1:</label>
			<input type="text" id="street1" />
			<br />
			<label for="street2">Street 2:</label>
			<input type="text" id="street2" />
			<br />
			<label for="city">City:</label>
			<input type="text" id="city" />
			<br />
			<label for="state">State:</label>
			<select id="state"></select>
			<br />
			<label for="zip">Zip Code:</label>
			<input type="text" id="zip" title="5 digit Zip Code" />
		</div>
		<div id="search-by-name">
			<label for="search-name">Name:</label>
			<input type="text" id="search-name" />
		</div>
        <button id="search" type="button" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
        <div id="search-container">
          <div class="search-table-container" id="search-table-container">
            <table class="table table-responsive table-striped search-table" id="search-table">
              <thead>
                  <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Address</th>
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
    <!-- build:js scripts/main.min.js -->
    <script src="jquery/jquery-1.11.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="scripts/error.js"></script>
    <script src="jquery/jquery-ui.min.js"></script>
    <script src="jquery/jquery.loadmask.min.js"></script>
    <script src="jquery/jquery.toastmessage.js"></script>
    <!-- endbuild -->
	<script type="text/javascript">
		var el = $('#navbar a[id=search-nav]');
		el.attr('href', '#');
		el.parent().addClass('active');
	</script>
    <script src="scripts/login.js"></script>
    <script src="scripts/search.js"></script>
  </body>
</html>
