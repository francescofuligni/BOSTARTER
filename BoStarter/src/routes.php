<?php

define('BASE_PATH', __DIR__);
require_once __DIR__ . '/models/Router.php';

// Avvia la sessione
session_start();

// Istanzia il router
$router = new Router();

// Definizione delle rotte
$router->get('/', 'views/home.php');
$router->get('/login', 'views/login.php');
$router->get('/admin-login', 'views/admin-login.php');
$router->get('/register', 'views/register.php');
$router->get('/dashboard', 'views/dashboard.php');
$router->get('/create-project', 'views/create-project.php');
$router->get('/logout', 'controllers/LogoutController.php');

$router->post('/login', 'controllers/LoginController.php');
$router->post('/admin-login', 'controllers/AdminLoginController.php');
$router->post('/register', 'controllers/RegisterController.php');

// Risoluzione della rotta corrente
$router->resolve($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);