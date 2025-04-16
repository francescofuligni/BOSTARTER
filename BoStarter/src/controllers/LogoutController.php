<?php
require_once __DIR__ . '/../models/User.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user = new User(null); // Non serve db per cancellare il token
$user->logout();
?>