<?php
// Creo la classe User per metterci tutti le operazioni che servono per gli utenti come login, registrazione, ecc
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Effettua il login dell'utente.
     * Chiama una stored procedure che verifica l'autenticazione.
     * 
     * @param string $email Email dell'utente
     * @param string $hashedPassword Password già hashata
     * @return array|false Dati dell'utente se autenticato, false altrimenti
     */
    public function login($email, $hashedPassword) {
        try {
            $stmt = $this->conn->prepare("CALL autenticazione_utente(:email, :password, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            
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
     * Recupera i dati dell'utente dal database.
     * 
     * @param string $email Email dell'utente
     * @return array|false Dati dell'utente o false in caso di errore
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
     * Verifica se l'utente è un creatore.
     * 
     * @param string $email Email dell'utente
     * @return bool True se è un creatore, false altrimenti
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
     * Verifica se l'utente è un amministratore.
     * 
     * @param string $email Email dell'utente
     * @return bool True se è un admin, false altrimenti
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
     * Effettua il login dell'amministratore.
     * Chiama una stored procedure per autenticare l'amministratore.
     * 
     * @param string $email Email dell'amministratore
     * @param string $hashedPassword Password già hashata
     * @param string $hashedSecurityCode Codice di sicurezza hashato
     * @return bool True se autenticato, false altrimenti
     */
    public function adminLogin($email, $hashedPassword, $hashedSecurityCode) {
        try {
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
     * Registra un nuovo utente nel sistema.
     * Chiama una stored procedure che salva i dati nel database.
     * 
     * @param string $email Email dell'utente
     * @param string $hashedPassword Password già hashata
     * @param string $name Nome dell'utente
     * @param string $lastName Cognome dell'utente
     * @param string $nickname Nickname scelto
     * @param string $birthPlace Luogo di nascita
     * @param int $birthYear Anno di nascita
     * @param string $type Tipo di utente (es. standard, admin, creatore)
     * @param string $hashedSecurityCode Codice di sicurezza hashato
     * @return bool True se la registrazione è andata a buon fine, false altrimenti
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
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Errore durante la registrazione: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Effettua il logout dell'utente.
     * Distrugge la sessione e reindirizza alla home.
     * 
     * @return void
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['auth_token'])) {
            unset($_SESSION['auth_token']);
        }
        if (isset($_SESSION['token_expiration'])) {
            unset($_SESSION['token_expiration']);
        }
        $_SESSION = array();
        
        session_destroy();
        
        header("Location: /home");
        exit();
    }
}
