<?php
  session_start();
  include("utils/func.php");
  include("utils/user.php");
  $f = new Func();
  
  if(isset($_SESSION['user_id']))
	$homepage = User::getUser($_SESSION['user_id'], $f)['homepage'];
  else
	  $homepage = "index.php";
  if(!$f->doRedirect($_SESSION)) { if(isset($_GET['url'])) header("Location: ".$_GET['url']); else header("Location: ".$homepage); die(); } ?><!DOCTYPE html><html lang=en><head><meta charset=utf-8><meta http-equiv=X-UA-Compatible content="IE=edge"><meta name=viewport content="width=device-width, initial-scale=1"><meta name=description content="A simple web application for Guide Church to keep track of attendance"><link rel="shortcut icon" href=images/favicon.ico><title>Church Dashboard</title><link rel=stylesheet href=styles/main.min.css></head><body><nav class="navbar navbar-inverse navbar-fixed-top"><div class=container><div class=navbar-header><a class="navbar-brand readonly" href=#><div class=navbar-brand-text>Church Dashboard</div></a></div><div id=navbar class="collapse navbar-collapse"></div></div></nav><div class=container><h1>Login</h1><div class="app-content app-form login-form"><label for=username>Username:</label> <input type=text id=username><br><label for=password>Password:</label> <input type=password id=password><div class="form-bar background color--gray-keyline"><div class=spacer></div><button type=button class="btn btn-primary" id=login-btn>Login</button></div></div></div><script src=scripts/main.min.js></script><script type=text/javascript>
<?php
	echo "var homepage = '$homepage';";
?>
	</script><script src=scripts/login.js></script></body></html>