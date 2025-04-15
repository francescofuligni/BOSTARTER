<?php
// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Includi il database e il modello utente se esistono
$dbPath = __DIR__ . '/../config/Database.php';
$userPath = __DIR__ . '/../models/User.php';

class LoginController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    /**
     * Gestisce la richiesta POST per il login dell'utente.
     * Verifica le credenziali e avvia la sessione se l'autenticazione ha successo.
     *
     * @return void
     */
    public function handlePostRequest() {
        // Ottieni i dati del form
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        // Valida i dati: verifica che email e password siano forniti
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Inserisci sia email che password.";
            header('Location: /login'); // Torna alla pagina di login
            exit;
        }
        
        // Hash della password
        $hashedPassword = hash('sha256', $password);

        // Prova a effettuare il login con email e password forniti
        $userData = $this->user->login($email, $hashedPassword);
        
        if ($userData) {
            $token = bin2hex(random_bytes(32)); // Token per manteneere la sessione

            // Salva i dettagli dell'utente e il token nella sessione
            $_SESSION['user_id'] = $userData['email'];
            $_SESSION['user_name'] = $userData['nome'] . ' ' . $userData['cognome'];
            $_SESSION['user_nickname'] = $userData['nickname'];
            $_SESSION['user_type'] = $this->user->isCreator($email) ? 'creator' : 'user';
            $_SESSION['auth_token'] = $token;
            $_SESSION['token_expiration'] = time() + (60 * 60); // Token valido per 60 minuti 

            // Reindirizza in base al tipo di utente
            if ($_SESSION['user_type'] == 'creator') {
                header('Location: /creator-dashboard');
            } else {
                header('Location: /dashboard');
            }
            exit;
        } else {
            $_SESSION['error'] = "Email o password non validi.";
            header('Location: /login');
            exit;
        }
    }
}

if (file_exists($dbPath) && file_exists($userPath)) {
    require_once $dbPath;
    require_once $userPath;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new LoginController();
        $controller->handlePostRequest();
    }
} else {
    // Se i file non esistono, mostra un messaggio di errore
    $_SESSION['error'] = "Il sistema di login non è ancora configurato. Contattare l'amministratore.";
}
?>