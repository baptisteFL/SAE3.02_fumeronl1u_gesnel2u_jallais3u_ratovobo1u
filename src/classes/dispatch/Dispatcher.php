<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\AddUserAction;
use iutnc\touiteur\action\SignIn;

require_once "vendor/autoload.php";

class Dispatcher
{
    private $action;

    public function __construct()
    {
        // Récupère la valeur du paramètre "action" du query-string
        $this->action = isset($_GET['action']) ? $_GET['action'] : 'add-user';
    }

    public function run():void
    {
        // Utilise un switch pour déterminer quelle classe Action instancier
        switch ($this->action) {
            case 'add-user':
                $action = new AddUserAction();
                break;
            case 'sign-in':
                $action = new SignIn();
                break;
            default:
                $action = new AddUserAction();
                break;
        }
        $this->renderPage($action->execute());
    }

    private function renderPage(string $html): void
    {
        echo '<html>';
        echo '<head>';
        echo '<title></title>';
        echo '</head>';
        echo '<body>';
        echo $html;
        echo '</body>';
        echo '</html>';
    }
}
