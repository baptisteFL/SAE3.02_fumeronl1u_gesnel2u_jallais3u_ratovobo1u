<?php

namespace iutnc\touiteur\action;

use DateTime;
use Exception;
use iutnc\touiteur\db\ConnectionFactory;
use PDO;

require_once "vendor/autoload.php";

class FeedAction extends Action
{

    /**
     * @return string : affiche la page principal de l'application "feed" avec les touites dans du plus récent au plus ancien
     */
    public function execute(): string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $emailUtil = "";

        $verif=true;
        if (isset($_SESSION['user'])) {
            $user = unserialize($_SESSION['user']);
            $emailUtil = $user->__get('email');
        } else {
            $verif=false;
        }
        $limite = 10;
        $_GET['page'] = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $page = $_GET['page'];

        $decalage = ($page - 1) * $limite;

        $req = $bdd->prepare("SELECT * FROM TOUITE ORDER BY DATETOUITE DESC LIMIT :limite OFFSET :decalage");
        $req->bindValue(":limite", $limite, PDO::PARAM_INT);
        $req->bindValue(":decalage", $decalage, PDO::PARAM_INT);
        $html = "";
        $mail = "";
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            try {
                $result = $req->execute();
                if ($result) {
                    while ($row = $req->fetch()) {
                        $html .= '<div class="tweet">
                                    <span id="titleTweet"> ';
                        $req2 = $bdd->prepare("select * from UTILISATEUR natural join ATOUITE where ID_TOUITE = :idTouite");
                        $req2->bindValue(":idTouite", $row['id_touite']);
                        $result2 = $req2->execute();
                        if ($result2) {
                            while ($row2 = $req2->fetch()) {
                                $html .= '<div class="author">' . "<a href='?action=display-touite-user&emailUtil={$row2['emailUtil']}'>" . $row2['prenomUtil'] . ' ' . $row2['nomUtil'] . "</a>" . '</div>';
                                $mail = $row2['emailUtil'];
                            }
                        }

                        /* BOUTON SUIVRE */
                        //si on ne suit pas l'utilisateur on peut follow
                        if ($verif==false){
                            $html .= "<a href='?action=sign-in'><button id='follow'>Suivre</button></a>";
                        } elseif (!self::estMonTouite($row['id_touite'])) {
                            if (!SuivreUtilAction::connaitreSuivi($emailUtil, $mail)) {
                                $html .= "<a href='?action=follow-user&emailSuivi={$mail}'><button id='follow'>Suivre</button></a>";
                            }
                            //si on suit l'utilisateur on peut unfollow
                            if (SuivreUtilAction::connaitreSuivi($emailUtil, $mail)) {
                                $html .= "<a href='?action=unfollow-user&emailSuivi={$mail}'><button id='grayedFollow'>Ne plus suivre</button></a>";
                            }
                        }
                        $html .= "</span>";
                        if (self::estMonTouite($row['id_touite'])) {
                            $html .= '<a href="?action=supprimer-touite&id=' . $row['id_touite'] . '&page=' . $_GET['page'] . '"><button id="delete">Supprimer</button></a>';
                        }
                        $html .= '<div class="timestamp">' . "Il y a " . $this->calculerDepuisQuand($row['id_touite']) . '</div>';
                        $html .= '<div class="content">' . $row['texte'] . '</div>';
                        $html .= '<div class="tags">';
                        $req3 = $bdd->prepare("SELECT * FROM TAG natural join TOUITEPARTAG where id_touite = :idTouite");
                        $req3->bindValue(":idTouite", $row['id_touite']);
                        $result3 = $req3->execute();
                        if ($result3) {
                            while ($row3 = $req3->fetch()) {
                                if ($row3['id_tag'] == self::obtenirTendance()) {
                                    $html .= '<p class="trending">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . ' </a><p id="numberTweet" class="trending">' . self::calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                                } else {
                                    $html .= '<p class="tags">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . ' </a><p id="numberTweet" class="tags">' . self::calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                                }
                            }
                        }
                        //permet d'afficher plus d'informations sur le touite
                        $html .= "<a href='?action=display-touite&id_touite={$row['id_touite']}'>Voir plus</a>";
                        $html .= '</div>';
                        $html .= '<div class="actions">';
                        if (self::connaitreLikeDislike($row['id_touite'])[0] == 0) {
                            $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&page=' . $page . '"><button id = "like">Like</b
utton></a>';
                        } else {
                            $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&page=' . $page . '"><button id = "grayed">Retirer</button></a>';
                        }
                        if (self::connaitreLikeDislike($row['id_touite'])[1] == 0) {
                            $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&page=' . $page . '"><button id = "dislike">Dislike</button></a>';
                        } else {
                            $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&page=' . $page . '"><button id = "grayed">Retirer</button></a>';
                        }
                        $html .= '
    </div>
</div>';
                    }
                }
                $html .= $this->genererPagination($page);
            } catch
            (Exception $e) {
                $html .= "<br> Erreur lors de l'affichage des touites !<br>";
            }
        }
        return $html . '
    <a id="postTweet" href="?action=touite">
    <img src="images/postTweet.png" alt="post a tweet"/>
    </a>';
    }

    /**
     * methode qui permet de calculer le nombre de touite par tag
     * @param int $idTag
     * @return int
     */

    public static function calculerNombreTouiteParTag(int $idTag): int
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM TOUITEPARTAG WHERE id_tag = :idTag");
        $req->bindValue(":idTag", $idTag);
        $result = $req->execute();
        $i = 0;
        if ($result) {
            while ($row = $req->fetch()) {
                $i++;
            }
        }
        return $i;
    }

    /**
     * methode qui permet de calculer depuis combien de temps un touite a été posté
     * @param $id_touite
     * @return string
     * @throws Exception
     */

    public static function calculerDepuisQuand($id_touite)
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM TOUITE WHERE id_touite = :idTouite");
        $req->bindValue(":idTouite", $id_touite);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                $date = $row['dateTouite'];
            }
        }
        $date = new DateTime($date);
        $now = new DateTime();
        $interval = $now->diff($date);
        if ($interval->y > 0) {
            return $interval->y . " ans";
        } else if ($interval->m > 0) {
            return $interval->m . " mois";
        } else if ($interval->d > 0) {
            return $interval->d . " jours";
        } else if ($interval->h > 0) {
            return $interval->h . " heures";
        } else if ($interval->i > 0) {
            return $interval->i . " minutes";
        } else if ($interval->s > 0) {
            return $interval->s . " secondes";
        } else {
            return "0 secondes";
        }
    }

    /**
     * Méthode qui permet de générer une pagination pour les pages qui affiche des touites
     * @param $page
     * @param $action
     * @param $emailUtil
     * @return string
     */
    public static function genererPagination($page, $action = 'feed', $emailUtil = "")
    {
        $html = '<div class="pagination">';
        switch ($action) {
            case 'display-touite-user':
                if ($page != 1) {
                    $html .= '<a id="lefta" href="?action=display-touite-user&emailUtil=' . $emailUtil . '&page=' . ($page - 1) . '"><</a>';
                } else {
                    $html .= '<a id="lefta" href="?action=display-touite-user&emailUtil=' . $emailUtil . '&page=' . $page . '"><</a>';
                }
                $html .= '<p>Page ' . $page . '</p>';
                if ($page < self::calculerNombrePage()) {
                    $html .= '<a id="righta" href="?action=display-touite-user&emailUtil=' . $emailUtil . '&page=' . ($page + 1) . '">></a>';
                } else {
                    $html .= '<a id="righta" href="?action=display-touite-user&emailUtil=' . $emailUtil . '&page=' . $page . '">></a>';
                }
                $html .= '</div>';
                break;
            case 'display-touite-tag':
                if ($page != 1) {
                    $html .= '<a id="lefta" href="?action=display-touite-tag&page=' . ($page - 1) . '"><</a>';
                } else {
                    $html .= '<a id="lefta" href="?action=display-touite-tag&page=' . $page . '"><</a>';
                }
                $html .= '<p>Page ' . $page . '</p>';
                if ($page < self::calculerNombrePage()) {
                    $html .= '<a id="righta" href="?action=display-touite-tag&page=' . ($page + 1) . '">></a>';
                } else {
                    $html .= '<a id="righta" href="?action=display-touite-tag&page=' . $page . '">></a>';
                }
                $html .= '</div>';
                break;
            case 'user-page':
                if ($page != 1) {
                    $html .= '<a id="lefta" href="?action=user-page&page=' . ($page - 1) . '"><</a>';
                } else {
                    $html .= '<a id="lefta" href="?action=user-page&page=' . $page . '"><</a>';
                }
                $html .= '<p>Page ' . $page . '</p>';
                if ($page < self::calculerNombrePage()) {
                    $html .= '<a id="righta" href="?action=user-page&page=' . ($page + 1) . '">></a>';
                } else {
                    $html .= '<a id="righta" href="?action=user-page&page=' . $page . '">></a>';
                }
                $html .= '</div>';
                break;
            default:
                if ($page != 1) {
                    $html .= '<a id="lefta" href="?action=feed&page=' . ($page - 1) . '"><</a>';
                } else {
                    $html .= '<a id="lefta" href="?action=feed&page=' . $page . '"><</a>';
                }
                $html .= '<p>Page ' . $page . '</p>';
                if ($page < self::calculerNombrePage()) {
                    $html .= '<a id="righta" href="?action=feed&page=' . ($page + 1) . '">></a>';
                } else {
                    $html .= '<a id="righta" href="?action=feed&page=' . $page . '">></a>';
                }
                $html .= '</div>';
        }
        return $html;
    }

    /**
     * Méthode qui à partir d'un id vérifie si un touite appartient à l'utilisateur connecté
     * @param $id
     * @return bool
     */
    public static function estMonTouite($id)
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM ATOUITE WHERE id_touite = :idTouite");
        $req->bindValue(":idTouite", $id);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                if (isset($_SESSION['user'])) {
                    $user = unserialize($_SESSION['user']);
                    if ($user->__get('email') == $row['emailUtil']) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Permet de savoir le nombre de la page
     * @return false|float
     */

    public static function calculerNombrePage()
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT count(*) FROM TOUITE");
        $result = $req->execute();
        $nombreTouite = $req->fetchColumn();
        return ceil($nombreTouite / 10);
    }

    /**
     * Méthode qui permet de savoir si un touite possède un like ou un dislike pour que l'application réagisse comme il le faut
     * @param $id
     * @return array|int[]
     */
    public static function connaitreLikeDislike($id)
    {
        if (isset($_SESSION['user'])) {
            $user = unserialize($_SESSION['user']);
            $email = $user->__get('email');
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            $req = $bdd->prepare("SELECT count(*) FROM ALIKE WHERE id_touite = :id AND emailUtil = :email");
            $req->bindValue(":id", $id);
            $req->bindValue(":email", $email);
            $result = $req->execute();
            $verifLike = $req->fetchColumn();

            $req = $bdd->prepare("SELECT count(*) FROM ADISLIKE WHERE id_touite = :id AND emailUtil = :email");
            $req->bindValue(":id", $id);
            $req->bindValue(":email", $email);
            $result = $req->execute();
            $verifDislike = $req->fetchColumn();

            return [$verifLike, $verifDislike];
        } else {
            return [0, 0];
        }
    }

    /**
     * Méthode qui permet de savoir le tag le plus touité
     * @return mixed
     */
    public static function obtenirTendance()
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT id_tag FROM TOUITEPARTAG GROUP BY id_tag HAVING count(id_touite) >= ALL(SELECT count(id_touite) FROM TOUITEPARTAG GROUP BY id_tag)");
        $result = $req->execute();
        $id = $req->fetchColumn();
        return $id;
    }
}