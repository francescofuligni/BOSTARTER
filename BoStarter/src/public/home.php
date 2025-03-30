<?php
require_once __DIR__.'/templates/header.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Database connection
$db = new mysqli('mysql', 'root', 'root_password', 'bostarter_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Ottieni informazioni sull'utente
$userEmail = getUserEmail();
$userType = getUserType();

// Hero section personalizzata in base al tipo di utente
echo <<<HTML
<div class="bg-primary text-white py-5 mb-5 rounded-3 shadow">
    <div class="container py-4">
        <h1 class="display-4 fw-bold">Benvenuto nella tua area personale</h1>
        <p class="fs-4 mb-4">Gestisci i tuoi progetti e finanziamenti su BoStarter.</p>
        
HTML;

// Mostra pulsanti diversi in base al tipo di utente
if ($userType === 'creatore') {
    echo <<<HTML
        <a href="create-project.php" class="btn btn-light btn-lg px-4 me-2">Crea Nuovo Progetto</a>
        <a href="#my-projects" class="btn btn-outline-light btn-lg px-4">I Miei Progetti</a>
    HTML;
} elseif ($userType === 'amministratore') {
    echo <<<HTML
        <a href="#" class="btn btn-light btn-lg px-4 me-2">Gestione Utenti</a>
        <a href="#" class="btn btn-outline-light btn-lg px-4">Gestione Progetti</a>
    HTML;
} else { // utente normale
    echo <<<HTML
        <a href="#" class="btn btn-light btn-lg px-4 me-2">Esplora Progetti</a>
        <a href="#" class="btn btn-outline-light btn-lg px-4">I Miei Finanziamenti</a>
    HTML;
}

echo <<<HTML
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="mb-4">Progetti in Evidenza</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="#" class="btn btn-outline-primary">Vedi Tutti i Progetti</a>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
HTML;

// Fetch featured projects
$query = "SELECT p.*, u.affidabilita, 
         (SELECT SUM(importo) FROM FINANZIAMENTO WHERE nome_progetto = p.nome) as total_funded 
         FROM PROGETTO p 
         JOIN UTENTE_CREATORE u ON p.email_utente_creatore = u.email_utente 
         ORDER BY p.data_inserimento DESC LIMIT 6";

$result = $db->query($query);

if ($result && $result->num_rows > 0) {
    while ($project = $result->fetch_assoc()) {
        $totalFunded = $project['total_funded'] ?? 0;
        $progress = $project['budget'] > 0 ? ($totalFunded / $project['budget']) * 100 : 0;
        $truncatedDesc = strlen($project['descrizione']) > 100 ? substr($project['descrizione'], 0, 100) . '...' : $project['descrizione'];
        
        echo <<<HTML
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">{$project['nome']}</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary">{$project['tipo']}</span>
                        <small class="text-muted">Affidabilità: {$project['affidabilita']}%</small>
                    </div>
                    <p class="card-text">{$truncatedDesc}</p>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: {$progress}%" 
                            aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100">{$progress}%</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">{$totalFunded}€ raccolti su {$project['budget']}€</small>
                        <a href="project.php?name={$project['nome']}" class="btn btn-sm btn-outline-primary">Dettagli</a>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
} else {
    echo '<div class="col-12"><p class="text-center">Nessun progetto disponibile al momento.</p></div>';
}

echo '</div>';

// Sezione specifica per creatori
if ($userType === 'creatore') {
    echo '<h2 id="my-projects" class="mb-4">I Miei Progetti</h2>';
    echo '<div class="row row-cols-1 row-cols-md-3 g-4 mb-5">';
    
    $query = "SELECT p.*, 
             (SELECT SUM(importo) FROM FINANZIAMENTO WHERE nome_progetto = p.nome) as total_funded 
             FROM PROGETTO p 
             WHERE p.email_utente_creatore = ? 
             ORDER BY p.data_inserimento DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($project = $result->fetch_assoc()) {
            $totalFunded = $project['total_funded'] ?? 0;
            $progress = $project['budget'] > 0 ? ($totalFunded / $project['budget']) * 100 : 0;
            $truncatedDesc = strlen($project['descrizione']) > 100 ? substr($project['descrizione'], 0, 100) . '...' : $project['descrizione'];
            
            echo <<<HTML
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{$project['nome']}</h5>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary">{$project['tipo']}</span>
                            <small class="text-muted">Creato il: {$project['data_inserimento']}</small>
                        </div>
                        <p class="card-text">{$truncatedDesc}</p>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: {$progress}%" 
                                aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100">{$progress}%</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{$totalFunded}€ raccolti su {$project['budget']}€</small>
                            <a href="project.php?name={$project['nome']}" class="btn btn-sm btn-outline-primary">Gestisci</a>
                        </div>
                    </div>
                </div>
            </div>
            HTML;
        }
    } else {
        echo '<div class="col-12"><p class="text-center">Non hai ancora creato nessun progetto. <a href="create-project.php">Crea il tuo primo progetto</a>.</p></div>';
    }
    
    echo '</div>';
}

// Sezione per utenti normali: i miei finanziamenti
if ($userType === 'utente') {
    echo '<h2 class="mb-4">I Miei Finanziamenti</h2>';
    echo '<div class="row">';
    
    $query = "SELECT f.*, p.nome as nome_progetto, p.descrizione, p.tipo 
             FROM FINANZIAMENTO f 
             JOIN PROGETTO p ON f.nome_progetto = p.nome 
             WHERE f.email_utente = ? 
             ORDER BY f.data_finanziamento DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        echo '<div class="table-responsive"><table class="table table-striped">';
        echo '<thead><tr><th>Progetto</th><th>Tipo</th><th>Importo</th><th>Data</th><th>Azioni</th></tr></thead>';
        echo '<tbody>';
        
        while ($finanziamento = $result->fetch_assoc()) {
            echo <<<HTML
            <tr>
                <td>{$finanziamento['nome_progetto']}</td>
                <td><span class="badge bg-primary">{$finanziamento['tipo']}</span></td>
                <td>{$finanziamento['importo']}€</td>
                <td>{$finanziamento['data_finanziamento']}</td>
                <td>
                    <a href="project.php?name={$finanziamento['nome_progetto']}" class="btn btn-sm btn-outline-primary">Dettagli</a>
                </td>
            </tr>
            HTML;
        }
        
        echo '</tbody></table></div>';
    } else {
        echo '<div class="col-12"><p class="text-center">Non hai ancora effettuato nessun finanziamento.</p></div>';
    }
    
    echo '</div>';
}

