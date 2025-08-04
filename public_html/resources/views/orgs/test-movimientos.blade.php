<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Modales Ingresos y Egresos</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }
        .form-group { margin: 15px 0; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 80px; resize: vertical; }
        button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background-color: #0056b3; }
        .btn-success { background-color: #28a745; }
        .btn-danger { background-color: #dc3545; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px; border-radius: 4px; color: white; z-index: 1000; max-width: 300px; }
        .notification.success { background-color: #28a745; }
        .notification.error { background-color: #dc3545; }
        .notification.info { background-color: #17a2b8; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Prueba de Modales de Ingresos y Egresos</h1>
        <p><strong>Organización ID:</strong> {{ $orgId }}</p>
        
        <div class="grid">
            <!-- Modal de Ingresos -->
            <div class="test-section">
                <h2>💰 Registro de Ingresos</h2>
                <form id="testIngresosForm">
                    <div class="form-group">
                        <label for="fecha-ingresos">Fecha:</label>
                        <input type="date" id="fecha-ingresos" name="fecha" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nro-dcto-ingresos">N° Comprobante:</label>
                        <input type="text" id="nro-dcto-ingresos" name="nro_dcto" value="COMP-001" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria-ingresos">Categoría:</label>
                        <select id="categoria-ingresos" name="categoria_id" required>
                            <option value="">-- Selecciona categoría --</option>
                            @foreach($categoriasIngresos as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->subcategoria }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cuenta-destino">Cuenta Destino:</label>
                        <select id="cuenta-destino" name="cuenta_destino" required>
                            <option value="">-- Selecciona cuenta --</option>
                            <option value="caja_general">Caja General</option>
                            <option value="cuenta_corriente_1">Cuenta Corriente 1</option>
                            <option value="cuenta_corriente_2">Cuenta Corriente 2</option>
                            <option value="cuenta_ahorro">Cuenta de Ahorro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion-ingresos">Descripción:</label>
                        <textarea id="descripcion-ingresos" name="descripcion" required>Ingreso de prueba desde formulario de test</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto-ingresos">Monto:</label>
                        <input type="number" id="monto-ingresos" name="monto" value="50000" step="0.01" required>
                    </div>
                    
                    <button type="submit" class="btn-success">💰 Registrar Ingreso</button>
                </form>
            </div>
            
            <!-- Modal de Egresos -->
            <div class="test-section">
                <h2>💸 Registro de Egresos</h2>
                <form id="testEgresosForm">
                    <div class="form-group">
                        <label for="fecha-egresos">Fecha:</label>
                        <input type="date" id="fecha-egresos" name="fecha" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nro-dcto-egresos">N° Boleta/Factura:</label>
                        <input type="text" id="nro-dcto-egresos" name="nro_dcto" value="FACT-001" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria-egresos">Categoría:</label>
                        <select id="categoria-egresos" name="categoria_id" required>
                            <option value="">-- Selecciona categoría --</option>
                            @foreach($categoriasEgresos as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->subcategoria }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cuenta-origen">Cuenta Origen:</label>
                        <select id="cuenta-origen" name="cuenta_origen" required>
                            <option value="">-- Selecciona cuenta --</option>
                            <option value="caja_general">Caja General</option>
                            <option value="cuenta_corriente_1">Cuenta Corriente 1</option>
                            <option value="cuenta_corriente_2">Cuenta Corriente 2</option>
                            <option value="cuenta_ahorro">Cuenta de Ahorro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="razon_social">Razón Social Proveedor:</label>
                        <input type="text" id="razon_social" name="razon_social" value="Proveedor de Prueba SpA" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rut">RUT Proveedor:</label>
                        <input type="text" id="rut" name="rut_proveedor" value="12.345.678-9">
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion-egresos">Descripción:</label>
                        <textarea id="descripcion-egresos" name="descripcion" required>Egreso de prueba desde formulario de test</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto-egresos">Monto:</label>
                        <input type="number" id="monto-egresos" name="monto" value="25000" step="0.01" required>
                    </div>
                    
                    <button type="submit" class="btn-danger">💸 Registrar Egreso</button>
                </form>
            </div>
        </div>
        
        <div id="resultado" style="margin-top: 20px; padding: 15px; border-radius: 4px; display: none;"></div>
        
        <div class="test-section">
            <h3>📊 Información de Categorías</h3>
            <p><strong>Categorías de Ingresos:</strong> {{ $categoriasIngresos->count() }} encontradas</p>
            <p><strong>Categorías de Egresos:</strong> {{ $categoriasEgresos->count() }} encontradas</p>
        </div>
    </div>
    
    <script>
        function mostrarNotificacion(mensaje, tipo = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${tipo}`;
            notification.textContent = mensaje;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        
        function mostrarResultado(data, tipo) {
            const resultado = document.getElementById('resultado');
            resultado.style.display = 'block';
            
            if (tipo === 'success') {
                resultado.style.backgroundColor = '#d4edda';
                resultado.style.color = '#155724';
                resultado.innerHTML = `
                    <h3>✅ ${data.message}</h3>
                    <p><strong>Nuevo saldo de cuenta:</strong> $${data.nuevo_saldo.toLocaleString()}</p>
                    <p><strong>ID del movimiento:</strong> ${data.movimiento.id}</p>
                `;
            } else {
                resultado.style.backgroundColor = '#f8d7da';
                resultado.style.color = '#721c24';
                resultado.innerHTML = `<h3>❌ Error</h3><p>${data.message}</p>`;
            }
        }
        
        // Manejo del formulario de ingresos
        document.getElementById('testIngresosForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            const inputs = this.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.name) {
                    formData.append(input.name, input.value);
                }
            });
            
            mostrarNotificacion('⏳ Registrando ingreso en la base de datos...', 'info');
            
            fetch('/{{ $orgId }}/ingresos', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarResultado(data, 'success');
                    mostrarNotificacion('✅ Ingreso registrado exitosamente');
                    this.reset();
                } else {
                    mostrarResultado(data, 'error');
                    mostrarNotificacion('❌ Error al registrar ingreso', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('❌ Error de conexión', 'error');
            });
        });
        
        // Manejo del formulario de egresos
        document.getElementById('testEgresosForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            const inputs = this.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.name) {
                    formData.append(input.name, input.value);
                }
            });
            
            mostrarNotificacion('⏳ Registrando egreso en la base de datos...', 'info');
            
            fetch('/{{ $orgId }}/egresos', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarResultado(data, 'success');
                    mostrarNotificacion('✅ Egreso registrado exitosamente');
                    this.reset();
                } else {
                    mostrarResultado(data, 'error');
                    mostrarNotificacion('❌ Error al registrar egreso', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('❌ Error de conexión', 'error');
            });
        });
    </script>
</body>
</html>
