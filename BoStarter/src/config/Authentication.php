<?php

class Authentication
{
    public function validateAuthToken()
    {
        // Avvia la sessione se non è già stata avviata
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Controlla se il token esiste
        if (!isset($_SESSION['auth_token']) || !isset($_SESSION['token_expiration'])) {
            header('Location: /login');
            exit;
        }

        // Controlla se il token è scaduto
        if (time() > $_SESSION['token_expiration']) {
            // Token scaduto, distruggi la sessione
            session_destroy();
            header('Location: /login');
            exit;
        }

        // Estendi il tempo di scadenza del token
        $_SESSION['token_expiration'] = time() + (60 * 60); // Estendi di 60 minuti
    
        // Example logic for token validation
        if (empty($authToken)) {
            return false;
        }
        // Controlla se il token fornito corrisponde a quello della sessione
        if ($authToken !== $_SESSION['auth_token']) {
            return false;
        }
        return true;    

    }

}