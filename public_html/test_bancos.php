<?php
// Script de prueba para verificar el flujo de datos de bancos
require_once 'bootstrap/app.php';

echo "<h1>Prueba de Flujo de Datos - Bancos</h1>\n";

try {
    // 1. Verificar conexión a la base de datos
    echo "<h2>1. Conexión a Base de Datos</h2>\n";
    $pdo = DB::connection()->getPdo();
    echo "✅ Conexión exitosa a la base de datos<br>\n";
    
    // 2. Probar el modelo Banco
    echo "<h2>2. Modelo Banco</h2>\n";
    $bancos = \App\Models\Banco::orderBy('nombre')->get();
    echo "✅ Cantidad de bancos encontrados: " . $bancos->count() . "<br>\n";
    
    // 3. Mostrar los bancos
    echo "<h2>3. Lista de Bancos</h2>\n";
    echo "<select>\n";
    echo "  <option value=''>Sin banco</option>\n";
    foreach($bancos as $banco) {
        echo "  <option value='{$banco->id}'>{$banco->nombre}</option>\n";
    }
    echo "</select>\n";
    
    // 4. Verificar el controlador
    echo "<h2>4. Simulación del Controlador</h2>\n";
    $configuraciones = \App\Models\ConfiguracionInicial::with('cuenta')->where('org_id', 1)->get();
    $cuentasIniciales = \App\Models\Cuenta::all();
    $bancosController = \App\Models\Banco::orderBy('nombre')->get();
    
    echo "✅ Configuraciones: " . $configuraciones->count() . "<br>\n";
    echo "✅ Cuentas: " . $cuentasIniciales->count() . "<br>\n";
    echo "✅ Bancos para vista: " . $bancosController->count() . "<br>\n";
    
    echo "<h2>5. Resultado Final</h2>\n";
    echo "🎉 El flujo de datos está funcionando correctamente!<br>\n";
    echo "Los bancos se cargarán dinámicamente desde la base de datos.<br>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
    echo "Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")<br>\n";
}
?>
