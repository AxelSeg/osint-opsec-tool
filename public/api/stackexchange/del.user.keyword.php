<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    if(isset($_POST['user']) && isset($_POST['keyword'])){
	require_once($_SERVER['DOCUMENT_ROOT'].'/libs/functions.php');
        $user = $_POST['user'];
	$keyword = $_POST['keyword'];
        delUserKeyword($keyword, 'stackexchange', $user);
    }
}
else{
    header('Location: ../../index.php');
}
?>
