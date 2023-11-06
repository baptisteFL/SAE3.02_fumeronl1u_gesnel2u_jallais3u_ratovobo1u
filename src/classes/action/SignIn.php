<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;
use iutnc\touiteur\db\User;

require_once "vendor/autoload.php";

class SignIn extends Action
{


    public function execute(): string
    {
        $html = "";
        $html .= "<br> Tentative d'authentification...<br>";
        try{
            Auth::authentificate($_POST['email'], $_POST['password']);
            $html .= "<br> Authentification réussie !<br>";
            $user = new User($_POST['email'], $_POST['password'], "user");
        }catch (AuthException $e){
            $html .= "<br> Authentification échouée !<br>";
        }
        return $html;
    }
}