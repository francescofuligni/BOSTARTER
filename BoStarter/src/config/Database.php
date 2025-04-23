<?php
/**
 * Classe per la gestione della connessione al database MySQL tramite PDO.
 * I parametri di connessione dovrebbero essere caricati da variabili d'ambiente per motivi di sicurezza.
 */
class Database {
    private $host = 'mysql';
    private $db_name = 'bostarter_db';
    private $username = 'root';
    private $password = 'root_password';
    private $conn = null;

    /**
     * Stabilisce e restituisce una connessione PDO al database.
     *
     * @return PDO|null Oggetto PDO se la connessione ha successo, null altrimenti.
     */
    public function getConnection() {
        try {
            // L'oggetto PDO ci serve per usare il db (vedi slides)
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo " Errore durante la connessione: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
