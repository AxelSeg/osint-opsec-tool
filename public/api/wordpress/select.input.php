<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/wordpress.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/functions.php');
    genSelectedSourceBoxHeader('Wordpress', 'Selection');
    genSelectInput();
}
else{
    header('Location: ../../index.php');
}
?>
