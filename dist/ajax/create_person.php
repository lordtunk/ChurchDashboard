<?php
  session_start();
  include("../utils/func.php");
  $f = new Func();
  $person_display = $_POST['person_display'];
  $dict = array();
  
  if(!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
    $dict['success'] = FALSE;
    $f->logMessage('Session information missing');
  } else {
    $session_id = $_SESSION['session_id'];
    $user_id = $_SESSION['user_id'];


    try {
      $dict['success'] = $f->isLoggedIn($user_id, $session_id);
    } catch (Exception $e) {
      $dict['success'] = FALSE;
      $f->logMessage($e->getMessage());
    }
  }
  if($dict['success'] == TRUE) {
    try {
      $pos = strpos($person_display, ",");
      $name = "";
      if($pos == FALSE) {
        $name = $person_display;
        $query = "INSERT INTO People (description, adult, last_modified_dt, modified_by, creation_dt, created_by) VALUES (:description, :adult, NOW(), :user_id, NOW(), :user_id)";
        $person_id = $f->queryLastInsertId($query, array(":description"=>$person_display, ":adult"=>1, ":user_id"=>$user_id));
      } else {
        $names = explode(",", $person_display);
        $last_name = trim($names[0]);
        $first_name = trim($names[1]);
        $name = "$first_name $last_name";
        $query = "INSERT INTO People (last_name, first_name, adult, last_modified_dt, modified_by, creation_dt, created_by) VALUES (:last_name, :first_name, :adult, NOW(), :user_id, NOW(), :user_id)";
        $person_id = $f->queryLastInsertId($query, array(":last_name"=>$last_name, ":first_name"=>$first_name, ":adult"=>1, ":user_id"=>$user_id));
      }
      $dict['person_id'] = $person_id;
      $dict['person_name'] = $name;
      $dict['success'] = TRUE;
    } catch (Exception $e) {
      $dict['success'] = FALSE;
    }
  } else {
    $dict['error'] = 1;
  }
  echo json_encode($dict);
?>