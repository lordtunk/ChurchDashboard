<?php
  session_start();
  include("../utils/func.php");
  include("../utils/password_storage.php");
  $f = new Func();
  $username = $_GET['username'];
  $password = $_GET['password'];
  $session_id = uniqid("", true);
  $dict = array();
  
  $query = "SELECT password FROM Users WHERE UPPER(username)=:username";
  $results = $f->fetchAndExecute($query, array(":username"=>strtoupper($username)));
  if(count($results) == 0) {
	  $dict['success'] = false;
	  echo json_encode($dict);
	  die();
  } 
  $passwordHash = hash('sha256', $password);
  $newPasswordHash = PasswordStorage::create_hash($passwordHash);
  $existingPasswordHash = $results[0]['password'];
  $matchesNew = PasswordStorage::verify_password($passwordHash, $newPasswordHash);
  $matchesExisting = PasswordStorage::verify_password($passwordHash, $existingPasswordHash);
  echo "Existing password: $existingPasswordHash<br />";
  echo "Password: $passwordHash<br />";
  echo "New password hash: $newPasswordHash<br />";
  echo "Matches new? ".(($matchesNew) ? "TRUE":"FALSE")."<br />";
  echo "Matches existing? ".(($matchesExisting) ? "TRUE":"FALSE")."<br />";
  die();
  if(!PasswordStorage::verify_password($password, $results[0]['password'])) {
	  $dict['success'] = false;
	  echo json_encode($dict);
	  die();
  }
  $query = "UPDATE Users SET session_id=:session_id where UPPER(username)=:username";
  $results = $f->executeAndReturnResult($query, array(":session_id"=>$session_id, ":username"=>strtoupper($username)));

  
  if(!$results) {
    $dict['success'] = false;
  } else {
    $query = "SELECT id FROM Users WHERE UPPER(username)=:username";
    $results = $f->fetchAndExecute($query, array(":username"=>strtoupper($username)));
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