<?php
require_once __DIR__.'/../templates/header.php';

// Database connection
$db = new mysqli('mysql', 'root', 'root_password', 'bostarter_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get project ID from URL
$projectId = $_GET['id'] ?? '';

// Fetch project details
$projectQuery = "SELECT p.*, u.affidabilita FROM PROGETTO p JOIN UTENTE_CREATORE u ON p.email_utente_creatore = u.email_utente WHERE p.nome = ?";
$stmt = $db->prepare($projectQuery);
$stmt->bind_param('s', $projectId);
$stmt->execute();
$projectResult = $stmt->get_result();

// Calculate funding progress
if ($projectResult->num_rows > 0) {
    $project = $projectResult->fetch_assoc();
    
    // Calculate funding progress
    $fundingQuery = "SELECT SUM(importo) as total FROM FINANZIAMENTO WHERE nome_progetto = ?";
    $stmt = $db->prepare($fundingQuery);
    $stmt->bind_param('s', $projectId);
    $stmt->execute();
    $fundingResult = $stmt->get_result();
    $fundingData = $fundingResult->fetch_assoc();
    $totalFunded = $fundingData['total'] ?? 0;
    $progress = ($project['budget'] > 0) ? ($totalFunded / $project['budget']) * 100 : 0;
    
    // Display project details
    echo <<<HTML
    <div class="row mb-5">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="card-title display-5 fw-bold text-primary mb-3">{$project['nome']}</h1>
                    <p class="lead">{$project['descrizione']}</p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-user-circle fa-2x text-muted"></i>
                        </div>
                        <div>
                            <p class="mb-0">Created by: <strong>{$project['email_utente_creatore']}</strong></p>
                            <div class="d-flex align-items-center">
                                <div class="me-2">Reliability:</div>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {$project['affidabilita']}%" 
                                        aria-valuenow="{$project['affidabilita']}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="ms-2">{$project['affidabilita']}%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="text-muted mb-3">Funding Progress</h5>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                                style="width: {$progress}%" aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100">
                                {$progress}%
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">€{$totalFunded}</h4>
                                <small class="text-muted">raised so far</small>
                            </div>
                            <div class="text-end">
                                <h4 class="mb-0">€{$project['budget']}</h4>
                                <small class="text-muted">funding goal</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Back this project</h4>
                </div>
                <div class="card-body">
                    <form method="POST" class="mb-3">
                        <div class="mb-3">
                            <label for="email" class="form-label">Your Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (€)</label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" class="form-control" id="amount" name="amount" min="1" required>
                            </div>
                        </div>
                        <button type="submit" name="back_project" class="btn btn-primary w-100"><i class="fas fa-donate me-2"></i>Back Project</button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0"><i class="fas fa-bell me-2"></i>Follow this project</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="follow_email" class="form-label">Your Email</label>
                            <input type="email" class="form-control" id="follow_email" name="email" required>
                        </div>
                        <button type="submit" name="follow_project" class="btn btn-secondary w-100"><i class="fas fa-star me-2"></i>Follow Project</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    HTML;
} else {
    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Project not found</div>';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['back_project'])) {
        $amount = $_POST['amount'];
        $email = $_POST['email'];
        $db->query("CALL BackProject('$projectId', '$email', $amount)");
        echo '<div class="alert alert-success mt-3"><i class="fas fa-check-circle me-2"></i>Thank you for backing this project!</div>';
    } elseif (isset($_POST['follow_project'])) {
        $email = $_POST['email'];
        $db->query("CALL FollowProject('$projectId', '$email')");
        echo '<div class="alert alert-success mt-3"><i class="fas fa-check-circle me-2"></i>You are now following this project!</div>';
    }
}

require_once __DIR__.'/../templates/footer.php';
?>