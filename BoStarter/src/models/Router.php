<?php

class Router {
    protected $routes = [];

    public function get($uri, $file) {
        $this->add('GET', $uri, $file);
    }

    public function post($uri, $file) {
        $this->add('POST', $uri, $file);
    }

    protected function add($method, $uri, $file) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'file' => $file
        ];
    }

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