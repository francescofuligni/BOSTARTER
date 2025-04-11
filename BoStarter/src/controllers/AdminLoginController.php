<?php
// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Includi il database e il modello utente
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

// Gestisci il form di login amministratore
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Ottieni i dati dal form
    // Ottieni i dati dal form
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $securityCode = isset($_POST['security_code']) ? trim($_POST['security_code']) : '';
    
    // Valida i dati
    if (empty($email) || empty($password) || empty($securityCode)) {
        $_SESSION['error'] = "Inserisci email, password e codice di sicurezza.";
        header('Location: /admin-login');
        exit;
    }
    
    // Connettiti al database
    $database = new Database();
    $db = $database->getConnection();
    
    // Crea un oggetto utente
    $user = new User($db);
    

    // Hash della password e security code
    $hashedPassword = hash('sha256', $password);
    $hashedSecurityCode = hash('sha256', $securityCode);



    // Prova a effettuare il login come amministratore
    $isAdmin = $user->adminLogin($email, $hashedPassword, $hashedSecurityCode);
    
    if ($isAdmin) {
        // Ottieni i dettagli dell'utente
        $userData = $user->getDataByEmail($email);
        

        $_SESSION['user_id'] = $userData['email'];
        $_SESSION['user_name'] = $userData['nome'] . ' ' . $userData['cognome'];
        $_SESSION['user_nickname'] = $userData['nickname'];
        $_SESSION['user_type'] = 'admin';
        
        header('Location: /admin-dashboard');
        exit;
    } else {

        $_SESSION['error'] = "Email, password o codice di sicurezza non validi.";
        header('Location: /admin-login');
        exit;
    }
}
?>