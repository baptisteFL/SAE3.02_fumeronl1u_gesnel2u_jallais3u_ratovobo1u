<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\db\ConnectionFactory;

class UserPageAction extends Action
{
        public function execute() : string
        {
            ConnectionFactory::makeConnection();
            $bdd = ConnectionFactory::$bdd;
            $html = "";
            if(isset($_SESSION['user'])){
                $user = unserialize($_SESSION['user']);
            }else{
                header('Location:?action=sign-in');
                return $html;
            }
            $req = $bdd->prepare("SELECT * FROM utilisateur WHERE emailUtil = :email");
            $req->bindValue(":email", $user->__get('email'));
                try{
                    $result = $req->execute();
                    if ($result) {
                        while($row = $req->fetch()){
                            $html .= "<br> Nom : ". $row['nomUtil'] ."<br>";
                            $html .= "<br> Prenom : ". $row['prenomUtil'] ."<br>";
                            $html .= "<br> Email : ". $row['emailUtil'] ."<br>";
                        }
                    }
                    $html .= "<br> POUR VOUS <br>";
                    $user=unserialize($_SESSION['user']);
                    $email=$user->__get('email');
                    $requete = $bdd->prepare("(SELECT DISTINCT T.* , U.* FROM TOUITE AS T JOIN ATOUITE AS AT ON T.id_touite = AT.id_touite JOIN SUIVIS AS S ON AT.emailUtil = S.emailUtilSuivi JOIN UTILISATEUR AS U ON S.emailUtilSuivi = U.emailUtil WHERE S.emailUtil = :email) UNION (SELECT DISTINCT T.*, U.* FROM TOUITE AS T JOIN TOUITEPARTAG AS TP ON T.id_touite = TP.id_touite JOIN TAGSUIVI AS TS ON TP.id_tag = TS.id_tag JOIN UTILISATEUR AS U ON TS.emailUtil = U.emailUtil WHERE TS.emailUtil =  :email) ORDER BY dateTouite DESC");
                    $requete->bindValue(":email", $email);
                    $requete->execute();
                            while($row = $requete->fetch()) {
                                $html .= '<div class="tweet">
                    <span id="titleTweet"> ';
                                $html .= '<div class="author">' . "<a href='?action=display-touite-user&nomUtil={$row['nomUtil']}'>" . $row['prenomUtil'] . ' ' . $row['nomUtil'] . '</a></div>';
                                if (FeedAction::estMonTouite($row['id_touite'])) {
                                    $html .= '<a href="?action=supprimer-touite&id='. $row['id_touite'] .'&displayUser=true"><button id="delete">Supprimer</button></a>';
                                } else {
                                    if (!SuivreUtilAction::connaitreSuivi($email, $row['emailUtil'])) {
                                        $html .= "<a href='?action=follow-user&emailSuivi={$row['emailUtil']}&display=user-page'><button id='follow'>Suivre</button></a>";
                                    }
                                    //si on suit l'utilisateur on peut unfollow
                                    if (SuivreUtilAction::connaitreSuivi($email, $row['emailUtil'])) {
                                        $html .= "<a href='?action=unfollow-user&emailSuivi={$row['emailUtil']}&display=user-page'><button id='grayed'>Ne plus suivre</button></a>";
                                    }
                                }
                                $html .= "</span>";
                                $html .= '<div class="timestamp">' . "Il y a " . FeedAction::calculerDepuisQuand($row['id_touite']) . '</div>';
                                $html .= '<div class="content">' . $row['texte'] . '</div>';
                                $html .= '<div class="note">' . "Score : " . $row['note'] . '</div>';

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
                                $html .= "<br><a href='?action=display-touite&id_touite={$row['id_touite']}'>Voir plus</a>";
                                $html .= '</div>';
                                $html .= '<div class="actions">';
                                if (FeedAction::connaitreLikeDislike($row['id_touite'])[0]==0) {
                                    $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&userpage=true' . '"><button id = "like">Like</button></a>';
                                } else {
                                    $html .= '<a href="?action=like&id=' . $row['id_touite'] . '&userpage=true' . '"><button id = "grayed">Retirer</button></a>';
                                }
                                if (FeedAction::connaitreLikeDislike($row['id_touite'])[1]==0) {
                                    $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&userpage=true' . '"><button id = "dislike">Dislike</button></a>';
                                } else {
                                    $html .= '<a href="?action=dislike&id=' . $row['id_touite'] . '&userpage=true' . '"><button id = "grayed">Retirer</button></a>';
                                }
                                $html .= '<button>Retouite</button>
                            </div>
                        </div>';
                            }
                            $abo = $bdd->prepare("SELECT nomUtil, prenomUtil FROM utilisateur as u join suivis as s on u.emailUtil = s.emailUtilsuivi where s.emailUtil = :email");
                            $abo->bindValue(":email", $email);
                            $abo->execute();

                    $html .= "<br> Vous suivez :<br><br>";
                    while($row4 = $abo->fetch()) {
                        $html .= $row4['nomUtil'] . " ". $row4['prenomUtil']. "<br>";
                    }

                    $suiv = $bdd->prepare("SELECT nomUtil, prenomUtil FROM utilisateur as u join suivis as s on u.emailUtil = s.emailUtilsuivi where s.emailUtilsuivi = :email");
                    $suiv->BindValue(":email", $email);
                    $suiv->execute();
                    $html .= "<br>Il vous suive :<br>";
                    while($row5 = $suiv->fetch()) {
                        $html .= $row5['nomUtil'] . " ". $row5['prenomUtil']. "<br>";
                    }


                }catch (\Exception $e){
                    $html .= "<br> Vous n'avez pas accès à cet utilisateur !<br>";
                }
            return $html;
 
        }

}