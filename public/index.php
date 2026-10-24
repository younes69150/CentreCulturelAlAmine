<?php
declare(strict_types=1);

// Serveur de développement PHP : servir les fichiers statiques directement.
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require dirname(__DIR__) . '/app/bootstrap.php';

send_security_headers();
start_session();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = $path === '/' ? '/' : rtrim($path, '/');

if ($method === 'POST') {
    verify_csrf();
}

$routes = require BASE_PATH . '/app/routes.php';
$allowed = [];

foreach ($routes as [$routeMethod, $pattern, $handler]) {
    if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
        if ($routeMethod !== $method) {
            $allowed[] = $routeMethod;
            continue;
        }
        $args = array_map('intval', array_slice($matches, 1));
        $handler(...$args);
        exit;
    }
}

if ($allowed) {
    header('Allow: ' . implode(', ', array_unique($allowed)));
    abort(405, 'Méthode non autorisée.');
}
abort(404);
