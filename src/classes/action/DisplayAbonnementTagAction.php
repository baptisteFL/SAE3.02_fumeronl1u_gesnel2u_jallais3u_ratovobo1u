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
        if(isset($_SESSION['user'])) {
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            //afficher un touite en detail
            $html = "";
            $user = unserialize($_SESSION['user']);
            $email = $user->__get('email');
            // On récupère l'ensemble des id_tag sur lesquels un utilisateur est abonné.
            $stock = $bdd->prepare("SELECT DISTINCT T.*, U.* FROM TOUITE AS T JOIN TOUITEPARTAG AS TP ON T.id_touite = TP.id_touite JOIN TAGSUIVI AS TS ON TP.id_tag = TS.id_tag JOIN ATOUITE AS AT ON AT.id_touite=T.id_touite JOIN UTILISATEUR AS U ON AT.emailUtil = U.emailUtil WHERE TS.emailUtil =  :email ORDER BY dateTouite DESC");
            // $requeteTest = $bdd->prepare("SELECT id_touite from tagsuivi natural join tagpartouite natural join ")
            $stock->bindValue(":email", $email);
            $stock->execute();
            // On stock dans un tableau les id_tags
                    while ($row = $stock->fetch()) {
                        $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                        $html .= '<div class="author">' . "<a href='?action=display-touite-user&nomUtil={$row['nomUtil']}'>" . $row['prenomUtil'] . ' ' . $row['nomUtil'] . '</a></div>';
                        if (FeedAction::estMonTouite($row['id_touite'])) {
                            $html .= '<a href="?action=supprimer-touite&id=' . $row['id_touite'] . '&displayAboTag=true"><button id="delete">Supprimer</button></a>';
                        } else {
                            if (!SuivreUtilAction::connaitreSuivi($email, $row['emailUtil'])) {
                                $html .= "<a href='?action=follow-user&emailSuivi={$row['emailUtil']}&display=displayabotag'><button id='follow'>Suivre</button></a>";
                            }
                            //si on suit l'utilisateur on peut unfollow
                            if (SuivreUtilAction::connaitreSuivi($email, $row['emailUtil'])) {
                                $html .= "<a href='?action=unfollow-user&emailSuivi={$row['emailUtil']}&display=displayabotag'><button id='grayedFollow'>Ne plus suivre</button></a>";
                            }
                        }
                        $html .= '</span>';
                        $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                        $html .= '<div class="content">' . $row['texte'] . '</div>';
                        $html .= '<div class="note">' . "Score : " . $row['note'] . '</div>';

                    //afficher les tags du touite
                    $html .= '<div class="tags">';
                    $req3 = $bdd->prepare("SELECT * FROM TAG natural join TOUITEPARTAG where id_touite = :idTouite");
                    $req3->bindValue(":idTouite", $row['id_touite']);
                    $result3 = $req3->execute();
                    $tag = " ";
                    if ($result3) {
                        while ($row3 = $req3->fetch()) {
                            if ($row3['id_tag'] == FeedAction::obtenirTendance()) {
                                $html .= '<p class="trending">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . ' </a><p id="numberTweet" class="trending">' . FeedAction::calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                            } else {
                                $html .= '<p class="tags">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . ' </a><p id="numberTweet" class="tags">' . FeedAction::calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                            }
                        }
                    }
                    $html .="<br><a href='?action=display-touite&id_touite={$row['id_touite']}'>Voir plus</a>";
                    $html .= '</div>';
                    $html .= '<div class="actions">';
                    if (FeedAction::connaitreLikeDislike($row['id_touite'])[0]==0) {
                        $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&tags=true"><button id = "like">Like</button></a>';
                    } else {
                        $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&tags=true"><button id = "grayed">Retirer</button></a>';
                    }
                    if (FeedAction::connaitreLikeDislike($row['id_touite'])[1]==0) {
                        $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&tags=true"><button id = "dislike">Dislike</button></a>';
                    } else {
                        $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&tags=true"><button id = "grayed">Retirer</button></a>';
                    }
                    $html .= '
                            </div>
                        </div>';
                    }

        }else  {
            header('Location:?action=sign-in');
        }
        return $html;
    }

}