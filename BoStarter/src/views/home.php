<?php
require_once __DIR__ . '/components/navbar.php';
require_once __DIR__ . '/../controllers/HomeController.php';
// Le variabili $expiringProjects, $topCreators, $topFunders sono ora disponibili dal controller
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoStarter - Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/style/card.css">
</head>
<body>
    <div class="container mt-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($_SESSION['info']); unset($_SESSION['info']); ?>
            </div>
        <?php endif; ?>
        <div class="jumbotron py-5">
            <h1 class="display-4">Benvenuto su BoStarter!</h1>
            <p class="lead">Una piattaforma per il crowdfunding di progetti innovativi.</p>
        </div>

        <!-- Preview Progetti in Scadenza -->
        <h3 class="mt-4">Progetti in scadenza</h3>
        <div class="row">
            <?php
            if (!empty($expiringProjects)) {
                foreach (array_slice($expiringProjects, 0, 3) as $project) {
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
                    echo '<p class="card-text">Budget mancante: ' . htmlspecialchars($project['differenza_budget']) . ' €</p>';
                    if (!empty($project['data_scadenza'])) {
                        echo '<p class="card-text"><small class="text-muted">Scade il: ' . htmlspecialchars(date('d/m/Y', strtotime($project['data_scadenza']))) . '</small></p>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><span class="text-muted">Non ci sono progetti.</span></div>';
            }
            ?>
        </div>

        <!-- Top Creators -->
        <h3 class="mt-4">Classifica dei creatori</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center" style="width: 10%;">#</th>
                    <th class="text-center" style="width: 45%;">Nickname</th>
                    <th class="text-center" style="width: 45%;">Affidabilità</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($topCreators)): ?>
                    <?php foreach (array_slice($topCreators, 0, 3) as $i => $creator): ?>
                        <tr>
                            <td class="text-center" style="width: 10%;"><?php echo $i + 1; ?></td>
                            <td class="text-center" style="width: 45%;"><?php echo htmlspecialchars($creator['nickname'] ?? ''); ?></td>
                            <td class="text-center" style="width: 45%;"><?php echo htmlspecialchars($creator['affidabilita'] ?? '') . '%'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-muted text-center">Non ci sono creatori.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Top Funders -->
        <h3 class="mt-4">Classifica dei finanziatori</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center" style="width: 10%;">#</th>
                    <th class="text-center" style="width: 45%;">Nickname</th>
                    <th class="text-center" style="width: 45%;">Totale Finanziamenti</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($topFunders)): ?>
                    <?php foreach (array_slice($topFunders, 0, 3) as $i => $funder): ?>
                        <tr>
                            <td class="text-center" style="width: 10%;"><?php echo $i + 1; ?></td>
                            <td class="text-center" style="width: 45%;"><?php echo htmlspecialchars($funder['nickname'] ?? ''); ?></td>
                            <td class="text-center" style="width: 45%;"><?php echo htmlspecialchars($funder['tot_finanziamenti'] ?? ''); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-muted text-center">Non ci sono finanziatori.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
