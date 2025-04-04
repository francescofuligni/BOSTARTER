<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database and user model if they exist
$dbPath = __DIR__ . '/../config/Database.php';
$userPath = __DIR__ . '/../models/User.php';

if (file_exists($dbPath) && file_exists($userPath)) {
    require_once $dbPath;
    require_once $userPath;

    // Process registration form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $cognome = isset($_POST['cognome']) ? trim($_POST['cognome']) : '';
        $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
        $luogoNascita = isset($_POST['luogo_nascita']) ? trim($_POST['luogo_nascita']) : '';
        $annoNascita = isset($_POST['anno_nascita']) ? (int)$_POST['anno_nascita'] : 0;
        $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'UTENTE';
        
        // Validate input
        if (empty($email) || empty($password) || empty($nome) || empty($cognome) || 
            empty($nickname) || empty($luogoNascita) || $annoNascita <= 0) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header('Location: /register');
            exit;
        }
        
        // Connect to database
        $database = new Database();
        $db = $database->getConnection();
        
        // Create user object
        $user = new User($db);
        
        // Attempt registration
        $success = $user->register($email, $password, $nome, $cognome, $nickname, $luogoNascita, $annoNascita, $tipo);
        
        if ($success) {
            // Registration successful
            $_SESSION['success'] = "Registration successful! You can now log in.";
            header('Location: /login');
            exit;
        } else {
            // Registration failed
            $_SESSION['error'] = "Registration failed. Please try again.";
            header('Location: /register');
            exit;
        }
    }
} else {
    // If models don't exist yet, just show a message
    $_SESSION['error'] = "Registration system is still being set up. Please try again later.";
}
?>