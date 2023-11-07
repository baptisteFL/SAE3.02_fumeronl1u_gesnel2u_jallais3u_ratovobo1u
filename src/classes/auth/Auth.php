<?php

namespace iutnc\touiteur\auth;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\db\User;

require_once "vendor/autoload.php";

class Auth
{


    public static function authentificate(string $email, string $password): bool
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        $req = $bdd->prepare("SELECT * FROM utilisateur WHERE emailUtil = :email");
        $req->bindValue(":email", $email);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                if (password_verify($password, $row['password'])) {
                    $user = new User($row['emailUtil'], $row['password']);
                    $_SESSION['user'] = serialize($user);
                    return true;
                }
            }
        }
        throw new AuthException("L'authentification a échoué");
    }

    public static function register(string $email, string $password, string $nom, string $prenom)
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        // MDP > 10 caractères
        if (strlen($password) < 10) {
            echo "<br> Le mot de passe doit contenir au moins 10 caractères <br>";
            throw new AuthException("Le mot de passe doit contenir au moins 10 caractères");
        }
        // Email doit être unique
        $req = $bdd->prepare("SELECT * FROM utilisateur WHERE emailUtil = :email");
        $req->bindValue(":email", $email);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                if ($row['emailUtil'] == $email) {
                    echo "<br> L'email est déjà utilisé <br>";
                    throw new AuthException("L'email est déjà utilisé");
                }
            }
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $req = $bdd->prepare("INSERT INTO utilisateur(emailUtil, nomUtil, prenomUtil, password, role) VALUES (:email, :nom, :prenom, :password, 'user')");
        $req->bindValue(":email", $email);
        $req->bindValue(":password", $passwordHash);
        $req->bindValue(":nom", $nom);
        $req->bindValue(":prenom", $prenom);
        $result = $req->execute();
        if (!$result) {
            echo "<br> L'inscription a échoué <br>";
            throw new AuthException("L'inscription a échoué");
        }
    }
}