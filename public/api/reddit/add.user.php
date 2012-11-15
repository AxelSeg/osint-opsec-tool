<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    if(isset($_POST['user'])){
        require_once($_SERVER['DOCUMENT_ROOT'].'/libs/reddit.php');
        addUser($_POST['user']);
    }
}
else{
    header('Location: ../../index.php');
}
?>
