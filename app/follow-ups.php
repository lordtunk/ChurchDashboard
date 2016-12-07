<?php
	session_start();
	include("utils/func.php");
	include("utils/follow_ups.php");
	$f = new Func();
	$followUps = new FollowUps($f);

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
	  $follow_ups = $followUps->getFollowUpsByDate(date("n/j/Y"));
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
        <h1 id="follow-ups">Follow Ups</h1>
		
        <div class="app-content app-form">
            <div class="follow-ups-form app-form">
                <h3 id="follow-ups-form-title">Add Follow Up</h3>
                <div class="follow-ups-form-inner">
                    <input type="hidden" id="follow-up-id" value="-1" />
                    <div id="follow-up-person-container">
                        <label for="follow-up-person">Person:</label>
                        <div id="follow-up-person" personid="-1">(Select a person)</div>
                        <button id="select-person-btn" class="btn btn-xs btn-info">Select Person</button>
                    </div>
					<label for="add-to-spouse" id="add-to-spouse-label" style="display: none;">Add to spouse:</label>
					<input type="checkbox" id="add-to-spouse"  style="display: none;" />
					<br />
                    <label for="follow-up-type">Type:</label>
                    <select id="follow-up-type">
                    </select>
                    <br />
                    <label for="follow-up-date">Date:</label>
                    <input type="text" id="follow-up-date" class="date-field" />
                    <div id="follow-up-unknown-date-container">
                        <label for="unknown-date">Unknown</label>
                        <input type="checkbox" id="unknown-date" />
                    </div>
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
									</div>
								</div>
							</div>
						</div>
					</div>
                    <label for="follow-up-comments" id="follow-up-comments-label">Comments:</label>
                    <br />
                    <textarea rows="4" cols="40" id="follow-up-comments"></textarea>
                </div>
                <div class="form-bar background color--gray-keyline">
                    <!--Add and Clear-->
                    <button id="add-clear" type="button" class="btn btn-primary first-button"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>/<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                    </button>
                    <!--Add and Copy-->
                    <button id="add-copy" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>/<span class="glyphicon glyphicon-copy" aria-hidden="true"></span>
                    </button>
                    <div class="spacer"></div>
                    <!--Close-->
                    <button id="clear" type="button" class="btn btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            <label for="follow-ups-for-date">Follow Ups Entered On:</label>
            <input type="text" id="follow-ups-for-date" class="date-field" />
            <button id="get-follow-ups" class="btn btn-default"><span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
            </button>
            <div id="follow-up-table-container">
                <div class="legend">
                    <div class="item">
                        <div class="legend-icon first-time-visitor"></div>
                        <div class="text">First Time Visitor</div>
                    </div>
                </div>
                <table class="table table-responsive table-striped" id="follow-up-table">
                    <thead>
                        <tr>
                            <th>Name</th>
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
        </div>
    </div><!-- /.container -->

    <div class="dialog-form app-form" id="search-form" title="Select Person">
        <label for="search-name">Name</label>
        <input type="text" id="search-name" />
        <button id="search" type="button" class="button--primary-small"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>
        </button>
        <div id="search-container">
            <div class="search-table-container" id="search-table-container">
                <table class="table table-responsive table-striped search-table" id="search-table">
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
            <button id="close" type="button" class="btn btn-default"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
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
		echo "var visitors = ".json_encode($visitors).";
		var followUps = ".json_encode($follow_ups).";\n";
	} else {
		echo "$().toastmessage('showErrorToast', 'Error loading visitors');";
	}
?>
		var el = $('#navbar a[id=follow-ups-nav]');
		el.attr('href', '#');
		el.parent().addClass('active');
	</script>
    <script src="scripts/login.js"></script>
    <script src="scripts/follow-ups.js"></script>
  </body>
</html>
