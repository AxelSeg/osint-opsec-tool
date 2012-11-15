<?php

if(!isset($_SESSION)){
    session_start();
}

$_SESSION = array();

if (session_id() != "" || isset($_COOKIE[session_name()])){
    setcookie(session_name(), '', time() - 2592000, '/');
    session_destroy();
}

header('Location: index.php');

?>
