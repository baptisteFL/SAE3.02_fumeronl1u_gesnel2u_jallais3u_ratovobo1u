<?php

namespace iutnc\touiteur\auth;

use iutnc\touiteur\db\ConnectionFactory;
use iutnc\touiteur\db\User;

require_once "vendor/autoload.php";

class Auth
{

    /**
     * Méthode permettant de s'authentifier
     *
     * @param string $email
     * @param string $password
     * @return bool
     * @throws AuthException
     */
    public static function authentificate(string $email, string $password): bool
    {
        // Connection à la base de données
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        // Récupération de l'utilisateur
        $req = $bdd->prepare("SELECT * FROM UTILISATEUR WHERE emailUtil = :email");
        // filter var ok sinon exception
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new AuthException("L'email n'est pas valide");
        }
        $req->bindValue(":email", $email);
        $result = $req->execute();
        if ($result) {
            while ($row = $req->fetch()) {
                // Vérification du mot de passe
                if (password_verify($password, $row['password'])) {
                    // Création de l'objet User et stockage dans la session
                    $user = new User($row['emailUtil'], $row['password']);
                    $_SESSION['user'] = serialize($user);
                    return true;
                }
            }
        }
        throw new AuthException("L'authentification a échoué");
    }

    /**
     * Méthode permettant de s'inscrire
     * @param string $email
     * @param string $password
     * @param string $nom
     * @param string $prenom
     * @return void
     * @throws AuthException
     */
    public static function register(string $email, string $password, string $nom, string $prenom)
    {
        // Connection à la base de données
        ConnectionFactory::makeConnection();
        $bdd = ConnectionFactory::$bdd;
        // MDP > 10 caractères
        if (strlen($password) < 10) {
            echo "<br> Le mot de passe doit contenir au moins 10 caractères <br>";
            throw new AuthException("Le mot de passe doit contenir au moins 10 caractères");
        }
        // Email doit être unique
        $req = $bdd->prepare("SELECT * FROM UTILISATEUR WHERE emailUtil = :email");
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
        // Hashage du mot de passe
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        // Insertion dans la base de données
        $req = $bdd->prepare("INSERT INTO UTILISATEUR(emailUtil, nomUtil, prenomUtil, password, role) VALUES (:email, :nom, :prenom, :password, 'user')");
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new AuthException("L'email n'est pas valide");
        }
        $req->bindValue(":email", $email);
        $req->bindValue(":password", $passwordHash);
        if(!preg_match("/^[a-zA-Z ]*$/",$nom)){
            throw new AuthException("Le nom n'est pas valide");
        }
        $req->bindValue(":nom", $nom);
        if(!preg_match("/^[a-zA-Z ]*$/",$prenom)){
            throw new AuthException("Le prénom n'est pas valide");
        }
        $req->bindValue(":prenom", $prenom);
        $result = $req->execute();
        // Création de l'objet User et stockage dans la session
        $_SESSION['user'] = serialize(new User($email, $passwordHash));
        if (!$result) {
            echo "<br> L'inscription a échoué <br>";
            throw new AuthException("L'inscription a échoué");
        }
    }
}