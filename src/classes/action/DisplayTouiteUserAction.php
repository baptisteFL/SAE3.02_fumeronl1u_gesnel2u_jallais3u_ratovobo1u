<?php

namespace iutnc\touiteur\action;


use iutnc\touiteur\action\FeedAction;
use iutnc\touiteur\db\ConnectionFactory;
use PDO;

class DisplayTouiteUserAction extends Action
{
        public function execute() : string
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

            //afficher les touites de l'utilisateur a partir de son mail
            $html = "";
            $requete = $bdd->prepare("SELECT DISTINCT utilisateur.prenomUtil, utilisateur.nomUtil, touite.id_touite, touite.texte, touite.datetouite, utilisateur.emailUtil 
                                            FROM touite natural join atouite natural join utilisateur 
                                            WHERE utilisateur.emailUtil = :emailUtil ORDER BY touite.datetouite DESC");
            $requete->bindValue(":emailUtil", $_GET['emailUtil']);
            $result = $requete->execute();
            if($result){
                if (isset($_SESSION['user'])) {
                    $user = unserialize($_SESSION['user']);
                    $emailUtil = $user->__get('email');
                } else {
                    header('Location:?action=sign-in');
                }
                while($row = $requete->fetch()){
                    $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                    $html .= '<div class="author">'. "<a href='?action=display-touite-user&emailUtil={$row['emailUtil']}'>". $row['prenomUtil'] .' '. $row['nomUtil'] .'</a></div>';
                    if (FeedAction::estMonTouite($row['id_touite'])) {
                        $html .= '<a href="?action=supprimer-touite&id=' . $row['id_touite'] . '&page=' . $_GET['page'] . '"><button id="delete">Supprimer</button></a>';
                    } else {
                        if (!SuivreUtilAction::connaitreSuivi($emailUtil, $row['emailutil'])) {
                            $html .= "<a href='?action=follow-user&emailSuivi={$row['emailutil']}&display=displaytouiteuser&user={$_GET['emailUtil']}&page={$page}'><button id='follow'>Suivre</button></a>";
                        }
                        //si on suit l'utilisateur on peut unfollow
                        if (SuivreUtilAction::connaitreSuivi($emailUtil, $row['emailutil'])) {
                            $html .= "<a href='?action=unfollow-user&emailSuivi={$row['emailutil']}&display=displaytouiteuser&user={$_GET['emailUtil']}&page={$page}'><button id='grayedFollow'>Ne plus suivre</button></a>";
                        }
                    }
                    $html .= "</span>";
                    $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                    $html .= '<div class="content">' . $row['texte'] . '</div>';

                    //afficher les tags du touite
                    $html .= '<div class="tags">';
                    $req3 = $bdd->prepare("SELECT * FROM tag natural join touitepartag where id_touite = :idTouite");
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
                    $html .= '<div class="actions">
                                <button id = "like">Like</button>
                                <button id = "dislike">Dislike</button>
                                <button>Retouite</button>
                            </div>
                        </div>';
                }
            }
            $html .= FeedAction::genererPagination($page, 'display-touite-user', $_GET['emailUtil']);

            return $html;
        }
}