<?php
// Apri la sessione se non è già aperta
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include il file di configurazione del database e il modello User
$dbPath = __DIR__ . '/../config/Database.php';
$userPath = __DIR__ . '/../models/User.php';

// Controlla se i file esistono
require_once $dbPath;
require_once $userPath;

// Controlla se il metodo della richiesta è POST (invio del modulo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ottieni i dati del form html
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
    $brithPlace = isset($_POST['birth_place']) ? trim($_POST['birth_place']) : '';
    $birthYear = isset($_POST['birth_year']) ? (int)$_POST['birth_year'] : 0;
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $securityCode = isset($_POST['security_code']) ? trim($_POST['security_code']) : '';
    
    // Validazione dell'input
    if (empty($email) || empty($password) || empty($name) || empty($lastName) || 
        empty($nickname) || empty($brithPlace) || ($type == 'AMMINISTRATORE' && empty($securityCode))) {
        $_SESSION['error'] = 'Errore nella compilazione dei campi.';
        header('Location: /register');
        exit;
    }

    // Hash della password
    $hashedPassword = hash('sha256', $password);

    // Connessione al db
    $database = new Database();
    $db = $database->getConnection();
    
    // Crea un oggetto User (mi serve così posso usare i metodi del modello e ha già la connessione al db)
    $user = new User($db);

    // Se il codice di sicurezza non è vuoto, lo hash
    $hashedSecurityCode = '';
    if (!empty($securityCode)) {
        $hashedSecurityCode = hash('sha256', $securityCode);
    }

    $success = $user->register($email, $hashedPassword, $name, $lastName, $nickname, $brithPlace, $birthYear, $type, $hashedSecurityCode);
    
    if ($success) {
        $_SESSION['success'] = 'Registrazione avvenuta con successo. Ora puoi accedere.';
        header('Location: /login');
        exit;
    } else {
        $_SESSION['error'] = 'Errore durante la registrazione. L\'email potrebbe essere già in uso.';
        header('Location: /register');
        exit;
    }
}
?>
