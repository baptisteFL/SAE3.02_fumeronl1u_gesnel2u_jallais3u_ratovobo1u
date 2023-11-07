<?php

session_start();

use iutnc\touiteur\dispatch\Dispatcher;

require_once "vendor/autoload.php";

$dispatcher = new Dispatcher();
$dispatcher->run();

