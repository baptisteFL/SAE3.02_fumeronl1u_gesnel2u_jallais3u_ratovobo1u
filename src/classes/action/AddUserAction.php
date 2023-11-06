<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;

require_once "vendor/autoload.php";

class AddUserAction extends Action{

    public function execute(): string
    {
        $html = "";
        $html .= "<br> Tentative d'enregistrement...<br>";
        try{
            Auth::register($_POST['email'], $_POST['password']);
            $html .= "<br> Utilisateur enregistré !<br>";
        }catch (AuthException $e){
            $html .= "<br> Enregistrement échoué !<br>";
        }
        return $html;
    }
}