<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    if (isset($_GET['format'])){
        $format = $_GET['format'];
        require_once($_SERVER['DOCUMENT_ROOT'].'/libs/reddit.php');
        getUsers($format);
    }
}
else{
    header('Location: ../../index.php');
}
?>
