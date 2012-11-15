<?php

if(!isset($_SESSION)){
    session_start();
}
if (isset($_SESSION['user'])){
    if(isset($_GET['user'])){
        $user = $_GET['user'];
        if(isset($_GET['format'])){
            $format = $_GET['format'];
            require_once($_SERVER['DOCUMENT_ROOT'].'/libs/functions.php');
            getUsersKeywords('reddit', $user, $format);
        }
    }
}
else{
    header('Location: ../../index.php');
}
?>
