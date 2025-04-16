<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

// Connessione al database
$database = new Database();
$db = $database->getConnection();

/**
 * Ottiene tutti i progetti aperti dal database tramite la view progetti_aperti
 * @param PDO $db
 * @return array
 */
function getActiveProjects($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM progetti_aperti");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        return [];
    }
}

// Recupera i progetti attivi
$activeProjects = getActiveProjects($db);

// Crea l'oggetto User per la view
$user = new User($db);
?>