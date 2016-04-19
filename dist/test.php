<?php
	session_start();
	  include("ajax/func.php");
	  $f = new Func();
	  $f->logMessage("Test!"); echo "Test!"; if(isset($_GET['url'])) echo "URL: ".$_GET['url']; ?>