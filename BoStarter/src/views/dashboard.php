<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Includi il controller
require_once __DIR__ . '/../controllers/DashboardController.php'; // qui vengono creati $db e $user

// Includi la navbar
require_once __DIR__ . '/components/navbar.php';

// Usa direttamente $user e $_SESSION['user_id']
$isCreator = isset($_SESSION['user_id']) && $user->isCreator($_SESSION['user_id']);
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

        <?php if ($isCreator): ?>
            <div class="mb-4">
                <a href="/create-project" class="btn btn-success">Crea nuovo progetto</a>
            </div>
        <?php endif; ?>

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
                                    <?php echo nl2br(htmlspecialchars(substr($project['descrizione'], 0, 100))); ?>
                                    <?php echo (strlen($project['descrizione']) > 100) ? '...' : ''; ?>
                                </p>
                                <div class="mt-auto">
                                    <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#projectModal<?php echo md5($project['nome']); ?>">
                                        Dettagli
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal per i dettagli del progetto -->
                    <div class="modal fade" id="projectModal<?php echo md5($project['nome']); ?>" tabindex="-1" role="dialog" aria-labelledby="projectModalLabel<?php echo md5($project['nome']); ?>" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="projectModalLabel<?php echo md5($project['nome']); ?>">
                                <?php echo htmlspecialchars($project['nome']); ?>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($project['descrizione'])); ?></p>
                            <p><strong>Budget:</strong> € <?php echo number_format($project['budget'], 2, ',', '.'); ?></p>
                            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($project['tipo']); ?></p>
                            <p><strong>Email creatore:</strong> <?php echo htmlspecialchars($project['email_utente_creatore']); ?></p>
                            <?php if (!empty($project['immagine'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($project['immagine']); ?>" class="img-fluid" alt="Immagine progetto">
                            <?php endif; ?>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
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