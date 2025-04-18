<?php

class Competences {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Recupera tutte le competenze
    public function getAllCompetences() {
        $sql = "SELECT * FROM COMPETENZA";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Aggiungi una nuova competenza
    public function addCompetence($name) {
        
    }
}
?>
