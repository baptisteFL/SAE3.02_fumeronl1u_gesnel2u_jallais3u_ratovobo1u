<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;
use iutnc\touiteur\db\ConnectionFactory;
use const Exception;

require_once "vendor/autoload.php";

class FeedAction extends Action
{

    public function execute(): string
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM touite order by date desc");
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
                                $html .= '<div class="author">' . $row2['prenomUtil'] . ' ' . $row2['nomUtil'] . '</div>';
                            }
                        }
                        $html .= '<div class="actions" id="follow"><button>Suivre</button></div>
                    </span>';
                        $html .= '<div class="timestamp">' . $row['date'] . '</div>';
                        $html .= '<div class="content">' . $row['texte'] . '</div>';
                        $html .= '<div class="tags">';
                        $req3 = $bdd->prepare("SELECT * FROM tag natural join touitepartag where id_touite = :idTouite");
                        $req3->bindValue(":idTouite", $row['id_touite']);
                        $result3 = $req3->execute();
                        if ($result3) {
                            while ($row3 = $req3->fetch()) {
                                $html .= '<p class="trending">#' . $row3['libelleTag'] . '<p id="numberTweet" class="trending">' . $this->calculerNombreTouiteParTag($row3['id_tag']) . '</p></p>';
                            }
                        }
                        $html .= '</div>';
                        $html .= '<div class="actions">
        <button id = "like">Like</button>
        <button id = "dislike">Dislike</button>
        <button>Retouite</button>
    </div>
</div>

    <a id="postTweet" href="?action=touite">
    <img src="images/postTweet.png" alt="post a tweet"/>
    </a>';
                    }
                    return $html;
                }
            }catch
            (Exception $e) {
                $html .= "<br> Vous n'avez pas accès à cet utilisateur !<br>";
            }
        }
        return $html;
    }


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

}