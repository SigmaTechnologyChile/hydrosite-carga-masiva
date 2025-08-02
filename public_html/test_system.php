<?php
echo "=== PRUEBA DE SISTEMA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";

// Verificar que las clases existen
if (class_exists('Illuminate\Foundation\Application')) {
    echo "✅ Laravel Framework detectado\n";
} else {
    echo "❌ Laravel Framework NO detectado\n";
}

// Verificar archivo .env
if (file_exists(__DIR__ . '/.env')) {
    echo "✅ Archivo .env encontrado\n";
} else {
    echo "❌ Archivo .env NO encontrado\n";
}

// Verificar ruta de lecturas
if (file_exists(__DIR__ . '/app/Http/Controllers/Org/ReadingController.php')) {
    echo "✅ ReadingController encontrado\n";
} else {
    echo "❌ ReadingController NO encontrado\n";
}

echo "=== FIN DE PRUEBA ===\n";
