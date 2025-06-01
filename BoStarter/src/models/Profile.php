<?php
/**
 * Classe per la gestione dei profili e delle candidature.
 * Fornisce metodi per creare e gestire profili, competenze e candidature.
 */
class Profile {
    private $conn;
    private $logger;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->logger = new \MongoLogger();
    }
    
    /**
     * Ottiene tutti i profili per un progetto specifico.
     *
     * @param string $projectName Nome del progetto.
     * @return array ['success' => bool, 'data' => array]
     */
    public function getProjectProfiles($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM PROFILO WHERE nome_progetto = :nome_progetto");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
    
    /**
     * Ottiene le competenze richieste per un profilo specifico.
     *
     * @param int $profileId ID del profilo.
     * @return array ['success' => bool, 'data' => array]
     */
    public function getRequiredSkills($profileId) {
        try {
            $stmt = $this->conn->prepare("SELECT sr.nome_competenza, sr.livello, c.nome 
                                          FROM SKILL_RICHIESTA sr
                                          JOIN COMPETENZA c ON sr.nome_competenza = c.nome
                                          WHERE sr.id_profilo = :id_profilo");
            $stmt->bindParam(':id_profilo', $profileId);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
    
    /**
     * Ottiene le candidature per un profilo specifico.
     *
     * @param int $profileId ID del profilo.
     * @return array ['success' => bool, 'data' => array]
     */
    public function getProfileApplications($profileId) {
        try {
            $stmt = $this->conn->prepare("SELECT c.*, u.nickname 
                                          FROM CANDIDATURA c
                                          JOIN UTENTE u ON c.email_utente = u.email
                                          WHERE c.id_profilo = :id_profilo");
            $stmt->bindParam(':id_profilo', $profileId);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
    
    /**
     * Crea un nuovo profilo (solo per progetti SOFTWARE).
     *
     * @param string $name Nome del profilo.
     * @param string $projectName Nome del progetto.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool, 'profileId' => int|null]
     */
    public function createProfile($name, $projectName, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_profilo(:nome, :nome_progetto, :email_creatore, @is_creatore_progetto)");
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $stmt->execute();
            
            $resultEsito = $this->conn->query("SELECT @is_creatore_progetto as is_creatore_progetto")->fetch(PDO::FETCH_ASSOC);
            if (!$resultEsito || !$resultEsito['is_creatore_progetto']) {
                return ['success' => false, 'profileId' => null];
            }
            
            // Ottiene l'ID del profilo appena creato
            $stmt = $this->conn->prepare("SELECT id FROM PROFILO WHERE nome = :nome AND nome_progetto = :nome_progetto ORDER BY id DESC LIMIT 1");
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->logger->log("Nuovo profilo creato", [
                    'nome_profilo' => $name,
                    'nome_progetto' => $projectName,
                    'email_creatore' => $creatorEmail
                ]);
                return ['success' => true, 'profileId' => $result['id']];
            }
            return ['success' => false, 'profileId' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'profileId' => null];
        }
    }
    
    /**
     * Aggiunge una competenza richiesta a un profilo.
     *
     * @param int $profileId ID del profilo.
     * @param string $competence Nome della competenza.
     * @param int $level Livello di competenza.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool]
     */
    public function addRequiredSkill($profileId, $competence, $level, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_skill_richiesta(:id_profilo, :email_creatore, :competenza, :livello, @is_creatore_progetto)");
            $stmt->bindParam(':id_profilo', $profileId);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $stmt->bindParam(':competenza', $competence);
            $stmt->bindParam(':livello', $level);
            $stmt->execute();
            
            $resultEsito = $this->conn->query("SELECT @is_creatore_progetto as is_creatore_progetto")->fetch(PDO::FETCH_ASSOC);
            if (!$resultEsito || !$resultEsito['is_creatore_progetto']) {
                return ['success' => false];
            }
            
            $this->logger->log("Nuova skill richiesta aggiunta", [
                'id_profilo' => $profileId,
                'competenza' => $competence,
                'livello' => $level,
                'email_creatore' => $creatorEmail
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }
    
    /**
     * Invia una candidatura per un profilo.
     *
     * @param string $userEmail Email dell'utente.
     * @param int $profileId ID del profilo.
     * @return array ['success' => bool]
     */
    public function applyForProfile($userEmail, $profileId) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_candidatura(:email_utente, :id_profilo, @esito)");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':id_profilo', $profileId);
            $stmt->execute();

            $resultEsito = $this->conn->query("SELECT @esito as esito")->fetch(PDO::FETCH_ASSOC);
            if (!$resultEsito || !$resultEsito['esito']) {
                // Opzionale: informazioni di debug
                error_log("Candidatura fallita per $userEmail su profilo $profileId");
                return ['success' => false];
            }
            $this->logger->log("Nuova candidatura inserita", [
                'email_utente' => $userEmail,
                'id_profilo' => $profileId
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }
    
    /**
     * Gestisce una candidatura (accetta o rifiuta).
     *
     * @param string $applicantEmail Email del candidato.
     * @param int $profileId ID del profilo.
     * @param string $creatorEmail Email del creatore.
     * @param string $status Nuovo stato ('ACCETTATA' o 'RIFIUTATA').
     * @return array ['success' => bool]
     */
    public function manageApplication($applicantEmail, $profileId, $creatorEmail, $status) {
        try {
            $stmt = $this->conn->prepare("CALL gestisci_candidatura(:email_candidato, :id_profilo, :email_creatore, :stato, @is_creatore_progetto)");
            $stmt->bindParam(':email_candidato', $applicantEmail);
            $stmt->bindParam(':id_profilo', $profileId);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $stmt->bindParam(':stato', $status);
            $stmt->execute();
            
            $resultEsito = $this->conn->query("SELECT @is_creatore_progetto as is_creatore_progetto")->fetch(PDO::FETCH_ASSOC);
            if (!$resultEsito || !$resultEsito['is_creatore_progetto']) {
                return ['success' => false];
            }
            
            $this->logger->log("Candidatura gestita", [
                'email_candidato' => $applicantEmail,
                'id_profilo' => $profileId,
                'email_creatore' => $creatorEmail,
                'stato' => $status
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }
    
    /**
     * Verifica se un utente ha già fatto candidatura per un profilo.
     *
     * @param string $userEmail Email dell'utente.
     * @param int $profileId ID del profilo.
     * @return bool True se ha già fatto candidatura, false altrimenti.
     */
    public function hasUserApplied($userEmail, $profileId) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM CANDIDATURA 
                                          WHERE email_utente = :email_utente 
                                          AND id_profilo = :id_profilo");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':id_profilo', $profileId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result && $result['count'] > 0);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Restituisce lo stato della candidatura di un utente per un determinato profilo.
     * 
     * @param string $userEmail L'email dell'utente.
     * @param int $profileId L'ID del profilo.
     * @return string|null Lo stato della candidatura ('ACCETTATA', 'ATTESA', 'RIFIUTATA') o null se non esiste.
     */
    public function getUserApplicationStatus($userEmail, $profileId) {
        try {
            $stmt = $this->conn->prepare("SELECT stato FROM CANDIDATURA WHERE email_utente = :email_utente AND id_profilo = :id_profilo");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':id_profilo', $profileId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return $result['stato'];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return null;
    }
}
?>