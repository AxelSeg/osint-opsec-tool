<?php

if(!isset($_SESSION)){
    session_start();
}

if (isset($_SESSION['user'])){
    Header('Location: main.php');
}
else{

    require('./libs/bcrypt.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/config/db.php');
    $user_entered = $_POST['user'];
    $password_entered = $_POST['password'];
    
    if( ($user_entered == '')  || ($password_entered == '') ){ 
        header('Location: index.php');
    }
    else {
    
        $stmt = $GLOBALS['dbh']->prepare("SELECT user, password_hashed FROM `opsec_users` WHERE user = :user");
    
        $stmt->execute(array(':user' => $user_entered));
    
        $row = $stmt->fetch();
    
        $hashed_password_from_table = $row['password_hashed'];
        $user_from_table = $row['user'];
    
        $bcrypt = new bcrypt(12);
        $password_correct = $bcrypt->verify($password_entered, $hashed_password_from_table);
    
        if (($user_entered == $user_from_table) && $password_correct) {
    
            $login_history_stmt = $GLOBALS['dbh']->prepare("INSERT INTO `opsec_user_login_history` (`user`) VALUES (:user)");
            $login_history_stmt->execute(array(':user' => $user_entered));
    
            $_SESSION['user'] = $user_from_table;
            header('Location: main.php');
        }
        else{
            header('Location: index.php');
        }
    }
}
?>

