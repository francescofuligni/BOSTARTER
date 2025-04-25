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
            $sql = "SELECT * FROM COMPETENZA";
            $stmt = $this->conn->prepare($sql);
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
            $sql = "SELECT * FROM SKILL_POSSEDUTA
                    WHERE email_utente = :email_utente";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email_utente', $userEmail, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }
}
?>
