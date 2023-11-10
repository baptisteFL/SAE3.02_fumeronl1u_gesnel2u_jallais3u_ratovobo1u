<?php

namespace iutnc\touiteur\action;

class LogoutAction extends Action
{

    public function execute(): string
    {

        // on détruit la session
        session_destroy();
        header('Location:?action=feed');
        return " ";
    }
}