<?php
    session_start();
    include("func.php");
    $f = new Func();
    $size = $_POST['size'];
    $dict = array();
    try {
        $quota = $f->getQuota();
        if($quota > $size) {
            $ticket = $f->generateUploadTicket();
            $dict['ticket_uri'] = $ticket['body']['uri'];
            $dict['ticket_id'] = $ticket['body']['ticket_id'];
            $dict['upload_link'] = $ticket['body']['upload_link'];
            $dict['complete_uri'] = "https://api.vimeo.com".$ticket['body']['complete_uri'];
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