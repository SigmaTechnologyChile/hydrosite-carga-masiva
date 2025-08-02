<?php
// Archivo de prueba para verificar rutas DTE
echo "=== PRUEBA DE RUTAS DTE ===\n";

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    // Simular una solicitud
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Verificar si las rutas están registradas
    $routes = app('router')->getRoutes();
    
    $readingsRoutes = [];
    foreach ($routes as $route) {
        if (strpos($route->getName() ?? '', 'readings') !== false) {
            $readingsRoutes[] = [
                'name' => $route->getName(),
                'uri' => $route->uri(),
                'methods' => $route->methods()
            ];
        }
    }
    
    echo "Rutas de readings encontradas:\n";
    foreach ($readingsRoutes as $route) {
        echo "- {$route['name']}: {$route['uri']} [" . implode(', ', $route['methods']) . "]\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
