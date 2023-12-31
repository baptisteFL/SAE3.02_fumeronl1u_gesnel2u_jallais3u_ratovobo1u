<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\AbonnerTagAction;
use iutnc\touiteur\action\AddUserAction;
use iutnc\touiteur\action\DislikeAction;
use iutnc\touiteur\action\DisplayAbonnementTagAction;
use iutnc\touiteur\action\DisplayTouiteAction;
use iutnc\touiteur\action\DisplayTouiteTagAction;
use iutnc\touiteur\action\DisplayTouiteUserAction;
use iutnc\touiteur\action\FeedAction;
use iutnc\touiteur\action\LikeAction;
use iutnc\touiteur\action\LogoutAction;
use iutnc\touiteur\action\SignInAction;
use iutnc\touiteur\action\SuivreUtilAction;
use iutnc\touiteur\action\SupprimerTouiteAction;
use iutnc\touiteur\action\TouiteAction;
use iutnc\touiteur\action\UnfollowUserAction;
use iutnc\touiteur\action\UserPageAction;

require_once "vendor/autoload.php";

class Dispatcher
{
    private $action;

    /**
     * Constructeur
     */

    public function __construct()
    {
        // Récupère la valeur du paramètre "action" du query-string
        $this->action = isset($_GET['action']) ? $_GET['action'] : 'feed';
    }

    /**
     * Méthode run du dispatcher qui lance a l'exécution un une instruction associées
     * @return void
     * @throws \Exception
     */

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
            case 'display-touite':
                $action = new DisplayTouiteAction();
                break;
            case 'log-out':
                $action = new LogoutAction();
                break;
            case 'like':
                $action = new LikeAction();
                break;
            case 'dislike':
                $action = new DislikeAction();
                break;
            case 'mytags':
                $action = new DisplayAbonnementTagAction();
                break;
            case 'abonnerTag' :
                $action = new AbonnerTagAction();
                break;
            case 'follow-user':
                $action = new SuivreUtilAction();
                break;
            case 'unfollow-user':
                $action = new UnfollowUserAction();
                break;
            case 'supprimer-touite':
                $action = new SupprimerTouiteAction();
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
    <a href="?action=feed&page=1"><img src="images/touiteur.png" alt="logo" /></a>';

    if (!isset($_SESSION['user'])) {
        echo '<a href="?action=sign-in"><p>Connexion</p></a>
    <a href="?action=add-user"><p>Inscription</p></a>';
    } else {
        echo '<a href="?action=log-out"><p>Déconnexion</p></a>
            <a href="?action=mytags"><p>My tags</p></a>
            <a href="?action=abonnerTag"><p>Abonnement Tag</p></a>';
    }
    echo '
    <a href="?action=user-page" id="userlink"><img src="images/user.png" alt="user" id="user"/></a>

</header>';
        echo $html;
        echo '
</body>
</html>';
    }
}
