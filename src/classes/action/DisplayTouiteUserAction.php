<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\db\ConnectionFactory;

class DisplayTouiteUserAction extends Action
{
        public function execute() : string
        {
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            //afficher les touites de l'utilisateur a partir de son mail
            $html = "";
            $requete = $bdd->prepare("SELECT DISTINCT utilisateur.prenomUtil, utilisateur.nomUtil, touite.id_touite, touite.texte, touite.datetouite, utilisateur.emailUtil 
                                            FROM touite natural join atouite natural join utilisateur 
                                            WHERE utilisateur.emailUtil = :emailUtil ORDER BY touite.datetouite DESC");
            $requete->bindValue(":emailUtil", $_GET['emailUtil']);
            $result = $requete->execute();
            if($result){
                while($row = $requete->fetch()){
                    $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                    $html .= '<div class="author">'. "<a href='?action=display-touite-user&emailUtil={$row['emailUtil']}'>". $row['prenomUtil'] .' '. $row['nomUtil'] .'</a></div>';
                    $html .= '<div class="actions" id="follow"><button>Suivre</button></div>
                    </span>';
                    $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                    $html .= '<div class="content">' . $row['texte'] . '</div>';

                    //afficher les tags du touite
                    $html .= '<div class="tags">';
                    $req3 = $bdd->prepare("SELECT * FROM tag natural join touitepartag where id_touite = :idTouite");
                    $req3->bindValue(":idTouite", $row['id_touite']);
                    $result3 = $req3->execute();
                    if ($result3) {
                        while ($row3 = $req3->fetch()) {
                            $html .= '<p class="trending">'."<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>".'#' . $row3['libelleTag'] . '<p id="numberTweet" class="trending">' . FeedAction::calculerNombreTouiteParTag($row3['id_tag']) . '</a></p></p>';
                        }
                    }
                    //permet d'afficher plus d'informations sur le touite
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
            return $html;
        }

}