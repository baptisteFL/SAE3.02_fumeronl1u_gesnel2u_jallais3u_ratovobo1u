<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;

require_once "vendor/autoload.php";

class FollowAction extends Action {
    //suivi d'un utilisateur
    public function execute(): string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("INSERT INTO suivis VALUES (:emailUtil, :emailSuivi)");
        $req->bindValue(":emailUtil", $_SESSION['emailUtil']);
        $req->bindValue(":emailSuivi", $_GET['emailSuivi']);
        $result = $req->execute();
        if ($result) {
            return "Vous suivez désormais cet utilisateur";
        } else {
            return "Erreur lors du suivi de l'utilisateur";
        }

    }
}