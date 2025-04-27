<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

if (session_status() == PHP_SESSION_NONE) session_start();

/**
 * Reindirizza alla dashboard se è già autenticato.
 */
function alreadyLogged() {
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
        exit;
    }
}

/**
 * Valida i dati del form di registrazione.
 */
function checkRegistrationData($data) {
    return !(
        empty($data['email']) || empty($data['password']) || empty($data['name']) || empty($data['last_name']) ||
        empty($data['nickname']) || empty($data['birth_place']) ||
        ($data['type'] === 'AMMINISTRATORE' && empty($data['security_code']))
    );
}

/**
 * Esegue hash per password e codice di sicurezza.
 */
function hashSensitiveData(&$data) {
    $data['password'] = hash('sha256', $data['password']);
    if (!empty($data['security_code'])) {
        $data['security_code'] = hash('sha256', $data['security_code']);
    } else {
        $data['security_code'] = '';
    }
}

/**
 * Registra un nuovo utente e gestisce il flusso di risposta.
 */
function handleRegistration() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
    $data = [
        'email' => trim($_POST['email'] ?? ''),
        'password' => trim($_POST['password'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'nickname' => trim($_POST['nickname'] ?? ''),
        'birth_place' => trim($_POST['birth_place'] ?? ''),
        'birth_year' => (int)($_POST['birth_year'] ?? 0),
        'type' => trim($_POST['type'] ?? ''),
        'security_code' => trim($_POST['security_code'] ?? '')
    ];

    if (!checkRegistrationData($data)) {
        $_SESSION['error'] = 'Errore nella compilazione dei campi.';
        header('Location: /register');
        exit;
    }

    hashSensitiveData($data);

    $result = $userModel->register(
        $data['email'],
        $data['password'],
        $data['name'],
        $data['last_name'],
        $data['nickname'],
        $data['birth_place'],
        $data['birth_year'],
        $data['type'],
        $data['security_code']
    );

    if ($result['success']) {
        $_SESSION['success'] = 'Registrazione avvenuta con successo. Ora puoi accedere.';
        header('Location: /login');
        exit;
    } else {
        $_SESSION['error'] = 'Errore durante la registrazione. L\'email potrebbe essere già in uso.';
        header('Location: /register');
        exit;
    }
}


alreadyLogged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleRegistration();
}
?>
