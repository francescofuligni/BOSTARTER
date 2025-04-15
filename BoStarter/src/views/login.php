<?php
// Inizia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Includi il controller per il login se esiste
$controllerPath = __DIR__ . '/../controllers/LoginController.php';
if (file_exists($controllerPath)) {
    require_once $controllerPath;
}

// Includi la navbar
require_once __DIR__ . '/components/navbar.php';

// Gestione degli errori e dei successi
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;

if ($error) {
    unset($_SESSION['error']);
}
if ($success) {
    unset($_SESSION['success']);
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BoStarter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Accedi a BoStarter</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="/login" method="post">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Accedi</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p>Non hai un account? <a href="/register">Registrati qui</a></p>
                            <p>Sei un amministratore? <a href="/admin-login">Accedi come amministratore</a></p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>