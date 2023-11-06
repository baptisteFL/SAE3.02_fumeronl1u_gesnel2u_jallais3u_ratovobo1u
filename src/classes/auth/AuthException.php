<?php

namespace iutnc\touiteur\auth;

require_once "vendor/autoload.php";

class AuthException extends \Exception
{

    /**
     * @param string $string
     */
    public function __construct(string $string)
    {
    }
}