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

</body>
</html>



<?php

if(!isset($_GET['action'])){
    $_GET['action'] = 'tweet';
}

use iutnc\touiteur\dispatch\Dispatcher;

require_once "vendor/autoload.php";

if($_SERVER['REQUEST_METHOD']=='GET'){
    if($_GET['action']=='sign-in'){
        echo "
<form method='post' action='' class='tweet' id='signin'>
        <h1>Connectez-vous !</h1>
        <label for='email'>Email</label>
        <input type='text' name='email' id='email'><br>
        <label for='password'>Mot de passe</label>
        <input type='password' name='password' id='password'><br>
        <input type='submit' value='Envoyer'>
        </form>";
    }elseif ($_GET['action']=='add-user') {
        echo "
<form method='post' action='' class='tweet' id='signin'>
        <h1>Inscrivez-vous !</h1>
        <label for='email'>Email</label>
        <input type='text' name='email' id='email'><br>
        <label for='password'>Mot de passe</label>
        <input type='password' name='password' id='password'><br>
        <input type='submit' value='Envoyer'>
        </form>";
    } elseif($_GET['action']=='tweet'){
        echo '<div class="tweet">
        <span id="titleTweet">
            <div class="author">John Doe</div>
            <div class="actions" id="follow"><button>Suivre</button></div>
        </span>
    <div class="timestamp">2 hours ago</div>
    <div class="content">
        This is a sample tweet on Touiteur.app.
    </div>
    <div class="tags">
    <p class="trending">#Populaire <p id="numberTweet" class="trending">12k</p></p>
    <p>#Exemple <p id="numberTweet">1k</p></p>    
    </div>
    <div class="actions">
        <button>Like</button>
        <button>Dislike</button>
        <button>Retouite</button>
    </div>
</div>
    <a id="postTweet" href="?action=postTweet">
    <img src="images/postTweet.png" alt="post a tweet"/>
    </a>
';
    }
}elseif($_SERVER['REQUEST_METHOD']=='POST') {
    $dispatcher = new Dispatcher();
    $dispatcher->run();
}
