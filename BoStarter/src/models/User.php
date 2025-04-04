<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        try {
            // Call the stored procedure for user authentication
            $stmt = $this->conn->prepare("CALL autenticazione_utente(:email, :password, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            
            // Get the output parameter
            $result = $this->conn->query("SELECT @autenticato as autenticato")->fetch(PDO::FETCH_ASSOC);
            
            if ($result['autenticato']) {
                // Get user details
                $stmt = $this->conn->prepare("SELECT * FROM UTENTE WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            echo "Login error: " . $e->getMessage();
            return false;
        }
    }

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

    public function adminLogin($email, $password, $securityCode) {
        try {
            // Call the stored procedure for admin authentication
            $stmt = $this->conn->prepare("CALL autenticazione_amministratore(:email, :password, :security_code, @autenticato)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':security_code', $securityCode);
            $stmt->execute();
            
            // Get the output parameter
            $result = $this->conn->query("SELECT @autenticato as autenticato")->fetch(PDO::FETCH_ASSOC);
            
            return $result['autenticato'] ? true : false;
        } catch (PDOException $e) {
            echo "Admin login error: " . $e->getMessage();
            return false;
        }
    }

    public function register($email, $password, $nome, $cognome, $nickname, $luogoNascita, $annoNascita, $tipo) {
        try {
            // Call the stored procedure for user registration
            $stmt = $this->conn->prepare("CALL registrazione_utente(:email, :password, :nome, :cognome, :nickname, :luogo_nascita, :anno_nascita, :tipo)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cognome', $cognome);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->bindParam(':luogo_nascita', $luogoNascita);
            $stmt->bindParam(':anno_nascita', $annoNascita);
            $stmt->bindParam(':tipo', $tipo);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Registration error: " . $e->getMessage();
            return false;
        }
    }
}
?>