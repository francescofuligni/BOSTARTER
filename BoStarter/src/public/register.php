<?php
require_once __DIR__.'/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $tipo_utente = 'utente'; // Solo utenti normali possono registrarsi
    
    // Implementazione chiamata alla stored procedure di registrazione
    $auth_file = __DIR__.'/../public/includes/auth.php';
    $db = new mysqli('mysql', 'root', 'root_password', 'bostarter_db');
    
    $stmt = $db->prepare("CALL registra_utente(?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $email, $password, $nome, $cognome, $tipo_utente);
    
    if ($stmt->execute()) {
        header('Location: login.php');
    } else {
        $error = 'Errore durante la registrazione: ' . $db->error;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrazione - BoStarter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Registrazione</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="cognome" class="form-label">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome" required>
                            </div>
        <div>
            <label for="tipo_utente">Tipo utente:</label>
            <select id="tipo_utente" name="tipo_utente" required>
                <option value="utente">Utente</option>
                <option value="creatore">Creatore</option>
            </select>
        </div>
        <button type="submit">Registrati</button>
    </form>
    <p>Hai già un account? <a href="login.php">Accedi</a></p>
</body>
</html>