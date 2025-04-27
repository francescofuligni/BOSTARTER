<?php

require_once __DIR__ . '/../config/MongoLogger.php';

/**
 * Classe per la gestione delle foto dei progetti.
 * Fornisce metodi per aggiungere immagini al database.
 */
class Photo {
    private $conn;
    private $logger;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->logger = new \MongoLogger();
    }
    
    /**
     * Aggiunge una foto a un progetto nel database.
     *
     * @param string $nome_progetto Nome del progetto.
     * @param string $imgData Dati binari dell'immagine.
     * @return bool True se l'inserimento ha successo, false altrimenti.
     */
    public function addPhotoToProject($nome_progetto, $imgData) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_foto(:nome_progetto, :immagine)");
            $stmt->bindParam(':nome_progetto', $nome_progetto);
            $stmt->bindParam(':immagine', $imgData, PDO::PARAM_LOB); // <-- fondamentale!
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuova foto aggiunta al progetto", [
                    'nome_progetto' => $nome_progetto
                ]);
            }
            return ['success' => $result, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }
}
?>
