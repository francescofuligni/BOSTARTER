<?php
class Project {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Ottieni tutti i progetti aperti dalla vista dedicata
     * @return array
     */
    public function getOpenProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_aperti");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni tutte le immagini associate a un progetto
     * @param string $nomeProgetto
     * @return array
     */
    public function getProjectPhotos($nomeProgetto) {
        try {
            $stmt = $this->conn->prepare("SELECT immagine FROM foto_progetto WHERE nome_progetto = :nome_progetto");
            $stmt->bindParam(':nome_progetto', $nomeProgetto);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni tutti i commenti relativi a un progetto
     * @param string $nomeProgetto
     * @return array
     */
    public function getProjectComments($nomeProgetto) {
        try {
            $stmt = $this->conn->prepare("SELECT id, testo, nickname, data, risposta FROM commenti_progetto WHERE nome_progetto = :nome_progetto ORDER BY data DESC");
            $stmt->bindParam(':nome_progetto', $nomeProgetto);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni i dettagli di un progetto dalla vista progetti_con_foto
     * @param string $nomeProgetto
     * @return array|null
     */
    public function getProjectDetail($nomeProgetto) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE nome = :nome");
            $stmt->bindParam(':nome', $nomeProgetto);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Verifica se l'utente ha già finanziato il progetto nella data odierna
     * @param string $nomeProgetto
     * @param string $emailUtente
     * @return bool
     */
    public function hasFundedToday($nomeProgetto, $emailUtente) {
        try {
            $stmt = $this->conn->prepare(   // FORSE MEGLIO FATTO CON UNA STORED PROCEDURE?
                "SELECT COUNT(*) FROM FINANZIAMENTO 
                 WHERE nome_progetto = :nome_progetto 
                 AND email_utente = :email_utente 
                 AND data = CURDATE()"
            );
            $stmt->bindParam(':nome_progetto', $nomeProgetto);
            $stmt->bindParam(':email_utente', $emailUtente);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Ottieni tutti i progetti con la prima foto associata
     * @return array
     */
    public function getAllProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Ottieni tutti i progetti creati da un utente
     * @param string $emailUtente
     * @return array
     */
    public function getUserProjects($emailUtente) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE email_utente = :email_utente");
            $stmt->bindParam(':email_utente', $emailUtente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni tutte le ricompense associate a un progetto
     * @param string $nomeProgetto
     * @return array
     */
    public function getRewardsForProject($nomeProgetto) {
        try {
            $stmt = $this->conn->prepare("SELECT codice, descrizione, immagine FROM REWARD WHERE nome_progetto = :nome");
            $stmt->bindParam(':nome', $nomeProgetto);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
