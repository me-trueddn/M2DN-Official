<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable|array{0:class-string,1:string}>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): self
    {
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        $this->routes[$method][$path] = $handler;
        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        // public/altında çalışıyorsa /index.php temizliği
        if (str_ends_with($path, '/index.php')) {
            $path = substr($path, 0, -10) ?: '/';
        }

        $matched = $this->match(strtoupper($method), $path);
        if ($matched === null) {
            http_response_code(404);
            echo '404 — Sayfa bulunamadı.';
            return;
        }

        [$handler, $params] = $matched;

        // Ready Only: /admin POST işlemleri engellenir (salt görüntüleme)
        if (strtoupper($method) === 'POST' && str_starts_with($path, '/admin')) {
            $user = \App\Services\AuthService::user();
            if ($user !== null && \App\Services\AuthService::canAccessAdmin($user)) {
                \App\Services\PermissionService::denyIfReadOnly($user);
            }
        }

        if (is_array($handler)) {
            [$class, $action] = $handler;
            $controller = new $class();
            $controller->{$action}(...$params);
            return;
        }

        $handler(...$params);
    }

    /**
     * @return array{0:callable|array{0:class-string,1:string},1:array<string,string>}|null
     */
    private function match(string $method, string $path): ?array
    {
        $routes = $this->routes[$method] ?? [];
        if (isset($routes[$path])) {
            return [$routes[$path], []];
        }

        foreach ($routes as $route => $handler) {
            if (!str_contains($route, '{')) {
                continue;
            }
            $parts = preg_split('/(\{[a-zA-Z_][a-zA-Z0-9_]*\})/', $route, -1, PREG_SPLIT_DELIM_CAPTURE);
            if ($parts === false) {
                continue;
            }
            $regex = '';
            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }
                if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $pm)) {
                    $regex .= '(?P<' . $pm[1] . '>[^/]+)';
                } else {
                    $regex .= preg_quote($part, '#');
                }
            }
            if (!preg_match('#^' . $regex . '$#u', $path, $m)) {
                continue;
            }
            $params = [];
            foreach ($m as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = rawurldecode((string) $value);
                }
            }
            return [$handler, $params];
        }

        return null;
    }
}
