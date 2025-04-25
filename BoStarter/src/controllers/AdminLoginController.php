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
 * Valida i dati, autentica l'admin e imposta la sessione.
 */
function handleAdminLogin() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
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

    $isAdmin = $userModel->adminLogin($email, $hashedPassword, $hashedSecurityCode);

    if ($isAdmin) {
        $userData = $userModel->getUserData($email);

        $_SESSION['user_id'] = $userData['email'];
        $_SESSION['user_name'] = $userData['nome'] . ' ' . $userData['cognome'];
        $_SESSION['user_nickname'] = $userData['nickname'];
        $_SESSION['user_type'] = 'admin';

        header('Location: /dashboard');
        exit;
    } else {
        $_SESSION['error'] = "Email, password o codice di sicurezza non validi.";
        header('Location: /admin-login');
        exit;
    }
}


alreadyLogged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleAdminLogin();
}
?>
