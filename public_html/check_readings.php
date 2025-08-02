<?php
// Script simple para verificar si hay lecturas en la base de datos

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== VERIFICACIÓN DE LECTURAS ===\n\n";
    
    // Verificar conexión a la base de datos
    $dbName = DB::connection()->getDatabaseName();
    echo "Base de datos conectada: $dbName\n\n";
    
    // Contar total de lecturas
    $totalReadings = DB::table('readings')->count();
    echo "Total de lecturas en la base de datos: $totalReadings\n\n";
    
    // Mostrar algunas lecturas de ejemplo
    if ($totalReadings > 0) {
        echo "=== ÚLTIMAS 5 LECTURAS ===\n";
        $lastReadings = DB::table('readings')
            ->select('id', 'current_reading', 'period', 'total')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($lastReadings as $reading) {
            echo "ID: {$reading->id} | Lectura: {$reading->current_reading} | Período: {$reading->period} | Total: \${$reading->total}\n";
        }
    } else {
        echo "⚠️  No hay lecturas registradas en la base de datos.\n";
        echo "💡 Los botones DTE y Editar solo aparecen cuando hay lecturas históricas.\n";
    }
    
    // Verificar organizaciones
    $totalOrgs = DB::table('orgs')->count();
    echo "\nTotal de organizaciones: $totalOrgs\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
