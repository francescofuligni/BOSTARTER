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
    <link rel="stylesheet" href="/style/dashboard.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="/js/dashboard.js"></script>
</head>
<body>
    <div class="container mt-5">
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
                    foreach ($userProjects as $project): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 project-card">
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
                                        <a href="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" class="btn btn-primary btn-block">
                                            Dettagli
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="mt-5">
        <h3>Vedi i progetti</h3>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="filterOpenProjects" onclick="filterOpenProjects()">
            <label class="form-check-label" for="filterOpenProjects">
                Visualizza solo i progetti aperti
            </label>
        </div>
            <div class="row">
                <?php
                if ($allProjects) {
                    foreach ($allProjects as $project) {
                        echo '<div class="col-md-4 mb-4">';
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
                ?>
            </div>
        </div>
        </script>
    </div>
</body>
</html>