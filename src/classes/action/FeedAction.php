<?php

namespace iutnc\touiteur\action;

use DateTime;
use iutnc\touiteur\db\ConnectionFactory;
use Exception;
use PDO;

require_once "vendor/autoload.php";

class FeedAction extends Action
{

    public function execute(): string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;


        $limite = 10;
        $_GET['page'] = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $page = $_GET['page'];

        $decalage = ($page - 1) * $limite;

        $req = $bdd->prepare("SELECT * FROM touite order by dateTouite desc LIMIT :limite OFFSET :decalage");
        $req->bindValue(":limite", $limite, PDO::PARAM_INT);
        $req->bindValue(":decalage", $decalage, PDO::PARAM_INT);
        $html = "";
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            try {
                $result = $req->execute();
                if ($result) {
                    while ($row = $req->fetch()) {
                        $html .= '<div class="tweet">
                                    <span id="titleTweet"> ';
                        $req2 = $bdd->prepare("SELECT * FROM utilisateur natural join atouite where id_touite = :idTouite");
                        $req2->bindValue(":idTouite", $row['id_touite']);
                        $result2 = $req2->execute();
                        if ($result2) {
                            while ($row2 = $req2->fetch()) {
                                $html .= '<div class="author">' . "<a href='?action=display-touite-user&emailUtil={$row2['emailUtil']}'>" . $row2['prenomUtil'] . ' ' . $row2['nomUtil'] . "</a>" . '</div>';
                            }
                        }
                        $html .= "<div class='actions' id='follow'><button><a href='?action=follow-user&emailSuivi={$row['emailUtil']}'>Suivre</a></button></div>
                    </span>";
                        if($this->estMonTouite($row['id_touite'])){
                            $html .= '<div class="actions" id="delete"><a href="?action=supprimer-touite&id=' . $row['id_touite'] . '&page=' . $_GET['page'] . '"><button>Supprimer</button></a></div>';
                        }
                        $html .= '<div class="timestamp">' . "Il y a " . $this->calculerDepuisQuand($row['id_touite']) . '</div>';
                        $html .= '<div class="content">' . $row['texte'] . '</div>';
                        $html .= '<div class="tags">';
                        $req3 = $bdd->prepare("SELECT * FROM tag natural join touitepartag where id_touite = :idTouite");
                        $req3->bindValue(":idTouite", $row['id_touite']);
                        $result3 = $req3->execute();
                        if ($result3) {
                            while ($row3 = $req3->fetch()) {
                                $html .= '<p class="trending">' . "<a href='?action=display-touite-tag&libelleTag={$row3['libelleTag']}'>" . '#' . $row3['libelleTag'] . '</a><p id="numberTweet" class="trending">' . $this->calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                            }
                        }
                        //permet d'afficher plus d'informations sur le touite
                        $html .="<br><a href='?action=display-touite&id_touite={$row['id_touite']}'>Voir plus</a>";
                        $html .= '</div>';
                        $html .= '<div class="actions">
        <a href="?action=like&id=' . $row['id_touite'] . '"><button id = "like">Like</button></a>
        <a href="?action=dislike&id=' . $row['id_touite'] . '"><button id = "dislike">Dislike</button></a>
        <button>Retouite</button>
    </div>
</div>';
                    }
                }
                $html .= $this->genererPagination($page);
            } catch
            (Exception $e) {
                $html .= "<br> Erreur !<br>";
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
        $req = $bdd->prepare("SELECT * FROM touitepartag WHERE id_tag = :idTag");
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
        $req = $bdd->prepare("SELECT * FROM touite WHERE id_touite = :idTouite");
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
        }else{
            return "0 secondes";
        }
    }

    public static function genererPagination($page)
    {
        $html = "";
        $html .= '<div class="pagination">';
        if ($page != 1) {
            $html .= '<a id="lefta" href="?action=feed&page=' . ($page - 1) . '"><</a>';
        } else {
            $html .= '<a id="lefta" href="?action=feed&page=' . $page . '"><</a>';
        }
        $html .= '<p>Page '. $page . '</p>';
        if($page < self::calculerNombrePage()) {
            $html .= '<a id="righta" href="?action=feed&page=' . ($page + 1) . '">></a>';
        } else {
            $html .= '<a id="righta" href="?action=feed&page=' . $page . '">></a>';
        }
        $html .= '</div>';
        return $html;
    }

    public static function estMonTouite($id){
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM atouite WHERE id_touite = :idTouite");
        $req->bindValue(":idTouite", $id);
        $result = $req->execute();
        if($result){
            while($row = $req->fetch()){
                if(isset($_SESSION['user'])){
                    $user = unserialize($_SESSION['user']);
                    if($user->__get('email') == $row['emailUtil']){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function calculerNombrePage()
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT count(*) FROM touite");
        $result = $req->execute();
        $nombreTouite = $req->fetchColumn();
        return ceil($nombreTouite / 10);
    }
}