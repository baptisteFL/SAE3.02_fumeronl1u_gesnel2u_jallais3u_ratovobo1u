<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\db\ConnectionFactory;

class UserPageAction extends Action
{


        public function execute() : string
        {
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            $user = unserialize($_SESSION['user']);
            $req = $bdd->prepare("SELECT * FROM utilisateur WHERE emailUtil = :email");
            $req->bindValue(":email", $user->__get('email'));
            $html = "";
            if($_SERVER['REQUEST_METHOD'] == 'GET'){
                try{
                    $result = $req->execute();
                    if ($result) {
                        while($row = $req->fetch()){
                            $html .= "<br> Nom : ". $row['nomUtil'] ."<br>";
                            $html .= "<br> Prenom : ". $row['prenomUtil'] ."<br>";
                            $html .= "<br> Email : ". $row['emailUtil'] ."<br>";
                        }
                    }
                    $html .= "<br> AFFICHER TWEETS<br>";
                    //affichage des touites des personnes suivies
                    /*
                     * select touite.id_touite, touite.texte, utilisateur.prenomUtil, utilisateur.nomUtil
                     * from utilisateur, touite, atouite, suivis
                     * where emailUtilA='BaptisteFL@mail.com' and atouite.emailUtil='BastienJ@mail.com'
                     * and utilisateur.emailUtil = atouite.emailUtil and atouite.id_touite = touite.id_touite
                     * and suivis.emailUtilA= utilisateur.emailUtil and suivis.emailUtilB = utilisateur.emailUtil;
                     */


                    $html .= "<br> AFFICHER FOLLOWERS<br>";
                }catch (\Exception $e){
                    $html .= "<br> Vous n'avez pas accès à cet utilisateur !<br>";
                }
            }
            return $html;

        }

}