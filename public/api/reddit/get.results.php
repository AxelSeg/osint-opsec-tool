<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
   
    $page = '1'; 
    $user = 'all';
    $keyword = 'all';

    if (isset($_GET['page'])){
        $page = $_GET['page'];
    }   
    if (isset($_GET['user'])){
        $user = $_GET['user'];
    }
    if (isset($_GET['keyword'])){
        $keyword = $_GET['keyword'];
    }

    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/reddit.php');
    getResults($page, $user, $keyword);
   
}
else{
    header('Location: ../../index.php');
}
?>
