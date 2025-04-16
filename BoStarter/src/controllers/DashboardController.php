<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$projectModel = new Project($db);

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

// Recupera i progetti attivi tramite il model Project
$activeProjects = $projectModel->getActiveProjects();
?>