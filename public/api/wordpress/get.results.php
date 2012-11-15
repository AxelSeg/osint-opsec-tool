<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    
    $keyword = '';
    $page = '1'; 

    if (isset($_GET['keyword'])){
        $keyword = $_GET['keyword'];
    }

    if (isset($_GET['page'])){
        $page = $_GET['page'];
    }

    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/wordpress.php');
    getResults($page, $keyword);
   
}
else{
    header('Location: ../../index.php');
}
?>
