<?php
namespace SIG\Core;

use RuntimeException;

/**
 * Clase Router - Sistema de rutas simple
 * 
 * Registra rutas GET/POST y las resuelve contra controladores.
 * Soporta parámetros en la URL y middleware por ruta.
 */
class Router
{
    private array $routes = [];
    private array $middleware = [];
    private string $prefix = '';

    /**
     * Registrar una ruta GET
     */
    public function get(string $pattern, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $pattern, $handler, $middleware);
    }

    /**
     * Registrar una ruta POST
     */
    public function post(string $pattern, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $pattern, $handler, $middleware);
    }

    /**
     * Agregar una ruta al registro
     */
    private function addRoute(string $method, string $pattern, $handler, array $middleware): void
    {
        $pattern = $this->prefix . $pattern;
        
        // Convertir {param} a regex capture groups
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $pattern,
            'regex'      => $regex,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Agrupar rutas con un prefijo común
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $this->prefix = $previousPrefix . $prefix;
        $callback($this);
        $this->prefix = $previousPrefix;
    }

    /**
     * Registrar middleware global
     */
    public function addMiddleware($middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Resolver la ruta actual y ejecutar el handler
     */
    public function resolve(Request $request): mixed
    {
        $method = $request->getMethod();
        $uri    = $request->getUri();

        // Ejecutar middleware global
        foreach ($this->middleware as $mw) {
            $result = $this->executeMiddleware($mw, $request);
            if ($result === false) {
                return null;
            }
        }

        // Buscar ruta coincidente
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                // Ejecutar middleware de la ruta
                foreach ($route['middleware'] as $mw) {
                    $result = $this->executeMiddleware($mw, $request);
                    if ($result === false) {
                        return null;
                    }
                }

                // Extraer parámetros de la URL
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                return $this->executeHandler($route['handler'], $request);
            }
        }

        // No se encontró la ruta
        return $this->handleNotFound($request);
    }

    /**
     * Ejecutar un middleware
     */
    private function executeMiddleware($middleware, Request $request): mixed
    {
        if (is_string($middleware)) {
            $middleware = new $middleware();
        }
        
        if (is_object($middleware) && method_exists($middleware, 'handle')) {
            return $middleware->handle($request);
        }
        
        if (is_callable($middleware)) {
            return $middleware($request);
        }

        return true;
    }

    /**
     * Ejecutar el handler de la ruta
     */
    private function executeHandler($handler, Request $request): mixed
    {
        // Si es un callable (función anónima)
        if (is_callable($handler)) {
            return $handler($request);
        }

        // Si es un string "Controlador@metodo"
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $method] = explode('@', $handler);
            $controller = 'SIG\\Controllers\\' . $controller;
            
            if (!class_exists($controller)) {
                throw new RuntimeException("Controlador no encontrado: {$controller}");
            }

            $instance = new $controller();
            
            if (!method_exists($instance, $method)) {
                throw new RuntimeException("Método {$method} no encontrado en {$controller}");
            }

            return $instance->$method($request);
        }

        throw new RuntimeException('Handler de ruta inválido');
    }

    /**
     * Manejar error 404
     */
    private function handleNotFound(Request $request): mixed
    {
        http_response_code(404);
        
        if ($request->isAjax()) {
            header('Content-Type: application/json');
            return json_encode(['error' => 'Ruta no encontrada', 'code' => 404]);
        }

        // Cargar vista 404 desde el path absoluto
        $config = require __DIR__ . '/../config/app.php';
        $viewsPath = $config['paths']['views'];
        $viewFile = $viewsPath . 'errors/404.php';

        if (file_exists($viewFile)) {
            ob_start();
            require $viewFile;
            return ob_get_clean();
        }

        return '<!DOCTYPE html><html><head><title>404</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f5f6fa"><div style="text-align:center"><h1 style="font-size:100px;color:#764ba2;margin:0">404</h1><p style="color:#666;font-size:18px">Página no encontrada</p></div></body></html>';
    }

    /**
     * Obtener todas las rutas registradas
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
