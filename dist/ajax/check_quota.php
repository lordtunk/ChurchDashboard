<?php
    session_start();
    include("func.php");
    $f = new Func();
    $size = $_POST['size'];
    $dict = array();
    try {
        $quota = $f->getQuota();
        if($quota > $size) {
            $dict['access_token'] = $f->access_token;
            $dict['success'] = true;
        } else {
            $dict['msg'] = "Video will exceed existing size quota";
            $dict['success'] = false;
        }
    }  catch (Exception $e) {
      $f->logMessage($e->getMessage());
      $dict['success'] = false;
    }
    echo json_encode($dict);
?>