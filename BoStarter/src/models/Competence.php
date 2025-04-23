<?php

class Competence {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Recupera tutte le competenze dal database
     * @return array
     */
    public function getAllCompetences() {
        $sql = "SELECT * FROM COMPETENZA";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
