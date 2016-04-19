<?php
    session_start();
    include("../utils/func.php");
    $f = new Func();
    $email = $_POST['email'];
    $video_status = $_POST['video_status'];
    $dict = array();
    $headers =  'From: auto@gcb.my-tasks.info' . "\r\n" .
                'Reply-To: auto@gcb.my-tasks.info';
    try {
        if($video_status == "1")
            mail($email, 'Video Conversion Complete', 'Video has been successfully uploaded and transcoded!', $headers);
        else if($video_status == "2")
            mail($email, 'Video Upload Failure', 'There was an error uploading the video', $headers);
        else if($video_status == "3")
            mail($email, 'Video Conversion Failure', 'There was an error transcoding the video', $headers);
        else if($video_status == "4")
            mail($email, 'Video Rename Failure', 'There was an error renaming the video', $headers);
    }  catch (Exception $e) {
      $f->logMessage($e->getMessage());
      $dict['success'] = false;
    }
    echo json_encode($dict);
?>