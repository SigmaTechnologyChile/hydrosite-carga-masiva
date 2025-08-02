<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PROBANDO NUEVA CONSULTA DE LECTURAS ===\n\n";

try {
    $org_id = 1;
    $currentPeriod = date('Y-m');
    $previousMonth = date('Y-m', strtotime('-1 month'));
    
    echo "Organización ID: {$org_id}\n";
    echo "Período actual: {$currentPeriod}\n";
    echo "Mes anterior: {$previousMonth}\n\n";
    
    // Probar la consulta modificada
    $queryLecturasMes = DB::table('services')
        ->join('members', 'services.member_id', '=', 'members.id')
        ->leftJoin('locations', 'services.locality_id', '=', 'locations.id')
        ->leftJoin('readings as current_reading', function($join) use ($currentPeriod) {
            $join->on('current_reading.service_id', '=', 'services.id')
                 ->where('current_reading.period', '=', $currentPeriod)
                 ->whereRaw('current_reading.id = (
                     SELECT MAX(id) 
                     FROM readings r2 
                     WHERE r2.service_id = services.id 
                     AND r2.period = ?
                 )', [$currentPeriod]);
        })
        ->leftJoin('readings as prev_reading', function($join) use ($previousMonth) {
            $join->on('prev_reading.service_id', '=', 'services.id')
                 ->where('prev_reading.period', '=', $previousMonth)
                 ->whereRaw('prev_reading.id = (
                     SELECT MAX(id) 
                     FROM readings r3 
                     WHERE r3.service_id = services.id 
                     AND r3.period = ?
                 )', [$previousMonth]);
        })
        ->where('services.org_id', $org_id);
    
    $lecturasMesActual = $queryLecturasMes->select(
        'services.id as service_id',
        'services.nro as service_number',
        'members.id as member_id',
        'members.rut',
        'members.full_name',
        'locations.name as sector_name',
        'current_reading.current_reading',
        'prev_reading.current_reading as previous_reading',
        'current_reading.id as reading_id'
    )
    ->orderBy('services.nro', 'asc')
    ->get();
    
    echo "=== RESULTADOS ===\n";
    echo "Total de servicios encontrados: " . $lecturasMesActual->count() . "\n\n";
    
    // Verificar si hay duplicados en los resultados
    $serviciosUnicos = $lecturasMesActual->pluck('service_id')->unique();
    echo "Servicios únicos: " . $serviciosUnicos->count() . "\n";
    
    if ($lecturasMesActual->count() !== $serviciosUnicos->count()) {
        echo "⚠️  ADVERTENCIA: Hay servicios duplicados en los resultados!\n";
        
        // Encontrar duplicados
        $duplicados = $lecturasMesActual->groupBy('service_id')->filter(function($grupo) {
            return $grupo->count() > 1;
        });
        
        foreach ($duplicados as $service_id => $grupo) {
            echo "Servicio {$service_id} aparece {$grupo->count()} veces:\n";
            foreach ($grupo as $item) {
                echo "  - N°: {$item->service_number}, RUT: {$item->rut}, Lectura: {$item->current_reading}\n";
            }
        }
    } else {
        echo "✅ No hay duplicados en los resultados\n";
    }
    
    // Mostrar algunos resultados como ejemplo
    echo "\n=== PRIMEROS 5 RESULTADOS ===\n";
    foreach ($lecturasMesActual->take(5) as $lectura) {
        echo "N°: {$lectura->service_number} | RUT: {$lectura->rut} | Cliente: {$lectura->full_name} | Lectura: " . ($lectura->current_reading ?? 'Sin lectura') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}

echo "\n=== PRUEBA COMPLETADA ===\n";
