<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\AddUserAction;
use iutnc\touiteur\action\DislikeAction;
use iutnc\touiteur\action\FeedAction;
use iutnc\touiteur\action\LikeAction;
use iutnc\touiteur\action\SignInAction;
use iutnc\touiteur\action\TouiteAction;
use iutnc\touiteur\action\UserPageAction;
use iutnc\touiteur\action\DisplayTouiteUserAction;
use iutnc\touiteur\action\DisplayTouiteTagAction;
use iutnc\touiteur\action\DisplayTouiteAction;

require_once "vendor/autoload.php";

class Dispatcher
{
    private $action;

    public function __construct()
    {
        // Récupère la valeur du paramètre "action" du query-string
        $this->action = isset($_GET['action']) ? $_GET['action'] : 'feed';
    }

    public function run():void
    {
        // Utilise un switch pour déterminer quelle classe Action instancier
        switch ($this->action) {
            case 'add-user':
                $action = new AddUserAction();
                break;
            case 'sign-in':
                $action = new SignInAction();
                break;
            case 'user-page':
                $action = new UserPageAction();
                break;
            case 'touite':
                $action = new TouiteAction();
                break;
            case 'display-touite-user':
                $action = new DisplayTouiteUserAction();
                break;
            case 'display-touite-tag':
                $action = new DisplayTouiteTagAction();
                break;
            default:
                $action = new FeedAction();
                break;
        }
        $this->renderPage($action->execute());
    }

    private function renderPage(string $html): void
    {
        echo '<!DOCTYPE html>
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
    <a href="?action=feed"><img src="images/touiteur.png" alt="logo" /></a>
    <a href="?action=sign-in"><p>Connexion</p></a>
    <a href="?action=add-user"><p>Inscription</p></a>
    <a href="?action=user-page" id="userlink"><img src="images/user.png" alt="user" id="user"/></a>

</header>';
        echo $html;
        echo '
</body>
</html>';
    }
}
