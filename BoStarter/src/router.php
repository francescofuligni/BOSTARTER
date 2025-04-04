<?php
// Start session
session_start();

// Define routes
$routes = [
    '/' => 'views/home.php',
    '/login' => 'views/login.php',
    '/admin-login' => 'views/admin-login.php',
    '/register' => 'views/register.php',
    '/logout' => 'controllers/logout.php',
    '/dashboard' => 'views/dashboard.php',

];

// Get the current URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle the index.php case
if ($uri == '/index.php') {
    $uri = '/';
}

// Check if route exists
if (array_key_exists($uri, $routes)) {
    require __DIR__ . '/' . $routes[$uri];
} else {
    // Route not found, redirect to home
    header('Location: /');
    exit;
}
?>