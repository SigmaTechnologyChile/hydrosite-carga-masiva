<?php
require_once 'vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DEL SERVICIO 216 ===\n";

try {
    // 1. Buscar el servicio 216
    $servicio = DB::table('services')->where('nro', 216)->first();
    
    if (!$servicio) {
        echo "❌ Servicio 216 no encontrado en la tabla services.\n";
    } else {
        echo "✅ Servicio 216 encontrado:\n";
        echo "   - ID: {$servicio->id}\n";
        echo "   - Member ID: {$servicio->member_id}\n";
        echo "   - Org ID: {$servicio->org_id}\n";
        
        // 2. Contar lecturas restantes
        $lecturas = DB::table('readings')->where('service_id', $servicio->id)->count();
        echo "   - Lecturas actuales: {$lecturas}\n";
        
        if ($lecturas > 0) {
            echo "⚠️ Aún hay lecturas pendientes de eliminar.\n";
            
            // Mostrar algunas lecturas
            $lecturasDetalle = DB::table('readings')
                ->where('service_id', $servicio->id)
                ->select('id', 'period', 'current_reading')
                ->limit(5)
                ->get();
                
            echo "Primeras 5 lecturas:\n";
            foreach ($lecturasDetalle as $lectura) {
                echo "   - ID: {$lectura->id}, Período: {$lectura->period}, Lectura: {$lectura->current_reading}\n";
            }
        } else {
            echo "✅ No hay lecturas para el servicio 216.\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
