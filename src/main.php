<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Touiteur.app</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
</head>
<body>
<header>
    <h1><a href="?action=tweet"><img src="images/logo.png" alt="logo" /></a></h1>
    <nav>
        <a href="?action=sign-in">Connexion</a>
        <a href="?action=add-user">Inscription</a>
    </nav>
</header>
</body>
</html>



<?php

if(!isset($_GET['action'])){
    $_GET['action'] = 'tweet';
}

use iutnc\touiteur\dispatch\Dispatcher;

require_once "vendor/autoload.php";

if($_SERVER['REQUEST_METHOD']=='GET'){
    if($_GET['action']=='sign-in' || $_GET['action']=='add-user'){
        echo "<form method='post' action=''>
        <label for='email'>Email</label>
        <input type='text' name='email' id='email'><br>
        <label for='password'>Mot de passe</label>
        <input type='password' name='password' id='password'><br>
        <input type='submit' value='Envoyer'>
        </form>";
    }elseif($_GET['action']=='tweet'){
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
}elseif($_SERVER['REQUEST_METHOD']=='POST') {
    $dispatcher = new Dispatcher();
    $dispatcher->run();
}
