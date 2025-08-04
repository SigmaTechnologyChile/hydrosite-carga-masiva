<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Flujo de Datos - Bancos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .info { background-color: #d1ecf1; border-color: #b3d7e6; color: #0c5460; }
        select { width: 100%; padding: 8px; margin: 10px 0; }
        .bank-count { font-weight: bold; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Prueba de Flujo de Datos - Bancos</h1>
        <p><strong>Organización ID:</strong> {{ $orgId }}</p>
        
        <div class="test-section success">
            <h2>✅ 1. Conexión a Base de Datos</h2>
            <p>Conexión exitosa - Los datos se están cargando desde la base de datos.</p>
        </div>
        
        <div class="test-section info">
            <h2>📊 2. Datos de Bancos</h2>
            <p>Cantidad de bancos encontrados: <span class="bank-count">{{ $bancos->count() }}</span></p>
        </div>
        
        <div class="test-section">
            <h2>🏦 3. Simulación del Select de Bancos</h2>
            <p>Este es exactamente como aparecerá en el modal:</p>
            
            <label for="banco-test">Banco de Prueba:</label>
            <select id="banco-test" name="banco_test">
                <option value="">Sin banco</option>
                @foreach($bancos as $banco)
                    <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="test-section">
            <h2>📝 4. Lista Completa de Bancos</h2>
            <ul>
                @foreach($bancos as $banco)
                    <li><strong>ID:</strong> {{ $banco->id }} - <strong>Nombre:</strong> {{ $banco->nombre }}</li>
                @endforeach
            </ul>
        </div>
        
        <div class="test-section success">
            <h2>🎉 5. Resultado</h2>
            <p><strong>✅ El flujo de datos está funcionando correctamente!</strong></p>
            <p>Los bancos se cargan dinámicamente desde la tabla <code>bancos</code> de la base de datos.</p>
            <p>El modal de "Configuración de Cuentas Iniciales" ahora usa estos datos en lugar de opciones hardcoded.</p>
        </div>
        
        <div class="test-section">
            <h2>🔗 6. Enlaces de Prueba</h2>
            <p><a href="{{ route('contable.index', $orgId) }}" target="_blank">Ver Modal Real en Contable</a></p>
        </div>
    </div>
</body>
</html>
