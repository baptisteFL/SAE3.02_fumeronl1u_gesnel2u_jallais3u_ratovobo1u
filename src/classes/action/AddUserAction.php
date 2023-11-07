<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;

require_once "vendor/autoload.php";

class AddUserAction extends Action
{

    public function execute(): string
    {
        $html = "";
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $html .= "
<form method='post' action='' class='tweet' id='signin'>
        <h1>Inscrivez-vous !</h1>
        <label for='email'>Email</label>
        <input type='text' name='email' id='email'><br>
        <label for='password'>Mot de passe</label>
        <input type='password' name='password' id='password'><br>
        <input type='submit' value='Envoyer'>
        </form>";
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $html .= "<br> Tentative d'enregistrement...<br>";
            try {
                Auth::register($_POST['email'], $_POST['password']);
                $html .= "<br> Utilisateur enregistré !<br>";
            } catch (AuthException $e) {
                $html .= "<br> Enregistrement échoué !<br>";
            }
        }
        return $html;
    }
}