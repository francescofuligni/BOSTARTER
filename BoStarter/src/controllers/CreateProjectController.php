<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Component.php';

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
        empty($post['name']) ||
        empty($post['description']) ||
        empty($post['budget']) || floatval($post['budget']) <= 0 ||
        empty($post['deadline']) ||
        empty($post['type']) ||
        empty($_POST['reward_description']) ||
        empty($_FILES['reward_image']['tmp_name'][0])
    ) {
        return false;
    }
    return true;
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

    $rewards = [];
    if (isset($_POST['reward_description']) && is_array($_POST['reward_description'])) {
        foreach ($_POST['reward_description'] as $idx => $rewardDescription) {
            $imageTmp = $_FILES['reward_image']['tmp_name'][$idx] ?? '';
            if ($rewardDescription && $imageTmp && is_uploaded_file($imageTmp)) {
                $imageData = file_get_contents($imageTmp);
                $rewards[] = ['image' => $imageData, 'desc' => $rewardDescription];
            }
        }
    }
    $photos = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $idx => $tmpName) {
            if ($_FILES['images']['error'][$idx] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                $photos[] = file_get_contents($tmpName);
            }
        }
    }

    $creationSuccess = $projectModel->create($name, $description, $budget, $deadline, $type, $creatorEmail, $rewards, $photos);

    if ($creationSuccess['success']) {
        if ($type === 'HARDWARE' && !empty($_POST['component_name'])) {
            $componentModel = new Component($conn);
            foreach ($_POST['component_name'] as $idx => $componentName) {
                $qty = intval($_POST['component_qty'][$idx]);
                if ($componentName && $qty > 0) {
                    $componentModel->addComponentToProject($componentName, $qty, $name, $creatorEmail);
                }
            }
        }
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

$db = new Database();
$conn = $db->getConnection();
$componentModel = new Component($conn);
$allComponents = $componentModel->getAllComponents()['data'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aggiunta nuova componente hardware
    if (isset($_POST['add_component'])) {
        $db = new Database();
        $conn = $db->getConnection();
        $componentModel = new Component($conn);

        $name = trim($_POST['new_component_name']);
        $desc = trim($_POST['new_component_desc']);
        $price = floatval($_POST['new_component_price']);
        $creatorEmail = $_SESSION['user_id'];
        $result = $componentModel->addComponent($name, $desc, $price, $creatorEmail);
        if ($result['success']) {
            $_SESSION['success'] = "Componente aggiunta con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'aggiunta della componente.";
        }
        header('Location: /create-project');
        exit;
    }
    handleCreateProject();
}
?>
