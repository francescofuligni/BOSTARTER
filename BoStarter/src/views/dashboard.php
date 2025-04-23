<?php
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/components/navbar.php';
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BoStarter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/style/dashboard.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="/js/dashboard.js"></script>
</head>
<body>
    <div class="container mt-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <div class="jumbotron">
            <h1 class="display-4">Benvenuto nella tua Dashboard, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p class="lead">Scopri i progetti e inizia a finanziare quelli che ti interessano.</p>
        </div>

        <?php if ($isAdmin): ?>
            <div class="mb-4">
                <button class="btn btn-primary" data-toggle="modal" data-target="#competencesModal">
                    Lista Competenze
                </button>
            </div>

            <!-- Modal Competenze -->
            <div class="modal fade" id="competencesModal" tabindex="-1" role="dialog" aria-labelledby="competencesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="competencesModalLabel">Lista delle Competenze</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php
                            if ($competencesList && count($competencesList) > 0) {
                                echo '<ul class="list-group">';
                                foreach ($competencesList as $competence) {
                                    echo '<li class="list-group-item">' . htmlspecialchars($competence['nome']) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '<p class="text-muted">Nessuna competenza trovata.</p>';
                            }
                            ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isCreator): ?>
            <div class="mb-4">
                <a href="/create-project" class="btn btn-success">
                    Crea un nuovo progetto
                </a>
            </div>

            <h3>I tuoi progetti</h3>
            <div class="row mb-5">

                <?php
                if ($userProjects) {
                    foreach ($userProjects as $project) {
                        echo '<div class="col-lg-3 col-md-6 mb-4">';
                        echo '<div class="card h-100 project-card" style="cursor: pointer;" onclick="window.location.href=\'/project-detail?nome=' . urlencode($project['nome']) . '\'">';
                        if (!empty($project['immagine'])) {
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($project['immagine']) . '" class="card-img-top" alt="' . htmlspecialchars($project['nome']) . '" style="height: 200px; object-fit: cover;">';
                        } else {
                            echo '<div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">';
                            echo '<span>Nessuna immagine</span>';
                            echo '</div>';
                        }
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($project['nome']) . '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars($project['descrizione']) . '</p>';
                        echo '<span class="badge badge-'.($project['stato'] === 'APERTO' ? 'success' : 'secondary').'" style="font-size:1rem;">';
                        echo ucfirst(strtolower($project['stato']));
                        echo '</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    } else { ?>
                        <div class="col-12"><span class="text-muted">Non hai ancora creato progetti.</span></div>
                    <?php } ?>
                <?php
                ?>
            </div>
        <?php endif; ?>

        <div class="mt-5">
        <h3>Vedi tutti i progetti</h3>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="filterOpenProjects" onclick="filterOpenProjects()">
            <label class="form-check-label" for="filterOpenProjects">
                Visualizza solo i progetti aperti
            </label>
        </div>
            <?php
            echo '<div id="all-projects" class="row">';
            if ($allProjects) {
                foreach ($allProjects as $project) {
                    echo '<div class="col-lg-3 col-md-6 mb-4 project-card-container" data-status="' . strtolower($project['stato']) . '">';
                    echo '<div class="card h-100 project-card" style="cursor: pointer;" onclick="window.location.href=\'/project-detail?nome=' . urlencode($project['nome']) . '\'">';
                    // Mostra la prima foto se presente
                    if (!empty($project['immagine'])) {
                        echo '<img src="data:image/jpeg;base64,' . base64_encode($project['immagine']) . '" class="card-img-top" alt="' . htmlspecialchars($project['nome']) . '" style="height: 200px; object-fit: cover;">';
                    } else {
                        echo '<div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">';
                        echo '<span>Nessuna immagine</span>';
                        echo '</div>';
                    }
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($project['nome']) . '</h5>';
                    echo '<p class="card-text">' . htmlspecialchars($project['descrizione']) . '</p>';
                    echo '<span class="badge badge-'.($project['stato'] === 'APERTO' ? 'success' : 'secondary').'" style="font-size:1rem;">';
                    echo ucfirst(strtolower($project['stato']));
                    echo '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><span class="text-muted">Nessun progetto trovato.</span></div>';
            }
            echo '</div>';
            ?>
        </div>
    </div>
</body>
</html>
