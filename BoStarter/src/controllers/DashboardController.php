<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Competence.php';

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
 * Gestisce l'inserimento di una nuova competenza (solo admin).
 */
function handleAddCompetence() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
    if (!$userModel->isAdmin($_SESSION['user_id'])) {
        $_SESSION['error'] = "Solo gli amministratori possono aggiungere competenze.";
        header('Location: /dashboard');
        exit;
    }

    $newCompetence = trim($_POST['new_competence']);
    $securityCode = $_POST['security_code'];
    $adminEmail = $_SESSION['user_id'] ?? '';

    if ($newCompetence && $securityCode && $adminEmail) {
        $hashedSecurityCode = hash('sha256', $securityCode);
        if ($userModel->addCompetence($newCompetence, $adminEmail, $hashedSecurityCode)) {
            $_SESSION['success'] = "Competenza aggiunta con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'aggiunta della competenza. Controlla il codice di sicurezza.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi per aggiungere una competenza.";
    }

    header('Location: /dashboard');
    exit;
}

/**
 * Gestisce l'inserimento di un commento a un progetto.
 */
function handleAddComment() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
    $nomeProgetto = $_POST['nome_progetto'];
    $testoCommento = trim($_POST['testo_commento']);
    $emailUtente = $_SESSION['user_id'] ?? '';

    if ($nomeProgetto && $testoCommento && $emailUtente) {
        if ($userModel->addComment($nomeProgetto, $emailUtente, $testoCommento)) {
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

/**
 * Gestisce l'inserimento di una risposta a un commento.
 */
function handleAddReply() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    
    $idCommento = $_POST['id_commento'];
    $testoRisposta = trim($_POST['testo_risposta']);
    $emailCreatore = $_SESSION['user_id'] ?? '';

    if ($idCommento && $testoRisposta && $emailCreatore) {
        if ($userModel->addReply($idCommento, $testoRisposta, $emailCreatore)) {
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

/**
 * Gestisce l'aggiunta di una competenza da parte dell'utente.
 */
function handleAddSkill() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);

    $skillName = trim($_POST['skill_name']);
    $skillLevel = (int) $_POST['skill_level'];
    $userEmail = $_SESSION['user_id'] ?? '';

    if ($skillName !== '' && $userEmail !== '' && $skillLevel >= 0 && $skillLevel <= 5) {
        if ($userModel->addSkill($userEmail, $skillName, $skillLevel)) {
            $_SESSION['success'] = "Competenza aggiunta con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'aggiunta della competenza.";
        }
    } else {
        $_SESSION['error'] = "Compila correttamente tutti i campi per aggiungere una competenza.";
    }

    header('Location: /dashboard');
    exit;
}

/**
 * Recupera dati per il rendering della dashboard.
 */
function loadDashboardData() {
    $db = new Database();
    $conn = $db->getConnection();
    $userModel = new User($conn);
    $projectModel = new Project($conn);
    $competenceModel = new Competence($conn);
    
    $isCreator = isset($_SESSION['user_id']) && $userModel->isCreator($_SESSION['user_id']);
    $isAdmin = isset($_SESSION['user_id']) && $userModel->isAdmin($_SESSION['user_id']);

    $allProjects = $projectModel->getAllProjects();
    $userProjects = $isCreator ? $userModel->getProjects($_SESSION['user_id']) : [];
    $allCompetences = $competenceModel->getAllCompetences();
    $userSkills = isset($_SESSION['user_id']) ? $userModel->getSkills($_SESSION['user_id']) : [];

    return [$isCreator, $isAdmin, $allProjects, $userProjects, $allCompetences, $userSkills];
}


checkAccess();

// Crea il modello per il controllo iniziale admin
$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);
$isAdmin = isset($_SESSION['user_id']) && $userModel->isAdmin($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_competence'], $_POST['security_code']) && $isAdmin) {
        handleAddCompetence();
    } elseif (isset($_POST['nome_progetto'], $_POST['testo_commento'])) {
        handleAddComment();
    } elseif (isset($_POST['id_commento'], $_POST['testo_risposta'])) {
        handleAddReply();
    } elseif (isset($_POST['skill_name'], $_POST['skill_level'])) {
        handleAddSkill();
    }
}

[$isCreator, $isAdmin, $allProjects, $userProjects, $allCompetences, $userSkills] = loadDashboardData();
?>
