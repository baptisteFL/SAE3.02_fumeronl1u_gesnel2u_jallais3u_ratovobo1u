<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;

require_once "vendor/autoload.php";

class SupprimerTouiteAction extends Action{

    public function execute(): string
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_SESSION['user'])) {
                ConnectionFactory::makeConnection();
                $bdd = ConnectionFactory::$bdd;

                $req = $bdd->prepare("DELETE FROM atouite WHERE id_touite = :id");
                $req2 = $bdd->prepare("DELETE FROM alike WHERE id_touite = :id");
                $req3 = $bdd->prepare("DELETE FROM adislike WHERE id_touite = :id");
                $req4 = $bdd->prepare("DELETE FROM touitepartag WHERE id_touite = :id");
                $req5 = $bdd->prepare("DELETE FROM touite WHERE id_touite = :id");
                $req->bindValue(":id", $_GET['id']);
                $req2->bindValue(":id", $_GET['id']);
                $req3->bindValue(":id", $_GET['id']);
                $req4->bindValue(":id", $_GET['id']);
                $req5->bindValue(":id", $_GET['id']);
                $result = $req->execute();
                $result2 = $req2->execute();
                $result3 = $req3->execute();
                $result4 = $req4->execute();
                $result5 = $req5->execute();
                header("Location:?action=feed&page=" . $_GET['page']);
            }
        }
        return "";
    }

}