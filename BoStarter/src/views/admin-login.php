<?php
require_once __DIR__ . '/../controllers/AdminLoginController.php';
require_once __DIR__ . '/components/navbar.php';
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info" role="alert">
                <?php echo htmlspecialchars($_SESSION['info']); unset($_SESSION['info']); ?>
            </div>
        <?php endif; ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Accesso Amministratore</h3>
                    </div>
                    <div class="card-body">
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
                            <p>Non hai ancora un account? <a href="/register">Registrati qui</a></p>
                            <p><a href="/login">Vai all'accesso utente</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
