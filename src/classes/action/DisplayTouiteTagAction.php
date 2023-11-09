<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\action\FeedAction;
use PDO;

class DisplayTouiteTagAction extends Action
{
    public function execute(): string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;

        //gestion de la pagination
        $limite = 10;
        $_GET['page'] = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $page = $_GET['page'];

        $decalage = ($page - 1) * $limite;

        $req = $bdd->prepare("SELECT * FROM touite order by dateTouite desc LIMIT :limite OFFSET :decalage");
        $req->bindValue(":limite", $limite, PDO::PARAM_INT);
        $req->bindValue(":decalage", $decalage, PDO::PARAM_INT);

        //afficher les touites de l'utilisateur
        $html = "";
        $requete = $bdd->prepare("SELECT DISTINCT utilisateur.prenomUtil, utilisateur.nomUtil, touite.id_touite, touite.texte, touite.datetouite, utilisateur.emailUtil
                                        FROM touite, tag, touitepartag, utilisateur, atouite 
                                        where libelleTag= :libelle 
                                        and touite.id_touite=touitepartag.id_touite 
                                        and tag.id_tag=touitepartag.id_tag
                                        and atouite.emailUtil = utilisateur.emailUtil
                                        and atouite.id_touite = touite.id_touite
                                        ORDER BY touite.datetouite DESC;");
        $requete->bindValue(":libelle", $_GET['libelleTag']);

        //afficher les touites associés au tag
        $result = $requete->execute();
        if ($result) {
            while ($row = $requete->fetch()) {
                //
                $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                $html .= '<div class="author">' . "<a href='?action=display-touite-user&emailUtil={$row['emailUtil']}'>" . $row['prenomUtil'] . ' ' . $row['nomUtil'] . '</a></div>';
                $html .= "<div class='actions' id='follow'><a href='?action=follow-user&emailSuivi={$row['emailUtil']}'><button>Suivre</button></a></div>
                    </span>";
                $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                $html .= '<div class="content">' . $row['texte'] . '</div>';

                //afficher les tags du touite
                $html .= '<div class="tags">';
                $req3 = $bdd->prepare("SELECT * FROM tag natural join touitepartag where id_touite = :idTouite");
                $req3->bindValue(":idTouite", $row['id_touite']);
                $result3 = $req3->execute();
                if ($result3) {
                    while ($row3 = $req3->fetch()) {
                        $html .= '<p class="trending">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . '</a><p id="numberTweet" class="trending">' . FeedAction::calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
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
                $html .= '<button>Retouite</button>
                            </div>
                        </div>';
            }
        }
        $html .= FeedAction::genererPagination($page, 'display-touite-tag');
        return $html;
    }

}