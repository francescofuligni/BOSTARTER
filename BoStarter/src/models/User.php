<?php
// Creo la classe User per metterci tutti le operazioni che servono per gli utenti come login, registrazione, ...
require_once __DIR__ . '/../config/MongoLogger.php';

/**
 * Classe per la gestione degli utenti.
 * Fornisce metodi per login, registrazione, gestione progetti e interazioni utente.
 */
class User {
    private $conn;
    private $logger;

    public function __construct($db) {
        $this->conn = $db;
        $this->logger = new \MongoLogger();
    }

    /**
     * Effettua il login dell'utente tramite stored procedure.
     *
     * @param string $email Email dell'utente.
     * @param string $hashedPassword Password hashata dell'utente.
     * @return array ['success' => bool, 'data' => array|null]
     *               Dove 'data' contiene i dati dell'utente autenticato, oppure null in caso di errore o autenticazione fallita.
     */
    public function login($email, $hashedPassword) {
        try {
            $stmt = $this->conn->prepare("CALL autenticazione_utente(:email, :password, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            
            $result = $this->conn->query("SELECT @autenticato as autenticato")->fetch(PDO::FETCH_ASSOC);
            
            if ($result['autenticato']) {
                $userData = $this->getUserData($email);
                return ['success' => true, 'data' => $userData['data']];
            }
            
            return ['success' => false, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }
    
    /**
     * Recupera i dati dell'utente tramite email.
     *
     * @param string $email Email dell'utente.
     * @return array ['success' => bool, 'data' => array|null]
     *               Dove 'data' contiene i dati dell'utente oppure null in caso di errore o utente non trovato.
     */
    public function getUserData($email) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM UTENTE WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }
    
    /**
     * Verifica se un utente è creatore.
     *
     * @param string $email Email dell'utente.
     * @return bool Restituisce true se l'utente è creatore, false altrimenti.
     *              Il valore false può derivare da un controllo fallito o da un errore di database.
     */
    public function isCreator($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM UTENTE_CREATORE WHERE email_utente = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['count'] > 0);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se un utente è amministratore.
     *
     * @param string $email Email dell'utente.
     * @return bool Restituisce true se l'utente è amministratore, false altrimenti.
     *              Il valore false può derivare da un controllo fallito o da un errore di database.
     */
    public function isAdmin($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM UTENTE_AMMINISTRATORE WHERE email_utente = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['count'] > 0);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    /**
     * Effettua il login di un amministratore tramite stored procedure.
     *
     * @param string $email Email dell'amministratore.
     * @param string $hashedPassword Password hashata.
     * @param string $hashedSecurityCode Codice di sicurezza hashato.
     * @return bool Restituisce true se l'amministratore è autenticato, false altrimenti.
     *              Il valore false può derivare da autenticazione fallita o da un errore di database.
     */
    public function adminLogin($email, $hashedPassword, $hashedSecurityCode) {
        try {
            $stmt = $this->conn->prepare("CALL autenticazione_amministratore(:email, :password, :security_code, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':security_code', $hashedSecurityCode);
            $stmt->execute();
            $result = $this->conn->query("SELECT @autenticato as autenticato")->fetch(PDO::FETCH_ASSOC);
            return ($result['autenticato'] ? true : false);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Registra un nuovo utente tramite stored procedure.
     *
     * @param string $email Email dell'utente.
     * @param string $hashedPassword Password hashata.
     * @param string $name Nome dell'utente.
     * @param string $lastName Cognome dell'utente.
     * @param string $nickname Nickname dell'utente.
     * @param string $birthPlace Luogo di nascita.
     * @param string $birthYear Anno di nascita.
     * @param string $type Tipo di utente.
     * @param string $hashedSecurityCode Codice di sicurezza hashato.
     * @return array ['success' => bool, 'data' => null]
     *               Dove 'success' indica l'esito dell'operazione e 'data' è sempre null.
     */
    public function register($email, $hashedPassword, $name, $lastName, $nickname, $birthPlace, $birthYear, $type, $hashedSecurityCode) {
        try {
            $stmt = $this->conn->prepare("CALL registrazione_utente(:email, :password, :nome, :cognome, :nickname, :luogo_nascita, :anno_nascita, :tipo, :codice_sicurezza)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':cognome', $lastName);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->bindParam(':luogo_nascita', $birthPlace);
            $stmt->bindParam(':anno_nascita', $birthYear);
            $stmt->bindParam(':tipo', $type);
            $stmt->bindParam(':codice_sicurezza', $hashedSecurityCode);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuovo utente registrato", [
                    'email' => $email,
                    'nome' => $name,
                    'cognome' => $lastName,
                    'nickname' => $nickname,
                    'tipo' => $type
                ]);
            }
            return ['success' => $result, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Termina la sessione utente.
     *
     * @return void
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Crea un nuovo progetto (solo creatori).
     *
     * @param string $name Nome del progetto.
     * @param string $desc Descrizione del progetto.
     * @param float $budget Budget previsto.
     * @param string $maxDate Data limite.
     * @param string $type Tipo di progetto.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool, 'data' => null]
     *               Dove 'success' indica l'esito della creazione e 'data' è sempre null.
     */
    public function createProject($name, $desc, $budget, $maxDate, $type, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL crea_progetto(:nome, :descrizione, :budget, :data_limite, :tipo, :email_creatore)");
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':descrizione', $desc);
            $stmt->bindParam(':budget', $budget);
            $stmt->bindParam(':data_limite', $maxDate);
            $stmt->bindParam(':tipo', $type);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuovo progetto creato", [
                    'nome_progetto' => $name,
                    'descrizione' => $desc,
                    'budget' => $budget,
                    'data_limite' => $maxDate,
                    'tipo' => $type,
                    'email_creatore' => $creatorEmail
                ]);
            }
            return ['success' => $result, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Aggiunge un commento a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @param string $userEmail Email dell'utente.
     * @param string $text Testo del commento.
     * @return array ['success' => bool, 'data' => null]
     *               Dove 'success' indica l'esito dell'inserimento e 'data' è sempre null.
     */
    public function addComment($projectName, $userEmail, $text) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_commento(:nome_progetto, :email_utente, :testo)");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':testo', $text);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuovo commento inserito", [
                    'nome_progetto' => $projectName,
                    'email_utente' => $userEmail,
                    'testo' => $text
                ]);
            }
            return ['success' => $result, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Aggiunge una risposta a un commento (solo creatore del progetto).
     *
     * @param int $commentId ID del commento.
     * @param string $text Testo della risposta.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool, 'data' => null]
     *               Dove 'success' indica l'esito dell'inserimento e 'data' è sempre null.
     */
    public function addReply($commentId, $text, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_risposta(:id_commento, :testo, :email_creatore)");
            $stmt->bindParam(':id_commento', $commentId);
            $stmt->bindParam(':testo', $text);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuova risposta inserita", [
                    'id_commento' => $commentId,
                    'testo' => $text,
                    'email_creatore' => $creatorEmail
                ]);
            }
            return ['success' => $result, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Aggiunge una ricompensa a un progetto (solo creatori).
     *
     * @param string $code Codice della ricompensa.
     * @param string $image Immagine della ricompensa.
     * @param string $desc Descrizione della ricompensa.
     * @param string $projectName Nome del progetto.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool, 'data' => null]
     *               Dove 'success' indica l'esito dell'inserimento e 'data' è sempre null.
     */
    public function addRewardToProject($code, $image, $desc, $projectName, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_reward(:codice, :immagine, :descrizione, :nome_progetto, :email_creatore)");
            $stmt->bindParam(':codice', $code);
            $stmt->bindParam(':immagine', $image, PDO::PARAM_LOB);
            $stmt->bindParam(':descrizione', $desc);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuova ricompensa aggiunta", [
                    'codice' => $code,
                    'nome_progetto' => $projectName,
                    'email_creatore' => $creatorEmail,
                    'descrizione' => $desc
                ]);
            }
            return ['success' => $result, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Esegue il finanziamento di un progetto e associa una ricompensa.
     *
     * @param string $projectName Nome del progetto.
     * @param float $amount Importo del finanziamento.
     * @param string $userEmail Email dell'utente.
     * @param string $rewardCode Codice della ricompensa.
     * @return array ['success' => bool, 'data' => null]
     *               Dove 'success' indica l'esito del finanziamento e 'data' è sempre null.
     */
    public function fundProject($projectName, $amount, $userEmail, $rewardCode) {
        try {
            $stmt = $this->conn->prepare("CALL finanzia_progetto(:email_utente, :nome_progetto, :importo)");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':importo', $amount);
            
            if ($stmt->execute()) {
                $stmt2 = $this->conn->prepare("CALL scegli_reward(:email_utente, :nome_progetto, :codice_reward)");
                $stmt2->bindParam(':email_utente', $userEmail);
                $stmt2->bindParam(':nome_progetto', $projectName);
                $stmt2->bindParam(':codice_reward', $rewardCode);
                $stmt2->execute();
                $this->logger->log("Nuovo finanziamento", [
                    'nome_progetto' => $projectName,
                    'email_utente' => $userEmail,
                    'importo' => $amount,
                    'codice_reward' => $rewardCode
                ]);
                return ['success' => true, 'data' => null];
            } else {
                return ['success' => false, 'data' => null];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Aggiunge una nuova competenza al database (solo amministratori).
     *
     * @param string $name Nome della competenza.
     * @param string $adminEmail Email dell'amministratore.
     * @param string $hashedSecurityCode Codice di sicurezza hashato.
     * @return array ['success' => bool, 'data' => null]
     *               Dove 'success' indica l'esito dell'inserimento e 'data' è sempre null.
     */
    public function addCompetence($name, $adminEmail, $hashedSecurityCode) {
        try {
            $stmt = $this->conn->prepare("CALL aggiungi_competenza(:nome, :email, :codice_sicurezza)");
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':email', $adminEmail);
            $stmt->bindParam(':codice_sicurezza', $hashedSecurityCode);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuova competenza aggiunta", [
                    'nome_competenza' => $name,
                    'email_utente' => $adminEmail
                ]);
            }
            return ['success' => $result, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Verifica se l'utente ha già finanziato il progetto nella data odierna.
     *
     * @param string $projectName Nome del progetto.
     * @param string $userEmail Email dell'utente.
     * @return bool Restituisce true se ha finanziato oggi, false altrimenti.
     *              Il valore false può derivare da un controllo fallito o da un errore di database.
     */
    public function hasFundedToday($projectName, $userEmail) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM FINANZIAMENTO
                 WHERE nome_progetto = :nome_progetto
                 AND email_utente = :email_utente
                 AND data = CURDATE()"
            );
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            $hasFunded = $stmt->fetchColumn() > 0;
            return $hasFunded;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Recupera tutti i progetti creati da un utente.
     *
     * @param string $userEmail Email dell'utente.
     * @return array ['success' => bool, 'data' => array]
     *               Dove 'data' è un array di progetti creati dall'utente, o un array vuoto in caso di errore.
     */
    public function getProjects($userEmail) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE email_utente_creatore = :email_utente");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Recupera tutte le competenze associate a uno specifico utente.
     *
     * @param string $userEmail Email dell'utente.
     * @return array ['success' => bool, 'data' => array]
     *               Dove 'data' è un array delle competenze dell'utente, o un array vuoto in caso di errore.
     */
    public function getSkills($userEmail) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM SKILL_POSSEDUTA WHERE email_utente = :email_utente");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Aggiunge una competenza a un utente.
     *
     * @param string $userEmail Email dell'utente.
     * @param string $name Nome della competenza.
     * @param int $level Livello di competenza.
     * @return array ['success' => bool, 'data' => null]
     *               Dove 'success' indica l'esito dell'inserimento e 'data' è sempre null.
     */
    public function addSkill($userEmail, $name, $level) {
        try {
            $stmt = $this->conn->prepare("CALL aggiungi_skill(:email, :nome, :livello)");
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':email', $userEmail);
            $stmt->bindParam(':livello', $level);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuova competenza aggiunta", [
                    'email_utente' => $userEmail,
                    'nome_competenza' => $name,
                    'livello' => $level
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
