<?php
/**
 * File di gestione dell'autenticazione e delle sessioni
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se l'utente è autenticato
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_email']);
}

/**
 * Verifica se l'utente è un creatore
 * @return bool
 */
function isCreatore() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'creatore';
}

/**
 * Verifica se l'utente è un amministratore
 * @return bool
 */
function isAmministratore() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'amministratore';
}

/**
 * Restituisce l'email dell'utente loggato
 * @return string|null
 */
function getUserEmail() {
    return $_SESSION['user_email'] ?? null;
}

/**
 * Restituisce il tipo di utente loggato
 * @return string|null
 */
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Effettua il login dell'utente
 * @param string $email Email dell'utente
 * @param string $userType Tipo di utente (utente, creatore, amministratore)
 * @return void
 */
function loginUser($email, $userType = 'utente') {
    $_SESSION['user_email'] = $email;
    $_SESSION['user_type'] = $userType;
    $_SESSION['logged_in'] = true;
}

/**
 * Effettua il logout dell'utente
 * @return void
 */
function logoutUser() {
    session_unset();
    session_destroy();
}

/**
 * Reindirizza l'utente se non è autenticato
 * @param string $redirect URL di reindirizzamento
 * @return void
 */
function requireLogin($redirect = '/login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Reindirizza l'utente se non è un creatore
 * @param string $redirect URL di reindirizzamento
 * @return void
 */
function requireCreatore($redirect = '/login.php') {
    if (!isCreatore()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Reindirizza l'utente se non è un amministratore
 * @param string $redirect URL di reindirizzamento
 * @return void
 */
function requireAmministratore($redirect = '/login.php') {
    if (!isAmministratore()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Verifica il codice di sicurezza per gli amministratori
 * @param string $code Il codice inserito
 * @return bool True se il codice è valido
 */
function checkAdminCode($code) {
   // Codice di sicurezza da cambiare check con quello vero quando lo creaiamo dal DB
    $validCode = 'ADMIN123';
    return $code === $validCode;
}