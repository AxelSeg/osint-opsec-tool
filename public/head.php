<?php 

if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){
    header('Location: index.php');
}
else{
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<script src="./js/jquery-1.8.2.min.js"></script>
<script src="./js/opsec-ajax.js"></script>              
<link rel="stylesheet" href="./css/style.css">
<title>OSINT OPSEC Tool</title>
</head>
<body>
<?

}

?>
