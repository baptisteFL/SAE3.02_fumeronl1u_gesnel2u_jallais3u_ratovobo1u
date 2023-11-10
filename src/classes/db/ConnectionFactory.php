<?php

namespace iutnc\touiteur\db;

use Exception;
use PDO;

require_once "vendor/autoload.php";
class ConnectionFactory{

    private static $configTab = [];
    public static $bdd;


    /**
     * Méthode permet de set la config depuis un paramètre
     * @param $file
     * @return void
     */

    public static function setConfig($file){
        self::$configTab = @parse_ini_file($file);

    }

    /**
     * Méthode qui permet de se connecter à la base de donnée
     * @return void
     */

    public static function makeConnection(){
        // On se connecte à la base s'il y a une fichier de config et sinon on se connecte à la base de donnée par défaut
        try{
            self::setConfig("db.config.ini");
            // afiche le driver nécessaire pour la connexion
            $bdd = @new PDO(self::$configTab['dsn'], self::$configTab['user'], self::$configTab['password']);
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$bdd = $bdd;
        }catch(Exception $e){
            echo $e->getMessage();
            $bdd = new PDO("mysql:host=localhost;dbname=sae", "root", "");
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$bdd = $bdd;
        }
    }

}