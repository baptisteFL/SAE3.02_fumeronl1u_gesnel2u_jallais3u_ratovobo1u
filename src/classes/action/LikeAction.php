<?php

namespace iutnc\touiteur\action;

use Exception;
use iutnc\touiteur\db\ConnectionFactory;
use PDO;

require_once "vendor/autoload.php";

class LikeAction extends Action {

    /**
     * Methode qui permet à un utilisateur connecté de mettre un like sur un touite
     * Dans la base donné modifie la table touite(note) et aLike
     * @return string : " "
     */
    public function execute(): string
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            if (isset($_SESSION['user'])) {
                ConnectionFactory::makeConnection();
                $bdd = ConnectionFactory::$bdd;
                $user = unserialize($_SESSION['user']);
                $email = $user->__get('email');

                $verifLike = $bdd->prepare("SELECT count(*) FROM ALIKE WHERE emailUtil = :email AND id_touite = :id");
                $verifLike->bindValue(":email", $email);
                $verifLike->bindValue(":id", $_GET['id']);
                $verifLike->execute();
                $verifieLike = $verifLike->fetchColumn();

                $verifDislike = $bdd->prepare("SELECT count(*) FROM ADISLIKE WHERE emailUtil = :email AND id_touite = :id");
                $verifDislike->bindValue(":email", $email);
                $verifDislike->bindValue(":id", $_GET['id']);
                $verifDislike->execute();
                $verifieDislike = $verifDislike->fetchColumn();


                if ($verifieLike == 0 && $verifieDislike == 1) {
                    $req = $bdd->prepare("SELECT note FROM TOUITE WHERE id_touite = :id");
                    $req->bindValue(":id", $_GET['id']);
                    $result = $req->execute();
                    $ancienneVal = $req->fetchColumn();

                    $update = $bdd->prepare("UPDATE TOUITE SET note = :note WHERE id_touite = :id");
                    $update->bindValue(":id", $_GET['id']);
                    $update->bindValue(":note", $ancienneVal + 2);
                    $result = $update->execute();

                    $addUser = $bdd->prepare("INSERT INTO ALIKE VALUES (:email, :id)");
                    $addUser->bindValue(":email", $email);
                    $addUser->bindValue(":id", $_GET['id']);
                    $result = $addUser->execute();

                    $delUser = $bdd->prepare("DELETE FROM ADISLIKE WHERE emailUtil = :email and id_touite = :id");
                    $delUser->bindValue(":email", $email);
                    $delUser->bindValue(":id", $_GET['id']);
                    $result = $delUser->execute();

                } elseif ($verifieLike == 0 && $verifieDislike == 0) {
                    $req = $bdd->prepare("SELECT note FROM TOUITE WHERE id_touite = :id");
                    $req->bindValue(":id", $_GET['id']);
                    $result = $req->execute();
                    $ancienneVal = $req->fetchColumn();

                    $update = $bdd->prepare("UPDATE TOUITE SET note = :note WHERE id_touite = :id");
                    $update->bindValue(":id", $_GET['id']);
                    $update->bindValue(":note", $ancienneVal + 1);
                    $result = $update->execute();

                    $addUser = $bdd->prepare("INSERT INTO ALIKE VALUES (:email, :id)");
                    $addUser->bindValue(":email", $email);
                    $addUser->bindValue(":id", $_GET['id']);
                    $result = $addUser->execute();
                } elseif ($verifieLike == 1) {
                    $req = $bdd->prepare("SELECT note FROM TOUITE WHERE id_touite = :id");
                    $req->bindValue(":id", $_GET['id']);
                    $result = $req->execute();
                    $ancienneVal = $req->fetchColumn();

                    $update = $bdd->prepare("UPDATE TOUITE SET note = :note WHERE id_touite = :id");
                    $update->bindValue(":id", $_GET['id']);
                    $update->bindValue(":note", $ancienneVal - 1);
                    $result = $update->execute();

                    $delUser = $bdd->prepare("DELETE FROM ALIKE WHERE emailUtil = :email and id_touite = :id");
                    $delUser->bindValue(":email", $email);
                    $delUser->bindValue(":id", $_GET['id']);
                    $result = $delUser->execute();
                }

                if (isset($_GET['display'])){
                    header('Location:?action=display-touite&id_touite=' . $_GET['id']);
                } elseif (isset($_GET['libelleTag'])) {
                    header('Location:?action=display-touite-tag&libelleTag=' . $_GET['libelleTag']);
                } elseif (isset($_GET['userpage'])) {
                    header('Location:?action=user-page');
                } elseif (isset($_GET['tags'])) {
                    header('Location:?action=mytags');
                } elseif (isset($_GET['page'])) {
                    header('Location:?action=feed&page=' . $_GET['page']);
                } else {
                    header('Location:?action=feed&page=1');
                }
            } else {
                header('Location:?action=sign-in');
            }
        }
        return " ";
    }
}