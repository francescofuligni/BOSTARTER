<?php

class Router {
    protected $routes = [];

    /**
     * Registra una route per richieste HTTP GET
     * @param string $uri
     * @param string $file
     */
    public function get($uri, $file) {
        $this->add('GET', $uri, $file);
    }

    /**
     * Registra una route per richieste HTTP POST
     * @param string $uri
     * @param string $file
     */
    public function post($uri, $file) {
        $this->add('POST', $uri, $file);
    }

    /**
     * Aggiunge una nuova route all'elenco
     * @param string $method Metodo HTTP (GET o POST)
     * @param string $uri
     * @param string $file
     */
    protected function add($method, $uri, $file) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'file' => $file
        ];
    }

    /**
     * Risolve la route corrispondente alla richiesta attuale
     * @param string $requestUri
     * @param string $requestMethod
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
