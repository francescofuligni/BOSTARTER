<?php
// Includiamo il database
require_once __DIR__ . '/../config/Database.php';

// Creiamo la classe controller per la dashboard
class HomeController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Ottiene tutti i progetti aperti dal database
     * @return array Array dei progetti aperti
     */
    public function getProjectsPreview() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM progetti_con_foto LIMIT 6");
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
$homeController = new HomeController();
$projects = $homeController->getProjectsPreview();
?>