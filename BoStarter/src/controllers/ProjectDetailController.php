<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$projectModel = new Project($db);

/**
 * Gestisce l'inserimento di un commento
 * @param User $user
 */
function handleAddComment($user) {
    $projectName = $_POST['nome_progetto'] ?? '';
    $commentText = trim($_POST['testo_commento'] ?? '');
    $userEmail = $_SESSION['user_id'] ?? '';

    if ($projectName && $commentText && $userEmail) {
        if ($user->addComment($projectName, $userEmail, $commentText)) {
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
 * Gestisce l'inserimento di una risposta
 * @param User $user
 */
function handleAddReply($user) {
    $commentId = $_POST['id_commento'] ?? '';
    $responseText = trim($_POST['testo_risposta'] ?? '');
    $creatorEmail = $_SESSION['user_id'] ?? '';
    $projectName = $_POST['nome_progetto'] ?? '';

    if ($commentId && $responseText && $creatorEmail) {
        if ($user->addReply($commentId, $responseText, $creatorEmail)) {
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
 * Gestisce il finanziamento di un progetto
 * @param Database $db
 */
function handleFundProject($db) {
    $projectName = $_POST['nome_progetto'] ?? '';
    $amount = floatval($_POST['importo'] ?? 0);
    $userEmail = $_SESSION['user_id'] ?? '';
    $rewardCode = $_POST['codice_reward'] ?? '';
    
    if ($projectName && $amount > 0 && $userEmail && $rewardCode) {
        if($projectModel->fundProject($projectName, $amount, $userEmail, $rewardCode)) {
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

// --- Routing interno del controller ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nome_progetto'], $_POST['testo_commento'])) {
        handleAddComment($user);
    }
    if (isset($_POST['id_commento'], $_POST['testo_risposta'])) {
        handleAddReply($user);
    }
    if (isset($_POST['nome_progetto'], $_POST['importo'], $_POST['codice_reward'])) {
        handleFundProject($db);
    }
}

// GET: recupera i dati per la view

if (isset($_GET['nome'])) {
    [$project, $photos, $comments] = getProjectDetailData($projectModel, $_GET['nome']);
    if (isset($_SESSION['user_id']) && isset($project['nome'])) {
        $hasFundedToday = $projectModel->hasFundedToday($project['nome'], $_SESSION['user_id']);
    }
    $rewards = [];
    if ($project && isset($project['nome'])) {
        $rewards = $projectModel->getRewardsForProject($project['nome']);
    }
}
?>
