<?php
// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$projectModel = new Project($db);

// --- Routing interno del controller ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nome_progetto'], $_POST['testo_commento'])) {
        $projectModel->addComment($user);
    }
    if (isset($_POST['id_commento'], $_POST['testo_risposta'])) {
        $projectModel->addReply($user);
    }
    if (isset($_POST['nome_progetto'], $_POST['importo'], $_POST['codice_reward'])) {
        $projectModel->fundProject($db);
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
        $rewards = $projectModel->getProjectRewards($project['nome']);
    }
}
?>
