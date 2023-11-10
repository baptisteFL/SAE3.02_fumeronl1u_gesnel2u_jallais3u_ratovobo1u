<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\action\FeedAction;
use iutnc\touiteur\db\ConnectionFactory;
use PDO;

class DisplayTouiteUserAction extends Action
{

    /**
     * @return string : la page qui affiche les touites d'un utilisateur voulu
     * @throws \Exception
     */
        public function execute() : string
        {
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;

            //gestion de la pagination
            $limite = 10;
            $_GET['page'] = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $page = $_GET['page'];

            $decalage = ($page - 1) * $limite;

            $req = $bdd->prepare("SELECT * FROM TOUITE ORDER BY datetouite DESC LIMIT :limite OFFSET :decalage");
            $req->bindValue(":limite", $limite, PDO::PARAM_INT);
            $req->bindValue(":decalage", $decalage, PDO::PARAM_INT);

            //afficher les touites de l'utilisateur a partir de son mail
            $html = "";
            $requete = $bdd->prepare("select distinct UTILISATEUR.prenomUtil, UTILISATEUR.nomUtil, TOUITE.id_touite, TOUITE.texte, TOUITE.datetouite, UTILISATEUR.emailUtil
                                            from TOUITE natural join ATOUITE natural join UTILISATEUR 
                                            where UTILISATEUR.emailUtil = :emailUtil order by TOUITE.datetouite desc");
            $requete->bindValue(":emailUtil", $_GET['emailUtil']);
            $result = $requete->execute();
            if($result){
                $verif=true;
                if (isset($_SESSION['user'])) {
                    $user = unserialize($_SESSION['user']);
                    $emailUtil = $user->__get('email');
                } else {
                    $verif=false;
                }
                while($row = $requete->fetch()){
                    $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                    $html .= '<div class="author">'. "<a href='?action=display-touite-user&emailUtil={$row['emailUtil']}'>". $row['prenomUtil'] .' '. $row['nomUtil'] .'</a></div>';
                    if (FeedAction::estMonTouite($row['id_touite'])) {
                        $html .= '<a href="?action=supprimer-touite&id=' . $row['id_touite'] . '&page=' . $_GET['page'] . '"><button id="delete">Supprimer</button></a>';
                    } else {
                        if ($verif == false) {
                            $html .= "<a href='?action=sign-in'><button id='follow'>Suivre</button></a>";
                        } elseif (!SuivreUtilAction::connaitreSuivi($emailUtil, $row['emailUtil'])) {
                            $html .= "<a href='?action=follow-user&emailSuivi={$row['emailUtil']}&display=displaytouiteuser&user={$_GET['emailUtil']}&page={$page}'><button id='follow'>Suivre</button></a>";
                        } elseif (SuivreUtilAction::connaitreSuivi($emailUtil, $row['emailUtil'])) {
                            $html .= "<a href='?action=unfollow-user&emailSuivi={$row['emailUtil']}&display=displaytouiteuser&user={$_GET['emailUtil']}&page={$page}'><button id='grayedFollow'>Ne plus suivre</button></a>";
                        }
                    }
                    $html .= "</span>";
                    $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                    $html .= '<div class="content">' . $row['texte'] . '</div>';

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
                    //permet d'afficher plus d'informations sur le touite
                    $html .="<br><a href='?action=display-touite&id_touite={$row['id_touite']}'>Voir plus</a>";
                    $html .= '</div>';
                    $html .= '<div class="actions">';
                    if (FeedAction::connaitreLikeDislike($row['id_touite'])[0] == 0) {
                        $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&page=' . $page . '"><button id = "like">Like</button></a>';
                    } else {
                        $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&page=' . $page . '"><button id = "grayed">Retirer</button></a>';
                    }
                    if (FeedAction::connaitreLikeDislike($row['id_touite'])[1] == 0) {
                        $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&page=' . $page . '"><button id = "dislike">Dislike</button></a>';
                    } else {
                        $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&page=' . $page . '"><button id = "grayed">Retirer</button></a>';
                    }
                      $html .='      </div>
                        </div>';
                }
            }
            $html .= FeedAction::genererPagination($page, 'display-touite-user', $_GET['emailUtil']);

            return $html;
        }
}