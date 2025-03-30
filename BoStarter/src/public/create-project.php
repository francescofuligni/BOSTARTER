<?php
require_once __DIR__.'/templates/header.php';

// Connessione al database
$db = new mysqli('mysql', 'root', 'root_password', 'bostarter_db');
if ($db->connect_error) {
    die("Connessione fallita: " . $db->connect_error);
}

// Inizializza le variabili
$success = false;
$error = '';
$formSubmitted = false;

// Gestione dell'invio del modulo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project'])) {
    $formSubmitted = true;
    
    // Ottieni i dati del form
    $nome = $_POST['nome'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $budget = $_POST['budget'] ?? 0;
    $data_limite = $_POST['data_limite'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $email_creatore = $_POST['email_creatore'] ?? '';
    
    // Valida i dati del form
    if (empty($nome) || empty($descrizione) || empty($budget) || empty($data_limite) || empty($tipo) || empty($email_creatore)) {
        $error = 'Tutti i campi sono obbligatori';
    } else {
        // Chiama la stored procedure per creare un progetto
        $stmt = $db->prepare("CALL crea_progetto(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdsss', $nome, $descrizione, $budget, $data_limite, $tipo, $email_creatore);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Errore nella creazione del progetto: ' . $db->error;
        }
    }
}
?>

<div class="row mb-5">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Crea Nuovo Progetto</h2>
            </div>
            <div class="card-body">
                <?php if($formSubmitted && $success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Progetto creato con successo!</div>
                <?php endif; ?>
                <?php if($formSubmitted && !empty($error)): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Progetto</label>
                        <input type="text" class="form-control" id="nome" name="nome" required maxlength="32">
                        <div class="form-text">Scegli un nome unico per il tuo progetto (max 32 caratteri)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descrizione" class="form-label">Descrizione</label>
                        <textarea class="form-control" id="descrizione" name="descrizione" rows="4" required maxlength="255"></textarea>
                        <div class="form-text">Descrivi il tuo progetto in dettaglio (max 255 caratteri)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="budget" class="form-label">Obiettivo di Finanziamento (€)</label>
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" class="form-control" id="budget" name="budget" step="0.01" min="1" required>
                        </div>
                        <div class="form-text">Imposta un obiettivo di finanziamento realistico per il tuo progetto</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="data_limite" class="form-label">Data di Scadenza</label>
                        <input type="date" class="form-control" id="data_limite" name="data_limite" required>
                        <div class="form-text">Imposta una data di scadenza per la tua campagna di finanziamento</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo di Progetto</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" id="tipo_software" value="SOFTWARE" required>
                            <label class="form-check-label" for="tipo_software">
                                <i class="fas fa-code me-2"></i>Software
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" id="tipo_hardware" value="HARDWARE">
                            <label class="form-check-label" for="tipo_hardware">
                                <i class="fas fa-microchip me-2"></i>Hardware
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email_creatore" class="form-label">Email del Creatore</label>
                        <input type="email" class="form-control" id="email_creatore" name="email_creatore" required maxlength="32">
                        <div class="form-text">Inserisci la tua email registrata come creatore</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="create_project" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket me-2"></i>Lancia Progetto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Validazione del form
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php
require_once __DIR__.'/templates/footer.php';
?>