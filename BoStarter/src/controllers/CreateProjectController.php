<?php
// Avvia la sessione se non è già stata avviata
if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Photo.php';

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);
$photoModel = new Photo($conn);

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Solo i creatori possono creare progetti
if (!$userModel->isCreator($_SESSION['user_id'])) {
    $_SESSION['error'] = "Solo i creatori possono creare progetti.";
    header('Location: /dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
    $deadline = isset($_POST['deadline']) ? $_POST['deadline'] : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $creatorEmail = $_SESSION['user_id'];

    // Validazione base
    if (empty($name) || empty($description) || $budget <= 0 || empty($deadline) || empty($type)) {
        $_SESSION['error'] = "Compila tutti i campi obbligatori.";
        header('Location: /create-project');
        exit;
    }

    // Crea il progetto tramite stored procedure (usa la funzione del modello User)
    $creationSuccess = $userModel->createProject($name, $description, $budget, $deadline, $type, $creatorEmail);

    if ($creationSuccess) {
        error_log("Progetto creato: $name da $creatorEmail");

        // Gestisci upload immagini
        error_log(print_r($_FILES, true));
        if (!empty($_FILES['immagini']['name'][0])) {
            foreach ($_FILES['immagini']['tmp_name'] as $idx => $tmpName) {
                if ($_FILES['immagini']['error'][$idx] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                    $imageData = file_get_contents($tmpName);
                    if (!$photoModel->addPhotoToProject($name, $imageData)) {
                        error_log("Errore salvataggio immagine $idx");
                    }
                } else {
                    error_log("Errore upload file $idx: " . $_FILES['immagini']['error'][$idx]);
                }
            }
        }

        // Gestione rewards
        if (!empty($_POST['reward_code'])) {
            foreach ($_POST['reward_code'] as $idx => $code) {
                $description = $_POST['reward_description'][$idx] ?? '';
                $imageTmp = $_FILES['reward_image']['tmp_name'][$idx] ?? '';
                if ($code && $description && $imageTmp && is_uploaded_file($imageTmp)) {
                    $imageData = file_get_contents($imageTmp);
                    $rewardSuccess = $userModel->addRewardToProject($code, $imageData, $description, $name, $creatorEmail);
                    error_log("Reward $code inserita per progetto $name: " . ($rewardSuccess ? "OK" : "FALLITO"));
                } else {
                    error_log("Reward non valida: codice=$code, descrizione=$description, imgTmp=$imageTmp");
                }
            }
        }

        $_SESSION['success'] = "Progetto creato con successo!";
        header('Location: /dashboard');
        exit;
    } else {
        $_SESSION['error'] = "Errore nella creazione del progetto.";
        header('Location: /create-project');
        exit;
    }
}
?>
