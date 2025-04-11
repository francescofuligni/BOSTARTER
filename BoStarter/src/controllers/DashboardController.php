<?php
// Includiamo il database
require_once __DIR__ . '/../config/Database.php';

// Creiamo la classe controller per la dashboard
class DashboardController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Ottiene tutti i progetti aperti dal database
     * @return array Array dei progetti aperti
     */
    public function getActiveProjects() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM progetti_aperti");
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            return [];
        }
    }
}

// Istanziamo il controller per utilizzarlo nella vista
$dashboardController = new DashboardController();
$activeProjects = $dashboardController->getActiveProjects();
?>