<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Component.php';
require_once __DIR__ . '/../models/Profile.php'; 
require_once __DIR__ . '/../models/Competence.php'; // Add this line to import Competence class

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
    $profileModel = new Profile($conn);
    
    $projectName = $_GET['nome'] ?? '';
    $project = $photos = $comments = $rewards = $components = [];
    $profiles = [];
    $userSkills = [];
    $applications = [];
    $hasFundedToday = false;
    $isCreator = false;

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

        if (isset($_SESSION['user_id'])) {
            $isCreator = $userModel->isProjectCreator($_SESSION['user_id'], $projectName);
            
            $userSkillsResult = $userModel->getSkills($_SESSION['user_id']);
            if (isset($userSkillsResult['success']) && $userSkillsResult['success']) {
                $userSkills = $userSkillsResult['data'] ?? [];
            }
        }
        
        $profilesResult = $profileModel->getProjectProfiles($projectName);
        if (isset($profilesResult['success']) && $profilesResult['success']) {
            $profiles = $profilesResult['data'] ?? [];
            
            foreach ($profiles as &$profile) {
                $skillsResult = $profileModel->getRequiredSkills($profile['id']);
                $profile['skills'] = (isset($skillsResult['success']) && $skillsResult['success']) ? $skillsResult['data'] : [];
                
                if ($isCreator) {
                    $applicationsResult = $profileModel->getProfileApplications($profile['id']);
                    $profile['applications'] = (isset($applicationsResult['success']) && $applicationsResult['success']) ? $applicationsResult['data'] : [];
                }
                
                if (isset($_SESSION['user_id']) && !$isCreator) {
                    $profile['has_applied'] = $profileModel->hasUserApplied($_SESSION['user_id'], $profile['id']);
                }
            }
        }
    }


    $competenceModel = new Competence($conn);
    $allCompetencesResult = $competenceModel->getAllCompetences();
    $allCompetences = ($allCompetencesResult['success']) ? $allCompetencesResult['data'] : [];

    return [$project, $photos, $comments, $rewards, $components, $hasFundedToday, $profiles, $userSkills, $isCreator, $allCompetences];
}

/**
 * Creates a new profile.
 */
