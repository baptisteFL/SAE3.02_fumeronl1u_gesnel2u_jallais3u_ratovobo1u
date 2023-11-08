<?php

namespace iutnc\touiteur\action;

use Exception;
use iutnc\touiteur\db\ConnectionFactory;
use PDO;

require_once "vendor/autoload.php";

class DislikeAction extends Action {

    public function execute(): string
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            $req = $bdd->prepare("SELECT note FROM touite WHERE id_touite = :id");
            $req->bindValue(":id", $_GET['id']);
            $result = $req->execute();
            $ancienneVal = $req->fetchColumn();

            $update = $bdd->prepare("UPDATE touite SET note = :note WHERE id_touite = :id");
            $update->bindValue(":id", $_GET['id']);
            $update->bindValue(":note", $ancienneVal-1);
            $result = $update->execute();
        }
        header('Location:?action=feed');
        return " ";
    }
}