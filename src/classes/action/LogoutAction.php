<?php

namespace iutnc\touiteur\action;

class LogoutAction extends Action
{

    /**
     * Méthode qui permet à un utilisateur connecté de se déconnecté
     * @return string
     */
    public function execute(): string
    {

        // on détruit la session
        session_destroy();
        header('Location:?action=feed');
        return " ";
    }
}