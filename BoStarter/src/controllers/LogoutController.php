<?php
require_once __DIR__ . '/../models/User.php';

class LogoutController {
    
    public function __construct() {}

    /**
     * Esegue il logout dell'utente.
     * Avvia la sessione se non attiva e chiama il metodo logout del modello User.
     *
     * @return void
     */
    public function handle() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $user = new User(null); // Non serve db per cancellare il token
        $user->logout();
    }
}

$controller = new LogoutController();
$controller->handle();
