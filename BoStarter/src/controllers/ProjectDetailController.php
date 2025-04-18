<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';

if (session_status() == PHP_SESSION_NONE) session_start();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$projectModel = new Project($db);

/**
 * Recupera i dati del progetto, le foto e i commenti
 * @param Project $projectModel
 * @param string $nomeProgetto
 * @return array
 */
function getProjectDetailData($projectModel, $nomeProgetto) {
    $project = $projectModel->getProjectDetail($nomeProgetto);
    $photos = $projectModel->getProjectPhotos($nomeProgetto);
    $comments = $projectModel->getProjectComments($nomeProgetto);
    return [$project, $photos, $comments];
}

/**
 * Gestisce l'inserimento di un commento
 * @param User $user
 */
function handleAddComment($user) {
    $nomeProgetto = $_POST['nome_progetto'] ?? '';
    $testoCommento = trim($_POST['testo_commento'] ?? '');
    $emailUtente = $_SESSION['user_id'] ?? '';

    if ($nomeProgetto && $testoCommento && $emailUtente) {
        if ($user->addComment($nomeProgetto, $emailUtente, $testoCommento)) {
            $_SESSION['success'] = "Commento aggiunto con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'inserimento del commento.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi per inserire un commento.";
    }
    header('Location: /project-detail?nome=' . urlencode($nomeProgetto));
    exit;
}

/**
 * Gestisce l'inserimento di una risposta
 * @param User $user
 */
function handleAddReply($user) {
    $idCommento = $_POST['id_commento'] ?? '';
    $testoRisposta = trim($_POST['testo_risposta'] ?? '');
    $emailCreatore = $_SESSION['user_id'] ?? '';
    $nomeProgetto = $_POST['nome_progetto'] ?? '';

    if ($idCommento && $testoRisposta && $emailCreatore) {
        if ($user->addReply($idCommento, $testoRisposta, $emailCreatore)) {
            $_SESSION['success'] = "Risposta aggiunta con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'inserimento della risposta.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi per inserire una risposta.";
    }
    header('Location: /project-detail?nome=' . urlencode($nomeProgetto));
    exit;
}
 
/**
 * Gestisce il finanziamento di un progetto
 * @param Database $db
 */
function handleFundProject($db) {
    $nomeProgetto = $_POST['nome_progetto'] ?? '';
    $importo = floatval($_POST['importo'] ?? 0);
    $emailUtente = $_SESSION['user_id'] ?? '';

    // Get the PDO connection from the Database object
    $pdo = $db instanceof Database ? $db->getConnection() : $db;

    if ($nomeProgetto && $importo > 0 && $emailUtente) {
        try {
            $stmt = $pdo->prepare("CALL finanzia_progetto(:email_utente, :nome_progetto, :importo)");
            $stmt->bindParam(':email_utente', $emailUtente);
            $stmt->bindParam(':nome_progetto', $nomeProgetto);
            $stmt->bindParam(':importo', $importo);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Finanziamento effettuato con successo!";
            } else {
                $_SESSION['error'] = "Errore nell'inserimento del finanziamento.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Errore nell'inserimento del finanziamento.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi per finanziare il progetto.";
    }
    header('Location: /project-detail?nome=' . urlencode($nomeProgetto));
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
    if (isset($_POST['nome_progetto'], $_POST['importo'])) {
        handleFundProject($db);
    }
}

// GET: recupera i dati per la view
$project = null;
$photos = [];
$comments = [];
$hasFundedToday = false;
if (isset($_GET['nome'])) {
    [$project, $photos, $comments] = getProjectDetailData($projectModel, $_GET['nome']);
    if (isset($_SESSION['user_id']) && isset($project['nome'])) {
        $hasFundedToday = $projectModel->hasFundedToday($project['nome'], $_SESSION['user_id']);
    }
}
?>