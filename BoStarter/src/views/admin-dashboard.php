<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclusione del controller e del path di autenticazione (è una rotta protetta)
$controllerPath = __DIR__ . '/../controllers/AdminDashboardController.php';
$authPath = __DIR__ . '/../config/Authentication.php';
if (file_exists($controllerPath)) {
    require_once $controllerPath;
}
if (file_exists($authPath)) {
    require_once $authPath;
}
$auth = new Authentication();
$auth->validateAuthToken();

// Inclusione della navbar
require_once __DIR__ . '/components/navbar.php';
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Creatori - BoStarter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="jumbotron">
            <h1 class="display-4">Benvenuto nella tua Dashboard, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p class="lead">Qui puoi gestire i tuoi progetti e monitorare le tue campagne di crowdfunding.</p>
            <hr class="my-4">
            <p>DASHBOARD CREATORE IN MANUTENZIONE.</p>
        </div>

        <!-- Analogamente per la dashboard dell'amministratore: BOTTONE MODIFICA COMPETENZE -->
        <!-- FORSE UNA SOLA VISTA IN CUI IL BOTTONE CAMBIA A SECONDA DELL'UTENTE? (riuso codice, così tanto codice uguale) -->
        
    </div>
</body>
</html>
