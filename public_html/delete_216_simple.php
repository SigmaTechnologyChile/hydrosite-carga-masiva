<?php
// SCRIPT SIMPLE PARA ELIMINAR SERVICIO 216
// Ejecutar: php delete_216_simple.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Iniciando eliminación del servicio 216...\n";

try {
    // Incluir Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    // Usar DB directamente
    $deletedCount = DB::table('readings')
        ->whereIn('service_id', function($query) {
            $query->select('id')
                  ->from('services')
                  ->where('nro', 216);
        })
        ->delete();

    echo "✅ ÉXITO: Se eliminaron {$deletedCount} lecturas del servicio 216.\n";
    echo "El servicio 216 ya no aparecerá en las vistas.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
