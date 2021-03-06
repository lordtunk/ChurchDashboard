<?php
	session_start();
	include("utils/func.php");
	include("utils/person.php");
	include("utils/attendance.php");
	$f = new Func();
	$att = new Attendance($f);
	$id = $_GET['id'];

	if($f->doRedirect($_SESSION)) {
		header("Location: ".$f->getLoginUrl());
		die();
	}
	
	$success = TRUE;
	try {
	  $query = "SELECT
                  p.id,
                  p.first_name,
                  p.last_name,
                  p.description
                FROM
                  People p
                WHERE
                  p.visitor=true";
          $visitors = $f->fetchAndExecute($query);
          
          $campuses = $att->getSetting('campuses');
	  $p = Person::getPerson($id, $f);
	  if($p != NULL) {
		$_SESSION['scroll_to_id'] = $id;
	  }
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

      <h1 id="manage-person">Manage Person</h1>

        <form action="javascript:void(0)" class="app-content app-form view-person">
            <label for="first-name">First Name:</label>
            <input type="text" id="first-name" />
            <br />
            <label for="last-name">Last Name:</label>
            <input type="text" id="last-name" />
            <br />
            <label for="description">Description:</label>
            <input type="text" id="description" />
            <br />
            <label>First Recorded Visit:</label>
            <div id="first-recorded-visit"></div>
            <br />
            <label>Last Recorded Visit:</label>
            <div id="last-recorded-visit"></div>
            <br />
            <label for="attender-status">Attender Status:</label>
            <select id="attender-status"></select>
            <br />
            <div class="form-section checkbox-form-section">
                <div class="check">
                    <input type="checkbox" id="adult" />
                    <label for="adult">Adult?</label>
                </div>
                <div class="check">
                    <input type="checkbox" id="baptized" />
                    <label for="baptized">Baptized?</label>
                </div>
                <div class="check">
                    <input type="checkbox" id="active" />
                    <label for="active">Active?</label>
                </div>
                <div class="check">
                    <input type="checkbox" id="saved" />
                    <label for="saved">Saved?</label>
                </div>
<!--
                <div class="check">
                    <input type="checkbox" id="member" />
                    <label for="member">Member?</label>
                </div>
-->
                <div class="check">
                    <input type="checkbox" id="visitor" />
                    <label for="visitor">Performs Visits?</label>
                </div>
                <div class="check">
                    <input type="checkbox" id="assigned-agent" />
                    <label for="assigned-agent">Assigned an Agent?</label>
                </div>
                <div class="check">
                    <input type="checkbox" id="starting-point-notified" disabled/>
                    <label id="starting-point-notified-label" for="starting-point-notified">Starting Point notification sent?</label>
                </div>
            </div>
            <br />
            <div class="row">
				<div class="col-sm-4 checkbox-panel campuses">
                    <div class="panel panel-default">
                    	<div class="panel-heading">
                    	  <h3 class="panel-title">Campuses</h3>
                    	</div>
                    	<div class="panel-body">
                    		<?php
								foreach($campuses as $id => $name) {
									echo "<div class='check'><input type='checkbox' campusid='$id' id='campus-$id' /><label for='campus-$id'>$name</label></div>";
								}
							?>
                    	</div>
                    </div>
                </div>
            </div>
			<div class="row">
				<div class="col-sm-8 contact-panel">
                    <div class="panel panel-default">
                    	<div class="panel-heading">
                    	  <h3 class="panel-title">Contact</h3>
                    	</div>
                    	<div class="panel-body">
                    		<label for="email">Email:</label>
							<input type="email" id="email" />
							<br />
							<div class="phone-panel">
								<label for="primary-phone">Primary Phone:</label>
								<input type="tel" id="primary-phone" />
								<select id="primary-phone-type">
									<option value="1">Home</option>
									<option value="2" selected>Mobile</option>
									<option value="3">Work</option>
								</select>
							</div>
							<div class="phone-panel">
								<label for="secondary-phone">Secondary Phone:</label>
								<input type="tel" id="secondary-phone" />
								<select id="secondary-phone-type">
									<option value="1">Home</option>
									<option value="2" selected>Mobile</option>
									<option value="3">Work</option>
								</select>
							</div>
                    	</div>
                    </div>
                </div>
			</div>
			<div class="row">
				<div class="col-sm-5 address-panel">
                    <div class="panel panel-default">
                    	<div class="panel-heading">
                    	  <h3 class="panel-title">Address</h3>
                    	</div>
                    	<div class="panel-body">
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
							<br />
							<button id="copy-address-to-spouse" type="button"  class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span> Copy to Spouse</button>
                    	</div>
                    </div>
                </div>
				<div class="col-sm-4" id="map-panel">
				</div>
            </div>
			<div class="row">
				<div class="col-sm-8 commitments-panel">
                    <div class="panel panel-default">
                    	<div class="panel-heading">
                    	  <h3 class="panel-title">Committing To...</h3>
                    	</div>
                    	<div class="panel-body">
                    		<input type="checkbox" id="commitment-christ" disabled />
							<label for="commitment-christ">Committing life to Christ</label>
							<br />
							<input type="checkbox" id="recommitment-christ" disabled />
							<label for="recommitment-christ">Recommitting life to Christ</label>
							<br />
							<input type="checkbox" id="commitment-tithe" disabled />
							<label for="commitment-tithe">Committing to tithe</label>
							<br />
							<input type="checkbox" id="commitment-ministry" disabled />
							<label for="commitment-ministry">Committing to serving in ministry at Guide Church Baptist</label>
							<br />
							<input type="checkbox" id="commitment-baptism" disabled />
							<label for="commitment-baptism">Sign up for the next baptism</label>
                    	</div>
                    </div>
                </div>
            </div>
			<div class="row">
				<div class="col-sm-8 interested-panel">
                    <div class="panel panel-default">
                    	<div class="panel-heading">
                    	  <h3 class="panel-title">Interested In...</h3>
                    	</div>
                    	<div class="panel-body">
                    		<input type="checkbox" id="info-next" disabled />
							<label for="info-next">Interested in attending Next</label>
							<br />
							<input type="checkbox" id="info-gkids" disabled />
							<label for="info-gkids">Get more information on serving in gKids (nursery to age 12)</label>
							<br />
							<input type="checkbox" id="info-ggroups" disabled />
							<label for="info-ggroups">Get more information on joining or hosting a gGroup</label>
							<br />
							<input type="checkbox" id="info-gteams" disabled />
							<label for="info-gteams">Get more information about gTeams</label>
							<br />
							<input type="checkbox" id="info-member" disabled />
							<label for="info-member">Get more information about joining Guide Church Baptist</label>
							<br />
							<input type="checkbox" id="info-visit" disabled />
							<label for="info-visit">I would like a visit from a Guide Church Pastor</label>
							<br />
							<input type="checkbox" id="info-growth" disabled />
							<label for="info-growth">Learn about growth opportunities</label>
                    	</div>
                    </div>
                </div>
            </div>

            <div class="form-bar background color--gray-keyline">
                <div class="spacer"></div>
                <button id="update" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>  <span class="btn-text">Save</span>
                </button>
                <button id="delete" type="button" class="btn btn-danger"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>  <span class="btn-text">Delete</span>
                </button>
                <button id="cancel" type="button" class="btn btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>  <span class="btn-text">Refresh</span>
                </button>
            </div>
        </form>

        <h3>Follow Ups</h3>
        <div id="follow-up-table-container">
            <table class="table table-responsive table-striped" id="follow-up-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Date</th>
                        <th>By</th>
                        <th>Comments</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <button id="add-follow-up" type="button" class="btn btn-default first-button"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add Follow Up</button>

        <div id="relationship-table-container">
            <h3>Relationships</h3>
            <table class="table table-responsive table-striped" id="relationship-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <button id="add-relationship" type="button" class="btn btn-default first-button"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add Relationship</button>
        </div>

    </div><!-- /.container -->

    <div class="dialog-form app-form manage-person-follow-up-form" title="Add Follow Up">
        <div class="dialog-form-inner">
            <input type="hidden" id="follow-up-id" value="-1" />
            <div id="add-to-spouse-container-follow-up">
                <label for="add-to-spouse-follow-up">Add to spouse:</label>
                <input type="checkbox" id="add-to-spouse-follow-up" />
            </div>
            <label for="follow-up-type">Type:</label>
            <select id="follow-up-type">
            </select>
            <br />
            <label for="follow-up-date">Date:</label>
            <input type="text" id="follow-up-date" class="date-field" />
            <label for="manage-unknown-date">Unknown</label>
            <input type="checkbox" id="manage-unknown-date" />
            <br />
            <label for="follow-up-by">By:</label>
            <div id="follow-up-visitors">
            </div>
            <br />
            <div id="follow-up-frequency-container">
                <label for="follow-up-frequency">Frequency:</label>
                <select id="follow-up-frequency">
                </select>
            </div>
            <div class="communication-card-options" style="display: none;">
				<div class="row">
				<div class="col-sm-11 committing-panel">
                    <div class="panel panel-default">
                    	<div class="panel-heading">
                    	  <h3 class="panel-title">Committing To...</h3>
                    	</div>
                    	<div class="panel-body">
                    		<input type="checkbox" id="follow-up-commitment-christ" />
							<label for="follow-up-commitment-christ">Committing life to Christ</label>
							<br />
							<input type="checkbox" id="follow-up-recommitment-christ" />
							<label for="follow-up-recommitment-christ">Recommitting life to Christ</label>
							<br />
							<input type="checkbox" id="follow-up-commitment-tithe" />
							<label for="follow-up-commitment-tithe">Committing to tithe</label>
							<br />
							<input type="checkbox" id="follow-up-commitment-ministry" />
							<label for="follow-up-commitment-ministry">Committing to serving in ministry at Guide Church Baptist</label>
							<br />
							<input type="checkbox" id="follow-up-commitment-baptism" />
							<label for="follow-up-commitment-baptism">Sign up for the next baptism</label>
                    	</div>
                    </div>
                </div>
            </div>
			<div class="row">
				<div class="col-sm-11 interested-panel">
                    <div class="panel panel-default">
                    	<div class="panel-heading">
                    	  <h3 class="panel-title">Interested In...</h3>
                    	</div>
                    	<div class="panel-body">
                    		<input type="checkbox" id="follow-up-info-next" />
							<label for="follow-up-info-next">Interested in attending Next</label>
							<br />
							<input type="checkbox" id="follow-up-info-gkids" />
							<label for="follow-up-info-gkids">Get more information on serving in gKids (nursery to age 12)</label>
							<br />
							<input type="checkbox" id="follow-up-info-ggroups" />
							<label for="follow-up-info-ggroups">Get more information on joining or hosting a gGroup</label>
							<br />
							<input type="checkbox" id="follow-up-info-gteams" />
							<label for="follow-up-info-gteams">Get more information about gTeams</label>
							<br />
							<input type="checkbox" id="follow-up-info-member" />
							<label for="follow-up-info-member">Get more information about joining Guide Church Baptist</label>
							<br />
							<input type="checkbox" id="follow-up-info-visit" />
							<label for="follow-up-info-visit">I would like a visit from a Guide Church Pastor</label>
							<br />
							<input type="checkbox" id="follow-up-info-growth" />
							<label for="follow-up-info-growth">Learn about growth opportunities</label>
                    	</div>
                    </div>
                </div>
            </div>
            </div>
            <label for="follow-up-comments">Comments:</label>
            <br />
            <textarea rows="4" cols="36" id="follow-up-comments"></textarea>
        </div>
        <div class="form-bar background color--gray-keyline">
            <!--Add and Clear-->
            <button id="add-clear" type="button" class="btn btn-primary first-button"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>/<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
            </button>
            <!--Add and Copy-->
            <button id="add-copy" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>/<span class="glyphicon glyphicon-copy" aria-hidden="true"></span>
            </button>
            <!--Add and Close-->
            <button id="add-close" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>/<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            </button>
            <div class="spacer"></div>
            <!--Close-->
            <button id="close" type="button" class="btn btn-default"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="dialog-form app-form manage-person-relationship-form" title="Add Relationship">
        <div class="dialog-form-inner">
            <input type="hidden" id="relationship-id" value="-1" />
            <div id="relationship-person-container">
                <div id="relationship-relation" relationid="-1">(Select a person)</div>
                <button id="select-person-btn" class="btn btn-xs btn-info">Select Person</button>
            </div>
            <div>is the <select id="relationship-type"></select> of <span id="relationship-person-name"></span></div>
            <div id="add-to-spouse-container-relationship">
                <label for="add-to-spouse-relationship">Add to spouse:</label>
                <input type="checkbox" id="add-to-spouse-relationship" />
            </div>
        </div>
        <div class="form-bar background color--gray-keyline">
            <!--Add and Close-->
            <button id="add-close-relationship" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>/<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            </button>
            <div class="spacer"></div>
            <!--Close-->
            <button id="close-relationship" type="button" class="btn btn-default"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="dialog-form app-form select-person-form" title="Select Person">
        <label for="search-name">Name</label>
        <input type="text" id="search-name" />
        <button id="search" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>
        </button>
        <div id="search-container">
            <div class="search-table-container" id="search-table-container">
                <table class="table table-responsive search-table" id="search-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="form-bar background color--gray-keyline">
            <button id="add-new-person" type="button" class="btn btn-primary first-button"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            </button>
            <div class="spacer"></div>
            <button id="close-select-person" type="button" class="btn btn-default"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            </button>
        </div>
    </div>

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
<?php
	if($success && count($visitors) > 0) {
		echo "var apiKey = '".$f->getGoogleApiKey()."';";
		echo "var visitors = ".json_encode($visitors).";
		var person = ".json_encode($p).";";
	} else {
		echo "$().toastmessage('showErrorToast', 'Error loading person');";
	}
?>
	</script>
    <script src="scripts/login.js"></script>
    <script src="scripts/manage-person.js"></script>
  </body>
</html>
