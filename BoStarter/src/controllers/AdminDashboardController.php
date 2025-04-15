<?php

class AdminDashboardController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Metodo principale per gestire la logica della dashboard dell'amministratore.
     *
     * @return void
     */
    public function handle() {
        // TODO
    }
}
