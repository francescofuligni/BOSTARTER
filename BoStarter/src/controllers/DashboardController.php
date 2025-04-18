<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Competences.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$projectModel = new Project($db);
$competencesModel = new Competences($db);

// Gestione inserimento commento direttamente qui
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_progetto'], $_POST['testo_commento'])) {
    $nomeProgetto = $_POST['nome_progetto'];
    $testoCommento = trim($_POST['testo_commento']);
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
    header('Location: /dashboard');
    exit;
}

// Gestione inserimento risposta direttamente qui
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commento'], $_POST['testo_risposta'])) {
    $idCommento = $_POST['id_commento'];
    $testoRisposta = trim($_POST['testo_risposta']);
    $emailCreatore = $_SESSION['user_id'] ?? '';

    // Verifica che sia il creatore del progetto associato al commento
    if ($idCommento && $testoRisposta && $emailCreatore) {
        if ($user->addReply($idCommento, $testoRisposta, $emailCreatore)) {
            $_SESSION['success'] = "Risposta aggiunta con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'inserimento della risposta.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi per inserire una risposta.";
    }
    header('Location: /dashboard');
    exit;
}

// Recupera i progetti attivi tramite il model Project
<<<<<<< Updated upstream
$openProjects = $projectModel->getOpenProjects();

=======
$activeProjects = $projectModel->getActiveProjects();
$allProjects = $projectModel->getAllProjectsWithPhoto();
>>>>>>> Stashed changes
?>