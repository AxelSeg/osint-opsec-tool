<?php

if(isset($_GET['token'])){
    $token_entered = $_GET['token'];
}

require_once($_SERVER['DOCUMENT_ROOT'].'/config/db.php');

$stmt = $GLOBALS['dbh']->prepare("SELECT token, issued FROM `opsec_registration_tokens` WHERE token = :token");

$stmt->execute(array(':token' => $token_entered));
$row = $stmt->fetch();
$token_from_table = $row['token'];

if($token_from_table != ''){

?>
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8" />
    <title>OSINT OPSEC Tool | Registration</title>
    <link href="./css/login.style.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
    <div id="login-box">
        <h1>OPSEC TOOL Registration</h1>
        <form id="register" method="post" action="register.php">
            <input type="hidden" name="token" value="<? echo htmlspecialchars($token_from_table);?>" />
            <label>User:</label><input type="text" name="user" autofocus="autofocus" />
            <label>Password:</label><input type="password" name="password" />
            <button type="submit">Register</button>
        </form>
    </div>
    </body>
    </html>
<?

}else{

    header('Location: index.php');

}
?>
