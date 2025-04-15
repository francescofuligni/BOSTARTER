<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// includdi il controller e il path per autenticarla (è una rotta protetta)
$controllerPath = __DIR__ . '/../controllers/CreatorDashboardController.php';
$authPath = __DIR__ . '/../config/Authentication.php';
if (file_exists($controllerPath)) {
    require_once $controllerPath;

}
if (file_exists($authPath)) {
    require_once $authPath;
}
$auth = new Authentication();
$auth->validateAuthToken();
// la metto sotto perchè se non è autenticata non la faccio nemmeno caricare
require_once __DIR__ . '/components/navbar.php';

?>


<!DOCTYPE html>
<html lang="en">
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

        <!-- Forse, più che mostrare i progetti aperti, metterei un filtro (aperti/chiusi/tutti) -->   

        <!-- Aggiungi qui il contenuto specifico della dashboard del creatore: BOTTONE CREA PROGETTO -->   
        <!-- Analogamente per la dashboard dell'amministratore: BOTTONE MODIFICA COMPETENZE -->
       
    </div>
</body>
</html>