<?php
require_once __DIR__.'/templates/header.php';

// Database connection
$db = new mysqli('mysql', 'root', 'root_password', 'bostarter_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch featured projects
$query = "SELECT * FROM PROGETTO ORDER BY data_inserimento DESC LIMIT 5";
$result = $db->query($query);

// Display projects
if ($result->num_rows > 0) {
    echo '<div class="project-list">';
    while($row = $result->fetch_assoc()) {
        echo '<div class="project-card">';
        echo '<h2>' . htmlspecialchars($row['nome']) . '</h2>';
        echo '<p>' . htmlspecialchars($row['descrizione']) . '</p>';
        echo '<p>Goal: €' . htmlspecialchars($row['budget']) . '</p>';
        echo '<a href="project.php?id=' . $row['nome'] . '">View Project</a>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo '<p>No projects found</p>';
}

require_once __DIR__.'/templates/footer.php';
?>