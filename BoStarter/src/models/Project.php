<?php

/**
 * Classe per la gestione dei progetti.
 * Fornisce metodi per recuperare dettagli, immagini, commenti e altre informazioni sui progetti.
 */
class Project {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Recupera tutti i progetti aperti dalla vista dedicata.
     *
     * @return array Array di progetti aperti.
     */
    public function getOpenProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_aperti");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Recupera tutte le immagini associate a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @return array Array di immagini del progetto.
     */
    public function getProjectPhotos($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT immagine FROM foto_progetto WHERE nome_progetto = :nome_progetto");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Recupera tutti i commenti relativi a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @return array Array di commenti del progetto.
     */
    public function getProjectComments($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT id, testo, nickname, data, risposta FROM commenti_progetto WHERE nome_progetto = :nome_progetto ORDER BY data DESC");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Ottiene i dettagli di un progetto dalla vista progetti_con_foto.
     *
     * @param string $projectName Nome del progetto.
     * @return array|null Dettagli del progetto o null in caso di errore.
     */
    private function getProjectDetail($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE nome = :nome");
            $stmt->bindParam(':nome', $projectName);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Restituisce dettagli, immagini e commenti di un progetto.
     *
     * @param Project $projectModel Modello progetto da cui chiamare i metodi.
     * @param string $projectName Nome del progetto.
     * @return array Array contenente dettagli, immagini e commenti del progetto.
     */
    function getProjectDetailData($projectModel, $projectName) {
        $project = $projectModel->getProjectDetail($projectName);
        $photos = $projectModel->getProjectPhotos($projectName);
        $comments = $projectModel->getProjectComments($projectName);
        return [$project, $photos, $comments];
    }

    /**
     * Verifica se l'utente ha già finanziato il progetto nella data odierna.
     *
     * @param string $projectName Nome del progetto.
     * @param string $userEmail Email dell'utente.
     * @return bool True se ha finanziato oggi, false altrimenti.
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
            echo "Errore: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Recupera tutti i progetti con la prima foto associata.
     *
     * @return array Array di progetti con foto.
     */
    public function getAllProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }
    
    /**
     * Recupera tutti i progetti creati da un utente.
     *
     * @param string $userEmail Email dell'utente.
     * @return array Array di progetti creati dall'utente.
     */
    public function getUserProjects($userEmail) {   // FORSE DA SPOSTARE NEL MODELLO UTENTE?
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE email_utente_creatore = :email_utente");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Recupera tutte le ricompense associate a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @return array Array di ricompense del progetto.
     */
    public function getProjectRewards($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT codice, descrizione, immagine FROM REWARD WHERE nome_progetto = :nome");
            $stmt->bindParam(':nome', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore: " . $e->getMessage();
            return [];
        }
    }
}
?>
