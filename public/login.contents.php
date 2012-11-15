<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    Header('Location: main.php');
}
else{

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>OSINT OPSEC Tool | Login</title>
<link href="./css/login.style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="login-box">
    <h1>OSINT OPSEC TOOL</h1>
    <form id="login" method="post" action="login.php">
        <label>User:</label><input type="text" name="user" autofocus="autofocus" />
        <label>Password:</label><input type="password" name="password" />
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
<?
}
