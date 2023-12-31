<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\db\ConnectionFactory;

class DisplayTouiteAction extends Action
{

    /**
     * Méthode qui permet d'afficher les touites sur le feed
     * @return string
     * @throws \Exception
     */
    public function execute(): string
    {

        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        //afficher un touite en detail
        $html = "";
        $requete = $bdd->prepare("SELECT DISTINCT UTILISATEUR.emailUtil, UTILISATEUR.nomUtil, UTILISATEUR.prenomUtil, TOUITE.id_touite, TOUITE.texte, TOUITE.datetouite, TOUITE.note, TOUITE.cheminIm
                                    from TOUITE, ATOUITE, UTILISATEUR where TOUITE.id_touite = :idTouite
                                                                        and UTILISATEUR.emailUtil = ATOUITE.emailUtil 
                                                                        and ATOUITE.id_touite = TOUITE.id_touite 
                                                                        order by TOUITE.datetouite desc");
        $requete->bindValue(":idTouite", $_GET['id_touite']);
        $result = $requete->execute();
        if ($result) {
            $verif = true;
            if (isset($_SESSION['user'])) {
                $user = unserialize($_SESSION['user']);
                $emailUtil = $user->__get('email');
            } else {
                $verif = false;
            }
            while ($row = $requete->fetch()) {
                $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                $html .= '<div class="author">' . "<a href='?action=display-touite-user&emailUtil={$row['emailUtil']}'>" . $row['prenomUtil'] . ' ' . $row['nomUtil'] . '</a></div>';
                if ($verif == false) {
                    $html .= "<div class='actions' id='follow'><a href='?action=sign-in'><button>Suivre</button></a>";
                } elseif (!SuivreUtilAction::connaitreSuivi($emailUtil, $row['emailUtil'])) {
                    $html .= "<a href='?action=follow-user&emailSuivi={$row['emailUtil']}&display=displaytouite&id=" . $row['id_touite'] . "'><button id='follow'>Suivre</button></a>";
                } elseif (SuivreUtilAction::connaitreSuivi($emailUtil, $row['emailUtil'])) {
                    $html .= "<a href='?action=unfollow-user&emailSuivi={$row['emailUtil']}&display=displaytouite&id=" . $row['id_touite'] . "'><button id='grayedFollow'>Ne plus suivre</button></a>";
                }

                $html .= "</span>";
                if (FeedAction::estMonTouite($row['id_touite'])) {
                    $html .= '<a href="?action=supprimer-touite&id=' . $row['id_touite'] . '&display=true"><button id="delete">Supprimer</button></a>';
                }
                $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                $html .= '<div class="content">' . $row['texte'] . '</div>';
                $html .= '<div class="note">' . "Score : " . $row['note'] . '</div>';

                // affichage de l'image s'il y en a une
                if ($row['cheminIm'] != null) {
                    // .png ou .jpg ou .jpeg ou .gif ou .bmp ou .svg
                    if (preg_match('/\.(png|jpg|jpeg|gif|bmp|svg)$/', $row['cheminIm']))
                        $html .= '<div class="media"><img src="' . $row['cheminIm'] . '" alt="image" ></div>';
                    elseif (preg_match('/\.(mp4|avi|mov|wmv|flv|mkv)$/', $row['cheminIm']))
                        $html .= '<div class="media"><video controls src="' . $row['cheminIm'] . '" alt="video" type="video/mp4"></video></div>';
                    elseif (preg_match('/\.(mp3|wav|ogg|wma|aac|flac)$/', $row['cheminIm']))
                        $html .= '<div class="media"><audio src="' . $row['cheminIm'] . '" alt="audio"></audio></div>';
                }

                //afficher les tags du touite
                $html .= '<div class="tags">';
                $req3 = $bdd->prepare("SELECT * FROM TAG natural join TOUITEPARTAG where id_touite = :idTouite");
                $req3->bindValue(":idTouite", $row['id_touite']);
                $result3 = $req3->execute();
                if ($result3) {
                    while ($row3 = $req3->fetch()) {
                        if ($row3['id_tag'] == FeedAction::obtenirTendance()) {
                            $html .= '<p class="trending">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . ' </a><p id="numberTweet" class="trending">' . FeedAction::calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                        } else {
                            $html .= '<p class="tags">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . ' </a><p id="numberTweet" class="tags">' . FeedAction::calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                        }
                    }
                }
                $html .= "<br><a href='?action=feed'>Voir moins</a>";
                $html .= '</div>';
                $html .= '<div class="actions">';
                if (FeedAction::connaitreLikeDislike($row['id_touite'])[0] == 0) {
                    $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&display=true' . '"><button id = "like">Like</button></a>';
                } else {
                    $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&display=true' . '"><button id = "grayed">Retirer</button></a>';
                }
                if (FeedAction::connaitreLikeDislike($row['id_touite'])[1] == 0) {
                    $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&display=true' . '"><button id = "dislike">Dislike</button></a>';
                } else {
                    $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&display=true' . '"><button id = "grayed">Retirer</button></a>';
                }
            }
        }
        return $html;
    }

}