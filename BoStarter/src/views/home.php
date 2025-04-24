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
</head>
<body>
    <div class="container mt-5">
        <div class="jumbotron">
            <h1 class="display-4">Benvenuto su BoStarter!</h1>
            <p class="lead">Una piattaforma per il crowdfunding di progetti innovativi.</p>
        </div>

        <!-- Preview Progetti in Scadenza -->
        <h2 class="mt-4">Progetti in scadenza</h2>
        <div class="row">
            <?php foreach (array_slice($expiringProjects, 0, 3) as $project): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($project['nome'] ?? 'Progetto'); ?></h5>
                            <p class="card-text">Budget mancante: <?php echo htmlspecialchars($project['differenza_budget'] ?? 'N/A'); ?> €</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Top Creators -->
        <h2 class="mt-4">Top Creatori</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nickname</th>
                    <th>Affidabilità</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($topCreators, 0, 3) as $i => $creator): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($creator['nickname'] ?? 'Creatore'); ?></td>
                        <td><?php echo htmlspecialchars($creator['affidabilita'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Top Funders -->
        <h2 class="mt-4">Top Finanziatori</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nickname</th>
                    <th>Totale Finanziamenti</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($topFunders, 0, 3) as $i => $funder): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($funder['nickname'] ?? 'Finanziatore'); ?></td>
                        <td><?php echo htmlspecialchars($funder['tot_finanziamenti'] ?? 'N/A'); ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
