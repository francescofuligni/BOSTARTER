<?php

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
$router->post('/dashboard', 'controllers/DashboardController.php');
$router->post('/create-project', 'controllers/CreateProjectController.php');