<?php

namespace iutnc\touiteur\action;

require_once "vendor/autoload.php";

class SuivreUtilAction extends Action {

    /**
     * Méthode qui permet à utilisateur de suivre un autre utilisateur
     * Dans la base de donné modifie la table suivis
     * @return string : l'utilisateur ou suis un autre ou demande au premier de se connecter
     */
    public function execute(): string {

        $html = "";

        if(isset($_SESSION['user'])){
            try{
                $connexion = new PDO('mysql:host=localhost; dbname=sae; charset=utf8','root','');
            } catch(Exception $e){
                die('erreur :'.$e->getMessage());
            }
            // on récupère le mail de celui qui s'abonne et de la l'abonnement
            $abonner = $_SESSION['user'];
            $abonnement = "SELECT email from atouite where id_touite = ?";
            $requeteAbonnement = $connexion->prepare($abonnement);
            $requeteAbonnement->bindParam(1, );

            // on insère dans la base de donnée
            $insert  = "INSERT INTO suivis(emailUtilA, emailUtilB) VALUES (?, ?)";
            $requeteInsert = $connexion->prepare($insert);
            $requeteInsert->bindParam(1, $abonner);
            $requeteInsert->bindParam(2, );
            $requeteInsert->execute();

            $html = "<p>Vous suivez {$abonnement}</p>";
        } else {
            header('Location :?action=signin');
            $html = "<p>veuillez vous connecter</p>";
        }

        return $html;
    }
}