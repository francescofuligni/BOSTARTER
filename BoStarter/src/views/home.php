<?php
require_once __DIR__ . '/components/navbar.php';
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
        
    <div class="container mt-4">
        <h4 class="mb-4">Scopri i nostri progetti</h4>
        <?php if (!empty($activeProjects)) : ?>
            <div class="row">
                <?php foreach ($activeProjects as $project): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($project['titolo']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($project['descrizione']) ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="/login" class="btn btn-primary">Scopri di più</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="alert alert-info">
                Al momento non ci sono progetti disponibili.
            </div>
        <?php endif; ?>
        <?php if (!empty($activeProjects)) : ?>
            <div class="text-center mt-4">
                <p>Per vedere altri progetti, <a href="/login">Accedi</a></p>
            </div>
        <?php endif; ?>
    </div>

    </div>
</body>
</html>