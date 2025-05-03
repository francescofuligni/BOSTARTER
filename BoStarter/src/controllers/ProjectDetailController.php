<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Component.php';

if (session_status() == PHP_SESSION_NONE) session_start();


/**
 * Verifica che l'utente sia autenticato.
 */
function checkAccess() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
}

/**
 * Aggiunge un commento a un progetto.
 */
function handleAddComment() {
    $db = new Database();
    $conn = $db->getConnection();
    $projectModel = new Project($conn);
    
    $projectName = $_POST['nome_progetto'] ?? '';
    $commentText = trim($_POST['testo_commento'] ?? '');
    $userEmail = $_SESSION['user_id'] ?? '';

    if ($projectName && $commentText && $userEmail) {
        $result = $projectModel->addComment($projectName, $userEmail, $commentText);
        if (isset($result['success']) && $result['success']) {
            $_SESSION['success'] = "Commento aggiunto con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'inserimento del commento.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi per inserire un commento.";
    }

    header('Location: /project-detail?nome=' . urlencode($projectName));
    exit;
}

/**
 * Aggiunge una risposta a un commento.
 */
function handleAddReply() {
    $db = new Database();
    $conn = $db->getConnection();
    $projectModel = new Project($conn);
    
    $commentId = $_POST['id_commento'] ?? '';
    $responseText = trim($_POST['testo_risposta'] ?? '');
    $creatorEmail = $_SESSION['user_id'] ?? '';
    $projectName = $_POST['nome_progetto'] ?? '';

    if ($commentId && $responseText && $creatorEmail) {
        $result = $projectModel->addReply($commentId, $responseText, $creatorEmail);
        if (isset($result['success']) && $result['success']) {
            $_SESSION['success'] = "Risposta aggiunta con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'inserimento della risposta.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi per inserire una risposta.";
    }

    header('Location: /project-detail?nome=' . urlencode($projectName));
    exit;
}

/**
 * Esegue un finanziamento su un progetto.
 */
function handleFundProject() {
    $db = new Database();
    $conn = $db->getConnection();
    $projectModel = new Project($conn);
    
    $projectName = $_POST['nome_progetto'] ?? '';
    $amount = floatval($_POST['importo'] ?? 0);
    $userEmail = $_SESSION['user_id'] ?? '';
    $rewardCode = $_POST['codice_reward'] ?? '';

    if ($projectName && $amount > 0 && $userEmail && $rewardCode) {
        $result = $projectModel->fund($projectName, $amount, $userEmail, $rewardCode);
        if (isset($result['success']) && $result['success']) {
            $_SESSION['success'] = "Progetto finanziato con successo!";
        } else {
            $_SESSION['error'] = "Errore nel finanziamento del progetto.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi per finanziare un progetto.";
    }

    header('Location: /project-detail?nome=' . urlencode($projectName));
    exit;
}

/**
 * Recupera i dati del progetto per la visualizzazione.
 */
function loadProjectData() {
    $db = new Database();
    $conn = $db->getConnection();
    $projectModel = new Project($conn);
    $userModel = new User($conn);
    $componentModel = new Component($conn);
    
    $projectName = $_GET['nome'] ?? '';
    $project = $photos = $comments = $rewards = $components = [];
    $hasFundedToday = false;

    if ($projectName) {
        $detailResult = $projectModel->getProjectDetailData($projectName);
        if (isset($detailResult['success']) && $detailResult['success']) {
            $data = $detailResult['data'] ?? [];
            $project = $data['project'] ?? [];
            $photos = $data['photos'] ?? [];
            $comments = $data['comments'] ?? [];
        } else {
            $project = $photos = $comments = [];
        }

        if (isset($_SESSION['user_id']) && isset($project['nome'])) {
            $hasFundedToday = $userModel->hasFundedToday($project['nome'], $_SESSION['user_id']);
            $rewardsResult = $projectModel->getRewards($project['nome']);
            if (isset($rewardsResult['success']) && $rewardsResult['success']) {
                $rewards = $rewardsResult['data'] ?? [];
            } else {
                $rewards = [];
            }
        }

        if ($project['tipo'] === 'HARDWARE') {
            $componentsResult = $componentModel->getProjectComponents($projectName);
            if (isset($componentsResult['success']) && $componentsResult['success']) {
                $components = $componentsResult['data'] ?? [];
            } else {
                $components = [];
            }
        }
    }

    return [$project, $photos, $comments, $rewards, $components, $hasFundedToday];
}


checkAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nome_progetto'], $_POST['testo_commento'])) {
        handleAddComment();
    }
    if (isset($_POST['id_commento'], $_POST['testo_risposta'])) {
        handleAddReply();
    }
    if (isset($_POST['nome_progetto'], $_POST['importo'], $_POST['codice_reward'])) {
        handleFundProject();
    }
}

if (isset($_GET['nome'])) {
    [$project, $photos, $comments, $rewards, $components, $hasFundedToday] = loadProjectData();
}
?>
