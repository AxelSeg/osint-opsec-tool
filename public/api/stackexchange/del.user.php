<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    if(isset($_POST['user'])){
        require_once($_SERVER['DOCUMENT_ROOT'].'/libs/stackexchange.php');
        delUser($_POST['user']);
    }
}
else{
    header('Location: ../../index.php');
}
?>
