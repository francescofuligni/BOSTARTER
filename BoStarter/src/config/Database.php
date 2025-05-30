<?php
/**
 * Classe per la gestione della connessione al database MySQL tramite PDO.
 * I parametri di connessione sono caricati da variabili d'ambiente per motivi di sicurezza.
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn = null;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'];
    }

    /**
     * Stabilisce e restituisce una connessione PDO al database.
     *
     * @return PDO|null Oggetto PDO se la connessione ha successo, null altrimenti.
     */
    public function getConnection() {
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo " Errore durante la connessione: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
