<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;
use iutnc\touiteur\db\User;

require_once "vendor/autoload.php";

class SignInAction extends Action
{
    /**
     * Methode qui permet à utilisateur de s'authentifier
     * @return string : imprime si l'authentification est réussi ou non
     */

    public function execute(): string
    {

        $html = "";
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $html .= "
<form method='post' action='' class='tweet' id='signin'>
        <h1>Connectez-vous !</h1>
        <label for='email'>Email</label>
        <input type='text' name='email' id='email'><br>
        <label for='password'>Mot de passe</label>
        <input type='password' name='password' id='password'><br>
        <input type='submit' value='Envoyer'>
        </form>";
        }elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
            $html .= "<br> Tentative d'authentification...<br>";
            try{
                Auth::authentificate($_POST['email'], $_POST['password']);
                header('Location:?action=feed');
            }catch (AuthException $e){
                $html .= "<br> Authentification échouée !<br>";
            }
        }
        return $html;
    }
}