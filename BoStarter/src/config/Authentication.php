<?php

/**
 * Classe responsabile della gestione dell'autenticazione tramite token di sessione.
 */
class Authentication
{
    /**
     * Valida il token di autenticazione presente nella sessione.
     * Verifica l'esistenza e la validità temporale del token e lo confronta con il valore atteso.
     * Reindirizza alla pagina di login in caso di token assente o non valido.
     *
     * @return bool True se il token è valido, false altrimenti
     */
    public function validateAuthToken($authToken)
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