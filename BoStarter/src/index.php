<?php
define('BASE_PATH', __DIR__);

// Avvia la sessione
session_start();

// Istanzia il router
require_once __DIR__ . '/models/Router.php';
$router = new Router();

require_once __DIR__ . '/routes.php';
$router->resolve($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
?>