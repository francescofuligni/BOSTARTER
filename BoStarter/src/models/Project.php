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
     * @param string $projectName
     * @return array
     */
    public function getProjectPhotos($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT immagine FROM foto_progetto WHERE nome_progetto = :nome_progetto");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni tutti i commenti relativi a un progetto
     * @param string $projectName
     * @return array
     */
    public function getProjectComments($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT id, testo, nickname, data, risposta FROM commenti_progetto WHERE nome_progetto = :nome_progetto ORDER BY data DESC");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni i dettagli di un progetto dalla vista progetti_con_foto
     * @param string $projectName
     * @return array|null
     */
    private function getProjectDetail($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE nome = :nome");
            $stmt->bindParam(':nome', $projectName);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Recupera i dati del progetto, le foto e i commenti
     * @param Project $projectModel
     * @param string $projectName
     * @return array
     */
    function getProjectDetailData($projectModel, $projectName) {
        $project = $projectModel->getProjectDetail($projectName);
        $photos = $projectModel->getProjectPhotos($projectName);
        $comments = $projectModel->getProjectComments($projectName);
        return [$project, $photos, $comments];
    }

    /**
     * Verifica se l'utente ha già finanziato il progetto nella data odierna
     * @param string $projectName
     * @param string $userEmail
     * @return bool
     */
    public function hasFundedToday($projectName, $userEmail) {  // FORSE DA SPOSTARE NEL MODELLO UTENTE?
        try {
            $stmt = $this->conn->prepare(   // FORSE MEGLIO FATTO CON UNA STORED PROCEDURE?
                "SELECT COUNT(*) FROM FINANZIAMENTO 
                 WHERE nome_progetto = :nome_progetto 
                 AND email_utente = :email_utente 
                 AND data = CURDATE()"
            );
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_utente', $userEmail);
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
     * @param string $userEmail
     * @return array
     */
    public function getUserProjects($userEmail) {   // FORSE DA SPOSTARE NEL MODELLO UTENTE?
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE email_utente_creatore = :email_utente");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni tutte le ricompense associate a un progetto
     * @param string $projectName
     * @return array
     */
    public function getProjectRewards($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT codice, descrizione, immagine FROM REWARD WHERE nome_progetto = :nome");
            $stmt->bindParam(':nome', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
