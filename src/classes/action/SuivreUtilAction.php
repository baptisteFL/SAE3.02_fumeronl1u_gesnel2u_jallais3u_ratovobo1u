<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;

require_once "vendor/autoload.php";

class SuivreUtilAction extends Action
{

    /**
     * Méthode qui permet à utilisateur de suivre un autre utilisateur
     * Dans la base de donné modifie la table suivis
     * @return string : l'utilisateur ou suis un autre ou demande au premier de se connecter
     */
    public function execute(): string
    {

        $html = "";
        if (isset($_SESSION['user'])) {
            try {
                ConnectionFactory::makeConnection();
                $bdd = ConnectionFactory::$bdd;
            } catch (Exception $e) {
                die('erreur :' . $e->getMessage());
            }
            // on récupère le mail de celui qui s'abonne et de la l'abonnement
            $emailUtil = unserialize($_SESSION['user'])->__get('email');
            $emailSuivi = $_GET['emailSuivi'];
            if ($emailUtil == $emailSuivi) {
                header('Location:?action=user-page');
            } else {
                // on vérifie que l'utilisateur ne suis pas déjà l'autre
                $req = $bdd->prepare("SELECT count(*) FROM suivis WHERE emailUtil = :emailUtil AND emailUtilSuivi = :emailSuivi");
                $req->bindValue(":emailUtil", $emailUtil);
                $req->bindValue(":emailSuivi", $emailSuivi);
                $result = $req->execute();
                $verif = $req->fetchColumn();
                // si il le suis on le désabonne sinon on l'abonne
            }
            if ($verif == 0) {
                // on ajoute l'abonnement dans la base de données
                $req = $bdd->prepare("INSERT INTO suivis VALUES (:emailUtil, :emailSuivi)");
                $req->bindValue(":emailUtil", $emailUtil);
                $req->bindValue(":emailSuivi", $emailSuivi);
                $result = $req->execute();
                $html = "<p>Vous suivez {$emailSuivi}</p>";
            } else {
                $req = $bdd->prepare("DELETE FROM suivis WHERE emailUtil = :emailUtil AND emailUtilSuivi = :emailSuivi");
                $req->bindValue(":emailUtil", $emailUtil);
                $req->bindValue(":emailSuivi", $emailSuivi);
                $result = $req->execute();
                $html = "<p>Vous ne suivez plus {$emailSuivi}</p>";
            }
            // on redirige vers la page de connexion si l'utilisateur n'est pas connecté
        } else {
            header('Location:?action=sign-in');
            $html = "<p>veuillez vous connecter</p>";
        }
        if (isset($_GET['display'])) {
            switch ($_GET['display']) {
                case 'displaytouitetag':
                    header('Location:?action=display-touite-tag&tag=' . $_GET['tag']);
                    break;
                case 'displaytouiteuser':
                    header('Location:?action=display-touite-user&emailUtil=' . $_GET['user'] . '&page=' . $_GET['page']);
                    break;
                case 'displayabotag':
                    header('Location:?action=mytags');
                    break;
            }
        } else {
            header('Location:?action=feed&page=1');
            }
        return $html;
    }

    public static function connaitreSuivi(string $email, string $emailSuivi)
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("
        select emailUtilSuivi 
        FROM suivis 
        WHERE emailUtil = :emailUtil");
        $req->bindValue(":emailUtil", $email);
        $result = $req->execute();
        $suivi = false;
        while ($row = $req->fetch()) {
            if ($row['emailUtilSuivi'] == $emailSuivi) {
                $suivi = true;
            }
        }
        return $suivi;
    }
}