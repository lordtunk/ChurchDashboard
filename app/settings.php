<?php
  session_start();
  include("utils/func.php");
  $f = new Func();
  
  if($f->doRedirect($_SESSION)) {
	header("Location: ".$f->getLoginUrl());
    die();
  }
  // TODO: change this to be configurable
  if($_SESSION['user_id'] != "1") {
	  // Must be me to access this page
	  header("Location: attendance.php");
	  die();
  }
  
  $success = TRUE;
  try {
      $query = "SELECT
                  starting_point_emails,
                  campuses,
                  service_labels,
                  default_campus,
                  default_first_service_label,
                  default_second_service_label
                FROM
                  Settings";
      $results = $f->fetchAndExecute($query);
    } catch (Exception $e) {
	  $success = FALSE;
      $f->logMessage($e->getMessage());
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

      <h1 id="settings-h">Settings</h1>

      <div class="app-content app-form settings-form">
        <label for="starting-point-emails">Email(s) to send Starting Point notifications (comma-separated)</label><br />
        <div class="setting"><textarea type="text" id="starting-point-emails" ><?php echo $results[0]['starting_point_emails'] ?></textarea></div>
        <label for="campuses">Campuses (id|label format, comma-separated. Ex: 1|Main,2|South Hilliard)</label><br />
        <div class="setting"><textarea type="text" id="campuses" ><?php echo $results[0]['campuses'] ?></textarea></div>
        
        <label for="service-labels">Service Labels (id|label format, comma-separated. Ex: 1|9:00 AM,2|10:30 AM)</label><br />
        <div class="setting"><textarea type="text" id="service-labels" ><?php echo $results[0]['service_labels'] ?></textarea></div>
        <label for="default-campus">Default Campus (enter id of campus)</label><br />
        <input type="number" class="setting" id="default-campus" min="1" value="<?php echo $results[0]['default_campus'] ?>" /><br />
        <label for="default-service-label">Default First Service Label (enter id of service label)</label><br />
        <input type="number" class="setting" id="default-service-label-first" min="1" value="<?php echo $results[0]['default_first_service_label'] ?>" /><br />
        <label for="default-service-label">Default Second Service Label (enter id of service label)</label><br />
        <input type="number" class="setting" id="default-service-label-second" min="1" value="<?php echo $results[0]['default_second_service_label'] ?>" /><br />
        <br />
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
    <!-- build:js scripts/main.min.js -->
    <script src="jquery/jquery-1.11.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="scripts/error.js"></script>
    <script src="jquery/jquery-ui.min.js"></script>
    <script src="jquery/jquery.loadmask.min.js"></script>
    <script src="jquery/jquery.toastmessage.js"></script>
    <!-- endbuild -->
    <script src="scripts/login.js"></script>
    <script src="scripts/settings.js"></script>
	<script type="text/javascript">
<?php
	if(!$success)
		echo "$().toastmessage('showErrorToast', 'Error loading settings');";
?>
	</script>
  </body>
</html>
