<?php
require_once 'vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ELIMINACIÓN DEL SERVICIO 216 ===\n";

try {
    // 1. Buscar el servicio 216
    $servicio = DB::table('services')->where('nro', 216)->first();
    
    if (!$servicio) {
        echo "❌ Servicio 216 no encontrado.\n";
        exit;
    }
    
    echo "✅ Servicio 216 encontrado:\n";
    echo "   - ID: {$servicio->id}\n";
    echo "   - Member ID: {$servicio->member_id}\n";
    echo "   - Org ID: {$servicio->org_id}\n";
    
    // 2. Obtener información del miembro
    $member = DB::table('members')->where('id', $servicio->member_id)->first();
    if ($member) {
        echo "   - Cliente: {$member->full_name}\n";
        echo "   - RUT: {$member->rut}\n";
    }
    
    // 3. Contar lecturas existentes
    $lecturas = DB::table('readings')->where('service_id', $servicio->id)->count();
    echo "   - Lecturas registradas: {$lecturas}\n\n";
    
    // 4. Eliminar todas las lecturas del servicio
    echo "🗑️ Eliminando lecturas del servicio 216...\n";
    $deletedReadings = DB::table('readings')->where('service_id', $servicio->id)->delete();
    echo "✅ Se eliminaron {$deletedReadings} lecturas.\n\n";
    
    echo "✅ PROCESO COMPLETADO\n";
    echo "El servicio 216 ya no aparecerá en las vistas porque no tiene lecturas.\n";
    echo "El servicio sigue existiendo pero sin datos de lecturas.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
