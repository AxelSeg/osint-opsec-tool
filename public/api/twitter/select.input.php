<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/twitter.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/functions.php');
    genSelectedSourceBoxHeader('Twitter', 'Selection');
    genSelectInputUsers();
}
else{
    header('Location: ../../index.php');
}
?>
