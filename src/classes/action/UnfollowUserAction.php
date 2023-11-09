<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;

require_once "vendor/autoload.php";

class UnfollowUserAction extends Action {

    /**
     * Méthode qui permet à utilisateur de ne plus suivre un autre utilisateur
     * Dans la base de donné modifie la table suivis
     * @return string : l'utilisateur ou suis un autre ou demande au premier de se connecter
     */
    public function execute(): string {

        $html = "";
        if(isset($_SESSION['user'])){
            try{
                ConnectionFactory::makeConnection();
                $bdd = ConnectionFactory::$bdd;
            } catch(Exception $e){
                die('erreur :'.$e->getMessage());
            }
            // on récupère le mail de celui qui s'abonne et de la l'abonnement
            $emailUtil = unserialize($_SESSION['user'])->__get('email');
            $emailSuivi = $_GET['emailSuivi'];
            if($emailUtil == $emailSuivi){
                header('Location:?action=user-page');
            }else{
                //on vérifie que les utilisateurs sont bien inscrits
                $req = $bdd->prepare("SELECT count(*) FROM suivis WHERE emailUtil = :emailUtil and emailUtilSuivi = :emailSuivi");
                $req->bindValue(":emailUtil", $emailUtil);
                $req->bindValue(":emailSuivi", $emailSuivi);
                $result = $req->execute();
                $verifUtil = $req->fetchColumn();
                //s'ils sont abonnés on les supprrime de la table suivis
                if ($verifUtil == 1){
                    $req2 = $bdd->prepare("DELETE FROM suivis WHERE emailUtil = :emailUtil AND emailUtilSuivi = :emailSuivi");
                    $req2->bindValue(":emailUtil", $emailUtil);
                    $req2->bindValue(":emailSuivi", $emailSuivi);
                    $result2 = $req2->execute();
                    $html = "<p>Vous ne suivez plus {$emailSuivi}</p>";
                }
            }
            // on redirige vers la page de connexion si l'utilisateur n'est pas connecté
        } else {
            header('Location:?action=sign-in');
            $html = "<p>veuillez vous connecter</p>";
        }

        return $html;
    }

    public static function connaitreSuivi(string $email, string $emailSuivi){
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("
        select emailUtilSuivi 
        FROM suivis 
        WHERE emailUtil = :emailUtil");
        $req->bindValue(":emailUtil", $email);
        $result = $req->execute();
        $suivi = false;
        while($row = $req->fetch()){
            if($row['emailUtilSuivi'] == $emailSuivi){
                $suivi = true;
            }
        }
        return $suivi;
    }
}