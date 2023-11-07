<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;

class DisplayTouiteUserAction extends Action
{
        public function execute() : string
        {
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            //TODO : afficher les touites de l'utilisateur
            $html = "";
            return $html;
        }

}