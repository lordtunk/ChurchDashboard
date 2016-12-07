<?php
  session_start();
  include("utils/func.php");
  $f = new Func();
  
  if($f->doRedirect($_SESSION)) { header("Location: ".$f->getLoginUrl()); die(); } ?><!DOCTYPE html><html lang=en><head><?php include("head.php"); ?><link rel=stylesheet href=styles/main.min.css></head><body><?php include("navbar.php"); ?><div class=container><h1 id=search-h>Person Search</h1><div class="app-content app-form search-form"><label for=search-name>Name</label> <input type=text id=search-name> <button id=search type=button class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden=true></span></button><div id=search-container><div class=search-table-container id=search-table-container><table class="table table-responsive table-striped search-table" id=search-table><thead><tr><th>Name</th><th>Email</th><th>Address</th></tr></thead><tbody></tbody></table></div><div class=navigation-links><a href=javascript:void(0); id=mia-top>Top</a> <a href=javascript:void(0); id=mia-bottom>Bottom</a></div><div class=clear></div></div></div></div><div id=error-container style="display: none;"><div id=error-msg></div><button id=email-error type=button class="btn btn-primary"><span class="glyphicon glyphicon-envelope" aria-hidden=true></span> <span class=btn-text>Email Error</span></button></div><script src=scripts/main.min.js></script><script type=text/javascript>
		var el = $('#navbar a[id=search-nav]');
		el.attr('href', '#');
		el.parent().addClass('active');
	</script><script src=scripts/login.js></script><script src=scripts/search.js></script></body></html>