<?php
// TODO: da cambiare con dati presi da .env (anche nel docker file dobbiamo nasconderli per sicurezza)

class Database {
    private $host = 'mysql';
    private $db_name = 'bostarter_db';
    private $username = 'root';
    private $password = 'root_password';
    private $conn;

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
?>
