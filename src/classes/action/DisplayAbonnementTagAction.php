<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\db\ConnectionFactory;

class DisplayAbonnementTagAction extends Action
{

    /**
     * Méthode qui qui d'afficher  les touites qui possède un tag avec lequel l'utilisateur est abonné
     * @return string
     * @throws \Exception
     */
    public function execute() : string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        //afficher un touite en detail
        $html = "";
        $user = unserialize($_SESSION['user']);
        $email = $user->__get('email');
        // On récupère l'ensemble des id_tag sur lesquels un utilisateur est abonné.
        $stock = $bdd->prepare("SELECT id_tag FROM TAGSUIVI WHERE emailUtil = :email");
        $stock->bindValue(":email", $email);
        $stock->execute();
        // On stock dans un tableau les id_tags
        $idtags = [];
        $i = 0;
        while($row = $stock->fetch()){
            $idtags[$i] = $row['id_tag'];
            $i++;
        }
        // Boucle qui pour chaque id_tag retourne les touites en rapport
        foreach ($idtags as $value) {
            // requête qui permet de selectionner les touites qui possède un tag avec lequel l'utilisateur est abonné.
            $requete = $bdd->prepare("SELECT DISTINCT * , Utilisateur.nomUtil FROM touite natural join touitepartag natural join Utilisateur where id_tag = :id");
            $requete->bindValue(":id", $value);
            $result = $requete->execute();
            if($result){
                while($row = $requete->fetch()){
                    $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                    $html .= '<div class="author">'. "<a href='?action=display-touite-user&nomUtil={$row['nomUtil']}'>". $row['prenomUtil'] .' '. $row['nomUtil'] .'</a></div>';
                    $html .= '<div class="actions" id="follow"><button>Suivre</button></div>
                    </span>';
                    $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                    $html .= '<div class="content">' . $row['texte'] . '</div>';
                    $html .= '<div class="note">' . "Score : " . $row['note'] . '</div>';

                    //afficher les tags du touite
                    $html .= '<div class="tags">';
                    $req3 = $bdd->prepare("SELECT * FROM tag natural join touitepartag where id_touite = :idTouite");
                    $req3->bindValue(":idTouite", $row['id_touite']);
                    $result3 = $req3->execute();
                    if ($result3) {
                        while ($row3 = $req3->fetch()) {
                            $html .= '<p class="trending">'."<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>".'#' . $row3['libelleTag'] . '</a><p id="numberTweet" class="trending">' . FeedAction::calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                        }
                    }
                    $html .="<br><a href='?action=display-touite&id_touite={$row['id_touite']}'>Voir plus</a>";
                    $html .= '</div>';
                    $html .= '<div class="actions">
                                <button id = "like">Like</button>
                                <button id = "dislike">Dislike</button>
                                <button>Retouite</button>
                            </div>
                        </div>';
                }
            }
        }
        return $html;
    }

}