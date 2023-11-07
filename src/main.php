<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Touiteur.app</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap" rel="stylesheet"></head>
<body>
<header>
    <a href="?action=tweet"><img src="images/touiteur.png" alt="logo" /></a>
    <a href="?action=sign-in"><p>Connexion</p></a>
    <a href="?action=add-user"><p>Inscription</p></a>
    <a href="?action=user-page" id="userlink"><img src="images/user.png" alt="user" id="user"/></a>
</header>
<!--
<div class="tweet">
    <div class="author">John Doe</div>
    <div class="timestamp">2 hours ago</div>
    <div class="content">
        This is a sample tweet on Touiteur.app. #TouiteurApp
    </div>
    <div class="actions">
        <button>Like</button>
        <button>Retweet</button>
    </div>
</div>
-->
</body>
</html>



<?php

if(!isset($_GET['action'])){
    $_GET['action'] = 'tweet';
}

use iutnc\touiteur\dispatch\Dispatcher;

require_once "vendor/autoload.php";

$dispatcher = new Dispatcher();
$dispatcher->run();

if($_SERVER['REQUEST_METHOD']=='GET') {
    if ($_GET['action'] == 'tweet') {
        echo '<div class="tweet">
            <div class="author">John Doe</div>
    <div class="timestamp">2 hours ago</div>
    <div class="content">
        This is a sample tweet on Touiteur.app. #TouiteurApp
    </div>
    <div class="actions">
        <button>Like</button>
        <button>Retweet</button>
    </div>
</div>';
    }
}
