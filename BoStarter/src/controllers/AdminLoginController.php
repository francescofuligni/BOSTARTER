<?php
// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class AdminLoginController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    /**
     * Gestisce la richiesta POST per il login amministratore.
     * Verifica le credenziali e reindirizza in base all'esito.
     *
     * @return void
     */
    public function handlePostRequest() {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $securityCode = isset($_POST['security_code']) ? trim($_POST['security_code']) : '';

        if (empty($email) || empty($password) || empty($securityCode)) {
            $_SESSION['error'] = "Inserisci email, password e codice di sicurezza.";
            header('Location: /admin-login');
            exit;
        }

        $hashedPassword = hash('sha256', $password);
        $hashedSecurityCode = hash('sha256', $securityCode);

        $isAdmin = $this->user->adminLogin($email, $hashedPassword, $hashedSecurityCode);

        if ($isAdmin) {
            $userData = $this->user->getDataByEmail($email);
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
}

// Istanzia e gestisci la richiesta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AdminLoginController();
    $controller->handlePostRequest();
}