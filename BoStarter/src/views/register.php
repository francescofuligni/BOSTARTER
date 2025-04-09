<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// includi il controller per la registrazione
$controllerPath = __DIR__ . '/../controllers/RegisterController.php';
if (file_exists($controllerPath)) {
    require_once $controllerPath;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - BoStarter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="/js/register.js" defer></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Crea il tuo account BoStarter</h3>
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
                        
                        <form action="/register" method="post">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="tipo">Tipo Utente</label>
                                    <select class="form-control" id="tipo" name="tipo" required>
                                        <option value="UTENTE">Utente</option>
                                        <option value="CREATORE">Creatore</option>
                                        <option value="AMMINISTRATORE">Amministratore</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nickname">Nickname</label>
                                    <input type="text" class="form-control" id="nickname" name="nickname" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nome">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="cognome">Cognome</label>
                                    <input type="text" class="form-control" id="cognome" name="cognome" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="anno_nascita">Anno di nascita</label>
                                    <input type="number" class="form-control" id="anno_nascita" name="anno_nascita" min="1900" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="luogo_nascita">Luogo di nascita</label>
                                    <input type="text" class="form-control" id="luogo_nascita" name="luogo_nascita" required>
                                </div>
                            </div>
                            <div class="form-row d-none" id="codice-container">
                                <div class="form-group col-md-8">
                                    <label for="codice_sicurezza">Codice di sicurezza</label>
                                    <input type="text" class="form-control" id="codice_sicurezza" name="codice_sicurezza" readonly>
                                </div>
                                <div class="form-group col-md-4 d-flex align-items-end">
                                    <button class="btn btn-secondary w-100" type="button" onclick="generaCodice()">Genera</button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Registrati</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p>Hai già un account? <a href="/login">Effettua il login!</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>