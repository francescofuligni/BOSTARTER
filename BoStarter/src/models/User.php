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
     * @return array|false Dati utente se autenticato, false altrimenti.
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
     * Recupera i dati dell'utente tramite email.
     *
     * @param string $email Email dell'utente.
     * @return array|false Dati utente se trovati, false altrimenti.
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
     * Verifica se un utente è creatore.
     *
     * @param string $email Email dell'utente.
     * @return bool True se è creatore, false altrimenti.
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
     * Verifica se un utente è amministratore.
     *
     * @param string $email Email dell'utente.
     * @return bool True se è amministratore, false altrimenti.
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
     * Effettua il login di un amministratore tramite stored procedure.
     *
     * @param string $email Email dell'amministratore.
     * @param string $hashedPassword Password hashata.
     * @param string $hashedSecurityCode Codice di sicurezza hashato.
     * @return bool True se autenticato, false altrimenti.
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
     * @return bool True se registrazione avvenuta, false altrimenti.
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
     * Termina la sessione utente e reindirizza alla home.
     *
     * @return void
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
     * Crea un nuovo progetto (solo per creatori).
     *
     * @param string $name Nome del progetto.
     * @param string $desc Descrizione del progetto.
     * @param float $budget Budget previsto.
     * @param string $maxDate Data limite.
     * @param string $type Tipo di progetto.
     * @param string $creatorEmail Email del creatore.
     * @return bool True se creazione avvenuta, false altrimenti.
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
     * Aggiunge un commento a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @param string $userEmail Email dell'utente.
     * @param string $text Testo del commento.
     * @return bool True se inserimento avvenuto, false altrimenti.
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
     * Aggiunge una risposta a un commento.
     *
     * @param int $commentId ID del commento.
     * @param string $text Testo della risposta.
     * @param string $creatorEmail Email del creatore.
     * @return bool True se inserimento avvenuto, false altrimenti.
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
     * Aggiunge una ricompensa a un progetto (solo per creatori).
     *
     * @param string $code Codice della ricompensa.
     * @param string $image Immagine della ricompensa.
     * @param string $desc Descrizione della ricompensa.
     * @param string $projectName Nome del progetto.
     * @param string $creatorEmail Email del creatore.
     * @return bool True se inserimento avvenuto, false altrimenti.
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
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Esegue il finanziamento di un progetto e associa una ricompensa.
     *
     * @param string $projectName Nome del progetto.
     * @param float $amount Importo del finanziamento.
     * @param string $userEmail Email dell'utente.
     * @param string $rewardCode Codice della ricompensa.
     * @return bool True se finanziamento avvenuto, false altrimenti.
     */
    public function fundProject($projectName, $amount, $userEmail, $rewardCode) {
        try {
            $stmt = $this->conn->prepare("CALL finanzia_progetto(:email_utente, :nome_progetto, :importo)");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':importo', $amount);
            
            if ($stmt->execute()) {
                // Associa la reward al finanziamento appena inserito
                $stmt2 = $this->conn->prepare("CALL scegli_reward(:email_utente, :nome_progetto, :codice_reward)");
                $stmt2->bindParam(':email_utente', $userEmail);
                $stmt2->bindParam(':nome_progetto', $projectName);
                $stmt2->bindParam(':codice_reward', $rewardCode);
                $stmt2->execute();
                // Logga il finanziamento
                $this->logger->log("Nuovo finanziamento", [
                    'nome_progetto' => $projectName,
                    'email_utente' => $userEmail,
                    'importo' => $amount,
                    'codice_reward' => $rewardCode
                ]);
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Aggiunge una nuova competenza al database.
     *
     * @param string $name Nome della competenza.
     * @param string $email Email dell'utente.
     * @param string $hashedSecurityCode Codice di sicurezza hashato.
     * @return bool True se inserimento avvenuto, false altrimenti.
     */
    public function addCompetence($name, $email, $hashedSecurityCode) {
        try {
            $stmt = $this->conn->prepare("CALL aggiungi_competenza(:nome, :email, :codice_sicurezza)");
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':codice_sicurezza', $hashedSecurityCode);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuova competenza aggiunta", [
                    'nome_competenza' => $name,
                    'email_utente' => $email
                ]);
            }
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
