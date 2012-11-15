<?php

if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){
    header('Location: index.php');
}
else {
    require_once($_SERVER['DOCUMENT_ROOT'].'/config/db.php');

    if (@is_readable('/dev/urandom')) { 
        $f = fopen('/dev/urandom', 'r'); 
        $urandom = fread($f, '512'); 
	fclose($f);
        $token_generated = hash('sha512', $urandom);	
        $token_stmt = $GLOBALS['dbh']->prepare("INSERT INTO `opsec_registration_tokens` (`token`) VALUES (:token)");
	$token_stmt->execute(array(':token' => $token_generated));
	echo '
        <!DOCTYPE html>
	<html>
	    <head>
	        <meta charset="UTF-8" />
	        <title>OSINT OPSEC Tool | Registration</title>
	        <link href="./css/login.style.css" rel="stylesheet" type="text/css" />
	    </head>
	    <body>
                <p>You can send the following URL to new users:</p>
		<p>https://'; echo gethostname(); echo '/registration.php?token='; echo $token_generated; echo '
                <p><a href="main.php">Go Back</a>
            </body>
	</html>';
    }

}
?>

