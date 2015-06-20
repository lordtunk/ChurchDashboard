<?php
    session_start();
    include("func.php");
    $f = new Func();
    $ticket = $f->generateUploadTicket();
    echo json_encode($ticket);
?>