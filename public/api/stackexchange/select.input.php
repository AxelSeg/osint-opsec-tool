<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/stackexchange.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/functions.php');
    genSelectedSourceBoxHeader('StackExchange', 'Selection');
    genSelectInputUsers();
}
else{
    header('Location: ../../index.php');
}
?>
