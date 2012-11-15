<?php

if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){
    header('Location: index.php');
}
else{
    require('libs/functions.php');
    require('head.php');
    require('nav.php');
    echo '</body>';
    echo '</html>';
}
