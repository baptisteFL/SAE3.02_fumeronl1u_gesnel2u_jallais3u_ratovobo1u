<?php

namespace iutnc\touiteur\action;

require_once "vendor/autoload.php";

/**
 * Class abstraite afin d'implémenter les actions
 */

abstract class Action {

    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    /**
     * Constructeur permettant de récupérer les informations du serveur
     */
    public function __construct(){
        
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }
    
    abstract public function execute() : string;
    
}
