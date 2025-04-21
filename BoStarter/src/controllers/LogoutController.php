<?php
require_once __DIR__ . '/../models/User.php';

// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) session_start();

$user = new User(null); // Non serve db per cancellare il token
$user->logout();
?>
