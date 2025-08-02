<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LIMPIANDO DUPLICADOS PARA ORG_ID = 1 ===\n\n";

try {
    // Buscar duplicados antes de limpiar
    $duplicates = DB::table('readings')
        ->select('service_id', 'period', DB::raw('COUNT(*) as count'))
        ->where('org_id', 1)
        ->groupBy('service_id', 'period')
        ->having('count', '>', 1)
        ->get();

    echo "Duplicados encontrados: " . $duplicates->count() . "\n\n";

    if ($duplicates->count() > 0) {
        foreach ($duplicates as $dup) {
            $serviceInfo = DB::table('services')->where('id', $dup->service_id)->first();
            echo "Servicio: " . ($serviceInfo ? $serviceInfo->nro : 'N/A') . " | Período: {$dup->period} | Lecturas: {$dup->count}\n";
        }
        
        echo "\n=== INICIANDO LIMPIEZA ===\n";
        
        DB::beginTransaction();
        
        $totalDeleted = 0;
        
        foreach ($duplicates as $duplicate) {
            // Obtener el ID más reciente (el que mantener)
            $keepId = DB::table('readings')
                ->where('org_id', 1)
                ->where('service_id', $duplicate->service_id)
                ->where('period', $duplicate->period)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->value('id');
            
            // Eliminar todos excepto el más reciente
            $deleted = DB::table('readings')
                ->where('org_id', 1)
                ->where('service_id', $duplicate->service_id)
                ->where('period', $duplicate->period)
                ->where('id', '!=', $keepId)
                ->delete();
                
            $totalDeleted += $deleted;
            echo "Servicio {$duplicate->service_id} período {$duplicate->period}: eliminados {$deleted} registros, conservado ID {$keepId}\n";
        }
        
        DB::commit();
        echo "\n=== LIMPIEZA COMPLETADA ===\n";
        echo "Total de registros eliminados: {$totalDeleted}\n";
        
        // Verificar que no queden duplicados
        $remainingDuplicates = DB::table('readings')
            ->select('service_id', 'period', DB::raw('COUNT(*) as count'))
            ->where('org_id', 1)
            ->groupBy('service_id', 'period')
            ->having('count', '>', 1)
            ->get();
            
        echo "Duplicados restantes: " . $remainingDuplicates->count() . "\n";
        
    } else {
        echo "No se encontraron duplicados.\n";
    }
    
} catch (Exception $e) {
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
