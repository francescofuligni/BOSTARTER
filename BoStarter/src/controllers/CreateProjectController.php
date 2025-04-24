<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Photo.php';

if (session_status() == PHP_SESSION_NONE) session_start();


/**
 * Verifica che l'utente sia autenticato e sia un creatore.
 */
function checkAccess() {
    // Crea Database e User localmente
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

    if (empty($post['reward_code']) || count(array_filter($post['reward_code'], fn($r) => !empty(trim($r)))) === 0) {
        return false;
    }

    return true;
}

/**
 * Gestisce il caricamento delle immagini del progetto.
 */
function handleImageUpload($projectName) {
    // Crea Database e Photo localmente
    $db = new Database();
    $conn = $db->getConnection();
    $photoModel = new Photo($conn);
    
    if (!empty($_FILES['immagini']['name'][0])) {
        foreach ($_FILES['immagini']['tmp_name'] as $idx => $tmpName) {
            if ($_FILES['immagini']['error'][$idx] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                $imageData = file_get_contents($tmpName);
                $photoModel->addPhotoToProject($projectName, $imageData);
            }
        }
    }
}

/**
 * Gestisce le ricompense associate al progetto.
 */
function handleRewards($projectName, $creatorEmail) {
    // Crea Database e User localmente
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
    foreach ($_POST['reward_code'] as $idx => $code) {
        $description = $_POST['reward_description'][$idx] ?? '';
        $imageTmp = $_FILES['reward_image']['tmp_name'][$idx] ?? '';
        if ($code && $description && $imageTmp && is_uploaded_file($imageTmp)) {
            $imageData = file_get_contents($imageTmp);
            $userModel->addRewardToProject($code, $imageData, $description, $projectName, $creatorEmail);
        }
    }
}

/**
 * Gestisce la richiesta POST per creare un nuovo progetto.
 */
function handleCreateProject() {
    // Crea Database e User localmente
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
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

    $creationSuccess = $userModel->createProject($name, $description, $budget, $deadline, $type, $creatorEmail);

    if ($creationSuccess) {
        handleImageUpload($name);
        handleRewards($name, $creatorEmail);

        $_SESSION['success'] = "Progetto creato con successo!";
        header('Location: /dashboard');
        exit;
    } else {
        $_SESSION['error'] = "Errore nella creazione del progetto.";
        header('Location: /create-project');
        exit;
    }
}


checkAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleCreateProject();
}
?>
