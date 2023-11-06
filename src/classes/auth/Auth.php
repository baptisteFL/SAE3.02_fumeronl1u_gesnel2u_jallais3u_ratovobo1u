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
        $req = $bdd->prepare("SELECT * FROM user WHERE email = :email");
        $req->bindValue(":email", $email);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                if (password_verify($password, $row['passwd'])) {
                    $user = new User($row['email'], $row['passwd'], $row['role']);
                    echo "<br> Authentification réussie <br>";
                    $_SESSION['user'] = serialize($user);
                    return true;
                }
            }
        }
        throw new AuthException("L'authentification a échoué");
    }

    public static function register(string $email, string $password)
    {
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        // MDP > 10 caractères
        if (strlen($password) < 10) {
            echo "<br> Le mot de passe doit contenir au moins 10 caractères <br>";
            throw new AuthException("Le mot de passe doit contenir au moins 10 caractères");
        }
        // Email doit être unique
        $req = $bdd->prepare("SELECT * FROM user WHERE email = :email");
        $req->bindValue(":email", $email);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                if ($row['email'] == $email) {
                    echo "<br> L'email est déjà utilisé <br>";
                    throw new AuthException("L'email est déjà utilisé");
                }
            }
        }

        $passwordHash = crypt(password_hash($password, PASSWORD_DEFAULT), "deefy");
        $req = $bdd->prepare("INSERT INTO user(email, passwd, role) VALUES (:email, :password, '1')");
        $req->bindValue(":email", $email);
        $req->bindValue(":password", $passwordHash);
        $result = $req->execute();
        if (!$result) {
            echo "<br> L'inscription a échoué <br>";
            throw new AuthException("L'inscription a échoué");
        }
    }
}