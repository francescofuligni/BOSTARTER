<?php
require_once __DIR__.'/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start(); // Start output buffering to prevent headers already sent errors
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Email e password sono obbligatorie";
    } else {
        $db = new mysqli('mysql', 'root', 'root_password', 'bostarter_db');
        if ($db->connect_error) {
            $error = "Errore di connessione al database";
        } else {
            $autenticato = false;
            // First check if the stored procedure exists
            $procedureExists = false;
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM information_schema.routines WHERE routine_name = 'autenticazione_utente' AND routine_schema = 'bostarter_db'");
            $checkStmt->execute();
            $checkStmt->bind_result($procedureExists);
            $checkStmt->fetch();
            $checkStmt->close();
            
            if ($procedureExists) {
                $stmt = $db->prepare("CALL autenticazione_utente(?, ?, @autenticato)");
                $stmt->bind_param('ssb', $email, $password);
                try {
                    $stmt->execute();
                    $stmt->bind_result($autenticato); // Matches the single OUT parameter of autenticazione_utente procedure
                    $stmt->fetch();
                    $stmt->close();
                    error_log("Authentication result for $email: " . ($autenticato ? 'SUCCESS' : 'FAILED'));
                } catch (mysqli_sql_exception $e) {
                    $error = "Errore durante l'autenticazione: " . $e->getMessage();
                    error_log("Authentication error for $email: " . $e->getMessage());
                }
                $stmt->close();
            } else {
                // Fallback to direct query if procedure doesn't exist
                $stmt = $db->prepare("SELECT COUNT(*) > 0 FROM UTENTE WHERE email = ? AND password = ?");
                $stmt->bind_param('ss', $email, $password);
                $stmt->execute();
                $stmt->bind_result($autenticato); // Matches the single OUT parameter of autenticazione_utente procedure
                try {
                    $stmt->execute();
                    $stmt->bind_result($autenticato); // Matches the single OUT parameter of autenticazione_utente procedure
                    $stmt->fetch();
                    $stmt->close();
                    error_log("Authentication result for $email (direct query): " . ($autenticato ? 'SUCCESS' : 'FAILED'));
                } catch (mysqli_sql_exception $e) {
                    $error = "Errore durante l'autenticazione: " . $e->getMessage();
                    error_log("Authentication error for $email (direct query): " . $e->getMessage());
                }
                $stmt->close();
            }
            $stmt->close();
            
            if ($autenticato) {
                error_log("Authentication successful for $email");
                // Clean any existing output buffer
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Verifica il tipo di utente
                $stmt = $db->prepare("SELECT tipo FROM UTENTE WHERE email = ?");
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->bind_result($tipo_utente);
                $stmt->fetch();
                $stmt->close();
                
                // Set session variables
                $_SESSION['user_email'] = $email;
                error_log("Session variables set for $email");
                $_SESSION['user_type'] = $tipo_utente;
                
                // Redirect to home page after login
                error_log("Attempting redirect to index.php");
                header('Location: index.php');
                error_log("Redirect header sent");
                exit;
            } else {
                $error = "Email o password non valide";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - BoStarter</title>
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
                        <h4 class="mb-0">Login</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3" id="adminCodeField" style="display:none;">
            <label for="security_code" class="form-label">Codice di sicurezza:</label>
            <input type="password" class="form-control" id="security_code" name="security_code">
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo utente:</label>
            <select class="form-select" name="tipo_utente" id="tipo_utente" onchange="toggleAdminCodeField()">
                <option value="utente">Utente</option>
                <option value="creatore">Creatore</option>
                <option value="amministratore">Amministratore</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Accedi</button>
    </form>
                        <p class="mt-3">Non hai un account? <a href="register.php">Registrati</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleAdminCodeField() {
            const userType = document.getElementById('tipo_utente').value;
            const adminCodeField = document.getElementById('adminCodeField');
            adminCodeField.style.display = userType === 'amministratore' ? 'block' : 'none';
        }
    </script>
</body>
</html>
