<?php


namespace iutnc\touiteur\action;

use iutnc\touiteur\db\ConnectionFactory;

require_once "vendor/autoload.php";

class AbonnerTagAction {

    /**
     * Fonction qui permet de s'abonner à un tag si et seulement si celui =-ci existe dans la base de donnée
     * Modifie la table tagsuivi si l'utilisateur est connecté et si le tag est existant
     * @return string
     */

    public function execute(): string {

        $html = "";
        if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            if(isset($_SESSION['user'])) {
                $html .= "<br> S'ABONNER A UN TAG<br>";
                $html .= '<div id = "formTag">
                                 <br><form method="post" action="" class = "tagSuivre" id = "tagSuivre">
                                            <input type="text" name="tag" id="tag" placeholder="tag1;tag2"><br>
                                            <input type="submit" id="submit" value="Suivre">
                                         </form>
                                </div>';
                $html .= "<br>Vos tags : <br>" ;
                // On se connecte à la base
                ConnectionFactory::makeConnection();
                $bdd = ConnectionFactory::$bdd;
                // On récupère les id_tag de la personne
                $user = unserialize($_SESSION['user']);
                $email = $user->__get('email');
                $requete = $bdd->prepare("SELECT id_tag FROM TAGSUIVI where emailUtil = ?");
                $requete->bindValue(1, $email);
                $requete->execute();
                // on stock tous les idTag dans un tableau
                $idtags = [];
                $i = 0;
                while($row = $requete->fetch()){
                    $idtags[$i] = $row['id_tag'];
                    $i++;
                }

                foreach ($idtags as $value) {
                    $tagUtil = $bdd->prepare("SELECT libelleTag FROM TAG WHERE id_tag = ?");
                    $tagUtil->bindValue(1, $value);
                    $tagUtil->execute();
                    $libelleTag = $tagUtil->fetchColumn();
                    $html .= "$libelleTag<br>";
                }
            }else {
                header('Location:?action=sign-in');
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // fonctionnalité pour rechercher un tag
            $tag = $_POST['tag'];
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            // on cherche si le tag existe dans la base de donné
            $sql = $bdd->prepare("SELECT libelleTag FROM TAG WHERE libelleTag = ? ");
            $sql->bindValue(1, $tag);
            $sql->execute();
            // on vérifie si le tag exsite
            if ($sql->rowCount() > 0) {
                // il faut donc créer une nouvelle table qui stock les tables que l'utilisateur suis
                $user = unserialize($_SESSION['user']);
                $email = $user->__get('email');
                // il faut retrouver l'id du tag
                $requeteIdTag = $bdd->prepare("SELECT id_tag FROM TAG WHERE libelleTag = ?");
                $requeteIdTag->bindValue(1, $tag);
                $requeteIdTag->execute();
                $id_tag = $requeteIdTag->fetchColumn();;
                // ensuite on prepare l'insertion dans la table
                $insert = $bdd->prepare("INSERT INTO tagSuivi(emailUtil, id_tag) VALUES (?, ?)");
                $insert->bindValue(1, $email);
                $insert->bindValue(2, $id_tag);
                $insert->execute();
                header('Location:?action=mytags');
            } else {
                $html .= "<br>le tag n'existe pas<br>";
            }
        }
        return $html;
    }
}