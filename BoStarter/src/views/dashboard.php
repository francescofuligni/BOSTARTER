<?php
// Inizia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include il file di autenticazione
$authPath = __DIR__ . '/../config/Authentication.php';
if (file_exists($authPath)) {
    require_once $authPath;
}

// Verifica l'autenticazione
$auth = new Authentication();
$auth->validateAuthToken();

// Includi la navbar solo se autenticato
require_once __DIR__ . '/components/navbar.php';

// Include il controller per la dashboard
require_once __DIR__ . '/../controllers/DashboardController.php';
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BoStarter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="jumbotron">
            <h1 class="display-4">Benvenuto nella tua Dashboard, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p class="lead">Scopri i progetti aperti e inizia a finanziare quelli che ti interessano.</p>
            <hr class="my-4">
        </div>
        
        <h2 class="mb-4">Progetti aperti</h2>
        
        <div class="row">
            <?php if (empty($openProjects)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Non ci sono progetti attivi al momento.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($openProjects as $project): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <?php if (!empty($project['immagine'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($project['immagine']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($project['nome']); ?>" 
                                     style="height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light text-muted d-flex align-items-center justify-content-center" 
                                     style="height: 150px;">
                                    <small>Nessuna immagine</small>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2"><?php echo htmlspecialchars($project['nome']); ?></h5>
                                <p class="card-text mb-3 small text-muted">
                                    <?php echo nl2br(htmlspecialchars(substr($project['descrizione'], 0, 100))); ?>
                                    <?php echo (strlen($project['descrizione']) > 100) ? '...' : ''; ?>
                                </p>
                                <a href="/project-details?name=<?php echo urlencode($project['nome']); ?>" class="btn btn-outline-primary btn-sm mt-auto">Visualizza</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>