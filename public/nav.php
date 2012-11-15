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
<div class="nav">
    <h1># OSINT OPSEC Tool v1.0</h1>
    <p></p>
    <div class="nav-label">[ <a href="#keyword-box" class="main-menu">Main Menu</a> ]</div>
    <p></p>
    <div class="nav-label">[ Current Time: <b><? echo date("M d H:i:s"); ?></b> ]</div>
    <div class="nav-label">[ Last hit time: <b><? echo htmlspecialchars(date('M d H:i:s',getLatestHit("time"))); ?></b> ]</div>
    <div class="nav-label">[ Last hit source:  <b><? echo htmlspecialchars(getLatestHit("source")); ?></b> ]</div>
    <div class="nav-label">[ Last known hit location (Twitter):  <b><? echo htmlspecialchars(getLatestHitLocation()); ?></b> ] </div><p></p>
    <div class="nav-label">[ Time since last hit: <b><? echo htmlspecialchars(getTimeSinceLastHit()); ?></b> ]</div>
    <p></p>
    <div class="nav-label">[ <a href="gen.reg.token.php">Generate Reg Token</a> ]</div>
    <p></p>
    <div class="nav-label">[ <a href="change.pw.input.php">Change Password</a> ]</div>
    <p></p>
    <div class="nav-label">[ <a href="logout.php">Logout</a> ]</div>
</div>
<div id="content">
    OSINT OPSEC Tool Ready. Please select a source from the Main Menu.
</div>

<?
    require('main.menu.php');
    require('map.php'); 
}
?>
