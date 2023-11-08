<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\db\ConnectionFactory;

class DisplayTouiteAction extends Action
{
        public function execute() : string
        {
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            //afficher un touite en detail
            $html = "";
            $requete = $bdd->prepare("SELECT DISTINCT utilisateur.nomUtil, utilisateur.prenomUtil, touite.id_touite, touite.texte, touite.datetouite, touite.note
                                    from touite, atouite, utilisateur where touite.id_touite = :idTouite
                                                                        and utilisateur.emailUtil = atouite.emailUtil 
                                                                        and atouite.id_touite = touite.id_touite 
                                                                        order by touite.datetouite desc");
            $requete->bindValue(":idTouite", $_GET['id_touite']);
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
            return $html;
        }

}