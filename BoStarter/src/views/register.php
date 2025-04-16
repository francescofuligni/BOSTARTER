<?php
// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../controllers/RegisterController.php';
require_once __DIR__ . '/components/navbar.php';
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
                            <div class="alert alert-error">
                                <?php 
                                    echo $_SESSION['error']; 
                                    unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="/register" method="post">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="type">Tipologia Utente</label>
                                    <select class="form-control" id="type" name="type" required>
                                        <option value="UTENTE">Standard</option>
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
                                    <label for="name">Nome</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="last_name">Cognome</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="birth_year">Anno di Nascita</label>
                                    <select class="form-control" id="birth_year" name="birth_year" required></select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="birth_place">Luogo di Nascita</label>
                                    <input type="text" class="form-control" id="birth_place" name="birth_place" required>
                                </div>
                            </div>
                            <div class="form-row d-none" id="security_code_container">
                                <div class="form-group col-md-8">
                                    <label for="security_code">Codice di Sicurezza</label>
                                    <input type="text" class="form-control" id="security_code" name="security_code" readonly>
                                </div>
                                <div class="form-group col-md-4 d-flex align-items-end">
                                    <button class="btn btn-secondary w-100" type="button" onclick="generateCode()">Genera</button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Registrati</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p>Hai già un account? <a href="/login">Effettua l'accesso!</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>