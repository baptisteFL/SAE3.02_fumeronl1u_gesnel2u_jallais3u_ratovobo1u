<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;

class UserPageAction extends Action
{


        public function execute() : string
        {
            $html = "";
            if($_SERVER['REQUEST_METHOD'] == 'GET'){
                $html .= "<br> Tentative d'affichage de la page de l'utilisateur...<br>";
                try{
                    $html.= "<br>Authentification avec l'utilisateur _SESSION[user]<br>";
                    $html .= "<br> AFFICHER PROFIL avec NOM PRENOM ETC..<br>";
                    $html .= "<br> AFFICHER TWEETS<br>";
                    $html .= "<br> AFFICHER FOLLOWERS<br>";
                }catch (\Exception $e){
                    $html .= "<br> Vous n'avez pas accès à cet utilisateur !<br>";
                }
            }
            return $html;

        }

}