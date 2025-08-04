<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Formulario de Bancos</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px; border-radius: 4px; color: white; z-index: 1000; }
        .notification.success { background-color: #28a745; }
        .notification.error { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Prueba de Flujo Completo - Bancos en Base de Datos</h1>
        <p><strong>Organización ID:</strong> {{ $orgId }}</p>
        
        <form id="testForm">
            <h3>Datos de Prueba</h3>
            
            <div class="form-group">
                <label for="saldo_caja_general">Saldo Caja General:</label>
                <input type="number" id="saldo_caja_general" name="saldo_caja_general" value="100000" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="banco_caja_general">Banco Caja General:</label>
                <select id="banco_caja_general" name="banco_caja_general">
                    <option value="">Sin banco</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="numero_caja_general">Número de Cuenta Caja General:</label>
                <input type="text" id="numero_caja_general" name="numero_caja_general" value="12345678-9">
            </div>
            
            <div class="form-group">
                <label for="saldo_cta_corriente_1">Saldo Cuenta Corriente 1:</label>
                <input type="number" id="saldo_cta_corriente_1" name="saldo_cta_corriente_1" value="50000" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="banco_cta_corriente_1">Banco Cuenta Corriente 1:</label>
                <select id="banco_cta_corriente_1" name="banco_cta_corriente_1">
                    <option value="">Sin banco</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="saldo_cta_corriente_2">Saldo Cuenta Corriente 2:</label>
                <input type="number" id="saldo_cta_corriente_2" name="saldo_cta_corriente_2" value="30000" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="banco_cta_corriente_2">Banco Cuenta Corriente 2:</label>
                <select id="banco_cta_corriente_2" name="banco_cta_corriente_2">
                    <option value="">Sin banco</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="saldo_cuenta_ahorro">Saldo Cuenta Ahorro:</label>
                <input type="number" id="saldo_cuenta_ahorro" name="saldo_cuenta_ahorro" value="20000" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="banco_cuenta_ahorro">Banco Cuenta Ahorro:</label>
                <select id="banco_cuenta_ahorro" name="banco_cuenta_ahorro">
                    <option value="">Sin banco</option>
                    @foreach($bancos as $banco)
                        <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="responsable">Responsable:</label>
                <input type="text" id="responsable" name="responsable" value="Juan Pérez" required>
            </div>
            
            <button type="submit">🚀 Probar Guardado en Base de Datos</button>
        </form>
        
        <div id="resultado" style="margin-top: 20px; padding: 15px; border-radius: 4px; display: none;"></div>
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
        
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('orgId', {{ $orgId }});
            
            // Obtener todos los valores del formulario
            const inputs = this.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name) {
                    formData.append(input.name, input.value);
                }
            });
            
            // También agregar campos que faltan
            formData.append('numero_cta_corriente_1', '98765432-1');
            formData.append('numero_cta_corriente_2', '56789012-3');
            formData.append('numero_cuenta_ahorro', '34567890-5');
            
            mostrarNotificacion('⏳ Enviando datos a la base de datos...', 'info');
            
            fetch('/configuracion-cuentas-iniciales', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const resultado = document.getElementById('resultado');
                resultado.style.display = 'block';
                
                if (data.success) {
                    resultado.style.backgroundColor = '#d4edda';
                    resultado.style.color = '#155724';
                    resultado.innerHTML = `
                        <h3>✅ ¡ÉXITO!</h3>
                        <p><strong>Mensaje:</strong> ${data.message}</p>
                        <p><strong>Estado:</strong> Los bancos seleccionados se guardaron correctamente en la base de datos</p>
                        <p><strong>Datos guardados:</strong></p>
                        <ul>
                            <li>Caja General: $${formData.get('saldo_caja_general')} - Banco ID: ${formData.get('banco_caja_general')}</li>
                            <li>Cuenta Corriente 1: $${formData.get('saldo_cta_corriente_1')} - Banco ID: ${formData.get('banco_cta_corriente_1')}</li>
                            <li>Cuenta Corriente 2: $${formData.get('saldo_cta_corriente_2')} - Banco ID: ${formData.get('banco_cta_corriente_2')}</li>
                            <li>Cuenta Ahorro: $${formData.get('saldo_cuenta_ahorro')} - Banco ID: ${formData.get('banco_cuenta_ahorro')}</li>
                        </ul>
                    `;
                    mostrarNotificacion('✅ Datos guardados exitosamente en la base de datos');
                } else {
                    resultado.style.backgroundColor = '#f8d7da';
                    resultado.style.color = '#721c24';
                    resultado.innerHTML = `<h3>❌ Error</h3><p>${data.message || 'Error desconocido'}</p>`;
                    mostrarNotificacion('❌ Error al guardar los datos', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const resultado = document.getElementById('resultado');
                resultado.style.display = 'block';
                resultado.style.backgroundColor = '#f8d7da';
                resultado.style.color = '#721c24';
                resultado.innerHTML = `<h3>❌ Error de Conexión</h3><p>${error.message}</p>`;
                mostrarNotificacion('❌ Error de conexión', 'error');
            });
        });
    </script>
</body>
</html>
