<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\db\ConnectionFactory;

class DisplayTouiteTagAction extends Action
{
    public function execute(): string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        //afficher les touites de l'utilisateur
        $html = "";
        $requete = $bdd->prepare("SELECT DISTINCT utilisateur.prenomUtil, utilisateur.nomUtil, touite.id_touite, touite.texte, touite.date 
                                        FROM touite, tag, touitepartag, utilisateur, atouite 
                                        where libelleTag= :libelle 
                                        and touite.id_touite=touitepartag.id_touite 
                                        and tag.id_tag=touitepartag.id_tag
                                        and atouite.emailUtil = utilisateur.emailUtil
                                        and atouite.id_touite = touite.id_touite;");
        $requete->bindValue(":libelle", $_GET['libelleTag']);

        //afficher les touites associés au tag
        $result = $requete->execute();
        if ($result) {
            while ($row = $requete->fetch()) {
                //
                $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                $html .= '<div class="author">' . "<a href='?action=display-touite-user&emailUtil={$row['emailUtil']}'>" . $row['prenomUtil'] . ' ' . $row['nomUtil'] . '</a></div>';
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
                        $html .= '<p class="trending">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . '<p id="numberTweet" class="trending">' . FeedAction::calculerNombreTouiteParTag($row3['id_tag']) . '</a></p></p>';
                    }
                }
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