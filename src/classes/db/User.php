<?php



namespace iutnc\touiteur\db;

require_once "vendor/autoload.php";

class User
{

    private String $email;
    private String $password;
    private String $Nom;
    private String $Prenom;
    private String $role;

    public function __construct(String $email, String $password){
        $this->email = $email;
        $this->password = $password;
    }


    public function __get($name){
        if($name === "email"){
            return $this->email;
        }else if($name === "password"){
            return $this->password;
        }else if($name === "role"){
            return $this->role;
        }else if($name === "Nom") {
            return $this->Nom;
        }else if($name === "Prenom") {
            return $this->Prenom;
        }
    }

}