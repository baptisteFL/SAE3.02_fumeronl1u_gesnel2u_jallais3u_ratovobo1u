<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;
use iutnc\touiteur\db\ConnectionFactory;

require_once "vendor/autoload.php";

class TouiteAction extends Action
{

    public function execute(): string
    {
        $html = "";
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $html .= '
        <form method="post" action="" class="tweet" id="formTouite">
            <input type="text" name="texte" id="text" placeholder="Quoi de neuf ?" maxlength="235"><br>
            <input type="submit" id="submitTouite" value="Envoyer">
        </form>
            ';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_SESSION['user'])) {
                $user = unserialize($_SESSION['user']);
                $texte = $_POST['texte'];
                ConnectionFactory::makeConnection();
                $bdd = ConnectionFactory::$bdd;
                $req = $bdd->prepare("INSERT INTO touite (id_Touite, texte, date,note) VALUES (:id, :texte, :date, :note)");
                $nouvelleId = self::trouverNouveauId();
                $req->bindValue(":id", $nouvelleId);
                $req->bindValue(":texte", self::retirerHastag($texte));
                $req->bindValue(":date", date("Y-m-d H:i:s"));
                $req->bindValue(":note", 0);
                $result = $req->execute();
                $req2 = $bdd->prepare("INSERT INTO atouite (emailUtil,date, id_Touite) VALUES (:emailUtil,:date, :idTouite)");
                $req2->bindValue(":emailUtil", $user->__get('email'));
                $req2->bindValue(":date", date("Y-m-d H:i:s"));
                $req2->bindValue(":idTouite", $nouvelleId);
                $result2 = $req2->execute();
                $req3 = $bdd->prepare("INSERT INTO tag (id_tag, libelleTag) VALUES (:idTag, :libelleTag)");
                $req4 = $bdd->prepare("INSERT INTO touitepartag (id_tag, id_touite) VALUES (:idTag, :idTouite)");
                $tags = self::extraireHastag($texte);
                if($tags[0] == ""){
                    $tags = [];
                }
                foreach ($tags as $tag) {
                    $nouveauIdTag = self::trouverIdTag($tag);
                    if(self::tagExistant($tag)){
                        $req4->bindValue(":idTag", $nouveauIdTag);
                        $req4->bindValue(":idTouite", $nouvelleId);
                        $result4 = $req4->execute();
                    }else{
                        $req3->bindValue(":idTag", $nouveauIdTag);
                        $req3->bindValue(":libelleTag", $tag);
                        $result3 = $req3->execute();
                        $req4->bindValue(":idTag", $nouveauIdTag);
                        $req4->bindValue(":idTouite", $nouvelleId);
                        $result4 = $req4->execute();
                    }
                }
                header('Location:?action=feed');
            }else{
                $html .= "<br> Vous n'êtes pas connecté !<br>";
            }

        }
        return $html;
    }

    public static function trouverIdTag($tag){
        // si le tag existe déjà, on récupère son id
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM tag WHERE libelleTag = :libelleTag");
        $req->bindValue(":libelleTag", $tag);
        $result = $req->execute();
        if($result){
            while($row = $req->fetch()){
                return $row['id_tag'];
            }
        }
        // sinon on retourne le max + 1
        $req2 = $bdd->prepare("SELECT MAX(id_tag) FROM tag");
        $result2 = $req2->execute();
        if($result2){
            $row = $req2->fetch();
            return $row[0]+1;
        }
        return 0;
    }

    public static function trouverNouveauId(){
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT MAX(id_touite) FROM touite");
        $result = $req->execute();
        if($result){
            $row = $req->fetch();
            return $row[0]+1;
        }
        return 0;
    }

    public static function tagExistant($tag){
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM tag WHERE libelleTag = :libelleTag");
        $req->bindValue(":libelleTag", $tag);
        $result = $req->execute();
        if($result){
            while($row = $req->fetch()){
                return true;
            }
        }
        return false;
    }

    public static function extraireHastag($texte){
        $tags = [];
        $mots = explode(" ", $texte);
        foreach($mots as $mot){
            if(substr($mot, 0, 1) == "#"){
                $tags[] = substr($mot, 1);
            }
        }
        return $tags;
    }

    public static function retirerHastag($texte){
        $mots = explode(" ", $texte);
        $nouveauTexte = "";
        foreach($mots as $mot){
            if(substr($mot, 0, 1) != "#"){
                $nouveauTexte .= $mot . " ";
            }
        }
        return $nouveauTexte;
    }
}