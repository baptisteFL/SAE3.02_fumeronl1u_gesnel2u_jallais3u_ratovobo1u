<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;
use iutnc\touiteur\db\ConnectionFactory;

require_once "vendor/autoload.php";

class TouiteAction extends Action
{
    /**
     * Permet de pulbier un touite sur le feed de touiter
     * Dans la base de donnée modifie les tables touite, atouite, tag et touitepartag
     * @return string : le touite sur le mur
     */

    public function execute(): string
    {
        $fileclient = NULL;
        $html = "";
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $html .= '
        <form method="post" action="" class="tweet" id="formTouite" enctype="multipart/form-data">
            <input type="text" name="texte" id="text" placeholder="Quoi de neuf ?" maxlength="235"><br>
            <input type="file" name="fichier" id="fichier" placeholder="choisir une image" enctype= "multipart/form-data"><br>
            <input type="submit" id="submitTouite" value="Envoyer">
        </form>
            ';
            //$html .= unserialize($_SESSION['user'])->__get('email');
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // sécurité pour éviter les injections
            if($_POST['texte'] == ""){
                header('Location:?action=touite');
                return "";
            }
            // texte doit contenir lettres chiffres caractères spéciaux, espaces, accent et #
            if(!preg_match("/^[a-zA-Z0-9àâäéèêëïîôöùûüç\'\s\#\.\,\?\!\:\;\-\(\)]+$/", $_POST['texte'])){
                header('Location:?action=touite');
                return "";
            }
            if (isset($_SESSION['user'])) {
                ConnectionFactory::makeConnection();
                $bdd = ConnectionFactory::$bdd;
                $user = unserialize($_SESSION['user']);
                $texte = $_POST['texte'];

                if (isset($_FILES["fichier"]) && $_FILES["fichier"]["error"] == 0) {

                    // Récupérer les informations sur le fichier
                    $fileName = $_FILES["fichier"]["name"];
                    $fileTmpName = $_FILES["fichier"]["tmp_name"];
                    var_dump($fileTmpName);
                    $NomUniqueFichier = uniqid() . $fileName;

                    // Déplacer le fichier vers le répertoire des sources
                    $destinationDirectory = "imagesTouite/";
                    $destinationPath = $destinationDirectory . $NomUniqueFichier;
                } else {
                    $destinationPath = NULL;
                }

                if ($destinationPath !== NULL) {
                    if (!file_exists($destinationDirectory)) {
                        mkdir($destinationDirectory, 0777, true);
                    }

                    move_uploaded_file($fileTmpName, $destinationPath);

                    // Insérer le fichier dans la base de données (exemple)

                    $reqIm = $bdd->prepare("INSERT INTO image (cheminIm, descIm) VALUES (:chemin, :desc)");
                    $reqIm->bindValue(":chemin", $destinationPath);
                    $reqIm->bindValue(":desc", $fileName);
                    $resultIm = $reqIm->execute();

                    $req = $bdd->prepare("INSERT INTO touite (id_Touite, texte, dateTouite,note, cheminIm) VALUES (:id, :texte, :date, :note, :chemin)");
                    $nouvelleId = self::trouverNouveauId();
                    $req->bindValue(":id", $nouvelleId);
                    $req->bindValue(":texte", $texte);
                    $req->bindValue(":date", date("Y-m-d H:i:s"));
                    $req->bindValue(":note", 0);
                    $req->bindValue(":chemin", $destinationPath);
                    $result = $req->execute();

                } else {
                    $req = $bdd->prepare("INSERT INTO touite (id_Touite, texte, dateTouite,note) VALUES (:id, :texte, :date, :note)");
                    $nouvelleId = self::trouverNouveauId();
                    $req->bindValue(":id", $nouvelleId);
                    $req->bindValue(":texte", $texte);
                    $req->bindValue(":date", date("Y-m-d H:i:s"));
                    $req->bindValue(":note", 0);
                    $result = $req->execute();
                }
                $req2 = $bdd->prepare("INSERT INTO atouite (emailUtil,dateTouite, id_Touite) VALUES (:emailUtil,:date, :idTouite)");
                $req2->bindValue(":emailUtil", $user->__get('email'));
                $req2->bindValue(":date", date("Y-m-d H:i:s"));
                $req2->bindValue(":idTouite", $nouvelleId);
                $result2 = $req2->execute();
                $req3 = $bdd->prepare("INSERT INTO tag (id_tag, libelleTag) VALUES (:idTag, :libelleTag)");
                $req4 = $bdd->prepare("INSERT INTO touitepartag (id_tag, id_touite) VALUES (:idTag, :idTouite)");
                $tags = self::extraireHastag($texte);
                if ($tags[0] == "") {
                    $tags = [];
                }
                foreach ($tags as $tag) {
                    $nouveauIdTag = self::trouverIdTag($tag);
                    if (self::tagExistant($tag)) {
                        $req4->bindValue(":idTag", $nouveauIdTag);
                        $req4->bindValue(":idTouite", $nouvelleId);
                        $result4 = $req4->execute();
                    } else {
                        $req3->bindValue(":idTag", $nouveauIdTag);
                        $req3->bindValue(":libelleTag", $tag);
                        $result3 = $req3->execute();
                        $req4->bindValue(":idTag", $nouveauIdTag);
                        $req4->bindValue(":idTouite", $nouvelleId);
                        $result4 = $req4->execute();
                    }
                }

                header('Location:?action=feed&page=1');
            } else {
                header('Location:?action=sign-in');
            }

        }
        return $html;
    }


    /**
     * Methode permettant de trouver l'id d'un tag
     * @param $tag
     * @return int|mixed
     */
    public static function trouverIdTag($tag)
    {
        // si le tag existe déjà, on récupère son id
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM tag WHERE libelleTag = :libelleTag");
        $req->bindValue(":libelleTag", $tag);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                return $row['id_tag'];
            }
        }
        // sinon on retourne le max + 1
        $req2 = $bdd->prepare("SELECT MAX(id_tag) FROM tag");
        $result2 = $req2->execute();
        if ($result2) {
            $row = $req2->fetch();
            return $row[0] + 1;
        }
        return 0;
    }

    /**
     * Methode permettant de donner un id unique à un touite
     * @return int|mixed
     */
    public static function trouverNouveauId()
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT MAX(id_touite) FROM touite");
        $result = $req->execute();
        if ($result) {
            $row = $req->fetch();
            return $row[0] + 1;
        }
        return 0;
    }

    /**
     * fonction qui vérifie si un tag existe dans la base de donnée
     * @param $tag
     * @return bool
     */

    public static function tagExistant($tag)
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM tag WHERE libelleTag = :libelleTag");
        $req->bindValue(":libelleTag", $tag);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                return true;
            }
        }
        return false;
    }

    /**
     * fonction qui permet d'extraire l'hashtag d'un touite
     * @param $texte
     * @return array
     */
    public static function extraireHastag($texte)
    {
        $tags = [];
        $mots = explode(" ", $texte);
        foreach ($mots as $mot) {
            if (substr($mot, 0, 1) == "#") {
                $tags[] = substr($mot, 1);
            }
        }
        if (count($tags) == 0) {
            $tags[0] = "";
        }
        return $tags;
    }
}