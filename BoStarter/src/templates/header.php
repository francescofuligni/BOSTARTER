<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Includi il file di autenticazione se esiste
$auth_file = __DIR__.'/../public/includes/auth.php';
if (file_exists($auth_file)) {
    require_once $auth_file;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoStarter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">BoStarter</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-search"></i> Discover</a>
                    </li>
                    <?php if (function_exists('isCreatore') && isCreatore()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/create-project.php"><i class="fas fa-plus-circle"></i> Start a Project</a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> 
                                <?php 
                                $userType = function_exists('getUserType') ? getUserType() : 'utente';
                                $icon = $userType === 'amministratore' ? '<i class="fas fa-user-shield"></i>' : 
                                       ($userType === 'creatore' ? '<i class="fas fa-rocket"></i>' : 
                                       '<i class="fas fa-user"></i>');
                                echo $icon . ' ' . (function_exists('getUserEmail') ? getUserEmail() : 'Utente');
                                ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Profilo</a></li>
                                <?php if (function_exists('isCreatore') && isCreatore()): ?>
                                <li><a class="dropdown-item" href="/create-project.php"><i class="fas fa-plus-circle"></i> Nuovo Progetto</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login.php"><i class="fas fa-sign-in-alt"></i> Accedi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register.php"><i class="fas fa-user-plus"></i> Registrati</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
    </div>
</body>
</html>