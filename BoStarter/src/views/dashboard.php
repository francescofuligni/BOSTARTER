<?php
// Start session if not already started
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

// Includi il controller
require_once __DIR__ . '/../controllers/DashboardController.php';

// Includi la navbar
require_once __DIR__ . '/components/navbar.php';
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
        
        <h2 class="mb-4">Progetti Attivi</h2>
        
        <div class="row">
            <?php if (empty($activeProjects)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Non ci sono progetti attivi al momento.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($activeProjects as $project): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($project['immagine'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($project['immagine']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($project['nome']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <span>Nessuna immagine</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($project['nome']); ?></h5>
                                <p class="card-text">
                                    <?php echo nl2br(htmlspecialchars(substr($project['descrizione'], 0, 150))); ?>
                                    <?php echo (strlen($project['descrizione']) > 150) ? '...' : ''; ?>
                                </p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($project['tipo']); ?></span>
                                        <span class="badge badge-success">€ <?php echo number_format($project['budget'], 2, ',', '.'); ?></span>
                                    </div>
                                    <a href="/project-details?name=<?php echo urlencode($project['nome']); ?>" class="btn btn-primary btn-block">Visualizza Progetto</a>
                                </div>
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