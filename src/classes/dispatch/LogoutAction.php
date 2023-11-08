<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\Action;

class LogoutAction extends Action
{

    public function execute(): string
    {
        session_destroy();
        header('Location:?action=feed');
        return " ";
    }
}