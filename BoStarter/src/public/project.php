<?php
require_once __DIR__.'/../templates/header.php';

// Database connection
$db = new mysqli('db', 'root', 'password', 'bostarter_db');
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
    $progress = ($totalFunded / $project['budget']) * 100;
    
    // Display project details
    echo '<div class="project-details">';
    echo '<h1>' . htmlspecialchars($project['nome']) . '</h1>';
    echo '<p>' . htmlspecialchars($project['descrizione']) . '</p>';
    echo '<div class="progress-bar"><div class="progress" style="width: ' . min($progress, 100) . '%"></div></div>';
    echo '<p>€' . htmlspecialchars($totalFunded) . ' raised of €' . htmlspecialchars($project['budget']) . ' goal</p>';
    echo '<p>Created by: ' . htmlspecialchars($project['email_utente_creatore']) . ' (Reliability: ' . htmlspecialchars($project['affidabilita']) . '%)</p>';
    
    // Funding form
    echo '<form method="post" action="fund.php">';
    echo '<input type="hidden" name="project_id" value="' . htmlspecialchars($project['nome']) . '">';
    echo '<input type="number" name="amount" min="1" step="0.01" placeholder="Amount to fund">';
    echo '<input type="submit" value="Fund this project">';
    echo '</form>';
    echo '</div>';
} else {
    echo '<p>Project not found</p>';
}

require_once __DIR__.'/../templates/footer.php';
?>