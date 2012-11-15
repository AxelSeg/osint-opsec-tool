<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    if(isset($_GET['format'])){
        $format = $_GET['format'];
        require_once($_SERVER['DOCUMENT_ROOT'].'/libs/functions.php');
        getUsersKeywords('facebook', 'all', $format);
    }
}
else{
    header('Location: ../../index.php');
}
?>
