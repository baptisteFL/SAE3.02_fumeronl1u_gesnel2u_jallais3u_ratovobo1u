<?php

namespace iutnc\touiteur\action;

require_once "vendor/autoload.php";

class Like extends Action {

    public function execute(): string
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // vérifie si un utilisateur est connecté
            if(isset($_SESSION['user'])){
                try{
                    $connexion = new PDO('mysql:host=localhost; dbname=carent; charset=utf8','root','');
                } catch(Exception $e){
                    die('erreur :'.$e->getMessage());
                }
                $idTouite = $_POST['id_touite'];
                // On récupère l'ancienne note
                $requeteNote = "SELECT note from TOUITE WHERE id_touite = ?";
                $note = $connexion->prepare($requeteNote);
                $note->bindParam(1, $idTouite);
                $note->execute();
                $noteValue = $note->fetchColumn();

                // Update de la note dans la bdd
                $sql = "UPDATE touite SET note = ? + 1 WHERE id_touite = ?";
                $update = $connexion->prepare($note);
                $update->bindParam(1, $noteValue);
                $update->bindParam(2, $idTouite);
                $update->execute();
                // mettre à jour la note sur la page main

            } else {
                // redirigé l'utilisateur vers la page de connection si il n'est pas connecté
                header('Location :?action=signin');
                exit;
            }
        }
        return  " ";
    }
}