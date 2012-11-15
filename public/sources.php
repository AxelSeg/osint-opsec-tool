<?php
if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){
    header('Location: index.php');
}
else{
    require_once('libs/functions.php');
?>
<div class="sourceBoxes">
<?
    genSourceBox("Twitter");
    genSourceBox("Reddit");    
    genSourceBox("StackExchange");
    genSourceBox("Facebook");
    genSourceBox("Pastebin");
    genSourceBox("Wordpress");
?>
</div>

<?

}

?>

