<?php



namespace iutnc\touiteur\db;

require_once "vendor/autoload.php";

class User
{

    private String $email;
    private String $password;
    private String $role;

    public function __construct(String $email, String $password, String $role){
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }


    public function __get($name){
        if($name === "email"){
            return $this->email;
        }else if($name === "password"){
            return $this->password;
        }else if($name === "role"){
            return $this->role;
        }
    }

}