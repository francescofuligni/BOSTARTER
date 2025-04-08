<?php
// creo la classe User per metterci tutti le operazioni che servono per gli utenti come login, registrazione, ecc
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

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
    // Funzione per ottenere i dati dell'utente
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
    // Funzione per controllare se l'utente è un creatore 
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

    // Funzione per controllare se l'utente è un admin
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
    
    // Funzione per il login dell'amministratore
    public function adminLogin($email, $password, $securityCode) {
        try {
            
            // chiama la stored procedure per autenticare l'amministratore
            $stmt = $this->conn->prepare("CALL autenticazione_amministratore(:email, :password, :security_code, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':security_code', $securityCode);
            $stmt->execute();
            
            
            $result = $this->conn->query("SELECT @autenticato as autenticato")->fetch(PDO::FETCH_ASSOC);
            
            return $result['autenticato'] ? true : false;
        } catch (PDOException $e) {
            echo "Admin login error: " . $e->getMessage();
            return false;
        }
    }

    // Funzione per la registrazione dell'utente
    public function register($email, $hashedPassword, $nome, $cognome, $nickname, $luogoNascita, $annoNascita, $tipo) {
        try {
            // Chiama la stored procedure per registrare l'utente
            $stmt = $this->conn->prepare("CALL registrazione_utente(:email, :password, :nome, :cognome, :nickname, :luogo_nascita, :anno_nascita, :tipo)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword); // Usa la password hashata
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cognome', $cognome);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->bindParam(':luogo_nascita', $luogoNascita);
            $stmt->bindParam(':anno_nascita', $annoNascita);
            $stmt->bindParam(':tipo', $tipo);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Errore durante la registrazione: " . $e->getMessage();
            return false;
        }
    }

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
}
?>