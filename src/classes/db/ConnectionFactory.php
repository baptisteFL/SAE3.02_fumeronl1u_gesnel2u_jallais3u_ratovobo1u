<?php

namespace iutnc\touiteur\db;

require_once "vendor/autoload.php";
class ConnectionFactory{
    public static $bdd;


    public static function makeConnection(){
        $bdd = new \PDO("mysql:host=localhost;dbname=sae", "root", "");
        $bdd->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        self::$bdd = $bdd;
    }

}