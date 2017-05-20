<?php
	session_start();
	include("utils/func.php");
	include("utils/attendance.php");
	$f = new Func();
	$att = new Attendance();

	if($f->doRedirect($_SESSION)) {
		header("Location: ".$f->getLoginUrl());
		die();
	}
	
	$success = TRUE;
	try {
	  $query = "SELECT
				  DATE_FORMAT(MAX(s.service_dt),'%m/%d/%Y') Last_Attendance_Dt,
				  DATE_FORMAT(MIN(s.service_dt),'%m/%d/%Y') First_Attendance_Dt
				FROM
				  Services s";
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
        
      <h1 id="reports">Reports</h1>

      <div class="app-content app-form reports-form">
        <div id="first-last-dates">
            <label>First Service:</label>
            <span id="first-date"><?php echo $results[0]['First_Attendance_Dt'] ?></span>
            <br />
            <label>Last Service:</label>
            <span id="last-date"><?php echo $results[0]['Last_Attendance_Dt'] ?></span>
        </div>
        <div id="report-type-container" style="display: block;">
          <label for="report-type">Report Type:</label>
          <select id="report-type">
            <option value="1">Attendance By Date</option>
            <option value="2">Attendance By Person</option>
            <option value="3">Missing In Action</option>
            <option value="4">Follow Up</option>
            <option value="5">People By Attender Status</option>
          </select>
        </div>
        <h4 id="communication-card-header" style="display: none;">Communication Card Received</h4>
        <div id="from-to-dates">
          <label for="from-date">From Date:</label>
          <input type="text" id="from-date" class="date-field" /><br />
          <label for="to-date">To Date:</label>
          <input type="text" id="to-date" class="date-field" />
        </div>
        <div id="service-options" style="display: inline-block;">
          <div class="service-label-container">
            <label for="service-label-1">First Service:</label>
            <select id="service-label-1">
            </select>
          </div>
          <div class="service-label-container">
            <label for="service-label-2">Second Service:</label>
            <select id="service-label-2">
            </select>
          </div>
          <div>
            <label for="campus">Campus:</label>
            <select id="campus">
            </select>
          </div>
		  <div id="missing-for-container" style="display: none;">
			<label for="missing-for">Missing for # of Sundays:</label>
			<input type="number" id="missing-for" />
		  </div>
        </div>
        <div id="follow-up-options-spacer" style="display: none;"></div>
        <div id="follow-up-options" style="display: none;">
          <h4 style="padding-top: 0;">Include Only:</h4>
          <input type="checkbox" id="ty-card-not-sent"/>
          <label for="ty-card-not-sent">Thank you card not sent</label>
          <input type="checkbox" id="not-visited"/>
          <label for="not-visited">Not yet visited</label><br />
          <input type="checkbox" id="active" checked/>
          <label for="active">Active</label>
          <br />
          <h4 style="padding-top: 0;">Include All:</h4>
          <div id="include-all-checkboxes">
            <input type="checkbox" id="first-time-visitor"/>
            <label for="first-time-visitor">First time visitor</label>
            <input type="checkbox" id="signed-up-for-baptism"/>
            <label for="signed-up-for-baptism">Signed up for baptism</label><br />
            <input type="checkbox" id="interested-in-gkids"/>
            <label for="interested-in-gkids">Interested in gKids</label>
            <input type="checkbox" id="interested-in-next"/>
            <label for="interested-in-next">Interested in Next</label><br />
            <input type="checkbox" id="interested-in-ggroups"/>
            <label for="interested-in-ggroups">Interested in gGroups</label>
            <input type="checkbox" id="interested-in-gteams"/>
            <label for="interested-in-gteams">Interested in gTeams</label><br />
            <input type="checkbox" id="interested-in-joining"/>
            <label for="interested-in-joining">Interested in joining GC</label><br />
			<input type="checkbox" id="interested-in-growth" />
			<label for="interested-in-growth">Interested in growth opportunities</label>
            <input type="checkbox" id="would-like-visit"/>
            <label for="would-like-visit">Would like a visit from a GC Pastor</label><br />
            <input type="checkbox" id="commitment-christ"/>
            <label for="commitment-christ">Committing life to Christ</label>
            <input type="checkbox" id="recommitment-christ"/>
            <label for="recommitment-christ">Recommitting life to Christ</label><br />
            <input type="checkbox" id="commitment-tithe"/>
            <label for="commitment-tithe">Committing to tithe</label>
            <input type="checkbox" id="commitment-ministry"/>
            <label for="commitment-ministry">Committing to serving in ministry</label><br />
            <input type="checkbox" id="baptized"/>
            <label for="baptized">Baptized</label>
            <input type="checkbox" id="no-agent"/>
            <label for="no-agent">Not assigned an agent</label>
          </div>
          <input type="checkbox" id="toggle-check"/>
          <label for="toggle-check">Select/Deselect All</label>
        </div>
        <button id="go-arrow" class="btn btn-default">Run <span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span></button>
        <br />
        <div id="attendance-by-date-container" style="display: none;">
          <div class="attendance-table-container" id="attendance-by-date-table-container">
            <table class="table table-responsive table-striped attendance-table" id="attendance-date-table">
              <thead>
                  <tr>
                    <th>Date</th>
                    <th>Total</th>
                    <th class="service-header first-service-header">1st Service</th>
                    <th class="service-header second-service-header">2nd Service</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
          <div class="table-bar">
            <div class="top-bottom-links">
                <div>
                  <a href="javascript:void(0);" id="date-top">Top</a>
                  <a href="javascript:void(0);" id="date-bottom">Bottom</a>
                </div>
                <div class="clear"></div>
            </div>
          </div>
          <div class="attendance-table-container" id="attendance-by-date-aggregates-table-container">
            <table class="table table-responsive table-striped attendance-table" id="attendance-date-aggregates-table">
              <thead>
                  <tr>
                    <th>Aggregate</th>
                    <th>Total</th>
                    <th class="service-header first-service-header">1st Service</th>
                    <th class="service-header second-service-header">2nd Service</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
        <div id="attendance-by-person-container" style="display: none;">
          <div class="attendance-table-container" id="attendance-by-person-table-container">
            <table class="table table-responsive table-striped attendance-by-person-table" id="adult-attendance-table">
              <thead>
                  <tr>
                      <th>Name</th>
                      <th class="service-header first-service-header">1st Service</th>
                      <th class="service-header second-service-header">2nd Service</th>
                      <th>Total</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
          <div class="table-bar">
            <div class="top-bottom-links">
                <div>
                  <a href="javascript:void(0);" id="person-top">Top</a>
                  <a href="javascript:void(0);" id="person-bottom">Bottom</a>
                </div>
                <div class="clear"></div>
            </div>
          </div>
        </div>
        <div id="attendance-by-mia-container" style="display: none;">
          <div class="attendance-table-container" id="attendance-by-mia-table-container">
            <table class="table table-responsive table-striped attendance-by-person-table" id="mia-attendance-table">
              <thead>
                  <tr>
                      <th>Name</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
          <div class="table-bar">
            <div class="top-bottom-links">
                <div>
                  <a href="javascript:void(0);" id="mia-top">Top</a>
                  <a href="javascript:void(0);" id="mia-bottom">Bottom</a>
                </div>
                <div class="clear"></div>
            </div>
          </div>
        </div>
        <div id="follow-up-container" style="display: none;">
          <div class="attendance-table-container" id="follow-up-table-container">
            <table class="table table-responsive table-striped follow-up-table" id="follow-up-table">
              <thead>
                  <tr>
                      <th>Name</th>
                      <th>Visited</th>
                      <th>Phone Number</th>
                      <th>Thank You<br />Card Sent</th>
                      <th>Communication Card<br />Received</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
          <div class="table-bar">
            <div class="top-bottom-links">
                <div>
                  <a href="javascript:void(0);" id="follow-up-top">Top</a>
                  <a href="javascript:void(0);" id="follow-up-bottom">Bottom</a>
                </div>
                <div class="clear"></div>
            </div>
          </div>
          <label id="email-label" for="email">Email: </label>
          <input type="email" id="email" />
          <button id="email-summary" class="btn btn-primary"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> Email Summary</button>
          <div class="clear"></div>
        </div>
        <div id="people-by-attender-status-container" style="display: none;">
          <div class="attendance-table-container" id="people-by-attender-status-table-container">
            <table class="table table-responsive table-striped attendance-by-person-table" id="people-by-attender-status-table">
              <thead>
                  <tr>
                      <th>Name</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
          <div class="table-bar">
            <div class="top-bottom-links">
                <div>
                  <a href="javascript:void(0);" id="attender-status-top">Top</a>
                  <a href="javascript:void(0);" id="attender-status-bottom">Bottom</a>
                </div>
                <div class="clear"></div>
            </div>
          </div>
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
		try {
		  echo "var options = ".json_encode($att->getServiceOptions()).";";
		} catch (Exception $e) {
		  $f->logMessage($e->getMessage());
		  echo "$().toastmessage('showErrorToast', 'Error loading service options');";
		}
	} else {
		echo "$().toastmessage('showErrorToast', 'Error loading settings');";
	}
?>
	var el = $('#navbar a[id=reports-nav]');
	el.attr('href', '#');
	el.parent().addClass('active');
	</script>
    <script src="scripts/login.js"></script>
    <script src="scripts/reports.js"></script>
  </body>
</html>
