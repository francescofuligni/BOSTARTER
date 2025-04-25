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
 * Valida credenziali, autentica utente e avvia sessione.
 */
function handleLogin() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Inserisci sia email che password.";
        header('Location: /login');
        exit;
    }

    if ($userModel->isAdmin($email)) {
        $_SESSION['info'] = "Gli amministratori devono usare il login amministratore.";
        header('Location: /admin-login');
        exit;
    }

    $hashedPassword = hash('sha256', $password);
    $userData = $userModel->login($email, $hashedPassword);

    if ($userData) {
        $token = bin2hex(random_bytes(32));

        $_SESSION['user_id'] = $userData['email'];
        $_SESSION['user_name'] = $userData['nome'] . ' ' . $userData['cognome'];
        $_SESSION['user_nickname'] = $userData['nickname'];
        $_SESSION['user_type'] = $userModel->isCreator($email) ? 'creator' : 'user';
        $_SESSION['auth_token'] = $token;
        $_SESSION['token_expiration'] = time() + (60 * 60);

        header('Location: /dashboard');
        exit;
    } else {
        $_SESSION['error'] = "Email o password non validi.";
        header('Location: /login');
        exit;
    }
}


alreadyLogged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleLogin();
}
?>
