<?php
  session_start();
  include("../utils/func.php");
  $f = new Func();
  $username = $_POST['username'];
  $password = $_POST['password'];
  $session_id = uniqid("", true);

  $query = "UPDATE Users SET session_id=:session_id where UPPER(username)=:username AND password=:password";
  $results = $f->executeAndReturnResult($query, array(":session_id"=>$session_id, ":username"=>strtoupper($username), ":password"=>$password));

  $dict = array();
  if(!$results) {
    $dict['success'] = false;
  } else {
    $query = "SELECT id FROM Users WHERE UPPER(username)=:username AND password=:password";
    $results = $f->fetchAndExecute($query, array(":username"=>strtoupper($username), ":password"=>$password));
    if(count($results) > 0) {
      $_SESSION['session_id'] = $session_id;
      $_SESSION['user_id'] = $results[0]['id'];
      $dict['success'] = true;
    } else {
      $dict['success'] = false;
    }
  }

  echo json_encode($dict);
?>