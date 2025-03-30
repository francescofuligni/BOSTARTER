<?php
class DB {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new mysqli('mysql', 'root', 'root_password', 'bostarter_db');
        
        if ($this->connection->connect_error) {
            die("Connessione al database fallita: " . $this->connection->connect_error);
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

function getDBConnection() {
    $db = DB::getInstance();
    return $db->getConnection();
}
?>