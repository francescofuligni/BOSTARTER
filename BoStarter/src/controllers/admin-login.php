<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database and user model
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

// Process admin login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $securityCode = isset($_POST['security_code']) ? trim($_POST['security_code']) : '';
    
    // Validate input
    if (empty($email) || empty($password) || empty($securityCode)) {
        $_SESSION['error'] = "Please enter email, password, and security code.";
        header('Location: /admin-login');
        exit;
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Create user object
    $user = new User($db);
    
    // Attempt admin login
    $isAdmin = $user->adminLogin($email, $password, $securityCode);
    
    if ($isAdmin) {
        // Get user details
        $stmt = $db->prepare("SELECT * FROM UTENTE WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Login successful
        $_SESSION['user_id'] = $userData['email'];
        $_SESSION['user_name'] = $userData['nome'] . ' ' . $userData['cognome'];
        $_SESSION['user_nickname'] = $userData['nickname'];
        $_SESSION['user_type'] = 'admin';
        
        header('Location: /admin-dashboard');
        exit;
    } else {
        // Login failed
        $_SESSION['error'] = "Invalid email, password, or security code.";
        header('Location: /admin-login');
        exit;
    }
}
?>