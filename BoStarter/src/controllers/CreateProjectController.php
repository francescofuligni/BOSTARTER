<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Photo.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$photoModel = new Photo($db);

// Solo i creatori possono creare progetti
if (!$user->isCreator($_SESSION['user_id'])) {
    $_SESSION['error'] = "Solo i creatori possono creare progetti.";
    header('Location: /dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : '';
    $budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
    $data_limite = isset($_POST['data_limite']) ? $_POST['data_limite'] : '';
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
    $email_creatore = $_SESSION['user_id'];

    // Validazione base
    if (empty($nome) || empty($descrizione) || $budget <= 0 || empty($data_limite) || empty($tipo)) {
        $_SESSION['error'] = "Compila tutti i campi obbligatori.";
        header('Location: /create-project');
        exit;
    }

    // Crea il progetto tramite stored procedure (usa la funzione del modello User)
    $success = $user->createProject($nome, $descrizione, $budget, $data_limite, $tipo, $email_creatore);

    if ($success) {
        // Gestisci upload immagini
        error_log(print_r($_FILES, true));
        if (!empty($_FILES['immagini']['name'][0])) {
            foreach ($_FILES['immagini']['tmp_name'] as $idx => $tmpName) {
                if ($_FILES['immagini']['error'][$idx] === UPLOAD_ERR_OK && is_uploaded_file($tmpName)) {
                    $imgData = file_get_contents($tmpName);
                    if (!$photoModel->addPhotoToProject($nome, $imgData)) {
                        error_log("Errore salvataggio immagine $idx");
                    }
                } else {
                    error_log("Errore upload file $idx: " . $_FILES['immagini']['error'][$idx]);
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