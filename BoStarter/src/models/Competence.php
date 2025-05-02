<?php

/**
 * Classe per la gestione delle competenze nel database.
 * Fornisce metodi per recuperare le competenze.
 */
class Competence {
    private $conn;
    private $logger;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->logger = new \MongoLogger();
    }
    
    /**
     * Recupera tutte le competenze dal database.
     *
     * @return array Elenco delle competenze o un array vuoto in caso di errore.
     */
    public function getAllCompetences() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM COMPETENZA");
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Aggiunge una nuova competenza al database (solo amministratori).
     *
     * @param string $name Nome della competenza.
     * @param string $adminEmail Email dell'amministratore.
     * @param string $hashedSecurityCode Codice di sicurezza hashato.
     * @return array ['success' => bool]
     *               Dove 'success' indica l'esito dell'inserimento.
     */
    public function addCompetence($name, $adminEmail, $hashedSecurityCode) {
        try {
            $stmt = $this->conn->prepare("CALL aggiungi_competenza(:competenza, :email, :codice_sicurezza, @is_amministratore)");
            $stmt->bindParam(':competenza', $name);
            $stmt->bindParam(':email', $adminEmail);
            $stmt->bindParam(':codice_sicurezza', $hashedSecurityCode);
            $stmt->execute();
            $result = $this->conn->query("SELECT @is_amministratore as is_amministratore")->fetch(PDO::FETCH_ASSOC);
            if ($result && $result['is_amministratore']) {
                $this->logger->log("Nuova competenza aggiunta", [
                    'nome_competenza' => $name,
                    'email_utente' => $adminEmail
                ]);
                return ['success' => true];
            }
            return ['success' => false];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }
}
?>
