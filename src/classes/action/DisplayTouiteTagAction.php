<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\action\FeedAction;
use PDO;

class DisplayTouiteTagAction extends Action
{
    /**
     * @return String: la page qui contient l'affichage des touites avec le tag voulu
     * @throws \Exception
     */
    public function execute(): string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;

        //gestion de la pagination
        $limite = 10;
        $_GET['page'] = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $page = $_GET['page'];

        $decalage = ($page - 1) * $limite;

        $req = $bdd->prepare("SELECT * FROM TOUITE order by dateTouite desc LIMIT :limite OFFSET :decalage");
        $req->bindValue(":limite", $limite, PDO::PARAM_INT);
        $req->bindValue(":decalage", $decalage, PDO::PARAM_INT);

        //afficher les touites de l'utilisateur
        $html = "";
        $requete = $bdd->prepare("select distinct UTILISATEUR.prenomUtil, UTILISATEUR.nomUtil, TOUITE.id_touite, TOUITE.texte, TOUITE.dateTouite, UTILISATEUR.emailUtil
                                        from TOUITE, TAG, TOUITEPARTAG, UTILISATEUR, ATOUITE 
                                        where libelleTag = :libelle
                                        and TOUITE.id_touite=TOUITEPARTAG.id_touite
                                        and tag.id_tag=TOUITEPARTAG.id_tag
                                        and ATOUITE.emailUtil = UTILISATEUR.emailUtil
                                        and ATOUITE.id_touite = TOUITE.id_touite
                                        order by TOUITE.dateTouite desc;");
        $requete->bindValue(":libelle", $_GET['libelleTag']);

        //afficher les touites associés au tag
        $result = $requete->execute();
        $emailUtil = "";
        $verif=true;
        if (isset($_SESSION['user'])) {
            $user = unserialize($_SESSION['user']);
            $emailUtil = $user->__get('email');
        } else {
            $verif = false;
        }
        if ($result) {
            $i=0;
            while ($row = $requete->fetch()) {
                //
                $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                $html .= '<div class="author">' . "<a href='?action=display-touite-user&emailUtil={$row['emailUtil']}'>" . $row['prenomUtil'] . ' ' . $row['nomUtil'] . '</a></div>';
                if (FeedAction::estMonTouite($row['id_touite'])) {
                    $html .= '<a href="?action=supprimer-touite&id=' . $row['id_touite'] . '&page=' . $_GET['page'] . '&displayTag=' . $_GET["libelleTag"] . '"><button id="delete">Supprimer</button></a>';
                } else {
                    if ($verif == false) {
                        $html .= "<a href='?action=sign-in'><button id='follow'>Suivre</button></a>";
                    } elseif (!SuivreUtilAction::connaitreSuivi($emailUtil, $row['emailUtil'])) {
                        $html .= "<a href='?action=follow-user&emailSuivi={$row['emailUtil']}&display=displaytouitetag&tag={$_GET["libelleTag"]}'><button id='follow'>Suivre</button></a>";
                    } elseif (SuivreUtilAction::connaitreSuivi($emailUtil, $row['emailUtil'])) {
                        $html .= "<a href='?action=unfollow-user&emailSuivi={$row['emailUtil']}&display=displaytouitetag&tag={$_GET["libelleTag"]}'><button id='grayedFollow'>Ne plus suivre</button></a>";
                    }
                }
                   $html .= "</span>";
                $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                $html .= '<div class="content">' . $row['texte'] . '</div>';

                //afficher les tags du touite
                $html .= '<div class="tags">';
                $req3 = $bdd->prepare("SELECT * FROM TAG NATURAL JOIN TOUITEPARTAG WHERE id_touite = :idTouite");
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
                //permet d'afficher plus d'informations sur le touite
                $html .="<br><a href='?action=display-touite&id_touite={$row['id_touite']}'>Voir plus</a>";
                $html .= '</div>';
                $html .= '<div class="actions">';
                if (FeedAction::connaitreLikeDislike($row['id_touite'])[0]==0) {
                    $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&libelleTag=' . $_GET['libelleTag'] .'"><button id = "like">Like</button></a>';
                } else {
                    $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&libelleTag=' . $_GET['libelleTag'] .'"><button id = "grayed">Retirer</button></a>';
                }
                if (FeedAction::connaitreLikeDislike($row['id_touite'])[1]==0) {
                    $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&libelleTag=' . $_GET['libelleTag'] .'"><button id = "dislike">Dislike</button></a>';
                } else {
                    $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&libelleTag=' . $_GET['libelleTag'] .'"><button id = "grayed">Retirer</button></a>';
                }
                $html .= '
                            </div>
                        </div>';
                $i++;
            }
            if ($i == 0){
                header('Location:?action=feed&page=1');
            }
        }
        $html .= FeedAction::genererPagination($page, 'display-touite-tag');
        return $html;
    }

}