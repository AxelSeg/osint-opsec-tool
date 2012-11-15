<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    if(isset($_POST['keyword'])){
	require_once($_SERVER['DOCUMENT_ROOT'].'/libs/functions.php');
        $keyword = $_POST['keyword'];
        addUserKeyword($keyword, 'pastebin', 'all');
    }
}
else{
    header('Location: ../../index.php');
}
?>
