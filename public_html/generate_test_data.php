<?php

require_once 'vendor/autoload.php';

// Cargar el framework de Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Service;
use App\Models\Member;
use App\Models\Reading;

echo "Generando archivo de datos reales para prueba de carga masiva...\n\n";

try {
    // Obtener servicios activos con sus miembros
    $services = Service::with(['member', 'org'])
        ->whereHas('member')
        ->where('status', 'active')
        ->orderBy('org_id')
        ->orderBy('nro')
        ->get();

    echo "Servicios encontrados: " . $services->count() . "\n";

    if ($services->count() === 0) {
        echo "No se encontraron servicios activos con miembros.\n";
        exit;
    }

    // Crear el archivo CSV
    $filename = 'storage/templates/datos_reales_prueba_lecturas.csv';
    $file = fopen($filename, 'w');

    // Escribir encabezados
    fputcsv($file, ['numero_servicio', 'rut', 'lectura_actual', 'periodo'], ';');

    $periodo = date('Y-m'); // Período actual

    foreach ($services->take(10) as $service) { // Limitar a 10 para prueba
        if ($service->member) {
            // Generar una lectura de ejemplo basada en datos existentes
            $lecturaAnterior = Reading::where('service_id', $service->id)
                ->orderBy('period', 'desc')
                ->first();
            
            $lecturaActual = $lecturaAnterior ? 
                $lecturaAnterior->current_reading + rand(10, 50) : 
                rand(100, 1000);

            $row = [
                str_pad($service->nro, 5, '0', STR_PAD_LEFT),
                $service->member->rut,
                $lecturaActual,
                $periodo
            ];
            
            fputcsv($file, $row, ';');
            
            echo "Servicio #" . str_pad($service->nro, 5, '0', STR_PAD_LEFT) . 
                 " - RUT: " . $service->member->rut . 
                 " - Org: " . ($service->org->name ?? 'N/A') . 
                 " - Lectura sugerida: " . $lecturaActual . "\n";
        }
    }

    fclose($file);
    
    echo "\n✅ Archivo generado exitosamente: " . $filename . "\n";
    echo "Puedes descargarlo desde: " . url('storage/templates/datos_reales_prueba_lecturas.csv') . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
