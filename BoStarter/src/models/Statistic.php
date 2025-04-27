<?php
/**
 * Classe per la gestione delle statistiche del sistema.
 * Fornisce metodi per ottenere classifiche e dati su progetti imminenti.
 */
class Statistic {
    private $conn;
    
    /**
     * Costruttore della classe Statistic.
     * 
     * @param PDO $db Connessione al database.
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Restituisce la classifica dei creatori di progetti.
     *
     * @return array ['success' => bool, 'data' => array]
     *               Dove 'data' è una lista dei creatori ordinati per performance, o un array vuoto in caso di errore.
     */
    public function getTopCreators() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM classifica_creatori");;
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Restituisce i progetti in scadenza.
     *
     * @return array ['success' => bool, 'data' => array]
     *               Dove 'data' è una lista di progetti prossimi alla scadenza, o un array vuoto in caso di errore.
     */
    public function getExpiringProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_in_scadenza");
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Restituisce la classifica dei finanziatori.
     *
     * @return array ['success' => bool, 'data' => array]
     *               Dove 'data' è una lista dei finanziatori ordinati per contributi, o un array vuoto in caso di errore.
     */
    public function getTopFunders() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM classifica_finanziatori");
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
}
?>
