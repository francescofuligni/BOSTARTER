<?php

/**
 * Classe per la gestione del routing delle richieste HTTP.
 * Permette di registrare e risolvere rotte GET e POST.
 */
class Router {
    protected $routes = [];

    /**
     * Registra una rotta per richieste HTTP GET.
     *
     * @param string $uri Percorso della rotta.
     * @param string $file File da includere quando la rotta è risolta.
     */
    public function get($uri, $file) {
        $this->add('GET', $uri, $file);
    }

    /**
     * Registra una rotta per richieste HTTP POST.
     *
     * @param string $uri Percorso della rotta.
     * @param string $file File da includere quando la rotta è risolta.
     */
    public function post($uri, $file) {
        $this->add('POST', $uri, $file);
    }

    /**
     * Aggiunge una nuova rotta all'elenco delle rotte disponibili.
     *
     * @param string $method Metodo HTTP (GET o POST).
     * @param string $uri Percorso della rotta.
     * @param string $file File da includere per la rotta.
     */
    protected function add($method, $uri, $file) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'file' => $file
        ];
    }

    /**
     * Risolve la rotta corrispondente alla richiesta attuale.
     * Includendo il file associato o reindirizzando alla home se non trovata.
     *
     * @param string $requestUri URI della richiesta.
     * @param string $requestMethod Metodo HTTP della richiesta.
     */
    public function resolve($requestUri, $requestMethod) {
        $uri = parse_url($requestUri, PHP_URL_PATH);

        if ($uri === '/index.php') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === strtoupper($requestMethod)) {
                require BASE_PATH . '/' . $route['file'];
                return;
            }
        }

        // Redirect to home if no route matches
        header('Location: /');
        exit;
    }
}
?>