function handleCreateProfile() {
    $db = new Database();
    $conn = $db->getConnection();
    $profileModel = new Profile($conn);
    
    $profileName = trim($_POST['profile_name'] ?? '');
    $projectName = $_POST['project_name'] ?? '';
    $creatorEmail = $_SESSION['user_id'] ?? '';
    
    if ($profileName && $projectName && $creatorEmail) {
        $result = $profileModel->createProfile($profileName, $projectName, $creatorEmail);
        if ($result['success'] && $result['profileId']) {
            if (isset($_POST['skill_name']) && isset($_POST['skill_level']) && is_array($_POST['skill_name'])) {
                foreach ($_POST['skill_name'] as $idx => $skillName) {
                    if (!empty($skillName) && isset($_POST['skill_level'][$idx])) {
                        $level = intval($_POST['skill_level'][$idx]);
                        if ($level >= 0 && $level <= 5) {
                            $profileModel->addRequiredSkill($result['profileId'], $skillName, $level, $creatorEmail);
                        }
                    }
                }
            }
            $_SESSION['success'] = "Profilo creato con successo!";
        } else {
            $_SESSION['error'] = "Errore nella creazione del profilo. Verifica che il progetto sia di tipo SOFTWARE.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi richiesti.";
    }
    
    header('Location: /project-detail?nome=' . urlencode($projectName));
    exit;
}

/**
 * Adds a required skill to a profile.
 */
function handleAddRequiredSkill() {
    $db = new Database();
    $conn = $db->getConnection();
    $profileModel = new Profile($conn);
    
    $profileId = intval($_POST['profile_id'] ?? 0);
    $skillName = trim($_POST['skill_name'] ?? '');
    $skillLevel = intval($_POST['skill_level'] ?? 0);
    $creatorEmail = $_SESSION['user_id'] ?? '';
    $projectName = $_POST['project_name'] ?? '';
    
    if ($profileId && $skillName && $creatorEmail && $skillLevel >= 0 && $skillLevel <= 5) {
        $result = $profileModel->addRequiredSkill($profileId, $skillName, $skillLevel, $creatorEmail);
        if ($result['success']) {
            $_SESSION['success'] = "Competenza richiesta aggiunta con successo!";
        } else {
            $_SESSION['error'] = "Errore nell'aggiunta della competenza richiesta.";
        }
    } else {
        $_SESSION['error'] = "Compila tutti i campi richiesti correttamente.";
    }
    
    header('Location: /project-detail?nome=' . urlencode($projectName));
    exit;
}

/**
 * Manages an application (accept or reject).
 */
function handleManageApplication() {
    $db = new Database();
    $conn = $db->getConnection();
    $profileModel = new Profile($conn);
    
    $applicantEmail = $_POST['applicant_email'] ?? '';
    $profileId = intval($_POST['profile_id'] ?? 0);
    $creatorEmail = $_SESSION['user_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $projectName = $_POST['project_name'] ?? '';
    
    if ($applicantEmail && $profileId && $creatorEmail && ($status === 'ACCETTATA' || $status === 'RIFIUTATA')) {
        $result = $profileModel->manageApplication($applicantEmail, $profileId, $creatorEmail, $status);
        if ($result['success']) {
            $_SESSION['success'] = "Candidatura " . strtolower($status) . " con successo!";
        } else {
            $_SESSION['error'] = "Errore nella gestione della candidatura.";
        }
    } else {
        $_SESSION['error'] = "Dati mancanti o non validi.";
    }
    
    header('Location: /project-detail?nome=' . urlencode($projectName));
    exit;
}

/**
 * Submits an application for a profile.
 */
function handleApplyForProfile() {
    $db = new Database();
    $conn = $db->getConnection();
    $profileModel = new Profile($conn);
    
    $userEmail = $_SESSION['user_id'] ?? '';
    $profileId = intval($_POST['profile_id'] ?? 0);
    $projectName = $_POST['project_name'] ?? '';
    
    if ($userEmail && $profileId) {
        if ($profileModel->hasUserApplied($userEmail, $profileId)) {
            $_SESSION['error'] = "Hai già inviato una candidatura per questo profilo.";
        } else {
            $result = $profileModel->applyForProfile($userEmail, $profileId);
            if ($result['success']) {
                $_SESSION['success'] = "Candidatura inviata con successo!";
            } else {
                $_SESSION['error'] = "Errore nell'invio della candidatura. Verifica di possedere tutte le competenze richieste al livello adeguato.";
            }
        }
    } else {
        $_SESSION['error'] = "Dati mancanti o non validi.";
    }
    
    header('Location: /project-detail?nome=' . urlencode($projectName));
    exit;
}

/**
 * Rimuove un commento.
 */
function handleRemoveComment() {
    $db = new Database();
    $conn = $db->getConnection();
    $projectModel = new Project($conn);
    
    $commentId = intval($_POST['id_commento'] ?? 0);
    $userEmail = $_SESSION['user_id'] ?? '';
    $projectName = $_POST['nome_progetto'] ?? '';

    if ($commentId && $userEmail) {
        $result = $projectModel->removeComment($commentId, $userEmail);
        if (isset($result['success']) && $result['success']) {
            $_SESSION['success'] = "Commento rimosso con successo!";
        } else {
            $_SESSION['error'] = "Errore nella rimozione del commento o permessi insufficienti.";
        }
    } else {
        $_SESSION['error'] = "Dati mancanti per la rimozione del commento.";
    }

    header('Location: /project-detail?nome=' . urlencode($projectName));
    exit;
}

checkAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nome_progetto'], $_POST['testo_commento'])) {
        handleAddComment();
    }
    if (isset($_POST['id_commento'], $_POST['testo_risposta'])) {
        handleAddReply();
    }
    if (isset($_POST['id_commento'], $_POST['rimuovi_commento'])) {
        handleRemoveComment();
    }
    if (isset($_POST['nome_progetto'], $_POST['importo'], $_POST['codice_reward'])) {
        handleFundProject();
    }
    if (isset($_POST['profile_name'], $_POST['project_name'])) {
        handleCreateProfile();
    }
    if (isset($_POST['applicant_email'], $_POST['profile_id'], $_POST['status'])) {
        handleManageApplication();
    }
    if (isset($_POST['profile_id']) && isset($_POST['apply'])) {
        handleApplyForProfile();
    }
    if (isset($_POST['profile_id'], $_POST['skill_name'], $_POST['skill_level'])) {
        handleAddRequiredSkill();
    }
}

if (isset($_GET['nome'])) {
    [$project, $photos, $comments, $rewards, $components, $hasFundedToday, $profiles, $userSkills, $isCreator, $allCompetences] = loadProjectData();
}
?>
