<?php

if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){

    header('Location: index.php');

}   
else{

    require('./libs/bcrypt.php');
    require('./libs/functions.php');

    $user = $_SESSION['user'];
    $old_password_entered = $_POST['old_password'];
    $new_password_entered = $_POST['new_password'];
    $new_password_confirm = $_POST['new_password_confirm'];

    if(($old_password_entered == '')  || ($new_password_entered == '') || ($new_password_confirm == '' )){  // Blank entries submitted
        header('Location: index.php');
    }
    else {
        if($new_password_entered == $new_password_confirm){
 
            if(check_strong_password($new_password_entered)){

                require_once($_SERVER['DOCUMENT_ROOT'].'/config/db.php');

	        $stmt = $GLOBALS['dbh']->prepare("SELECT password_hashed FROM `opsec_users` WHERE user = :user");
	        $stmt->execute(array(':user' => $user));

                $row = $stmt->fetch();
		                
                $hashed_password_from_table = $row['password_hashed'];
			                
                $bcrypt = new bcrypt(12);
                $password_correct = $bcrypt->verify($old_password_entered, $hashed_password_from_table);

    	        if($password_correct){
	            $hashed_pw = $bcrypt->genHash($new_password_entered);
                    $passwd_stmt = $GLOBALS['dbh']->prepare("UPDATE `opsec_users` SET `password_hashed` = :password_hashed WHERE `user` = :user");
		    $passwd_stmt->execute(array(':password_hashed' => $hashed_pw, ':user' => $user));
		    echo "Password updated successfully.";
	        }
	        else{
                    die("Old password not correct!");
	        }
	    }
	    else{die();}
	}
	else{
            die("New passwords do not match!");
	}
	echo "<p>Redirecting you...</p>";
	echo '<meta http-equiv="refresh" content="5;url=http://'; echo gethostname(); echo '/main.php">';
    }
}
?>