// Sezione per amministratori
if ($userType === 'amministratore') {
    echo '<h2 class="mb-4">Pannello di Amministrazione</h2>';
    echo '<div class="row">';
    
    echo <<<HTML
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Gestione Utenti</h5>
            </div>
            <div class="card-body">
                <p>Gestisci gli utenti della piattaforma, modifica i loro dati o elimina account.</p>
                <a href="#" class="btn btn-primary">Vai alla Gestione Utenti</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Gestione Progetti</h5>
            </div>
            <div class="card-body">
                <p>Approva, modifica o elimina progetti dalla piattaforma.</p>
                <a href="#" class="btn btn-primary">Vai alla Gestione Progetti</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Statistiche</h5>
            </div>
            <div class="card-body">
                <p>Visualizza le statistiche della piattaforma, come numero di utenti, progetti e finanziamenti.</p>
                <a href="#" class="btn btn-primary">Vai alle Statistiche</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Log di Sistema</h5>
            </div>
            <div class="card-body">
                <p>Visualizza i log di sistema per monitorare l'attività della piattaforma.</p>
                <a href="#" class="btn btn-primary">Vai ai Log</a>
            </div>
        </div>
    </div>
    HTML;
    
    echo '</div>';
}

require_once __DIR__.'/templates/footer.php';
?>