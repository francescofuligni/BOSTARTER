<?php
// Inizializzazione sessione
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determina il percorso corrente
$currentPath = $_SERVER['REQUEST_URI'];

// Controlla se la pagina corrente è una pagina di login o registrazione
$isAuthPage = strpos($currentPath, '/login') !== false || strpos($currentPath, '/admin-login') !== false || strpos($currentPath, '/register') !== false;

// Determina il link della dashboard in base al tipo di utente
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'creator') {
        $dashboardLink = '/creator-dashboard';
    } elseif ($_SESSION['user_type'] === 'admin') {
        $dashboardLink = '/dashboard';  // TODO: DA CAMBIARE CON admin-dashboard
    } else {
        $dashboardLink = '/dashboard';
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="/">BoStarter</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (!$isAuthPage): ?>
                <ul class="navbar-nav ml-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $dashboardLink ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">Esci</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Accedi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">Registrati</a>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>