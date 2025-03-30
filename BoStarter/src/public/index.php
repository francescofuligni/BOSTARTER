<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/templates/header.php';

// Database connection
$db = new mysqli('mysql', 'root', 'root_password', 'bostarter_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Hero section
echo <<<HTML
<div class="bg-primary text-white py-5 mb-5 rounded-3 shadow">
    <div class="container py-4">
        <h1 class="display-4 fw-bold">Fund Innovation & Creativity</h1>
        <p class="fs-4 mb-4">Discover amazing projects or share your ideas with the world.</p>
        <a href="#" class="btn btn-light btn-lg px-4 me-2">Explore Projects</a>
        <a href="#" class="btn btn-outline-light btn-lg px-4">Start Your Project</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="mb-4">Featured Projects</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="#" class="btn btn-outline-primary">View All Projects</a>
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
            <div class="card h-100 project-card">
                <div class="card-body">
                    <h5 class="card-title">{$project['nome']}</h5>
                    <p class="card-text">{$truncatedDesc}</p>
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: {$progress}%" 
                             aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="text-muted small mb-3">€{$totalFunded} raised of €{$project['budget']} goal</p>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="project.php?id={$project['nome']}" class="btn btn-primary w-100">View Project</a>
                </div>
            </div>
        </div>
        HTML;
    }
} else {
    echo '<div class="col-12"><div class="alert alert-info">No projects found</div></div>';
}

echo '</div>'; // Close row

// Categories section
echo <<<HTML
<div class="row mb-4 mt-5">
    <div class="col-12">
        <h2 class="mb-4">Explore Categories</h2>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-4 g-4 mb-5">
    <div class="col">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-laptop fa-3x mb-3 text-primary"></i>
                <h5 class="card-title">Technology</h5>
                <p class="card-text">Innovative tech projects and gadgets</p>
                <a href="#" class="btn btn-outline-primary">Explore</a>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-paint-brush fa-3x mb-3 text-primary"></i>
                <h5 class="card-title">Art & Design</h5>
                <p class="card-text">Creative works from talented artists</p>
                <a href="#" class="btn btn-outline-primary">Explore</a>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                <h5 class="card-title">Publishing</h5>
                <p class="card-text">Books, magazines, and literary works</p>
                <a href="#" class="btn btn-outline-primary">Explore</a>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-music fa-3x mb-3 text-primary"></i>
                <h5 class="card-title">Music</h5>
                <p class="card-text">Albums, concerts, and musical instruments</p>
                <a href="#" class="btn btn-outline-primary">Explore</a>
            </div>
        </div>
    </div>
</div>
HTML;