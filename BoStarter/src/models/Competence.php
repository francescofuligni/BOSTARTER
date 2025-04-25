<?php

/**
 * Classe per la gestione delle competenze nel database.
 * Fornisce metodi per recuperare le competenze.
 */
class Competence {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }
    
    /**
     * Recupera tutte le competenze associate a uno specifico utente.
     *
     * @param string $userEmail Email dell'utente.
     * @return array Elenco delle competenze dell'utente o un array vuoto in caso di errore.
     */
    public function getSkills($userEmail) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM SKILL_POSSEDUTA WHERE email_utente = :email_utente");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }
}
?>
