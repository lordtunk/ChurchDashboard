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

      <h1 id="attendance">Attendance</h1>

      <div class="app-content app-form attendance-form" style="padding-top: 0px;">
          <div class="attendance-search">
              <label for="attendance-date">Attendance Date:</label>
              <input type="text" id="attendance-date" class="date-field" />
              <div id="service-options">
                <div>
                  <label for="service-label-1">First Service:</label>
                  <select id="service-label-1">
                  </select>
                </div>
                <div>
                  <label for="service-label-2">Second Service:</label>
                  <select id="service-label-2">
                  </select>
                </div>
                <div>
                  <label for="campus">Campus:</label>
                  <select id="campus">
                  </select>
                </div>
              </div>
              <div class="active-input">
                  <input type="radio" id="active-true" name="active" value="true" checked />
                  <label for="active-true">Active</label>
                  <input type="radio" id="active-false" name="active" value="false" />
                  <label for="active-false">Inactive</label>
                  <br />
                  <input type="radio" id="adults-true" name="adults" value="true" checked />
                  <label for="adults-true">Adults</label>
                  <input type="radio" id="adults-false" name="adults" value="false" />
                  <label for="adults-false">Kids</label>
                  <br />
                  <!--<div id="go-arrow"></div>-->
                  <button id="go-arrow" class="btn btn-default">Run <span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
                  </button>
                  <br />
              </div>
          </div>
          <div class="attendance-stats">
              <p class="xlarge" id="attendance-date-display"></p>
              <table class="table table-responsive table-striped">
                  <thead>
                      <tr>
                          <th></th>
                          <th>Total</th>
                          <th>Adults</th>
                          <th>Kids</th>
                      </tr>
                  </thead>
                  <tbody>
                      <tr>
                          <td data-th="">Total Attendance:</td>
                          <td data-th="Total" id="total-total-count">0</td>
                          <td data-th="Adults" id="adult-total-count">0</td>
                          <td data-th="Kids" id="kid-total-count">0</td>
                      </tr>
                      <tr id="first-service-total-row">
                          <td id="first-service-total-header" data-th="">1st Service Attendance:</td>
                          <td data-th="Total" id="total-first-count">0</td>
                          <td data-th="Adults" id="adult-first-count">0</td>
                          <td data-th="Kids" id="kid-first-count">0</td>
                      </tr>
                      <tr id="second-service-total-row">
                          <td id="second-service-total-header" data-th="">2nd Service Attendance:</td>
                          <td data-th="Total" id="total-second-count">0</td>
                          <td data-th="Adults" id="adult-second-count">0</td>
                          <td data-th="Kids" id="kid-second-count">0</td>
                      </tr>
                  </tbody>
              </table>
          </div>
          <div class="clear"></div>
          <p class="campus-text xlarge"></p>
          <div id="visitors-container">
            <label id="visitors-first-service-label" for="visitors-first-service">Visitors:</label>
            <input type="number" id="visitors-first-service" /><br />
            <label id="visitors-second-service-label" for="visitors-second-service">Visitors:</label>
            <input type="number" id="visitors-second-service" /><br />
            <button id="refresh-visitors" type="button" class="btn btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Refresh Totals</button>
          </div>
          <div class="attendance-table-container" id="attendance-table-container">
              <table class="table table-responsive table-striped attendance-table" id="attendance-table">
                  <thead>
                      <tr>
                          <th id="name-table-header">Name</th>
                          <th class="first-service-header">1st Service</th>
                          <th class="second-service-header">2nd Service</th>
                      </tr>
                  </thead>
                  <tbody>
                  </tbody>
              </table>
          </div>
          <div class="table-bar">
            <button id="add-person" type="button" class="btn btn-default add-person"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add</button>
            <div class="navigation-links">
                <label for="jump-to">Jump To:</label>
                <select id="jump-to" class="jump-to"></select>
                <div>
                  <a href="javascript:void(0);" id="jump-to-top">Top</a>
                  <a href="javascript:void(0);" id="jump-to-bottom">Bottom</a>
                </div>
                <div class="clear"></div>
            </div>
          </div>
          <div class="form-bar background color--gray-keyline">
            <button id="export" type="button" style="display: none;" class="button--primary first-button"><span class="glyphicon glyphicon-export" aria-hidden="true"></span> <span class="btn-text">Export</span></button>
            <div class="spacer"></div>
            <button id="update" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> <span class="btn-text">Save</span></button>
            <button id="cancel" type="button" class="btn btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span><span class="btn-text">Refresh</span></button>
          </div>
      </div>

    </div><!-- /.container -->
    
    <div class="dialog-form" title="Attendance History">
        <div id="person-name"></div>
        <p class="campus-text large"></p>
        <div id="person-attendance-table-container">
            <table class="table table-responsive table-striped attendance-table" id="person-attendance-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="first-service-header">1st Service</th>
                        <th class="second-service-header">2nd Service</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

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
	try {
      echo "var options = ".json_encode($att->getServiceOptions()).";";
    } catch (Exception $e) {
	  $f->logMessage($e->getMessage());
      echo "$().toastmessage('showErrorToast', 'Error loading service options');";
    }
?>
		var el = $('#navbar a[id=attendance-nav]');
		el.attr('href', '#');
		el.parent().addClass('active');
	</script>
    <script src="scripts/login.js"></script>
    <script src="scripts/attendance.js"></script>
  </body>
</html>
