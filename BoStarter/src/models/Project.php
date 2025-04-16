<?php
class Project {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ottieni tutti i progetti attivi tramite la view
    public function getActiveProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_aperti");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Ottieni tutte le foto di un progetto tramite stored procedure
    public function getProjectPhotos($nomeProgetto) {
        try {
            $stmt = $this->conn->prepare("CALL get_foto_progetto(:nome_progetto)");
            $stmt->bindParam(':nome_progetto', $nomeProgetto);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $stmt->closeCursor(); // <-- fondamentale per liberare il cursore!
            return $result;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Ottieni tutti i commenti di un progetto tramite stored procedure
    public function getProjectComments($nomeProgetto) {
        try {
            $stmt = $this->conn->prepare("CALL get_commenti_progetto(:nome_progetto)");
            $stmt->bindParam(':nome_progetto', $nomeProgetto);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>