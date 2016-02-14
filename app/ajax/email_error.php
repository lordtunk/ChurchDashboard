<?php
    session_start();
    include("func.php");
    $f = new Func();
    $msg = $_POST['msg'];
    $msg = str_replace('|', '<br />', strip_tags(str_replace('<br />', '|', $msg)));
    $dict = array();
    $headers =  'From: auto@gcb.my-tasks.info' . "\r\n" .
                'Reply-To: auto@gcb.my-tasks.info';
    try {
        $f->sendEmail("stevvensa.550@gmail.com", "GCB Dash Error - " . $f->getEnvironment(), $msg);
        $dict['success'] = true;
    }  catch (Exception $e) {
      $f->logMessage($e->getMessage());
      $dict['success'] = false;
    }
    echo json_encode($dict);
?>