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

    // Process login form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        // Validate input
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Please enter both email and password.";
            header('Location: /login');
            exit;
        }
        
        // Connect to database
        $database = new Database();
        $db = $database->getConnection();
        
        // Create user object
        $user = new User($db);
        
        // Attempt login
        $userData = $user->login($email, $password);
        
        if ($userData) {
            // Login successful
            $_SESSION['user_id'] = $userData['email'];
            $_SESSION['user_name'] = $userData['nome'] . ' ' . $userData['cognome'];
            $_SESSION['user_nickname'] = $userData['nickname'];
            
            // Check user type
            if ($user->isCreator($email)) {
                $_SESSION['user_type'] = 'creator';
                header('Location: /creator-dashboard');
            } else {
                $_SESSION['user_type'] = 'user';
                header('Location: /dashboard');
            }
            exit;
        } else {
            // Login failed
            $_SESSION['error'] = "Invalid email or password.";
            header('Location: /login');
            exit;
        }
    }
} else {
    // If models don't exist yet, just show a message
    $_SESSION['error'] = "Login system is still being set up. Please try again later.";
}
?>