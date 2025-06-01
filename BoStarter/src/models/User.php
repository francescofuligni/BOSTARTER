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
     * Esegue il login di un utente.
     *
     * @param string $email Email dell'utente.
     * @param string $hashedPassword Password hashata.
     * @return array ['success' => bool, 'data' => array|null]
     */
    public function login($email, $hashedPassword) {
        $email = strtolower($email);
        try {
            $stmt = $this->conn->prepare("CALL autenticazione_utente(:email, :password, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            
            $result = $this->conn->query("SELECT @autenticato as autenticato")->fetch(PDO::FETCH_ASSOC);
            
            if ($result['autenticato']) {
                $userData = $this->getData($email);
                return ['success' => true, 'data' => $userData['data']];
            }
            
            return ['success' => false, 'data' => null];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }

    /**
     * Recupera i dati di un utente.
     *
     * @param string $email Email dell'utente.
     * @return array ['success' => bool, 'data' => array|null]
     */
    public function getData($email) {
        $email = strtolower($email);
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
     * Verifica se un utente è un creatore.
     *
     * @param string $email Email dell'utente.
     * @return bool True se l'utente è creatore, false altrimenti.
     */
    public function isCreator($email) {
        $email = strtolower($email);
        try {
            $stmt = $this->conn->prepare("CALL verifica_creatore(:email, @esito)");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $this->conn->query("SELECT @esito AS esito")->fetch(PDO::FETCH_ASSOC);
            return ($result['esito'] == 1);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se un utente è il creatore di un progetto specifico.
     *
     * @param string $email Email dell'utente.
     * @param string $projectName Nome del progetto.
     * @return bool True se è il creatore del progetto, false altrimenti.
     */
    public function isProjectCreator($email, $projectName) {
        $email = strtolower($email);
        try {
            $stmt = $this->conn->prepare("CALL verifica_creatore_progetto(:nome_progetto, :email_creatore, @esito)");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_creatore', $email);
            $stmt->execute();
            $result = $this->conn->query("SELECT @esito AS esito")->fetch(PDO::FETCH_ASSOC);
            return ($result['esito'] == 1);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se un utente è amministratore.
     *
     * @param string $email Email dell'utente.
     * @return bool True se l'utente è amministratore, false altrimenti.
     */
    public function isAdmin($email) {
        $email = strtolower($email);
        try {
            $stmt = $this->conn->prepare("CALL verifica_amministratore(:email, @esito)");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $this->conn->query("SELECT @esito AS esito")->fetch(PDO::FETCH_ASSOC);
            return ($result['esito'] == 1);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Esegue il login di un amministratore.
     *
     * @param string $email Email dell'amministratore.
     * @param string $hashedPassword Password hashata.
     * @param string $hashedSecurityCode Codice di sicurezza hashato.
     * @return bool True se autenticato, false altrimenti.
     */
    public function adminLogin($email, $hashedPassword, $hashedSecurityCode) {
        $email = strtolower($email);
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
     * Registra un nuovo utente (anche come creatore o amministratore).
     *
     * @param string $email
     * @param string $hashedPassword
     * @param string $name
     * @param string $lastName
     * @param string $nickname
     * @param string $birthPlace
     * @param string $birthYear
     * @param string $type Tipo di utente ('CREATORE' o 'AMMINISTRATORE').
     * @param string $hashedSecurityCode
     * @return array ['success' => bool]
     */
    public function register($email, $hashedPassword, $name, $lastName, $nickname, $birthPlace, $birthYear, $type, $hashedSecurityCode) {
        $email = strtolower($email);
        try {
            $this->conn->beginTransaction();

            // Inserimento dell'utente
            $stmt = $this->conn->prepare("CALL registrazione_utente(:email, :password, :nome, :cognome, :nickname, :luogo_nascita, :anno_nascita)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':cognome', $lastName);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->bindParam(':luogo_nascita', $birthPlace);
            $stmt->bindParam(':anno_nascita', $birthYear);
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                error_log("Errore in registrazione utente");
                return ['success' => false];
            }

            // Inserimento tabella specifica per creatore o amministratore
            if ($type === 'CREATORE') {
                $stmt2 = $this->conn->prepare("CALL registrazione_creatore(:email)");
                $stmt2->bindParam(':email', $email);
                if (!$stmt2->execute()) {
                    $this->conn->rollBack();
                    error_log("Errore in registrazione creatore");
                    return ['success' => false];
                }
            } elseif ($type === 'AMMINISTRATORE') {
                $stmt3 = $this->conn->prepare("CALL registrazione_amministratore(:email, :codice_sicurezza)");
                $stmt3->bindParam(':email', $email);
                $stmt3->bindParam(':codice_sicurezza', $hashedSecurityCode);
                if (!$stmt3->execute()) {
                    $this->conn->rollBack();
                    error_log("Errore in registrazione amministratore");
                    return ['success' => false];
                }
            }

            $this->conn->commit();
            $this->logger->log("Nuovo utente registrato", [
                'email' => $email,
                'nome' => $name,
                'cognome' => $lastName,
                'nickname' => $nickname,
                'tipo' => $type
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false];
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
     * Verifica se l'utente ha già finanziato oggi un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @param string $userEmail Email dell'utente.
     * @return bool True se ha finanziato oggi, false altrimenti.

     */
    public function hasFundedToday($projectName, $userEmail) {
        $userEmail = strtolower($userEmail);
        try {
            $stmt = $this->conn->prepare("CALL ha_finanziato_oggi(:nome_progetto, :email_utente, @esito)");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            $result = $this->conn->query("SELECT @esito AS esito")->fetch(PDO::FETCH_ASSOC);
            return ($result['esito'] == 1);
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
     */
    public function getProjects($userEmail) {
        $userEmail = strtolower($userEmail);
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
     * Recupera tutte le competenze di un utente.
     *
     * @param string $userEmail Email dell'utente.
     * @return array ['success' => bool, 'data' => array]
     */
    public function getSkills($userEmail) {
        $userEmail = strtolower($userEmail);
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
     * @return array ['success' => bool]
     */
    public function addSkill($userEmail, $name, $level) {
        $userEmail = strtolower($userEmail);
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
            return ['success' => $result];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }
}
?>
