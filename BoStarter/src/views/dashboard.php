<?php
// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../controllers/DashboardController.php'; // qui vengono creati $db e $user
require_once __DIR__ . '/components/navbar.php';

// Usa direttamente $user e $_SESSION['user_id']
$isCreator = isset($_SESSION['user_id']) && $user->isCreator($_SESSION['user_id']);
$isAdmin = isset($_SESSION['user_id']) && $user->isAdmin($_SESSION['user_id']);
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
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-4">
                <button class="btn btn-primary" data-toggle="modal" data-target="#competencesModal">
                    Lista Competenze
                </button>
            </div>
        <?php endif; ?>

        <?php if ($isCreator): ?>
            <h2 class="mb-4">I tuoi progetti</h2>
            <div class="row mb-5">
                <!-- Card per creare un nuovo progetto -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center border-success" style="cursor: pointer;" onclick="window.location.href='/create-project'">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <h5 class="card-title text-success">+ Crea un progetto</h5>
                        </div>
                    </div>
                </div>

                <?php
                if ($userProjects) {
                    foreach ($userProjects as $project): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if (!empty($project['immagine'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($project['immagine']); ?>"
                                        class="card-img-top" alt="<?php echo htmlspecialchars($project['nome']); ?>"
                                        style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center"
                                        style="height: 200px;">
                                        <span>Nessuna immagine per il progetto.</span>
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
                    <?php endforeach;
                }
                ?>
            </div>
        <?php endif; ?>

        <h2 class="mb-4">Progetti aperti</h2>
        <div class="row">
            <?php if (empty($openProjects)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Non ci sono progetti aperti al momento.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($openProjects as $project): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($project['immagine'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($project['immagine']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($project['nome']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <span>Nessuna immagine per il progetto.</span>
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

                    <!-- Modale per i dettagli del progetto -->
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

                            <!-- Gallery delle foto (via stored procedure) -->
                            <div class="mb-3">
                                <strong>Galleria foto:</strong>
                                <div class="d-flex flex-wrap">
                                    <?php
                                    $photos = $projectModel->getProjectPhotos($project['nome']);
                                    if ($photos) {
                                        foreach ($photos as $img) {
                                            echo '<img src="data:image/jpeg;base64,' . base64_encode($img) . '" class="img-thumbnail m-1" style="max-width:120px;max-height:120px;" alt="Foto progetto">';
                                        }
                                    } else {
                                        echo '<span class="text-muted">Nessuna foto disponibile.</span>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Lista commenti (via stored procedure) -->
                            <div class="mb-3">
                                <strong>Commenti:</strong>
                                <ul class="list-group">
                                    <?php
                                    $comments = $projectModel->getProjectComments($project['nome']);
                                    if ($comments) {
                                        foreach ($comments as $comment) {
                                            echo '<li class="list-group-item"><strong>' . htmlspecialchars($comment['nickname']) . ':</strong> ' . htmlspecialchars($comment['testo']) . '<br><small class="text-muted">' . htmlspecialchars($comment['data']) . '</small></li>';
                                        }
                                    } else {
                                        echo '<li class="list-group-item text-muted">Nessun commento.</li>';
                                    }
                                    ?>
                                </ul>
                            </div>

                            <!-- Form per aggiungere un commento -->
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/dashboard" method="post" class="mb-2">
                                <input type="hidden" name="nome_progetto" value="<?php echo htmlspecialchars($project['nome']); ?>">
                                <div class="form-group">
                                    <label for="testo_commento_<?php echo md5($project['nome']); ?>">Lascia un commento:</label>
                                    <textarea class="form-control" id="testo_commento_<?php echo md5($project['nome']); ?>" name="testo_commento" rows="2" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Invia commento</button>
                            </form>
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