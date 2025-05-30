<?php
require_once '/app/vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

/**
 * Classe per la gestione del logging degli eventi su MongoDB.
 * Registra messaggi di log con timestamp e dati opzionali.
 */
class MongoLogger {
    private $collection;

    public function __construct() {
        $host = $_ENV['MONGO_HOST'];
        $username = $_ENV['MONGO_USERNAME'];
        $password = $_ENV['MONGO_PASSWORD'];
        $database = $_ENV['MONGO_DATABASE'];
        
        $connectionString = "mongodb://{$username}:{$password}@{$host}:27017/?authSource=admin";
        
        $client = new Client($connectionString);
        $db = $client->selectDatabase($database);
        $this->collection = $db->selectCollection('event_log');
    }

    /**
     * Registra un messaggio di log su MongoDB.
     *
     * @param string $message Messaggio da registrare.
     * @param array $extra Dati aggiuntivi opzionali.
     * @return void
     */
    public function log($message, $extra = []) {
        $doc = [
            'message' => $message,
            'timestamp' => new UTCDateTime(),
            'extra' => $extra
        ];
        try {
            $this->collection->insertOne($doc);
        } catch (Exception $e) {
            error_log('MongoLogger error: ' . $e->getMessage());
        }
    }
}
?>
