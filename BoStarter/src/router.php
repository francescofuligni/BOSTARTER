<?php
// inizia la sessione
session_start();

// definisco le rotte
$routes = [
    '/' => 'views/home.php',
    '/login' => 'views/login.php',
    '/admin-login' => 'views/admin-login.php',
    '/register' => 'views/register.php',
    '/logout' => 'controllers/LogoutController.php',
    '/dashboard' => 'views/dashboard.php',
    '/admin-dashboard' => 'views/admin-dashboard.php',
    '/creator-dashboard' => 'views/creator-dashboard.php',

];

// Prende l'URI richiesta
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

//se si prova a inserire index.php nella barra degli indirizzi
// viene reindirizzato alla home
if ($uri == '/index.php') {
    $uri = '/';
}

// se rotta esiste, includi il file corrispondente
// se rotta non esiste, reindirizza alla home
if (array_key_exists($uri, $routes)) {
    require __DIR__ . '/' . $routes[$uri];
} else {

    header('Location: /');
    exit;
}
?>