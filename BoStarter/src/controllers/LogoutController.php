<?php

require_once __DIR__ . '/../models/User.php';

if (session_status() == PHP_SESSION_NONE) session_start();

$userModel = new User(null); // Non serve connessione al db per cancellare il token

$userModel->logout();
header("Location: /home");
exit();
?>
