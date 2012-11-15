<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    Header('Location: main.php');
}
else{
    require('login.contents.php');
}
