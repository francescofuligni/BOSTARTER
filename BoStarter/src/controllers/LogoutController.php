<?php
require_once __DIR__ . '/../models/User.php';

// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) session_start();

$userModel = new User(null); // Non serve connessione al db per cancellare il token
$userModel->logout();
?>
