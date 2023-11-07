<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;

require_once "vendor/autoload.php";

class TouiteAction extends Action
{

    public function execute(): string
    {
        $html = "";
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $html .= '
        <form method="post" action="" class="tweet" id="formTouite">
            <input type="text" name="texte" id="text" placeholder="Quoi de neuf ?" maxlength="235"><br>
            <input type="submit" id="submitTouite" value="Envoyer">
        </form>
            ';

            // TODO
            // INSERT INTO ...
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Location:?action=feed');
        }
        return $html;
    }
}