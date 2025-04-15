<?php
// TODO: da cambiare con dati presi da .env (anche nel docker file dobbiamo nasconderli per sicurezza)
class Database {
    private $host = 'mysql';
    private $db_name = 'bostarter_db';
    private $username = 'root';
    private $password = 'root_password';
    private $conn;

    /**
     * Restituisce una connessione al database tramite PDO.
     * In caso di errore durante la connessione, stampa un messaggio di errore.
     *
     * @return PDO|null Oggetto PDO se la connessione ha successo, null altrimenti
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // L'oggetto PDO ci serve per usare il db fula (guarda le slide)
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo " Errore durante la connessione: " . $e->getMessage();
        }

        return $this->conn;
    }
}
