<?php

$config_array = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/../backend/config.ini');

$db_name = $config_array['db_name'];
$db_user = $config_array['db_user'];
$db_pw   = $config_array['db_pw'];

try{
    $dbh = new PDO("mysql:host=localhost;dbname=$db_name", "$db_user", "$db_pw");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
}
catch(PDOException $e){
    echo $e->getMessage(); 
}
?>
