<?php
// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Controlla se l'utente è già loggato come amministratore
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
    header('Location: /dashboard');
    exit;
}

// Includi il controller per il login amministratore
require_once __DIR__ . '/../controllers/AdminLoginController.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Amministratore - BoStarter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Login Amministratore</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                    echo $_SESSION['error']; 
                                    unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="/admin-login" method="post">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="security_code">Codice di Sicurezza</label>
                                <input type="password" class="form-control" id="security_code" name="security_code" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Accedi</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p><a href="/login">Torna al Login Utente</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>