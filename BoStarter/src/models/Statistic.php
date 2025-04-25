<?php
/**
 * Classe per la gestione delle statistiche del sistema.
 * Fornisce metodi per ottenere classifiche e dati su progetti imminenti.
 */
class Statistic {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Restituisce la classifica dei creatori di progetti.
     *
     * @return array Lista dei creatori ordinati per performance.
     */
    public function getTopCreators() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM classifica_creatori");;
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Restituisce i progetti in scadenza.
     *
     * @return array Lista di progetti prossimi alla scadenza.
     */
    public function getExpiringProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_in_scadenza");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Restituisce la classifica dei finanziatori.
     *
     * @return array Lista dei finanziatori ordinati per contributi.
     */
    public function getTopFunders() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM classifica_finanziatori");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }
}
?>
