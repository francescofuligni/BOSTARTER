<?php
require_once '/app/vendor/autoload.php'; // Corretto: risale alla root del progetto

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

/**
 * Logger per eventi della piattaforma su MongoDB.
 */
class MongoLogger {
    private $collection;

    public function __construct() {
        // Connessione al servizio MongoDB definito in docker-compose (host: mongodb, porta: 27017)
        $client = new Client('mongodb://admin_username:admin_password@mongodb:27017/?authSource=admin');
        $db = $client->selectDatabase('bostarter_log'); // Puoi scegliere il nome che preferisci
        $this->collection = $db->selectCollection('event_log');
    }

    /**
     * Inserisce un evento di log.
     * @param string $message Messaggio da loggare
     * @param array $extra Dati aggiuntivi opzionali
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
            // Non bloccare il flusso dell'applicazione in caso di errore di logging
            error_log('MongoLogger error: ' . $e->getMessage());
        }
    }
}
?>