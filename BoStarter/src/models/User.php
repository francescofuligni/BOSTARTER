<?php
// Creo la classe User per metterci tutti le operazioni che servono per gli utenti come login, registrazione, ...
require_once __DIR__ . '/../config/MongoLogger.php';

class User {
    private $conn;
    private $logger;

    public function __construct($db) {
        $this->conn = $db;
        $this->logger = new \MongoLogger();
    }

    /**
     * Effettua il login dell'utente chiamando la stored procedure dedicata
     * @param string $email
     * @param string $hashedPassword
     * @return array|false
     */
    public function login($email, $hashedPassword) {
        try {
            // Chiamo la stored procedure per autenticare l'utente
            // La stored procedure restituisce un parametro di output @autenticato
            // che indica se l'autenticazione è andata a buon fine
            // stmt sta per statement perchè è una query 
            $stmt = $this->conn->prepare("CALL autenticazione_utente(:email, :password, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            
            // Restituisco il valore del parametro di output
            $result = $this->conn->query("SELECT @autenticato as autenticato")->fetch(PDO::FETCH_ASSOC);
            
            if ($result['autenticato']) {
                return $this->getUserData($email);
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            echo "Login error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Restituisce i dati dell'utente a partire dalla sua email
     * @param string $email
     * @return array|false
     */
    public function getUserData($email) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM UTENTE WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Errore durante il recupero dei dati dell'utente: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Verifica se l'utente è un creatore
     * @param string $email
     * @return bool
     */
    public function isCreator($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM UTENTE_CREATORE WHERE email_utente = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Verifica se l'utente è un amministratore
     * @param string $email
     * @return bool
     */
    public function isAdmin($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM UTENTE_AMMINISTRATORE WHERE email_utente = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Effettua il login di un amministratore tramite stored procedure
     * @param string $email
     * @param string $hashedPassword
     * @param string $hashedSecurityCode
     * @return bool
     */
    public function adminLogin($email, $hashedPassword, $hashedSecurityCode) {
        try {
            
            // Chiama la stored procedure per autenticare l'amministratore
            $stmt = $this->conn->prepare("CALL autenticazione_amministratore(:email, :password, :security_code, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':security_code', $hashedSecurityCode);
            $stmt->execute();
            
            
            $result = $this->conn->query("SELECT @autenticato as autenticato")->fetch(PDO::FETCH_ASSOC);
            
            return $result['autenticato'] ? true : false;
        } catch (PDOException $e) {
            echo "Admin login error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Registra un nuovo utente nel sistema tramite stored procedure
     * @param string $email
     * @param string $hashedPassword
     * @param string $name
     * @param string $lastName
     * @param string $nickname
     * @param string $birthPlace
     * @param string $birthYear
     * @param string $type
     * @param string $hashedSecurityCode
     * @return bool
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

            // Logga l'evento SOLO se l'inserimento è andato a buon fine
            if ($result) {
                $this->logger->log("Nuovo utente registrato", [
                    'email' => $email,
                    'nome' => $name,
                    'cognome' => $lastName,
                    'nickname' => $nickname,
                    'tipo' => $type
                ]);
            }
            return $result;
        } catch (PDOException $e) {
            echo "Errore durante la registrazione: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Effettua il logout dell'utente terminando la sessione
     */
    public function logout() {
        // Distruggi la sessione per effettuare il logout
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        

        // RIMOZIONE PER ESSERE SICURI MAGARI POI LO TOGLIAMO
        if (isset($_SESSION['auth_token'])) {
            unset($_SESSION['auth_token']);
        }
        if (isset($_SESSION['token_expiration'])) {
            unset($_SESSION['token_expiration']);
        }
        // Distruggi tutte le variabili di sessione
        $_SESSION = array();
        
        // Distruggi la sessione
        session_destroy();
        
        // Reindirizza alla pagina di login o alla home page
        header("Location: /home");
        exit();
    }

    /**
     * Crea un nuovo progetto (solo per creatori)
     * @param string $name
     * @param string $desc
     * @param float $budget
     * @param string $maxDate
     * @param string $type
     * @param string $creatorEmail
     * @return bool
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

            // Logga l'evento SOLO se l'inserimento è andato a buon fine
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
    
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Aggiunge un commento a un progetto
     * @param string $projectName
     * @param string $userEmail
     * @param string $text
     * @return bool
     */
    public function addComment($projectName, $userEmail, $text) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_commento(:nome_progetto, :email_utente, :testo)");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':testo', $text);
            $result = $stmt->execute();

            // Logga l'evento SOLO se l'inserimento è andato a buon fine
            if ($result) {
                $this->logger->log("Nuovo commento inserito", [
                    'nome_progetto' => $projectName,
                    'email_utente' => $userEmail,
                    'testo' => $text
                ]);
            }
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Aggiunge una risposta a un commento
     * @param int $commentId
     * @param string $text
     * @param string $creatorEmail
     * @return bool
     */
    public function addReply($commentId, $text, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_risposta(:id_commento, :testo, :email_creatore)");
            $stmt->bindParam(':id_commento', $commentId);
            $stmt->bindParam(':testo', $text);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $result = $stmt->execute();

            // Logga l'evento SOLO se l'inserimento è andato a buon fine
            if ($result) {
                $this->logger->log("Nuova risposta inserita", [
                    'id_commento' => $commentId,
                    'testo' => $text,
                    'email_creatore' => $creatorEmail
                ]);
            }
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Aggiunge una ricompensa a un progetto (solo per creatori)
     * @param string $code
     * @param string $image
     * @param string $desc
     * @param string $projectName
     * @param string $creatorEmail
     * @return bool
     */
    public function addRewardToProject($code, $image, $desc, $projectName, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_reward(:codice, :immagine, :descrizione, :nome_progetto, :email_creatore)");
            $stmt->bindParam(':codice', $code);
            $stmt->bindParam(':immagine', $image, PDO::PARAM_LOB);
            $stmt->bindParam(':descrizione', $desc);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
