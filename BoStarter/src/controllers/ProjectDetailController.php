<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';

if (session_status() == PHP_SESSION_NONE) session_start();

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);
$projectModel = new Project($conn);


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
    $projectName = $_POST['nome_progetto'] ?? '';
    $commentText = trim($_POST['testo_commento'] ?? '');
    $userEmail = $_SESSION['user_id'] ?? '';

    if ($projectName && $commentText && $userEmail) {
        $_SESSION['success'] = $userModel->addComment($projectName, $userEmail, $commentText)
            ? "Commento aggiunto con successo!"
            : "Errore nell'inserimento del commento.";
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
    $commentId = $_POST['id_commento'] ?? '';
    $responseText = trim($_POST['testo_risposta'] ?? '');
    $creatorEmail = $_SESSION['user_id'] ?? '';
    $projectName = $_POST['nome_progetto'] ?? '';

    if ($commentId && $responseText && $creatorEmail) {
        $_SESSION['success'] = $userModel->addReply($commentId, $responseText, $creatorEmail)
            ? "Risposta aggiunta con successo!"
            : "Errore nell'inserimento della risposta.";
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
    $projectName = $_POST['nome_progetto'] ?? '';
    $amount = floatval($_POST['importo'] ?? 0);
    $userEmail = $_SESSION['user_id'] ?? '';
    $rewardCode = $_POST['codice_reward'] ?? '';

    if ($projectName && $amount > 0 && $userEmail && $rewardCode) {
        $_SESSION['success'] = $userModel->fundProject($projectName, $amount, $userEmail, $rewardCode)
            ? "Progetto finanziato con successo!"
            : "Errore nel finanziamento del progetto.";
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
    $projectName = $_GET['nome'] ?? '';
    $project = $photos = $comments = $rewards = [];
    $hasFundedToday = false;

    if ($projectName) {
        [$project, $photos, $comments] = $projectModel->getProjectDetailData($projectModel, $projectName);
        if (isset($_SESSION['user_id']) && isset($project['nome'])) {
            $hasFundedToday = $projectModel->hasFundedToday($project['nome'], $_SESSION['user_id']);
            $rewards = $projectModel->getProjectRewards($project['nome']);
        }
    }

    return [$project, $photos, $comments, $rewards, $hasFundedToday];
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
    [$project, $photos, $comments, $rewards, $hasFundedToday] = loadProjectData();
}
?>
