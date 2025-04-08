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
            <hr class="my-4">
            <p>Unisciti alla nostra community per scoprire progetti straordinari o crearne uno tuo.</p>
            <a class="btn btn-primary btn-lg" href="/login" role="button">Accedi</a>
            <a class="btn btn-success btn-lg" href="/register" role="button">Registrati</a>
        </div>
    </div>
</body>
</html>