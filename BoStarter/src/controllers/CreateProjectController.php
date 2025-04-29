<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';

if (session_status() == PHP_SESSION_NONE) session_start();


/**
 * Verifica che l'utente sia autenticato e sia un creatore.
 */
function checkAccess() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    if (!$userModel->isCreator($_SESSION['user_id'])) {
        $_SESSION['error'] = "Solo i creatori possono creare progetti.";
        header('Location: /dashboard');
        exit;
    }
}

/**
 * Valida i dati del form di creazione progetto.
 */
function validateProjectForm($post) {
    if (
        empty($post['name']) || empty($post['description']) ||
        empty($post['budget']) || floatval($post['budget']) <= 0 ||
        empty($post['deadline']) || empty($post['type'])
    ) {
        return false;
    }
    return true;
}



/**
 * Gestisce le ricompense associate al progetto.
 */
function handleRewards($projectName, $creatorEmail) {
    $db = new Database();
    $conn = $db->getConnection();
    $projectModel = new Project($conn);
    foreach ($_POST['reward_coreward_name'] as $idx => $code) {
        $description = $_POST['reward_description'][$idx] ?? '';
        $imageTmp = $_FILES['reward_image']['tmp_name'][$idx] ?? '';
        if ($code && $description && $imageTmp && is_uploaded_file($imageTmp)) {
            $imageData = file_get_contents($imageTmp);
            $projectModel->addReward($imageData, $description, $projectName, $creatorEmail);
        }
    }
}

function handleCreateProject() {
    $db = new Database();
    $conn = $db->getConnection();
    $projectModel = new Project($conn);
    
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $budget = floatval($_POST['budget']);
    $deadline = $_POST['deadline'];
    $type = trim($_POST['type']);
    $creatorEmail = $_SESSION['user_id'];

    if (!validateProjectForm($_POST)) {
        $_SESSION['error'] = "Compila tutti i campi obbligatori e almeno una ricompensa.";
        header('Location: /create-project');
        exit;
    }

    // For project creation, collect rewards and photos arrays
    $rewards = [];
    foreach ($_POST['reward_description'] as $idx => $description) {
        $imageTmp = $_FILES['reward_image']['tmp_name'][$idx] ?? '';
        if ($description && $imageTmp && is_uploaded_file($imageTmp)) {
            $imageData = file_get_contents($imageTmp);
            $rewards[] = ['image' => $imageData, 'desc' => $description];
        }
    }
    $photos = [];
    if (!empty($_FILES['immagini']['name'][0])) {
        foreach ($_FILES['immagini']['tmp_name'] as $idx => $tmpName) {
            if ($_FILES['immagini']['error'][$idx] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                $photos[] = file_get_contents($tmpName);
            }
        }
    }

    $creationSuccess = $projectModel->create($name, $description, $budget, $deadline, $type, $creatorEmail, $rewards, $photos);

    if ($creationSuccess['success']) {
        $_SESSION['success'] = "Progetto creato con successo!";
        header('Location: /dashboard');
        exit;
    } else {
        $_SESSION['error'] = "Errore nella creazione del progetto. " . ($creationSuccess['error'] ?? 'Errore sconosciuto');
        header('Location: /create-project');
        exit;
    }
}


checkAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleCreateProject();
}
?>
