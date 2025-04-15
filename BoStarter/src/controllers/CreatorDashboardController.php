<?php

class CreatorDashboardController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Metodo principale per gestire la logica della dashboard del creatore.
     *
     * @return void
     */
    public function handle() {
        // Logica della dashboard del creatore da implementare
    }
}
