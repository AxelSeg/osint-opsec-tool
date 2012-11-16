<?php

if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){
    Header('Location: main.php');
}
else{

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>OSINT OPSEC Tool | Change Password</title>
<link href="./css/login.style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="login-box">
    <h1>Change Password</h1>
    <form id="changepw" method="post" action="change.pw.php">
        <label>Old PW:</label><input type="password" name="old_password" autofocus="autofocus" />
	<label>New PW:</label><input type="password" name="new_password" />
        <label>Confirm:</label><input type="password" name="new_password_confirm">
        <button type="submit">Change</button>
    </form>
</div>
</body>
</html>
<?
}
