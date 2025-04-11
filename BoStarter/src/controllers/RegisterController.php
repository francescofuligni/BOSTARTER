<?php
// Apri la sessione se non è già aperta
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include il file di configurazione del database e il modello User
$dbPath = __DIR__ . '/../config/Database.php';
$userPath = __DIR__ . '/../models/User.php';

// Controlla se i file esistono
if (file_exists($dbPath) && file_exists($userPath)) {
    require_once $dbPath;
    require_once $userPath;

    // Controlla se il metodo della richiesta è POST (invio del modulo)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ottieni i dati del form html
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $cognome = isset($_POST['cognome']) ? trim($_POST['cognome']) : '';
        $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
        $luogoNascita = isset($_POST['luogo_nascita']) ? trim($_POST['luogo_nascita']) : '';
        $annoNascita = isset($_POST['anno_nascita']) ? (int)$_POST['anno_nascita'] : 0;
        $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'UTENTE';
        
        // Validazione dell'input
        if (empty($email) || empty($password) || empty($nome) || empty($cognome) || 
            empty($nickname) || empty($luogoNascita) || $annoNascita <= 0) {
            $_SESSION['error'] = "Per favore, compila tutti i campi richiesti.";
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
        
        // Se il codice di sicurezza è definito, lo ottiene
        $codiceSicurezza = $_POST['codice_sicurezza'] ?? "";

        // Se il codice di sicurezza non è vuoto, lo hash
        if (!$codiceSicurezza == "") {
            $hashedCodiceSicurezza = hash('sha256', $codiceSicurezza);
        } else {
            $hashedCodiceSicurezza = "";
        }


        $success = $user->register($email, $hashedPassword, $nome, $cognome, $nickname, $luogoNascita, $annoNascita, $tipo, $hashedCodiceSicurezza);
        
        if ($success) {
            $_SESSION['success'] = "Registrazione avvenuta con successo. Ora puoi accedere.";
            header('Location: /login');
            exit;
        } else {
            $_SESSION['error'] = "Errore durante la registrazione. L'email potrebbe essere già in uso.";
            header('Location: /register');
            exit;
        }
    }
} else {
    $_SESSION['error'] = "Registrazione fallita.";
}
?>