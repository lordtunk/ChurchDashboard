<?php
    session_start();
    include("func.php");
    $f = new Func();
    $quota = $f->getQuota();
    echo json_encode($quota);
?>