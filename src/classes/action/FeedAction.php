<?php

namespace iutnc\touiteur\action;

use DateTime;
use iutnc\touiteur\db\ConnectionFactory;
use Exception;

require_once "vendor/autoload.php";

class FeedAction extends Action
{

    public function execute(): string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM touite order by dateTouite desc");
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
                        $html .= '<div class="actions" id="follow"><button>Suivre</button></div>
                    </span>';
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
        }
    }
}