<?php

if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){
    header('Location: index.php');
}
else{

require_once('libs/functions.php');

?>
<div id="keyword-box" class="popup-hidden">
<?
require('sources.php');
?>
</div>
<?
}
?>
