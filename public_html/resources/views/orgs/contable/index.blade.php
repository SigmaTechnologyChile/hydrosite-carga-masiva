<!-- Vista de Libro de Caja Tabular -->
@if(!($mostrarLibroCaja ?? false))

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Gestión Contable</title>
</head>
<style>
  .notification {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    min-width: 320px;
    max-width: 90vw;
    padding: 20px 40px;
    background: #222;
    color: #fff;
    border-radius: 10px;
    font-size: 1.2rem;
    text-align: center;
    box-shadow: 0 4px 24px rgba(0,0,0,0.25);
    display: none;
    opacity: 0.97;
    transition: opacity 0.2s;
  }
  .notification.success { background: #28a745; color: #fff; }
  .notification.error { background: #dc3545; color: #fff; }
  
  /* Debug - Eliminar restricciones de botones en modal */
  .modal button, .modal .btn {
    pointer-events: auto !important;
    z-index: 1000 !important;
  }
  
  /* Debug - Asegurar funcionalidad en modal de configuración */
  #modalConfiguracionCuentas button,
  #modalConfiguracionCuentas .btn,
  #saveCajaGeneralBtn,
  #editCajaGeneralBtn,
  #saveCuentaCorrienteBtn,
  #editCuentaCorrienteBtn,
  #saveCuentaAhorroBtn,
  #editCuentaAhorroBtn {
    pointer-events: auto !important;
    z-index: 1001 !important;
    cursor: pointer !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-flex !important;
  }
  
  /* Debug - Remover bloqueos de campos en modal */
  #modalConfiguracionCuentas input,
  #modalConfiguracionCuentas select {
    pointer-events: auto !important;
    cursor: text !important;
    background-color: #fff !important;
    color: #212529 !important;
  }
  
  /* Debug - Forzar funcionalidad de botones principales */
  #ingresosBtn, #egresosBtn, #balanceBtn, #conciliacionBtn {
    pointer-events: auto !important;
    cursor: pointer !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-flex !important;
    z-index: 1000 !important;
  }
  
  /* Debug - Eliminar cualquier overlay bloqueador */
  #ingresosBtn *, #egresosBtn *, #balanceBtn *, #conciliacionBtn * {
    pointer-events: auto !important;
  }
  
  /* Debug - Asegurar que x-boton-protegido funcione */
  x-boton-protegido, .boton-protegido {
    pointer-events: auto !important;
    cursor: pointer !important;
  }
</style>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión Contable</title>

  <!-- Favicons -->
  <link href="https://hydrosite.cl/public/theme/common/img/favicon.png" rel="icon">
  <link href="https://hydrosite.cl/public/theme/common/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="https://hydrosite.cl/public/theme/nice/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://hydrosite.cl/public/theme/nice/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">

  <!-- Chart.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- SheetJS para exportar a Excel -->
  <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

  <link rel="stylesheet" href="{{ asset('css/contable/style.css') }}">
  <style>
    /* Estilos adicionales para el modal de Cuentas Iniciales */
    .warning-box {
      background-color: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 10px 15px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }
    
    /* Asegurar que los botones principales sean clickeables */
    .btn-wrapper button,
    .btn-wrapper .btn {
      pointer-events: auto !important;
      z-index: 1000 !important;
      position: relative !important;
    }
    
    .warning-box i {
      font-size: 24px;
      color: #ffc107;
      margin-right: 10px;
    }
    
    .form-section {
      margin-bottom: 20px;
      padding: 15px;
      border: 1px solid #eee;
      border-radius: 8px;
    }
    
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 15px;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    .required {
      color: #e53935;
    }

    /* Nuevos estilos para funcionalidades avanzadas */
    .modal-header-buttons {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .section-header-with-button {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .add-account-btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
    }

    .add-account-btn:hover {
      background-color: #0056b3;
    }

    .account-item {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      background-color: #fafafa;
      position: relative;
    }

    .account-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .account-header h4 {
      margin: 0;
      color: #333;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .remove-account-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 5px 8px;
      border-radius: 3px;
      cursor: pointer;
      font-size: 12px;
    }

    .remove-account-btn:hover {
      background-color: #c82333;
    }

    .action-btn {
      background-color: #17a2b8;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .action-btn:hover {
      background-color: #138496;
    }

    .secondary-btn {
      background-color: #6c757d;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .secondary-btn:hover {
      background-color: #5a6268;
    }

    .left-buttons, .right-buttons {
      display: flex;
      gap: 10px;
    }

    .account-item.removing {
      animation: slideOut 0.3s ease-out forwards;
    }

    @keyframes slideOut {
      to {
        opacity: 0;
        transform: translateX(-100%);
        height: 0;
        margin: 0;
        padding: 0;
      }
    }

    .account-item.adding {
      animation: slideIn 0.3s ease-out forwards;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(100%);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    /* Estilos para botones específicos de cuentas */
    .account-actions {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .btn-edit-account, .btn-save-account {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
      display: flex;
      align-items: center;
      gap: 4px;
      transition: all 0.2s ease;
    }

    .btn-edit-account {
      background-color: #ffc107;
      color: #212529;
    }

    .btn-edit-account:hover {
      background-color: #e0a800;
    }

    .btn-save-account {
      background-color: #28a745;
      color: white;
    }

    .btn-save-account:hover {
      background-color: #218838;
    }

    .btn-save-account:disabled {
      background-color: #6c757d;
      cursor: not-allowed;
    }

    .account-status {
      margin-top: 10px;
      padding: 8px 12px;
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      border-radius: 4px;
      color: #155724;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .account-item.saved {
      border-color: #28a745;
      background-color: #f8fff9;
    }

    .account-item.editing {
      border-color: #ffc107;
      background-color: #fffdf5;
    }

    /* Estilos para estado bloqueado/guardado */
    .account-item.locked {
      background-color: #f8f9fa !important;
      border-color: #6c757d !important;
      opacity: 0.8;
      position: relative;
    }

    /* Clase locked removida - sin bloqueos */
    .account-item.locked {
      /* Sin restricciones - funcionalidad completa */
    }

    .account-item.locked .form-grid input,
    .account-item.locked .form-grid select {
      /* Campos funcionales sin bloqueo */
      background-color: #fff;
      color: #212529;
      cursor: text;
    }

    .account-item.locked .account-header h4 {
      color: #6c757d;
    }

    .account-item.locked .account-status {
      background-color: #e2e3e5;
      border-color: #babfc7;
      color: #495057;
    }

    /* Estilos para hacer los botones del modal más pequeños */
    .button-group .secondary-btn,
    .button-group .submit-btn {
      padding: 6px 12px !important;
      font-size: 13px !important;
      min-height: auto !important;
    }

    .button-group .secondary-btn i,
    .button-group .submit-btn i {
      font-size: 12px;
    }
  </style>
</head>

<body>
<!-- Notificación centrada y visible -->
<div id="notification" class="notification" style="z-index:99999; display:none; opacity:0.97;"></div>

<div class="container">
  <!-- Vista de Registro de Ingresos/Egresos -->
  <div id="registroSection" class="registro-section">
    <div class="card">
      <div class="card-header">
        <div>Sistema de Gestión Financiera</div>
      </div>
      <div class="card-body">
        <h1>Registro de Ingresos y Egresos</h1>

        <div class="btn-wrapper">
          <x-boton-protegido id="ingresosBtn" :habilitado="true">
            <i class="bi bi-cash-coin"></i>Registro de Ingresos
          </x-boton-protegido>
          <x-boton-protegido id="egresosBtn" :habilitado="true">
            <i class="bi bi-credit-card"></i>Registro de Egresos
          </x-boton-protegido>
          <x-boton-protegido id="giroDepositosBtn" :habilitado="true">
            <i class="bi bi-arrow-left-right"></i>Giros y Depósitos
          </x-boton-protegido>
          <x-boton-protegido id="libroCajaBtn" :habilitado="true">
            <i class="bi bi-journal-bookmark"></i>Libro de Caja Tabular
          </x-boton-protegido>
          <x-boton-protegido id="balanceBtn" :habilitado="true">
            <i class="bi bi-bar-chart"></i>Balance
          </x-boton-protegido>
          <x-boton-protegido id="conciliacionBtn" :habilitado="true">
            <i class="bi bi-bank"></i>Conciliación Bancaria
          </x-boton-protegido>
          <x-boton-protegido id="informeRubroBtn" :habilitado="true">
            <i class="bi bi-pie-chart"></i>Informe por Rubro
          </x-boton-protegido>
          <x-boton-protegido id="movimientosBtn" :habilitado="true">
            <i class="bi bi-list-check"></i>Movimientos
          </x-boton-protegido>
          <!-- Nuevo botón Cuentas Iniciales -->
          <x-boton-protegido id="cuentasInicialesBtn" style="background-color: #d32f2f; color: #fff; border: none;" :habilitado="true">
            <i class="bi bi-journal-plus"></i>Cuentas Iniciales
          </x-boton-protegido>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Ingresos MODIFICADO con pestañas -->
  <div id="ingresosModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2><i class="bi bi-cash-coin"></i> Registro de Ingresos</h2>
        <button class="modal-close" id="closeIngresosModal">Cerrar</button>
      </div>

      <form id="ingresosForm">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <!-- Columna 1 -->
          <div>
            <label for="fecha-ingresos">Fecha</label>
            <input type="date" id="fecha-ingresos" name="fecha" required style="width: 100%;">

            <label for="nro-dcto-ingresos">N° Comprobante</label>
            <input type="text" id="nro-dcto-ingresos" name="nro_dcto" placeholder="Ingrese número de comprobante" required style="width: 100%;">

            <label for="categoria-ingresos">Categoría de Ingreso</label>
            <select id="categoria-ingresos" name="categoria" required style="width: 100%;">
              <option value="">-- Selecciona una categoría --</option>
              <option value="venta_agua">Venta de Agua (Total Consumo)</option>
              <option value="cuotas_incorporacion">Cuotas de Incorporación (Cuotas de Incorporación)</option>
              <option value="venta_medidores">Venta de Medidores (Otros Ingresos)</option>
              <option value="trabajos_domicilio">Trabajos en Domicilio (Otros Ingresos)</option>
              <option value="subsidios">Subsidios (Otros Ingresos)</option>
              <option value="otros_aportes">Otros Aportes (Otros Ingresos)</option>
              <option value="multas_inasistencia">Multas Inasistencia (Otros Ingresos)</option>
              <option value="otras_multas">Otras Multas (Otros Ingresos)</option>
            </select>

            <label for="cuenta-destino">Cuenta Destino</label>
            <select id="cuenta-destino" name="cuenta_destino" required style="width: 100%;">
              <option value="">-- Selecciona una cuenta --</option>
              <option value="caja_general">Caja General</option>
              <option value="cuenta_corriente_1">Cuenta Corriente 1</option>
              <option value="cuenta_corriente_2">Cuenta Corriente 2</option>
              <!-- Agregar Cuenta de Ahorro -->
              <option value="cuenta_ahorro">Cuenta de Ahorro</option>
            </select>
          </div>

          <!-- Columna 2 -->
          <div>
            <label for="descripcion-ingresos">Descripción</label>
            <textarea id="descripcion-ingresos" name="descripcion" required
                      style="width: 100%; padding: 14px; min-height: 100px; border: 1px solid #ddd; border-radius: 8px; resize: vertical;"></textarea>

            <label for="monto-ingresos">Monto</label>
            <input type="number" id="monto-ingresos" name="monto" step="0.01" required class="monto-input" style="width: 100%;">
  </div>
        </div>

        <div style="grid-column: span 2; margin-top: 20px; display: flex; gap: 15px;">
          <button type="submit" class="submit-btn" style="flex: 1;">
            <i class="bi bi-save"></i> Registrar Ingreso
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de Egresos -->
  <div id="egresosModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2><i class="bi bi-credit-card"></i> Registro de Egresos</h2>
        <button class="modal-close" id="closeEgresosModal">Cerrar</button>
      </div>
      <form id="egresosForm">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <!-- Columna 1 -->
          <div>
            <label for="fecha-egresos">Fecha</label>
            <input type="date" id="fecha-egresos" name="fecha" required style="width: 100%;">

            <label for="nro-dcto-egresos">N° Boleta/Factura</label>
            <input type="text" id="nro-dcto-egresos" name="nro_dcto" placeholder="Ingrese número de comprobante" required style="width: 100%;">

            <label for="categoria-egresos">Categoría de Egreso</label>
            <select id="categoria-egresos" name="categoria" required style="width: 100%;">
              <option value="">-- Selecciona una categoría --</option>
              <option value="energia_electrica">Energía Eléctrica -> (Gastos de Operación)</option>
              <option value="sueldos">Sueldos y Leyes Sociales -> (Gastos de Operación)</option>
              <option value="otras_cuentas">Otras Ctas. (Agua, Int. Cel.) -> (Gastos de Operación)</option>
              <option value="mantencion">Mantención y reparaciones Instalaciones -> (Gastos de Mantención)</option>
              <option value="insumos_oficina">Insumos y Materiales (Oficina) -> (Gastos de Administración)</option>
              <option value="materiales_red">Materiales e Insumos (Red) -> (Gastos de Mejoramiento)</option>
              <option value="viaticos">Viáticos / Seguros / Movilización -> (Otros Gastos)</option>
              <option value="trabajos_domicilio">Gastos por Trabajos en domicilio -> (Gastos de Mantención)</option>
              <option value="mejoramiento">Mejoramiento / Inversiones -> (Gastos de Mejoramiento)</option>
            </select>

            <label for="cuenta-origen">Cuenta Origen</label>
            <select id="cuenta-origen" name="cuenta_origen" required style="width: 100%;">
              <option value="">-- Selecciona una cuenta --</option>
              <option value="caja_general">Caja General</option>
              <option value="cuenta_corriente_1">Cuenta Corriente 1</option>
              <!-- Agregar Cuenta de Ahorro -->
              <option value="cuenta_ahorro">Cuenta de Ahorro</option>
            </select>
          </div>

          <!-- Columna 2 -->
          <div>
            <label for="proveedor">Razón Social Proveedor</label>
            <input type="text" id="razon_social" name="razon_social" placeholder="Razón Social" required style="width: 100%;">

            <label for="domicilio">R.U.T.</label>
            <input type="text" id="rut" name="rut_proveedor" placeholder="RUT del Proveedor" style="width: 100%;">

            <label for="descripcion-egresos">Descripción</label>
              <textarea id="descripcion-egresos" name="descripcion" required
                        style="width: 100%; padding: 14px; min-height: 100px; border: 1px solid #ddd; border-radius: 8px; resize: vertical;"></textarea>

            <label for="monto-egresos">Monto</label>
            <input type="number" id="monto-egresos" name="monto" step="0.01" required class="monto-input" style="width: 100%;">
          </div>
        </div>

        <div style="grid-column: span 2; margin-top: 20px; display: flex; gap: 15px;">
          <button type="submit" class="submit-btn" style="flex: 1;">
            <i class="bi bi-save"></i> Registrar Egreso
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Nueva Vista: Giros y Depósitos -->
  <div id="girosDepositosSection" class="giros-depositos-section" style="display: none;">
    <div class="section-header">
      <h1>Giros y Depósitos</h1>
      <p>Movimientos entre cuentas</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
      <!-- Sección Giros -->
      <div class="form-section" style="border-right: 1px solid #eee; padding-right: 20px;">
        <h3><i class="bi bi-send"></i> GIROS (Desde Cta. Cte./ Cta. Ahorro -> Caja General)</h3>
        <form id="girosForm">
          <div style="display: grid; grid-template-columns: 1fr; gap: 0px 0px;">
            <div class="form-group">
              <label for="fecha-giro">Fecha</label>
              <input type="date" id="fecha-giro" name="fecha" required style="width: 100%;">
            </div>
            <div class="form-group">
              <label for="monto-giro">Monto</label>
              <input type="number" id="monto-giro" name="monto" step="0.01" required style="width: 100%;">
            </div>
            <div class="form-group">
              <label for="cuenta-giro">Cuenta Origen</label>
              <select id="cuenta-giro" name="cuenta" required style="width: 100%;">
                <option value="cuenta_corriente_1">Cuenta Corriente 1</option>
                <!-- Agregar Cuenta de Ahorro -->
                <option value="cuenta_ahorro">Cuenta de Ahorro</option>
              </select>
            </div>
            <div class="form-group">
              <label for="detalle-giro">Detalle</label>
              <input type="text" id="detalle-giro" name="detalle" placeholder="Detalle del giro" required style="width: 100%;">
            </div>
            <!-- Asegurarse de que el campo de N° Comprobante esté presente -->
            <div class="form-group">
              <label for="nro-dcto-giro">N° Comprobante</label>
              <input type="text" id="nro-dcto-giro" name="nro_dcto" required style="width: 100%;" readonly>
            </div>
          </div>

          <div style="margin-top: 20px; text-align: center;">
            <button type="submit" class="submit-btn">
              <i class="bi bi-save"></i> Registrar Giro
            </button>
          </div>
        </form>
      </div>

      <!-- Sección Depósitos -->
      <div class="form-section">
        <h3><i class="bi bi-bank"></i> DEPÓSITOS (Desde Caja General -> Cta. Cte./ Cta. Ahorro)</h3>
        <form id="depositosForm">
          <div style="display: grid; grid-template-columns: 1fr; gap: 0px px;">
            <div class="form-group">
              <label for="fecha-deposito">Fecha</label>
              <input type="date" id="fecha-deposito" name="fecha" required style="width: 100%;">
            </div>
            <div class="form-group">
              <label for="monto-deposito">Monto</label>
              <input type="number" id="monto-deposito" name="monto" step="0.01" required style="width: 100%;">
            </div>
            <div class="form-group">
              <label for="cuenta-deposito">Cuenta Destino</label>
              <select id="cuenta-deposito" name="cuenta" required style="width: 100%;">
                <option value="cuenta_corriente_1">Cuenta Corriente 1</option>
                <!-- Agregar Cuenta de Ahorro -->
                <option value="cuenta_ahorro">Cuenta de Ahorro</option>
              </select>
            </div>
            <div class="form-group">
              <label for="detalle-deposito">Detalle</label>
              <input type="text" id="detalle-deposito" name="detalle" placeholder="Detalle del depósito" required style="width: 100%;">
            </div>
            <!-- Asegurarse de que el campo de N° Comprobante esté presente -->
            <div class="form-group">
              <label for="nro-dcto-deposito">N° Comprobante</label>
              <input type="text" id="nro-dcto-deposito" name="nro_dcto" required style="width: 100%;" readonly>
            </div>
          </div>

          <div style="margin-top: 20px; text-align: center;">
            <button type="submit" class="submit-btn">
              <i class="bi bi-save"></i> Registrar Depósito
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="button-group" style="margin-top: 30px; text-align: center; grid-column: span 2;">
      <button id="volverGirosDepositosBtn" class="action-button volver">
        <i class="bi bi-arrow-left"></i> Volver al Registro
      </button>
    </div>
  </div>

  <!-- Modal Cuentas Iniciales Mejorado -->
  <div id="cuentasInicialesModal" class="modal">
    <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
      <div class="modal-header">
        <h2><i class="bi bi-journal-plus"></i> Configuración de Cuentas Iniciales</h2>
        <div class="modal-header-buttons">
          <button type="button" class="action-btn edit-btn" id="editConfigBtn" style="display: none;">
            <i class="bi bi-pencil-square"></i> Editar
          </button>
          <button class="modal-close" id="closeCuentasInicialesModal">Cerrar</button>
        </div>
      </div>
      
      <form id="cuentasInicialesForm">
        <div class="warning-box">
          <i class="bi bi-exclamation-triangle"></i>
          <p id="warningText">¿Está seguro(a) de guardar los cambios? Esta operación solo se podrá realizar una sola vez.</p>
        </div>
        
        <div class="form-section">
          <div class="section-header-with-button">
            <h3>Configuración de Cuentas</h3>
          </div>
          
          <!-- Contenedor de cuentas dinámicas -->
          <div id="accountsContainer">
            <!-- Caja General -->
            <div class="account-item" data-account-type="caja_general">
              <div class="account-header">
                <h4><i class="bi bi-cash-stack"></i> Caja General</h4>
                <div class="account-actions">
                  <button type="button" class="btn-edit-account" id="editCajaGeneralBtn">
                    <i class="bi bi-pencil-square"></i> Editar
                  </button>
                  <button type="button" class="btn-save-account" id="saveCajaGeneralBtn">
                    <i class="bi bi-save"></i> Guardar
                  </button>
                  <button type="button" class="remove-account-btn" data-account="caja_general" style="display: none;">
                    <i class="bi bi-x-circle"></i>
                  </button>
                </div>
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label for="saldo-caja-general">Saldo Inicial</label>
                  <input type="number" id="saldo-caja-general" name="saldo_caja_general" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                  <label for="banco-caja-general">Banco</label>
                  <select id="banco-caja-general" name="banco_caja_general">
                    <option value="">Sin banco</option>
                    @foreach($bancos as $banco)
                      <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label for="numero-caja-general">Número de Cuenta</label>
                  <input type="text" id="numero-caja-general" name="numero_caja_general" placeholder="Ej: 12345678-9">
                </div>
              </div>
              <div class="account-status" id="cajaGeneralStatus" style="display: none;">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Caja General configurada correctamente</span>
              </div>
            </div>

            <!-- Cuenta Corriente 1 -->
            <div class="account-item" data-account-type="cuenta_corriente_1">
              <div class="account-header">
                <h4 id="cuentaCorrienteTitle"><i class="bi bi-credit-card-2-front"></i> Cuenta Corriente</h4>
                <div class="account-actions">
                  <button type="button" class="btn-edit-account" id="editCuentaCorrienteBtn">
                    <i class="bi bi-pencil-square"></i> Editar
                  </button>
                  <button type="button" class="btn-save-account" id="saveCuentaCorrienteBtn">
                    <i class="bi bi-save"></i> Guardar
                  </button>
                  <button type="button" class="add-account-btn" id="addCuentaCorrienteBtn">
                    <i class="bi bi-plus-circle"></i> Agregar Cuenta
                  </button>
                  <button type="button" class="remove-account-btn" data-account="cuenta_corriente_1">
                    <i class="bi bi-x-circle"></i>
                  </button>
                </div>
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label for="saldo-cta-corriente-1">Saldo Inicial</label>
                  <input type="number" id="saldo-cta-corriente-1" name="saldo_cta_corriente_1" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                  <label for="banco-cta-corriente-1">Banco</label>
                  <select id="banco-cta-corriente-1" name="banco_cta_corriente_1">
                    <option value="">Sin banco</option>
                    @foreach($bancos as $banco)
                      <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label for="numero-cta-corriente-1">Número de Cuenta</label>
                  <input type="text" id="numero-cta-corriente-1" name="numero_cta_corriente_1" placeholder="Ej: 12345678-9">
                </div>
              </div>
              <div class="account-status" id="cuentaCorrienteStatus" style="display: none;">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Cuenta Corriente configurada correctamente</span>
              </div>
            </div>

            <!-- Cuenta de Ahorro -->
            <div class="account-item" data-account-type="cuenta_ahorro">
              <div class="account-header">
                <h4 id="cuentaAhorroTitle"><i class="bi bi-piggy-bank"></i> Cuenta de Ahorro</h4>
                <div class="account-actions">
                  <button type="button" class="btn-edit-account" id="editCuentaAhorroBtn">
                    <i class="bi bi-pencil-square"></i> Editar
                  </button>
                  <button type="button" class="btn-save-account" id="saveCuentaAhorroBtn">
                    <i class="bi bi-save"></i> Guardar
                  </button>
                  <button type="button" class="add-account-btn" id="addCuentaAhorroBtn">
                    <i class="bi bi-plus-circle"></i> Agregar Cuenta
                  </button>
                  <button type="button" class="remove-account-btn" data-account="cuenta_ahorro">
                    <i class="bi bi-x-circle"></i>
                  </button>
                </div>
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label for="saldo-cuenta-ahorro">Saldo Inicial</label>
                  <input type="number" id="saldo-cuenta-ahorro" name="saldo_cuenta_ahorro" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                  <label for="banco-cuenta-ahorro">Banco</label>
                  <select id="banco-cuenta-ahorro" name="banco_cuenta_ahorro">
                    <option value="">Sin banco</option>
                    @foreach($bancos as $banco)
                      <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label for="numero-cuenta-ahorro">Número de Cuenta</label>
                  <input type="text" id="numero-cuenta-ahorro" name="numero_cuenta_ahorro" placeholder="Ej: 12345678-9">
                </div>
              </div>
              <div class="account-status" id="cuentaAhorroStatus" style="display: none;">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Cuenta de Ahorro configurada correctamente</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="button-group" style="margin-top: 20px; display: flex; gap: 10px; justify-content: space-between;">
          <div class="left-buttons">
            <button type="button" class="secondary-btn" id="previewBtn">
              <i class="bi bi-eye"></i> Vista Previa
            </button>
          </div>
          <div class="right-buttons">
            <button type="button" class="secondary-btn" id="resetFormBtn">
              <i class="bi bi-arrow-clockwise"></i> Resetear
            </button>
            <button type="submit" class="submit-btn">
              <i class="bi bi-save"></i> Guardar Cuentas Iniciales
            </button>
          </div>
        </div>
        
        <div class="form-section" style="margin-top: 20px;">
          <div class="form-group">
            <label for="responsable">Nombre Responsable <span class="required">*</span></label>
            <input type="text" id="responsable" name="responsable" required placeholder="Nombre del responsable de la configuración">
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Vista de Balance -->
  <div id="balanceSection" class="balance-section" style="display: none;">
    <div class="section-header">
      <h1>Balance Financiero</h1>
      <p>Resumen gráfico y analítico de la situación financiera</p>
    </div>

    <div class="summary-card">
      <div class="summary-grid">
        <div class="summary-item ingresos">
          <div class="label">Total Ingresos</div>
          <div class="value" id="balanceTotalIngresos">$0</div>
        </div>
        <div class="summary-item egresos">
          <div class="label">Total Egresos</div>
          <div class="value" id="balanceTotalEgresos">$0</div>
        </div>
        <div class="summary-item saldo">
          <div class="label">Saldo Final</div>
          <div class="value" id="balanceSaldoFinal">$0</div>
        </div>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="chart-card">
        <h3>Distribución de Ingresos</h3>
        <canvas id="ingresosChart"></canvas>
      </div>
      <div class="chart-card">
        <h3>Distribución de Egresos</h3>
        <canvas id="egresosChart"></canvas>
      </div>
      <div class="chart-card">
        <h3>Flujo Mensual</h3>
        <canvas id="flujoChart"></canvas>
      </div>
      <div class="chart-card">
        <h3>Conciliación Bancaria</h3>
        <canvas id="conciliacionChart"></canvas>
      </div>
    </div>

    <div class="balance-grid">
      <div class="balance-card">
        <h3><i class="bi bi-arrow-up-circle"></i> Ingresos por Categoría</h3>
        <ul id="balanceIngresos">
          <!-- Los ingresos por categoría se generarán aquí -->
        </ul>
      </div>

      <div class="balance-card">
        <h3><i class="bi bi-arrow-down-circle"></i> Egresos por Categoría</h3>
        <ul id="balanceEgresos">
          <!-- Los egresos por categoría se generarán aquí -->
        </ul>
      </div>

      <div class="balance-card">
        <h3><i class="bi bi-clock-history"></i> Últimos Movimientos</h3>
        <ul id="balanceMovimientos">
          <!-- Los últimos movimientos se generarán aquí -->
        </ul>
      </div>

      <div class="balance-card">
        <h3><i class="bi bi-graph-up"></i> Análisis Financiero</h3>
        <div style="padding: 15px;">
          <div style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
              <span>Flujo de efectivo:</span>
              <span id="flujoEfectivo" style="font-weight: 600;">$0</span>
            </div>
            <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
              <div id="flujoBar" style="height: 100%; width: 50%; background: var(--success-color);"></div>
            </div>
          </div>

          <div style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
              <span>Proporción ingresos/egresos:</span>
              <span id="proporcionIngEgr" style="font-weight: 600;">1:1</span>
            </div>
            <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
              <div id="proporcionBar" style="height: 100%; width: 50%; background: var(--primary-color);"></div>
            </div>
          </div>

          <div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
              <span>Porcentaje de ahorro:</span>
              <span id="porcentajeAhorro" style="font-weight: 600;">0%</span>
            </div>
            <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
              <div id="ahorroBar" style="height: 100%; width: 0%; background: var(--secondary-color);"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="button-group">
      <button id="volverBalanceBtn" class="action-button volver">
        <i class="bi bi-arrow-left"></i> Volver al Registro
      </button>
    </div>
  </div>

  <!-- Vista de Conciliación Bancaria -->
  <div id="conciliacionSection" class="conciliacion-section" style="display: none;">
    <div class="section-header">
      <h1>Conciliación Bancaria</h1>
      <p>Comparación de movimientos registrados con extractos bancarios</p>
    </div>

    <div class="conciliacion-grid">
      <div class="conciliacion-card">
        <h3><i class="bi bi-journal-check"></i> Movimientos Registrados</h3>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Descripción</th>
                <th>Cuenta</th>
                <th>Monto</th>
                <th>Conciliado</th>
              </tr>
            </thead>
            <tbody id="tablaConciliacion">
              <!-- Movimientos para conciliación -->
            </tbody>
          </table>
        </div>
      </div>

      <div class="conciliacion-card">
        <h3><i class="bi bi-bank"></i> Extracto Bancario</h3>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>Conciliado</th>
              </tr>
            </thead>
            <tbody id="tablaExtracto">
              <!-- Extracto bancario -->
            </tbody>
          </table>
        </div>

        <div class="conciliacion-card">
          <h3><i class="bi bi-check-circle"></i> Estado de Conciliación</h3>
          <div class="conciliacion-status">
            <div class="status-item">
              <span>Conciliados</span>
              <div class="status-bar">
                <div class="status-fill" style="width: 75%"></div>
              </div>
              <span>75%</span>
            </div>
            <div class="status-item">
              <span>Pendientes</span>
              <div class="status-bar">
                <div class="status-fill pending" style="width: 25%"></div>
              </div>
              <span>25%</span>
            </div>
          </div>
        </div>

        <div class="button-group" style="margin-top: 20px;">
          <button onclick="cargarExtracto()" class="action-button">
            <i class="bi bi-upload"></i> Cargar Extracto
          </button>
          <button onclick="conciliar()" class="action-button">
            <i class="bi bi-check-circle"></i> Conciliar
          </button>
        </div>
      </div>
    </div>

    <div class="button-group">
      <button id="volverConciliacionBtn" class="action-button volver">
        <i class="bi bi-arrow-left"></i> Volver al Registro
      </button>
    </div>
  </div>

  <!-- Vista de Informe por Rubro -->
  <div id="informeRubroSection" class="balance-section" style="display: none;">
    <div class="section-header">
      <h1>Informe por Rubro</h1>
      <p>Comparación de ingresos y egresos por categorías</p>
    </div>

    <div class="rubro-container">
      <div class="rubro-header">
        <div class="rubro-title">Operaciones vs. Administración</div>
        <div class="rubro-value">$0 / $0</div>
      </div>
      <div class="rubro-bar">
        <div class="rubro-bar-fill" style="width: 50%;"></div>
      </div>
      <div class="rubro-info">
        <div class="rubro-label">Gastos Operativos:</div>
        <div class="rubro-value" id="gastoOperativo">$0</div>
      </div>
      <div class="rubro-info">
        <div class="rubro-label">Gastos Administrativos:</div>
        <div class="rubro-value" id="gastoAdministrativo">$0</div>
      </div>
    </div>

    <div class="rubro-container">
      <div class="rubro-header">
        <div class="rubro-title">Mantención vs. Multas</div>
        <div class="rubro-value">$0 / $0</div>
      </div>
      <div class="rubro-bar">
        <div class="rubro-bar-fill" style="width: 50%;"></div>
      </div>
      <div class="rubro-info">
        <div class="rubro-label">Gasto en Mantención:</div>
        <div class="rubro-value" id="gastoMantencion">$0</div>
      </div>
      <div class="rubro-info">
        <div class="rubro-label">Recaudado por Multas:</div>
        <div class="rubro-value" id="recaudoMultas">$0</div>
      </div>
    </div>

    <div class="button-group">
      <button id="volverInformeRubroBtn" class="action-button volver">
        <i class="bi bi-arrow-left"></i> Volver al Registro
      </button>
      <button onclick="exportarPDF('informeRubro')" class="action-button pdf">
        <i class="bi bi-file-earmark-pdf"></i> Exportar Informe
      </button>
    </div>
  </div>

  <!-- Nueva Vista: Movimientos -->
  <div id="movimientosSection" class="movimientos-section" style="display: none;">
    <div class="section-header">
      <h1>Registro de Movimientos</h1>
      <p>Historial completo de todos los movimientos financieros</p>
    </div>

    <div class="table-container">
      <div class="table-header">
        <h2>Movimientos Financieros</h2>
        <div class="filters">
          <div class="filter-group">
            <label>Tipo</label>
            <select id="filtroTipo">
              <option value="">Todos</option>
              <option value="ingreso">Ingreso</option>
              <option value="egreso">Egreso</option>
              <option value="transferencia">Transferencia</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Categoría</label>
            <select id="filtroCategoria">
              <option value="">Todas</option>
              <!-- Se llenará dinámicamente -->
            </select>
          </div>
          <div class="filter-group">
            <label>Cuenta</label>
            <select id="filtroCuenta">
              <option value="">Todas</option>
              <option value="caja_general">Caja General</option>
              <option value="cuenta_corriente_1">Cuenta Corriente 1</option>
              <option value="cuenta_corriente_2">Cuenta Corriente 2</option>
              <!-- Agregar Cuenta de Ahorro -->
              <option value="cuenta_ahorro">Cuenta de Ahorro</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Desde</label>
            <input id="filtroFechaDesde" type="date">
          </div>
          <div class="filter-group">
            <label>Hasta</label>
            <input id="filtroFechaHasta" type="date">
          </div>
          <div class="filter-group">
            <label>&nbsp;</label>
            <button onclick="filtrarMovimientos()" class="action-button" style="padding: 10px 15px;">
              <i class="bi bi-funnel"></i> Filtrar
            </button>
          </div>
        </div>
      </div>

      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Tipo</th>
              <th>Categoría</th>
              <th>Descripción</th>
              <th>Cuenta</th>
              <th>Monto</th>
              <th>N° Comprobante</th>
              <th>Proveedor</th>
              <th>RUT Proveedor</th>
            </tr>
          </thead>
          <tbody id="tablaTodosMovimientos">
            <!-- Todos los movimientos se generarán aquí -->
          </tbody>
        </table>
      </div>
    </div>

    <div class="button-group">
      <button id="volverMovimientosBtn" class="action-button volver">
        <i class="bi bi-arrow-left"></i> Volver al Registro
      </button>
      <button onclick="exportarExcel()" class="action-button excel">
        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
      </button>
    </div>
  </div>
</div>

<!-- Contenedor oculto para generar el PDF del comprobante -->
<div id="comprobantePDF" style="display: none;"></div>

<script>
  // Variables globales
  let movimientos = JSON.parse(localStorage.getItem('movimientos')) || [];
  let saldosCuentas = {
    caja_general: parseFloat(localStorage.getItem('saldoCajaGeneral')) || 0,
    cuenta_corriente_1: parseFloat(localStorage.getItem('saldoCuentaCorriente1')) || 0,
    cuenta_corriente_2: parseFloat(localStorage.getItem('saldoCuentaCorriente2')) || 0,
    // Agregar Cuenta de Ahorro
    cuenta_ahorro: parseFloat(localStorage.getItem('saldoCuentaAhorro')) || 0
  };
  
  // Detalles de cuentas
  let accountDetails = JSON.parse(localStorage.getItem('accountDetails')) || {
    caja_general: { banco: '', numero: '' },
    cuenta_corriente_1: { banco: '', numero: '' },
    cuenta_corriente_2: { banco: '', numero: '' },
    cuenta_ahorro: { banco: '', numero: '' }
  };
  
  let comprobanteCounter = parseInt(localStorage.getItem('comprobanteCounter')) || 1;
  let chartIngresos, chartEgresos, chartFlujo, chartConciliacion;
  let movimientoEditando = null;
  let initialAccountsSet = localStorage.getItem('initialAccountsSet') === 'true';

  // Mapeo de categorías
  const categoriasIngresos = {
    venta_agua: "Venta de Agua (Total Consumo)",
    cuotas_incorporacion: "Cuotas de Incorporación",
    venta_medidores: "Venta de Medidores",
    trabajos_domicilio: "Trabajos en Domicilio",
    subsidios: "Subsidios",
    otros_aportes: "Otros Aportes",
    multas_inasistencia: "Multas Inasistencia",
    otras_multas: "Otras Multas"
  };

  const categoriasEgresos = {
    energia_electrica: "ENERGÍA ELECTRICA",
    sueldos: "SUELDOS/LEYES SOCIALES",
    otras_cuentas: "Otras Ctas. (Agua, Int. Cel.)",
    mantencion: "Mantención y reparaciones Instalaciones",
    insumos_oficina: "Insumos y Materiales (Oficina)",
    materiales_red: "Materiales e Insumos (Red)",
    viaticos: "Viáticos / Seguros / Movilización",
    trabajos_domicilio: "Gastos por Trabajos en domicilio",
    mejoramiento: "Mejoramiento / Inversiones"
  };

  // Mapeo de categorías de egresos a grupos para el libro de caja
  const gruposEgresos = {
    energia_electrica: "ENERGÍA ELÉCTRICA",
    sueldos: "SUELDOS/LEYES SOCIALES",
    otras_cuentas: "OTROS GASTOS DE OPERACIÓN",
    mantencion: "GASTOS MANTENCION",
    trabajos_domicilio: "GASTOS MANTENCION",
    insumos_oficina: "GASTOS ADMINISTRACION",
    materiales_red: "GASTOS MEJORAMIENTO",
    mejoramiento: "GASTOS MEJORAMIENTO",
    viaticos: "OTROS EGRESOS"
  };

  // Mapeo de cuentas
  const cuentas = {
    caja_general: "Caja General",
    cuenta_corriente_1: "Cuenta Corriente 1",
    cuenta_corriente_2: "Cuenta Corriente 2",
    // Agregar Cuenta de Ahorro
    cuenta_ahorro: "Cuenta de Ahorro"
  };

  // Función para formatear valores monetarios
  function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CL', {
      style: 'currency',
      currency: 'CLP'
    }).format(valor);
  }

  // Función para obtener la fecha actual en formato YYYY-MM-DD
  function obtenerFechaActual() {
    const hoy = new Date();
    const mes = (hoy.getMonth() + 1).toString().padStart(2, '0');
    const dia = hoy.getDate().toString().padStart(2, '0');
    return `${hoy.getFullYear()}-${mes}-${dia}`;
  }

  // Función para generar número de comprobante con prefijo según tipo
  function generarNumeroComprobante(tipo) {
    const numero = comprobanteCounter.toString().padStart(4, '0');
    comprobanteCounter++;
    localStorage.setItem('comprobanteCounter', comprobanteCounter);
    if (tipo === 'ingreso') return 'ING-' + numero;
    if (tipo === 'egreso') return 'EGR-' + numero;
    return numero;
  }

  // Función para mostrar notificación
  function mostrarNotificacion(mensaje, tipo = 'success') {
    const notification = document.getElementById('notification');
    if (!notification) {
      alert(mensaje); // fallback
      return;
    }
    notification.textContent = mensaje;
    notification.className = `notification ${tipo}`;
    notification.style.display = 'block';
    notification.style.opacity = '0.97';
    notification.style.zIndex = '99999';
    notification.style.pointerEvents = 'none';
    console.log('Notificación mostrada:', mensaje, tipo);
    setTimeout(() => {
      notification.style.display = 'none';
    }, 3000);
  }

  // Función para actualizar los saldos de las cuentas
  function actualizarSaldosCuentas() {
    // Guardar en localStorage
    localStorage.setItem('saldoCajaGeneral', saldosCuentas.caja_general);
    localStorage.setItem('saldoCuentaCorriente1', saldosCuentas.cuenta_corriente_1);
    localStorage.setItem('saldoCuentaCorriente2', saldosCuentas.cuenta_corriente_2);
    localStorage.setItem('saldoCuentaAhorro', saldosCuentas.cuenta_ahorro);
  }

  // Función para guardar detalles de cuentas
  function guardarDetallesCuentas() {
    localStorage.setItem('accountDetails', JSON.stringify(accountDetails));
  }

  // ===============================
  // FUNCIONES PARA MODAL MEJORADO DE CUENTAS INICIALES
  // ===============================
  
  let accountCounter = 1;
  
  // Función para agregar nueva cuenta dinámicamente
  function agregarNuevaCuenta() {
    accountCounter++;
    const accountsContainer = document.getElementById('accountsContainer');
    const accountTypes = ['caja_adicional', 'cuenta_corriente', 'cuenta_ahorro', 'cuenta_plazo_fijo', 'cuenta_vista'];
    const icons = ['bi-cash-coin', 'bi-credit-card', 'bi-piggy-bank', 'bi-calendar-check', 'bi-eye'];
    
    const randomType = accountTypes[Math.floor(Math.random() * accountTypes.length)];
    const randomIcon = icons[Math.floor(Math.random() * icons.length)];
    const accountId = 'cuenta_' + accountCounter + '_' + Date.now();
    
    const newAccountHtml = `
      <div class="account-item adding" data-account-type="${accountId}">
        <div class="account-header">
          <h4><i class="bi ${randomIcon}"></i> Nueva Cuenta ${accountCounter}</h4>
          <button type="button" class="remove-account-btn" data-account="${accountId}">
            <i class="bi bi-x-circle"></i>
          </button>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label for="saldo-${accountId}">Saldo Inicial</label>
            <input type="number" id="saldo-${accountId}" name="saldo_${accountId}" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label for="banco-${accountId}">Banco</label>
            <select id="banco-${accountId}" name="banco_${accountId}">
              <option value="">Sin banco</option>
              @foreach($bancos as $banco)
                <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="numero-${accountId}">Número de Cuenta</label>
            <input type="text" id="numero-${accountId}" name="numero_${accountId}" placeholder="Ej: 12345678-9">
          </div>
        </div>
      </div>
    `;
    
    accountsContainer.insertAdjacentHTML('beforeend', newAccountHtml);
    
    // Agregar event listener al nuevo botón de eliminar
    const newRemoveBtn = document.querySelector(`[data-account="${accountId}"]`);
    newRemoveBtn.addEventListener('click', function() {
      eliminarCuenta(accountId);
    });
    
    mostrarNotificacion('✅ Nueva cuenta agregada correctamente', 'success');
  }
  
  // Función para eliminar cuenta
  function eliminarCuenta(accountId) {
    const accountItem = document.querySelector(`[data-account-type="${accountId}"]`);
    if (accountItem) {
      if (confirm('¿Está seguro de eliminar esta cuenta?')) {
        accountItem.classList.add('removing');
        setTimeout(() => {
          accountItem.remove();
          mostrarNotificacion('✅ Cuenta eliminada correctamente', 'success');
        }, 300);
      }
    }
  }
  
  // Función para mostrar vista previa
  function mostrarVistaPrevia() {
    const accountItems = document.querySelectorAll('.account-item');
    let preview = 'VISTA PREVIA DE CONFIGURACIÓN\\n\\n';
    let totalSaldo = 0;
    
    accountItems.forEach((item, index) => {
      const accountType = item.dataset.accountType;
      const saldoInput = item.querySelector('input[type="number"]');
      const bancoSelect = item.querySelector('select');
      const numeroInput = item.querySelector('input[type="text"]');
      
      const saldo = parseFloat(saldoInput.value || 0);
      const bancoText = bancoSelect.options[bancoSelect.selectedIndex].text;
      const numero = numeroInput.value || 'Sin número';
      
      totalSaldo += saldo;
      
      preview += `${index + 1}. ${item.querySelector('h4').textContent}\\n`;
      preview += `   • Saldo: $${saldo.toLocaleString()}\\n`;
      preview += `   • Banco: ${bancoText}\\n`;
      preview += `   • Número: ${numero}\\n\\n`;
    });
    
    const responsable = document.getElementById('responsable').value || 'Sin especificar';
    preview += `Responsable: ${responsable}\\n`;
    preview += `TOTAL GENERAL: $${totalSaldo.toLocaleString()}`;
    
    alert(preview);
  }
  
  // Función para resetear formulario
  function resetearFormulario() {
    if (confirm('¿Está seguro de resetear todo el formulario?')) {
      document.getElementById('cuentasInicialesForm').reset();
      
      // Remover cuentas dinámicas (mantener solo las básicas)
      const accountItems = document.querySelectorAll('.account-item');
      accountItems.forEach(item => {
        const accountType = item.dataset.accountType;
        if (!['caja_general', 'cuenta_corriente_1', 'cuenta_corriente_2', 'cuenta_ahorro'].includes(accountType)) {
          item.remove();
        }
      });
      
      accountCounter = 1;
      mostrarNotificacion('✅ Formulario reseteado correctamente', 'success');
    }
  }

  // ===============================
  // FUNCIONES ESPECÍFICAS PARA CAJA GENERAL
  // ===============================
  
  // Función para guardar configuración de Caja General
  function guardarCajaGeneral() {
    console.log('🔍 DEBUG: Función guardarCajaGeneral iniciada');
    
    const saldo = document.getElementById('saldo-caja-general').value;
    const banco = document.getElementById('banco-caja-general').value;
    const numero = document.getElementById('numero-caja-general').value;
    const responsable = document.getElementById('responsable').value;
    
    console.log('🔍 DEBUG: Valores obtenidos - Saldo:', saldo, 'Banco:', banco, 'Numero:', numero, 'Responsable:', responsable);
    
    // Validar campos requeridos
    if (!saldo || parseFloat(saldo) < 0) {
      mostrarNotificacion('⚠️ Por favor ingrese un saldo válido para Caja General', 'warning');
      return;
    }
    
    if (!responsable) {
      mostrarNotificacion('⚠️ Por favor ingrese el nombre del responsable', 'warning');
      return;
    }
    
    // Preparar datos para envío
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('orgId', {{ $orgId }});
    formData.append('saldo_caja_general', saldo);
    formData.append('banco_caja_general', banco);
    formData.append('numero_caja_general', numero);
    formData.append('responsable', responsable);
    formData.append('tipo_operacion', 'caja_general');
    
    // Mostrar indicador de carga
    mostrarNotificacion('⏳ Guardando configuración de Caja General...', 'info');
    
    // Deshabilitar botón mientras se procesa
    const saveBtn = document.getElementById('saveCajaGeneralBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    
    // Enviar al servidor
    fetch('/configuracion-cuentas-iniciales', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Marcar como guardado exitosamente sin bloqueo
        const cajaGeneralItem = document.querySelector('[data-account-type="caja_general"]');
        cajaGeneralItem.classList.add('saved');
        cajaGeneralItem.classList.remove('editing', 'locked');
        
        // Mostrar estado de guardado
        const statusDiv = document.getElementById('cajaGeneralStatus');
        statusDiv.style.display = 'flex';
        statusDiv.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i><span>Caja General guardada exitosamente</span>';
        
        // Mantener botones activos (sin restricciones)
        saveBtn.style.display = 'inline-flex';
        const editBtn = document.getElementById('editCajaGeneralBtn');
        editBtn.style.display = 'inline-flex';
        
        // Campos permanecen habilitados (sin bloqueo)
        const campos = ['saldo-caja-general', 'banco-caja-general', 'numero-caja-general'];
        campos.forEach(campoId => {
          const campo = document.getElementById(campoId);
          campo.disabled = false;
          campo.style.backgroundColor = '';
          campo.style.color = '';
          campo.style.cursor = '';
        });
        
        // Actualizar saldos locales
        saldosCuentas.caja_general = parseFloat(saldo);
        actualizarSaldosCuentas();
        
        mostrarNotificacion('✅ Caja General guardada y bloqueada correctamente!', 'success');
        
      } else {
        mostrarNotificacion('❌ Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarNotificacion('❌ Error de conexión al guardar Caja General', 'error');
    })
    .finally(() => {
      // Restaurar botón
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="bi bi-save"></i> Guardar';
    });
  }
  
  // Función para editar configuración de Caja General (sin restricciones)
  function editarCajaGeneral() {
    console.log('🔍 DEBUG: Función editarCajaGeneral iniciada');
    // Edición directa sin confirmación
    
    // Habilitar campos para edición
    const campos = ['saldo-caja-general', 'banco-caja-general', 'numero-caja-general'];
    campos.forEach(campoId => {
      const campo = document.getElementById(campoId);
      campo.disabled = false;
      campo.style.backgroundColor = '';
      campo.style.color = '';
      campo.style.cursor = '';
    });
    
    // Cambiar apariencia visual
    const cajaGeneralItem = document.querySelector('[data-account-type="caja_general"]');
    cajaGeneralItem.classList.add('editing');
    cajaGeneralItem.classList.remove('saved', 'locked');
    
    // Mantener ambos botones visibles
    const editBtn = document.getElementById('editCajaGeneralBtn');
    const saveBtn = document.getElementById('saveCajaGeneralBtn');
    editBtn.style.display = 'inline-flex';
    saveBtn.style.display = 'inline-flex';
    
    // Ocultar estado de guardado
    const statusDiv = document.getElementById('cajaGeneralStatus');
    statusDiv.style.display = 'none';
    
    mostrarNotificacion('✏️ Modo edición activado para Caja General', 'success');
  }

  // ===============================
  // FUNCIONES ESPECÍFICAS PARA CUENTA CORRIENTE
  // ===============================
  
  // Función para guardar configuración de Cuenta Corriente
  function guardarCuentaCorriente() {
    const saldo = document.getElementById('saldo-cta-corriente-1').value;
    const banco = document.getElementById('banco-cta-corriente-1').value;
    const numero = document.getElementById('numero-cta-corriente-1').value;
    const responsable = document.getElementById('responsable').value;
    
    // Validar campos requeridos
    if (!saldo || parseFloat(saldo) < 0) {
      mostrarNotificacion('⚠️ Por favor ingrese un saldo válido para Cuenta Corriente', 'warning');
      return;
    }
    
    if (!numero || numero.trim() === '') {
      mostrarNotificacion('⚠️ Por favor ingrese el número de cuenta', 'warning');
      return;
    }
    
    if (!responsable) {
      mostrarNotificacion('⚠️ Por favor ingrese el nombre del responsable', 'warning');
      return;
    }
    
    // Preparar datos para envío
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('orgId', {{ $orgId }});
    formData.append('saldo_cuenta_corriente_1', saldo);
    formData.append('banco_cuenta_corriente_1', banco);
    formData.append('numero_cuenta_corriente_1', numero);
    formData.append('responsable', responsable);
    formData.append('tipo_operacion', 'cuenta_corriente_1');
    
    // Mostrar indicador de carga
    mostrarNotificacion('⏳ Guardando configuración de Cuenta Corriente...', 'info');
    
    // Deshabilitar botón mientras se procesa
    const saveBtn = document.getElementById('saveCuentaCorrienteBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    
    // Enviar al servidor
    fetch('/configuracion-cuentas-iniciales', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Marcar como guardado exitosamente
        const cuentaCorrienteItem = document.querySelector('[data-account-type="cuenta_corriente_1"]');
        cuentaCorrienteItem.classList.add('saved');
        cuentaCorrienteItem.classList.remove('editing');
        
        // Actualizar título con el número de cuenta
        const titleElement = document.getElementById('cuentaCorrienteTitle');
        titleElement.innerHTML = `<i class="bi bi-credit-card-2-front"></i> Cuenta Corriente N° ${numero}`;
        
        // Mostrar estado de guardado
        const statusDiv = document.getElementById('cuentaCorrienteStatus');
        statusDiv.style.display = 'flex';
        statusDiv.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i><span>Cuenta Corriente N° ${numero} guardada correctamente</span>`;
        
        // Mantener botones activos (sin restricciones)
        saveBtn.style.display = 'inline-flex';
        const editBtn = document.getElementById('editCuentaCorrienteBtn');
        editBtn.style.display = 'inline-flex';
        
        // Mantener campos habilitados (sin bloqueo)
        document.getElementById('saldo-cta-corriente-1').disabled = false;
        document.getElementById('banco-cta-corriente-1').disabled = false;
        document.getElementById('numero-cta-corriente-1').disabled = false;
        
        // Actualizar saldos locales
        saldosCuentas.cuenta_corriente_1 = parseFloat(saldo);
        actualizarSaldosCuentas();
        
        mostrarNotificacion(`✅ Cuenta Corriente N° ${numero} guardada correctamente!`, 'success');
        
      } else {
        mostrarNotificacion('❌ Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarNotificacion('❌ Error de conexión al guardar Cuenta Corriente', 'error');
    })
    .finally(() => {
      // Restaurar botón
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="bi bi-save"></i> Guardar';
    });
  }
  
  // Función para editar configuración de Cuenta Corriente (sin restricciones)
  function editarCuentaCorriente() {
    // Edición directa sin confirmación
    
    // Habilitar campos para edición
    document.getElementById('saldo-cta-corriente-1').disabled = false;
    document.getElementById('banco-cta-corriente-1').disabled = false;
    document.getElementById('numero-cta-corriente-1').disabled = false;
    
    // Cambiar apariencia visual
    const cuentaCorrienteItem = document.querySelector('[data-account-type="cuenta_corriente_1"]');
    cuentaCorrienteItem.classList.add('editing');
    cuentaCorrienteItem.classList.remove('saved');
    
    // Mantener ambos botones visibles
    const editBtn = document.getElementById('editCuentaCorrienteBtn');
    const saveBtn = document.getElementById('saveCuentaCorrienteBtn');
    editBtn.style.display = 'inline-flex';
    saveBtn.style.display = 'inline-flex';
    
    // Ocultar estado de guardado
    const statusDiv = document.getElementById('cuentaCorrienteStatus');
    statusDiv.style.display = 'none';
    
    // Restaurar título original
    const titleElement = document.getElementById('cuentaCorrienteTitle');
    titleElement.innerHTML = '<i class="bi bi-credit-card-2-front"></i> Cuenta Corriente';
    
    mostrarNotificacion('✏️ Modo edición activado para Cuenta Corriente', 'success');
  }

  // ===============================
  // FUNCIONES ESPECÍFICAS PARA CUENTA DE AHORRO
  // ===============================
  
  // Función para guardar configuración de Cuenta de Ahorro
  function guardarCuentaAhorro() {
    const saldo = document.getElementById('saldo-cuenta-ahorro').value;
    const banco = document.getElementById('banco-cuenta-ahorro').value;
    const numero = document.getElementById('numero-cuenta-ahorro').value;
    const responsable = document.getElementById('responsable').value;
    
    // Validar campos requeridos
    if (!saldo || parseFloat(saldo) < 0) {
      mostrarNotificacion('⚠️ Por favor ingrese un saldo válido para Cuenta de Ahorro', 'warning');
      return;
    }
    
    if (!numero || numero.trim() === '') {
      mostrarNotificacion('⚠️ Por favor ingrese el número de cuenta de ahorro', 'warning');
      return;
    }
    
    if (!responsable) {
      mostrarNotificacion('⚠️ Por favor ingrese el nombre del responsable', 'warning');
      return;
    }
    
    // Preparar datos para envío
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('orgId', {{ $orgId }});
    formData.append('saldo_cuenta_ahorro', saldo);
    formData.append('banco_cuenta_ahorro', banco);
    formData.append('numero_cuenta_ahorro', numero);
    formData.append('responsable', responsable);
    formData.append('tipo_operacion', 'cuenta_ahorro');
    
    // Mostrar indicador de carga
    mostrarNotificacion('⏳ Guardando configuración de Cuenta de Ahorro...', 'info');
    
    // Deshabilitar botón mientras se procesa
    const saveBtn = document.getElementById('saveCuentaAhorroBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    
    // Enviar al servidor
    fetch('/configuracion-cuentas-iniciales', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Mantener campos habilitados (sin bloqueo)
        document.getElementById('saldo-cuenta-ahorro').disabled = false;
        document.getElementById('banco-cuenta-ahorro').disabled = false;
        document.getElementById('numero-cuenta-ahorro').disabled = false;
        
        // Actualizar título con el número de cuenta
        const titleElement = document.getElementById('cuentaAhorroTitle');
        titleElement.innerHTML = `<i class="bi bi-piggy-bank"></i> Cuenta de Ahorro N° ${numero}`;
        
        // Mostrar estado de guardado
        const statusDiv = document.getElementById('cuentaAhorroStatus');
        statusDiv.style.display = 'flex';
        statusDiv.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i><span>Cuenta de Ahorro N° ${numero} guardada correctamente</span>`;
        
        // Mantener ambos botones activos
        saveBtn.style.display = 'inline-flex';
        const editBtn = document.getElementById('editCuentaAhorroBtn');
        editBtn.style.display = 'inline-flex';
        
        // Actualizar saldos locales
        saldosCuentas.cuenta_ahorro = parseFloat(saldo);
        actualizarSaldosCuentas();
        
        mostrarNotificacion(`✅ Cuenta de Ahorro N° ${numero} guardada correctamente!`, 'success');
        
      } else {
        mostrarNotificacion('❌ Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarNotificacion('❌ Error de conexión al guardar Cuenta de Ahorro', 'error');
    })
    .finally(() => {
      // Restaurar botón
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="bi bi-save"></i> Guardar';
    });
  }
  
  // Función para editar configuración de Cuenta de Ahorro (sin restricciones)
  function editarCuentaAhorro() {
    // Edición directa sin confirmación
    
    // Habilitar campos para edición
    document.getElementById('saldo-cuenta-ahorro').disabled = false;
    document.getElementById('banco-cuenta-ahorro').disabled = false;
    document.getElementById('numero-cuenta-ahorro').disabled = false;
    
    // Mantener ambos botones activos
    const editBtn = document.getElementById('editCuentaAhorroBtn');
    const saveBtn = document.getElementById('saveCuentaAhorroBtn');
    editBtn.style.display = 'inline-flex';
    saveBtn.style.display = 'inline-flex';
    
    // Ocultar estado de guardado
    const statusDiv = document.getElementById('cuentaAhorroStatus');
    statusDiv.style.display = 'none';
    
    mostrarNotificacion('✏️ Modo edición activado para Cuenta de Ahorro', 'success');
  }

  // ===============================
  // FUNCIONES SIMPLES PARA GUARDAR Y EDITAR CADA SECCIÓN
  // ===============================

  // Función para guardar configuración de Caja General
  function guardarCajaGeneral() {
    const saldo = document.getElementById('saldo-caja-general').value;
    const banco = document.getElementById('banco-caja-general').value;
    const numero = document.getElementById('numero-caja-general').value;
    
    if (!saldo || parseFloat(saldo) < 0) {
      mostrarNotificacion('❌ Debe ingresar un saldo válido para Caja General', 'error');
      return;
    }
    
    // Mantener campos habilitados (sin bloqueo)
    document.getElementById('saldo-caja-general').disabled = false;
    document.getElementById('banco-caja-general').disabled = false;
    document.getElementById('numero-caja-general').disabled = false;
    
    // Mantener ambos botones activos
    const editBtn = document.getElementById('editCajaGeneralBtn');
    const saveBtn = document.getElementById('saveCajaGeneralBtn');
    editBtn.style.display = 'inline-flex';
    saveBtn.style.display = 'inline-flex';
    
    mostrarNotificacion('✅ Caja General guardada correctamente', 'success');
  }

  // Función para editar configuración de Caja General (versión 2 - sin restricciones)
  function editarCajaGeneral() {
    // Edición directa sin confirmación
    
    // Habilitar campos
    document.getElementById('saldo-caja-general').disabled = false;
    document.getElementById('banco-caja-general').disabled = false;
    document.getElementById('numero-caja-general').disabled = false;
    
    // Mantener ambos botones activos
    const editBtn = document.getElementById('editCajaGeneralBtn');
    const saveBtn = document.getElementById('saveCajaGeneralBtn');
    editBtn.style.display = 'inline-flex';
    saveBtn.style.display = 'inline-flex';
    
    mostrarNotificacion('✏️ Modo edición activado para Caja General', 'success');
  }

  // Función para guardar configuración de Cuenta Corriente
  function guardarCuentaCorriente() {
    const saldo = document.getElementById('saldo-cta-corriente-1').value;
    const banco = document.getElementById('banco-cta-corriente-1').value;
    const numero = document.getElementById('numero-cta-corriente-1').value;
    
    if (!saldo || parseFloat(saldo) < 0) {
      mostrarNotificacion('❌ Debe ingresar un saldo válido para Cuenta Corriente', 'error');
      return;
    }
    
    // Actualizar título dinámico si hay número de cuenta
    const titulo = document.getElementById('cuentaCorrienteTitle');
    if (numero && numero.trim() !== '') {
      titulo.innerHTML = '<i class="bi bi-credit-card-2-front"></i> Cuenta Corriente N° ' + numero;
    }
    
    // Mantener campos habilitados (sin bloqueo)
    document.getElementById('saldo-cta-corriente-1').disabled = false;
    document.getElementById('banco-cta-corriente-1').disabled = false;
    document.getElementById('numero-cta-corriente-1').disabled = false;
    
    // Mantener ambos botones activos
    const editBtn = document.getElementById('editCuentaCorrienteBtn');
    const saveBtn = document.getElementById('saveCuentaCorrienteBtn');
    editBtn.style.display = 'inline-flex';
    saveBtn.style.display = 'inline-flex';
    
    mostrarNotificacion('✅ Cuenta Corriente guardada correctamente', 'success');
  }

  // Función para editar configuración de Cuenta Corriente (versión 2 - sin restricciones)
  function editarCuentaCorriente() {
    // Edición directa sin confirmación
    
    // Habilitar campos
    document.getElementById('saldo-cta-corriente-1').disabled = false;
    document.getElementById('banco-cta-corriente-1').disabled = false;
    document.getElementById('numero-cta-corriente-1').disabled = false;
    
    // Mantener ambos botones activos
    const editBtn = document.getElementById('editCuentaCorrienteBtn');
    const saveBtn = document.getElementById('saveCuentaCorrienteBtn');
    editBtn.style.display = 'inline-flex';
    saveBtn.style.display = 'inline-flex';
    
    mostrarNotificacion('✏️ Modo edición activado para Cuenta Corriente', 'success');
  }
  
  // Función para editar configuración de Cuenta de Ahorro
  function editarCuentaAhorro() {
    // Edición directa sin confirmación
    
    // Habilitar campos para edición
    document.getElementById('saldo-cuenta-ahorro').disabled = false;
    document.getElementById('banco-cuenta-ahorro').disabled = false;
    document.getElementById('numero-cuenta-ahorro').disabled = false;
    
    // Cambiar apariencia visual
    const cuentaAhorroItem = document.querySelector('[data-account-type="cuenta_ahorro"]');
    cuentaAhorroItem.classList.add('editing');
    cuentaAhorroItem.classList.remove('saved');
    
    // Cambiar botones
    const editBtn = document.getElementById('editCuentaAhorroBtn');
    const saveBtn = document.getElementById('saveCuentaAhorroBtn');
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-flex';
    
    // Ocultar estado de guardado
    const statusDiv = document.getElementById('cuentaAhorroStatus');
    statusDiv.style.display = 'none';
    
    // Restaurar título original
    const titleElement = document.getElementById('cuentaAhorroTitle');
    titleElement.innerHTML = '<i class="bi bi-piggy-bank"></i> Cuenta de Ahorro';
    
    mostrarNotificacion('✏️ Modo edición activado para Cuenta de Ahorro', 'warning');
  }

  // Función para alternar modo edición
  function toggleEditMode() {
    const inputs = document.querySelectorAll('#cuentasInicialesForm input, #cuentasInicialesForm select');
    const isDisabled = inputs[0].disabled;
    
    inputs.forEach(input => {
      input.disabled = !isDisabled;
    });
    
    const editBtn = document.getElementById('editConfigBtn');
    const warningText = document.getElementById('warningText');
    
    if (isDisabled) {
      editBtn.innerHTML = '<i class="bi bi-lock"></i> Bloquear';
      warningText.textContent = 'MODO EDICIÓN ACTIVADO: Puede modificar la configuración existente.';
      mostrarNotificacion('⚠️ Modo edición activado', 'warning');
    } else {
      editBtn.innerHTML = '<i class="bi bi-pencil-square"></i> Editar';
      warningText.textContent = '¿Está seguro(a) de guardar los cambios? Esta operación solo se podrá realizar una sola vez.';
      mostrarNotificacion('🔒 Modo edición desactivado', 'info');
    }
  }

  // Función para configurar cuentas iniciales (MEJORADA)
  function configurarCuentasIniciales(e) {
    e.preventDefault();
    
    if (!confirm('¿Está seguro(a) de guardar los cambios? Esta operación solo se podrá realizar una sola vez.')) {
      return;
    }
    
    // Recopilar datos de todas las cuentas dinámicamente
    const accountItems = document.querySelectorAll('.account-item');
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('orgId', {{ $orgId }});
    formData.append('responsable', document.getElementById('responsable').value);
    
    let totalCuentas = 0;
    let totalSaldo = 0;
    
    accountItems.forEach(item => {
      const accountType = item.dataset.accountType;
      const saldoInput = item.querySelector('input[type="number"]');
      const bancoSelect = item.querySelector('select');
      const numeroInput = item.querySelector('input[type="text"]');
      
      if (saldoInput && saldoInput.value) {
        const saldo = parseFloat(saldoInput.value);
        totalSaldo += saldo;
        totalCuentas++;
        
        formData.append(`saldo_${accountType}`, saldo);
        formData.append(`banco_${accountType}`, bancoSelect.value);
        formData.append(`numero_${accountType}`, numeroInput.value);
      }
    });
    
    // Mostrar indicador de carga
    mostrarNotificacion(`⏳ Guardando ${totalCuentas} cuentas con saldo total de $${totalSaldo.toLocaleString()}...`, 'info');

    // Enviar al servidor
    fetch('/configuracion-cuentas-iniciales', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Actualizar localStorage para compatibilidad
        accountItems.forEach(item => {
          const accountType = item.dataset.accountType;
          const saldoInput = item.querySelector('input[type="number"]');
          const bancoSelect = item.querySelector('select');
          const numeroInput = item.querySelector('input[type="text"]');
          
          if (saldoInput && saldoInput.value) {
            const saldo = parseFloat(saldoInput.value);
            saldosCuentas[accountType] = saldo;
            
            accountDetails[accountType] = {
              banco: bancoSelect.value,
              numero: numeroInput.value
            };
          }
        });
        
        // Guardar en localStorage
        localStorage.setItem('saldosCuentas', JSON.stringify(saldosCuentas));
        guardarDetallesCuentas();
        localStorage.setItem('initialAccountsSet', 'true');
        initialAccountsSet = true;
        
        // Bloquear formulario y mostrar botón editar
        const inputs = document.querySelectorAll('#cuentasInicialesForm input, #cuentasInicialesForm select');
        inputs.forEach(input => {
          input.disabled = true;
        });
        
        document.getElementById('editConfigBtn').style.display = 'block';
        document.getElementById('addAccountBtn').style.display = 'none';
        
        // Actualizar UI
        actualizarSaldosCuentas();
        mostrarNotificacion(`✅ ${totalCuentas} cuentas guardadas exitosamente en la base de datos! Total: $${totalSaldo.toLocaleString()}`, 'success');
        
        // Cerrar modal después de 3 segundos
        setTimeout(() => {
          document.getElementById('cuentasInicialesModal').classList.remove('show');
        }, 3000);
        
      } else {
        mostrarNotificacion('❌ Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarNotificacion('❌ Error de conexión al guardar los datos', 'error');
    });
  }

  // Función original mantenida para compatibilidad
  function configurarCuentasInicialesOriginal(e) {
    e.preventDefault();
    
    if (!confirm('¿Está seguro(a) de guardar los cambios? Esta operación solo se podrá realizar una sola vez.')) {
      return;
    }
    
    // Obtener valores del formulario
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('orgId', {{ $orgId }});
    
    // Saldos
    formData.append('saldo_caja_general', document.getElementById('saldo-caja-general').value);
    formData.append('saldo_cta_corriente_1', document.getElementById('saldo-cta-corriente-1').value);
    formData.append('saldo_cta_corriente_2', document.getElementById('saldo-cta-corriente-2').value);
    formData.append('saldo_cuenta_ahorro', document.getElementById('saldo-cuenta-ahorro').value);
    
    // Bancos (IDs de la base de datos)
    formData.append('banco_caja_general', document.getElementById('banco-caja-general').value);
    formData.append('banco_cta_corriente_1', document.getElementById('banco-cta-corriente-1').value);
    formData.append('banco_cta_corriente_2', document.getElementById('banco-cta-corriente-2').value);
    formData.append('banco_cuenta_ahorro', document.getElementById('banco-cuenta-ahorro').value);
    
    // Números de cuenta
    formData.append('numero_caja_general', document.getElementById('numero-caja-general').value);
    formData.append('numero_cta_corriente_1', document.getElementById('numero-cta-corriente-1').value);
    formData.append('numero_cta_corriente_2', document.getElementById('numero-cta-corriente-2').value);
    formData.append('numero_cuenta_ahorro', document.getElementById('numero-cuenta-ahorro').value);
    
    // Responsable
    formData.append('responsable', document.getElementById('responsable').value);
    
    // Enviar al servidor
    fetch('/configuracion-cuentas-iniciales', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Guardar en localStorage para compatibilidad con código existente
        saldosCuentas.caja_general = parseFloat(document.getElementById('saldo-caja-general').value) || 0;
        saldosCuentas.cuenta_corriente_1 = parseFloat(document.getElementById('saldo-cta-corriente-1').value) || 0;
        saldosCuentas.cuenta_corriente_2 = parseFloat(document.getElementById('saldo-cta-corriente-2').value) || 0;
        saldosCuentas.cuenta_ahorro = parseFloat(document.getElementById('saldo-cuenta-ahorro').value) || 0;
        
        // Guardar detalles de cuentas
        accountDetails.caja_general = {
          banco: document.getElementById('banco-caja-general').value,
          numero: document.getElementById('numero-caja-general').value
        };
        
        accountDetails.cuenta_corriente_1 = {
          banco: document.getElementById('banco-cta-corriente-1').value,
          numero: document.getElementById('numero-cta-corriente-1').value
        };
        
        accountDetails.cuenta_corriente_2 = {
          banco: document.getElementById('banco-cta-corriente-2').value,
          numero: document.getElementById('numero-cta-corriente-2').value
        };
        
        accountDetails.cuenta_ahorro = {
          banco: document.getElementById('banco-cuenta-ahorro').value,
          numero: document.getElementById('numero-cuenta-ahorro').value
        };
        
        // Guardar en localStorage
        localStorage.setItem('saldosCuentas', JSON.stringify(saldosCuentas));
        guardarDetallesCuentas();
        localStorage.setItem('initialAccountsSet', 'true');
        initialAccountsSet = true;
        
        // Bloquear formulario
        const inputs = document.querySelectorAll('#cuentasInicialesForm input, #cuentasInicialesForm select');
        inputs.forEach(input => {
          input.disabled = true;
        });
        
        // Actualizar UI
        actualizarSaldosCuentas();
        mostrarNotificacion('✅ Cuentas iniciales y bancos guardados en la base de datos correctamente');
        
        // Cerrar modal después de 2 segundos
        setTimeout(() => {
          document.getElementById('cuentasInicialesModal').classList.remove('show');
        }, 2000);
        
      } else {
        mostrarNotificacion('❌ Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarNotificacion('❌ Error de conexión al guardar los datos', 'error');
    });
  }

  // Función para registrar un ingreso
  function registrarIngreso(e) {
    e.preventDefault();
    
    const fecha = document.getElementById('fecha-ingresos').value;
    const nro_dcto = document.getElementById('nro-dcto-ingresos').value;
    const categoria = document.getElementById('categoria-ingresos').value;
    const cuenta_destino = document.getElementById('cuenta-destino').value;
    const descripcion = document.getElementById('descripcion-ingresos').value;
    const monto = parseFloat(document.getElementById('monto-ingresos').value);
    
    // Validar monto positivo
    if (monto <= 0) {
      mostrarNotificacion('El monto debe ser mayor a cero', 'error');
      return;
    }

    // Preparar datos para envío
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('fecha', fecha);
    formData.append('nro_dcto', nro_dcto);
    formData.append('categoria', categoria); // Cambiado de categoria_id a categoria
    formData.append('cuenta_destino', cuenta_destino);
    formData.append('descripcion', descripcion);
    formData.append('monto', monto);

    // Mostrar indicador de carga
    mostrarNotificacion('⏳ Guardando ingreso...', 'info');

    // Enviar al servidor
    fetch(`/{{ $orgId }}/ingresos`, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Actualizar localStorage para compatibilidad
        const movimiento = {
          id: Date.now(),
          fecha,
          tipo: 'ingreso',
          nro_dcto,
          categoria: categoria,
          cuenta_destino,
          descripcion,
          monto,
          timestamp: new Date().getTime()
        };
        
        movimientos.push(movimiento);
        localStorage.setItem('movimientos', JSON.stringify(movimientos));
        
        // Actualizar saldo local
        saldosCuentas[cuenta_destino] += monto;
        actualizarSaldosCuentas();
        
        // Actualizar UI
        actualizarTotales();
        actualizarTablaMovimientos();
        
        // Actualizar libro de caja tabular si está disponible
        if (window.actualizarTablaLibroCaja) window.actualizarTablaLibroCaja();
        
        // Mostrar notificación y cerrar modal
        mostrarNotificacion(`✅ Ingreso registrado correctamente en la base de datos! Nuevo saldo: $${data.nuevo_saldo.toLocaleString()}`, 'success');
        document.getElementById('ingresosModal').classList.remove('show');
        document.getElementById('ingresosForm').reset();
        
      } else {
        mostrarNotificacion('❌ Error: ' + (data.message || 'Error desconocido'), 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarNotificacion('❌ Error de conexión al guardar el ingreso', 'error');
    });
  }

  // Función para registrar un egreso
  function registrarEgreso(e) {
    e.preventDefault();
    
    const fecha = document.getElementById('fecha-egresos').value;
    const nro_dcto = document.getElementById('nro-dcto-egresos').value;
    const categoria = document.getElementById('categoria-egresos').value;
    const cuenta_origen = document.getElementById('cuenta-origen').value;
    const razon_social = document.getElementById('razon_social').value;
    const rut_proveedor = document.getElementById('rut').value;
    const descripcion = document.getElementById('descripcion-egresos').value;
    const monto = parseFloat(document.getElementById('monto-egresos').value);
    
    // Validar monto positivo
    if (monto <= 0) {
      mostrarNotificacion('El monto debe ser mayor a cero', 'error');
      return;
    }

    // Preparar datos para envío
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('fecha', fecha);
    formData.append('nro_dcto', nro_dcto);
    formData.append('categoria', categoria);
    formData.append('cuenta_origen', cuenta_origen);
    formData.append('razon_social', razon_social);
    formData.append('rut_proveedor', rut_proveedor);
    formData.append('descripcion', descripcion);
    formData.append('monto', monto);

    // Mostrar indicador de carga
    mostrarNotificacion('⏳ Guardando egreso...', 'info');

    // Enviar al servidor
    fetch(`/{{ $orgId }}/egresos`, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Actualizar localStorage para compatibilidad
        const movimiento = {
          id: Date.now(),
          fecha,
          tipo: 'egreso',
          nro_dcto,
          categoria: categoria,
          cuenta_origen,
          razon_social,
          rut_proveedor,
          descripcion,
          monto,
          timestamp: new Date().getTime()
        };
        
        movimientos.push(movimiento);
        localStorage.setItem('movimientos', JSON.stringify(movimientos));
        
        // Actualizar saldo local
        saldosCuentas[cuenta_origen] -= monto;
        actualizarSaldosCuentas();
        
        // Actualizar UI
        actualizarTotales();
        actualizarTablaMovimientos();
        
        // Actualizar libro de caja tabular si está disponible
        if (window.actualizarTablaLibroCaja) window.actualizarTablaLibroCaja();
        
        // Mostrar notificación y cerrar modal
        mostrarNotificacion(`✅ Egreso registrado correctamente en la base de datos! Nuevo saldo: $${data.nuevo_saldo.toLocaleString()}`, 'success');
        document.getElementById('egresosModal').classList.remove('show');
        document.getElementById('egresosForm').reset();
        
      } else {
        mostrarNotificacion('❌ Error: ' + (data.message || 'Error desconocido'), 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarNotificacion('❌ Error de conexión al guardar el egreso', 'error');
    });
  }

  // Función para registrar un giro (transferencia entre cuentas)
  function registrarGiro(e) {
    e.preventDefault();
    
    const fecha = document.getElementById('fecha-giro').value;
    const monto = parseFloat(document.getElementById('monto-giro').value);
    const cuenta_origen = document.getElementById('cuenta-giro').value;
    const detalle = document.getElementById('detalle-giro').value;
    
    // Validar monto positivo
    if (monto <= 0) {
      mostrarNotificacion('El monto debe ser mayor a cero', 'error');
      return;
    }
    
    // Validar saldo suficiente
    if (saldosCuentas[cuenta_origen] < monto) {
      mostrarNotificacion('Saldo insuficiente en la cuenta seleccionada', 'error');
      return;
    }
    
    // Crear movimientos (uno de egreso y uno de ingreso)
    const id = Date.now();
    const timestamp = new Date().getTime();
    
    // Movimiento de egreso (desde cuenta origen)
    const movimientoEgreso = {
      id: id,
      fecha,
      tipo: 'transferencia',
      subtipo: 'giro',
      nro_dcto: `G-${generarNumeroComprobante()}`,
      categoria: 'transferencia',
      cuenta_origen,
      descripcion: `Giro a Caja General: ${detalle}`,
      monto,
      timestamp
    };
    
    // Movimiento de ingreso (a caja general)
    const movimientoIngreso = {
      id: id + 1,
      fecha,
      tipo: 'transferencia',
      subtipo: 'giro',
      nro_dcto: `G-${generarNumeroComprobante()}`,
      categoria: 'transferencia',
      cuenta_destino: 'caja_general',
      descripcion: `Giro desde ${cuentas[cuenta_origen]}: ${detalle}`,
      monto,
      timestamp: timestamp + 1
    };
    
    // Agregar a la lista de movimientos
    movimientos.push(movimientoEgreso, movimientoIngreso);
    localStorage.setItem('movimientos', JSON.stringify(movimientos));
    
    // Actualizar saldos
    saldosCuentas[cuenta_origen] -= monto;
    saldosCuentas.caja_general += monto;
    actualizarSaldosCuentas();
    
    // Actualizar UI
    actualizarTotales();
    actualizarTablaMovimientos();
    
    // Actualizar libro de caja tabular si está disponible
    if (window.actualizarTablaLibroCaja) window.actualizarTablaLibroCaja();
    // Mostrar notificación y limpiar formulario
    mostrarNotificacion('Giro registrado correctamente');
    document.getElementById('girosForm').reset();
  }

  // Función para registrar un depósito (transferencia entre cuentas)
  function registrarDeposito(e) {
    e.preventDefault();
    
    const fecha = document.getElementById('fecha-deposito').value;
    const monto = parseFloat(document.getElementById('monto-deposito').value);
    const cuenta_destino = document.getElementById('cuenta-deposito').value;
    const detalle = document.getElementById('detalle-deposito').value;
    
    // Validar monto positivo
    if (monto <= 0) {
      mostrarNotificacion('El monto debe ser mayor a cero', 'error');
      return;
    }
    
    // Validar saldo suficiente en caja general
    if (saldosCuentas.caja_general < monto) {
      mostrarNotificacion('Saldo insuficiente en Caja General', 'error');
      return;
    }
    
    // Crear movimientos (uno de egreso y uno de ingreso)
    const id = Date.now();
    const timestamp = new Date().getTime();
    
    // Movimiento de egreso (desde caja general)
    const movimientoEgreso = {
      id: id,
      fecha,
      tipo: 'transferencia',
      subtipo: 'deposito',
      nro_dcto: `D-${generarNumeroComprobante()}`,
      categoria: 'transferencia',
      cuenta_origen: 'caja_general',
      descripcion: `Depósito a ${cuentas[cuenta_destino]}: ${detalle}`,
      monto,
      timestamp
    };
    
    // Movimiento de ingreso (a cuenta destino)
    const movimientoIngreso = {
      id: id + 1,
      fecha,
      tipo: 'transferencia',
      subtipo: 'deposito',
      nro_dcto: `D-${generarNumeroComprobante()}`,
      categoria: 'transferencia',
      cuenta_destino,
      descripcion: `Depósito desde Caja General: ${detalle}`,
      monto,
      timestamp: timestamp + 1
    };
    
    // Agregar a la lista de movimientos
    movimientos.push(movimientoEgreso, movimientoIngreso);
    localStorage.setItem('movimientos', JSON.stringify(movimientos));
    
    // Actualizar saldos
    saldosCuentas.caja_general -= monto;
    saldosCuentas[cuenta_destino] += monto;
    actualizarSaldosCuentas();
    
    // Actualizar UI
    actualizarTotales();
    actualizarTablaMovimientos();
    
    // Actualizar libro de caja tabular si está disponible
    if (window.actualizarTablaLibroCaja) window.actualizarTablaLibroCaja();
    // Mostrar notificación y limpiar formulario
    mostrarNotificacion('Depósito registrado correctamente');
    document.getElementById('depositosForm').reset();
  }

  // Función para actualizar los totales
  function actualizarTotales() {

    const totalIngresos = movimientos
      .filter(m => m.tipo === 'ingreso')
      .reduce((total, m) => total + (Number(m.monto) || 0), 0);

    const totalEgresos = movimientos
      .filter(m => m.tipo === 'egreso')
      .reduce((total, m) => total + (Number(m.monto) || 0), 0);

    const saldoFinal = totalIngresos - totalEgresos;

    document.getElementById('balanceTotalIngresos').textContent = formatearMoneda(totalIngresos);
    document.getElementById('balanceTotalEgresos').textContent = formatearMoneda(totalEgresos);
    document.getElementById('balanceSaldoFinal').textContent = formatearMoneda(saldoFinal);

    actualizarInformeRubro();
  }

  // Función para actualizar informe por rubro
  function actualizarInformeRubro() {
    // Calcular gastos operativos (mantención, materiales, trabajos domicilio)

    const gastoOperativo = movimientos
      .filter(m => m.tipo === 'egreso' && 
        (m.categoria === 'mantencion' || m.categoria === 'trabajos_domicilio' || m.categoria === 'materiales_red'))
      .reduce((total, m) => total + (Number(m.monto) || 0), 0);

    // Calcular gastos administrativos (sueldos, administración, energía, etc.)

    const gastoAdministrativo = movimientos
      .filter(m => m.tipo === 'egreso' &&
        (m.categoria === 'sueldos' || m.categoria === 'insumos_oficina' || m.categoria === 'energia_electrica' ||
         m.categoria === 'otras_cuentas' || m.categoria === 'viaticos'))
      .reduce((total, m) => total + (Number(m.monto) || 0), 0);

    // Calcular gasto en mantención

    const gastoMantencion = movimientos
      .filter(m => m.tipo === 'egreso' && (m.categoria === 'mantencion' || m.categoria === 'trabajos_domicilio'))
      .reduce((total, m) => total + (Number(m.monto) || 0), 0);

    // Calcular recaudado por multas (asumiendo que las multas son ingresos con categoría específica)

    const recaudoMultas = movimientos
      .filter(m => m.tipo === 'ingreso' && (m.categoria === 'multas_inasistencia' || m.categoria === 'otras_multas'))
      .reduce((total, m) => total + (Number(m.monto) || 0), 0);

    // Actualizar la interfaz
    document.getElementById('gastoOperativo').textContent = formatearMoneda(gastoOperativo);
    document.getElementById('gastoAdministrativo').textContent = formatearMoneda(gastoAdministrativo);
    document.getElementById('gastoMantencion').textContent = formatearMoneda(gastoMantencion);
    document.getElementById('recaudoMultas').textContent = formatearMoneda(recaudoMultas);

    // Actualizar barras de progreso
    const totalOperAdmin = gastoOperativo + gastoAdministrativo;
    const porcentajeOperativo = totalOperAdmin > 0 ? (gastoOperativo / totalOperAdmin) * 100 : 50;
    const porcentajeMantencionMultas = (gastoMantencion + recaudoMultas) > 0 ?
      (gastoMantencion / (gastoMantencion + recaudoMultas)) * 100 : 50;

    document.querySelectorAll('.rubro-bar-fill')[0].style.width = `${porcentajeOperativo}%`;
    document.querySelectorAll('.rubro-bar-fill')[1].style.width = `${porcentajeMantencionMultas}%`;

    // Actualizar valores en los títulos
    document.querySelectorAll('.rubro-value')[0].textContent =
      `${formatearMoneda(gastoOperativo)} / ${formatearMoneda(gastoAdministrativo)}`;
    document.querySelectorAll('.rubro-value')[1].textContent =
      `${formatearMoneda(gastoMantencion)} / ${formatearMoneda(recaudoMultas)}`;
  }

  // Función para actualizar la tabla de movimientos
  function actualizarTablaMovimientos() {
    const tabla = document.getElementById('tablaTodosMovimientos');
    tabla.innerHTML = '';
    
    // Obtener filtros
    const tipo = document.getElementById('filtroTipo').value;
    const categoria = document.getElementById('filtroCategoria').value;
    const cuenta = document.getElementById('filtroCuenta').value;
    const fechaDesde = document.getElementById('filtroFechaDesde').value;
    const fechaHasta = document.getElementById('filtroFechaHasta').value;
    
    // Filtrar movimientos
    let movimientosFiltrados = movimientos;
    
    if (tipo) {
      movimientosFiltrados = movimientosFiltrados.filter(m => m.tipo === tipo);
    }
    
    if (categoria) {
      movimientosFiltrados = movimientosFiltrados.filter(m => {
        const catNombre = m.tipo === 'ingreso' ? categoriasIngresos[m.categoria] : categoriasEgresos[m.categoria];
        return catNombre === categoria;
      });
    }
    
    if (cuenta) {
      movimientosFiltrados = movimientosFiltrados.filter(m => 
        (m.cuenta_origen === cuenta) || (m.cuenta_destino === cuenta)
      );
    }
    
    if (fechaDesde) {
      movimientosFiltrados = movimientosFiltrados.filter(m => m.fecha >= fechaDesde);
    }
    
    if (fechaHasta) {
      movimientosFiltrados = movimientosFiltrados.filter(m => m.fecha <= fechaHasta);
    }
    
    // Ordenar por fecha (más reciente primero)
    movimientosFiltrados.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
    
    // Mostrar en la tabla
    movimientosFiltrados.forEach(movimiento => {
      const fila = document.createElement('tr');
      
      // Determinar tipo y categoría
      let tipoMovimiento = '';
      let categoriaMovimiento = '';
      let cuentaMovimiento = '';
      
      if (movimiento.tipo === 'transferencia') {
        tipoMovimiento = 'Transferencia';
        categoriaMovimiento = movimiento.subtipo === 'giro' ? 'Giro' : 'Depósito';
        cuentaMovimiento = movimiento.subtipo === 'giro' ? 
          `De ${cuentas[movimiento.cuenta_origen]} a Caja General` : 
          `De Caja General a ${cuentas[movimiento.cuenta_destino]}`;
      } else {
        tipoMovimiento = movimiento.tipo === 'ingreso' ? 'Ingreso' : 'Egreso';
        categoriaMovimiento = movimiento.tipo === 'ingreso' ? 
          categoriasIngresos[movimiento.categoria] : 
          categoriasEgresos[movimiento.categoria];
        cuentaMovimiento = movimiento.tipo === 'ingreso' ? 
          cuentas[movimiento.cuenta_destino] : 
          cuentas[movimiento.cuenta_origen];
      }
      
      // Crear fila
      fila.innerHTML = `
        <td>${movimiento.fecha}</td>
        <td>${tipoMovimiento}</td>
        <td>${categoriaMovimiento}</td>
        <td>${movimiento.descripcion}</td>
        <td>${cuentaMovimiento}</td>
        <td>${formatearMoneda(movimiento.monto)}</td>
        <td>${movimiento.nro_dcto}</td>
        <td>${movimiento.razon_social || '-'}</td>
        <td>${movimiento.rut_proveedor || '-'}</td>
      `;
      
      tabla.appendChild(fila);
    });
  }

  // Función para filtrar movimientos
  function filtrarMovimientos() {
    actualizarTablaMovimientos();
  }

  // Función para exportar a Excel
  function exportarExcel() {
    // Crear libro de Excel
    const wb = XLSX.utils.book_new();
    
    // Preparar datos
    const datos = [];
    
    // Encabezados
    datos.push([
      'Fecha', 'Tipo', 'Categoría', 'Descripción', 'Cuenta', 
      'Monto', 'N° Comprobante', 'Proveedor', 'RUT Proveedor'
    ]);
    
    // Agregar movimientos
    movimientos.forEach(movimiento => {
      // Determinar tipo y categoría
      let tipoMovimiento = '';
      let categoriaMovimiento = '';
      let cuentaMovimiento = '';
      
      if (movimiento.tipo === 'transferencia') {
        tipoMovimiento = 'Transferencia';
        categoriaMovimiento = movimiento.subtipo === 'giro' ? 'Giro' : 'Depósito';
        cuentaMovimiento = movimiento.subtipo === 'giro' ? 
          `De ${cuentas[movimiento.cuenta_origen]} a Caja General` : 
          `De Caja General a ${cuentas[movimiento.cuenta_destino]}`;
      } else {
        tipoMovimiento = movimiento.tipo === 'ingreso' ? 'Ingreso' : 'Egreso';
        categoriaMovimiento = movimiento.tipo === 'ingreso' ? 
          categoriasIngresos[movimiento.categoria] : 
          categoriasEgresos[movimiento.categoria];
        cuentaMovimiento = movimiento.tipo === 'ingreso' ? 
          cuentas[movimiento.cuenta_destino] : 
          cuentas[movimiento.cuenta_origen];
      }
      
      datos.push([
        movimiento.fecha,
        tipoMovimiento,
        categoriaMovimiento,
        movimiento.descripcion,
        cuentaMovimiento,
        movimiento.monto,
        movimiento.nro_dcto,
        movimiento.razon_social || '-',
        movimiento.rut_proveedor || '-'
      ]);
    });
    
    // Crear hoja de cálculo
    const ws = XLSX.utils.aoa_to_sheet(datos);
    
    // Agregar hoja al libro
    XLSX.utils.book_append_sheet(wb, ws, 'Movimientos');
    
    // Exportar
    XLSX.writeFile(wb, 'movimientos_financieros.xlsx');
  }

  // Función para inicializar gráficos
  function inicializarGraficos() {
    // Destruir gráficos existentes si los hay
    if (chartIngresos) chartIngresos.destroy();
    if (chartEgresos) chartEgresos.destroy();
    if (chartFlujo) chartFlujo.destroy();
    if (chartConciliacion) chartConciliacion.destroy();
    
    // Gráfico de distribución de ingresos
    const ingresosPorCategoria = {};
    movimientos
      .filter(m => m.tipo === 'ingreso')
      .forEach(m => {
        const categoria = categoriasIngresos[m.categoria] || 'Otros';
        ingresosPorCategoria[categoria] = (ingresosPorCategoria[categoria] || 0) + m.monto;
      });
    
    const ctxIngresos = document.getElementById('ingresosChart').getContext('2d');
    chartIngresos = new Chart(ctxIngresos, {
      type: 'pie',
      data: {
        labels: Object.keys(ingresosPorCategoria),
        datasets: [{
          data: Object.values(ingresosPorCategoria),
          backgroundColor: [
            '#4CAF50', '#8BC34A', '#CDDC39', '#FFC107', 
            '#FF9800', '#FF5722', '#9C27B0', '#3F51B5'
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `${label}: ${formatearMoneda(value)} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
    
    // Gráfico de distribución de egresos
    const egresosPorCategoria = {};
    movimientos
      .filter(m => m.tipo === 'egreso')
      .forEach(m => {
        const categoria = categoriasEgresos[m.categoria] || 'Otros';
        egresosPorCategoria[categoria] = (egresosPorCategoria[categoria] || 0) + m.monto;
      });
    
    const ctxEgresos = document.getElementById('egresosChart').getContext('2d');
    chartEgresos = new Chart(ctxEgresos, {
      type: 'pie',
      data: {
        labels: Object.keys(egresosPorCategoria),
        datasets: [{
          data: Object.values(egresosPorCategoria),
          backgroundColor: [
            '#F44336', '#E91E63', '#9C27B0', '#673AB7', 
            '#3F51B5', '#2196F3', '#03A9F4', '#00BCD4'
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `${label}: ${formatearMoneda(value)} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
    
    // Gráfico de flujo mensual
    const flujoMensual = {};
    movimientos.forEach(m => {
      const fecha = new Date(m.fecha);
      const mesAnio = `${fecha.getFullYear()}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}`;
      
      if (!flujoMensual[mesAnio]) {
        flujoMensual[mesAnio] = { ingresos: 0, egresos: 0 };
      }
      
      if (m.tipo === 'ingreso') {
        flujoMensual[mesAnio].ingresos += m.monto;
      } else if (m.tipo === 'egreso') {
        flujoMensual[mesAnio].egresos += m.monto;
      }
    });
    
    const meses = Object.keys(flujoMensual).sort();
    const ingresosMensuales = meses.map(mes => flujoMensual[mes].ingresos);
    const egresosMensuales = meses.map(mes => flujoMensual[mes].egresos);
    
    const ctxFlujo = document.getElementById('flujoChart').getContext('2d');
    chartFlujo = new Chart(ctxFlujo, {
      type: 'bar',
      data: {
        labels: meses,
        datasets: [
          {
            label: 'Ingresos',
            data: ingresosMensuales,
            backgroundColor: '#4CAF50'
          },
          {
            label: 'Egresos',
            data: egresosMensuales,
            backgroundColor: '#F44336'
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          x: {
            stacked: false,
          },
          y: {
            stacked: false,
            beginAtZero: true
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.dataset.label || '';
                const value = context.raw || 0;
                return `${label}: ${formatearMoneda(value)}`;
              }
            }
          }
        }
      }
    });
  }

  // Función para volver a la vista de registro principal
  function volverARegistro() {
    // Ocultar todas las secciones visibles
    const sections = [
      'girosDepositosSection',
      'balanceSection',
      'conciliacionSection',
      'informeRubroSection',
      'movimientosSection'
    ];
    
    sections.forEach(id => {
      const section = document.getElementById(id);
      if (section) section.style.display = 'none';
    });
    
    // Mostrar sección principal
    document.getElementById('registroSection').style.display = 'block';
  }

  // Inicialización al cargar la página
  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - iniciando configuración de eventos');
    
    // Verificar que los elementos principales existan
    const elementosVerificar = ['ingresosBtn', 'egresosBtn', 'balanceBtn', 'conciliacionBtn'];
    elementosVerificar.forEach(id => {
      const elemento = document.getElementById(id);
      if (elemento) {
        console.log(`✅ Elemento ${id} encontrado`);
      } else {
        console.error(`❌ Elemento ${id} NO encontrado`);
      }
    });
    // Manejar TODOS los botones "volver"
    document.querySelectorAll('button.volver').forEach(function(btn) {
      btn.addEventListener('click', volverARegistro);
    });

    // Configurar evento para formulario de cuentas iniciales
    const cuentasInicialesForm = document.getElementById('cuentasInicialesForm');
    if (cuentasInicialesForm) {
      cuentasInicialesForm.addEventListener('submit', configurarCuentasIniciales);
    }
    
    // Botón para abrir modal de cuentas iniciales
    const cuentasInicialesBtn = document.getElementById('cuentasInicialesBtn');
    if (cuentasInicialesBtn) {
      cuentasInicialesBtn.addEventListener('click', () => {
        console.log('🔍 DEBUG: Abriendo modal de configuración de cuentas');
        document.getElementById('cuentasInicialesModal').classList.add('show');
      });
    }
    
    // Cerrar modal de cuentas iniciales
    const closeCuentasInicialesModal = document.getElementById('closeCuentasInicialesModal');
    if (closeCuentasInicialesModal) {
      closeCuentasInicialesModal.addEventListener('click', () => {
        console.log('🔍 DEBUG: Cerrando modal de configuración de cuentas');
        document.getElementById('cuentasInicialesModal').classList.remove('show');
      });
    }

    // ===============================
    // EVENT LISTENERS PARA FUNCIONALIDADES AVANZADAS DEL MODAL
    // ===============================
    
    // Botón agregar cuenta
    const addAccountBtn = document.getElementById('addAccountBtn');
    if (addAccountBtn) {
      addAccountBtn.addEventListener('click', agregarNuevaCuenta);
    }
    
    // Botón editar configuración
    const editConfigBtn = document.getElementById('editConfigBtn');
    if (editConfigBtn) {
      editConfigBtn.addEventListener('click', toggleEditMode);
    }
    
    // Botón vista previa
    const previewBtn = document.getElementById('previewBtn');
    if (previewBtn) {
      previewBtn.addEventListener('click', mostrarVistaPrevia);
    }
    
    // Botón resetear formulario
    const resetFormBtn = document.getElementById('resetFormBtn');
    if (resetFormBtn) {
      resetFormBtn.addEventListener('click', resetearFormulario);
    }

    // ===============================
    // EVENT LISTENERS PARA CAJA GENERAL
    // ===============================
    
    // Botón guardar Caja General
    const saveCajaGeneralBtn = document.getElementById('saveCajaGeneralBtn');
    if (saveCajaGeneralBtn) {
      saveCajaGeneralBtn.addEventListener('click', function() {
        console.log('🔍 DEBUG: Click en saveCajaGeneralBtn detectado');
        console.log('🔍 DEBUG: Elemento button:', this);
        console.log('🔍 DEBUG: Disabled:', this.disabled);
        console.log('🔍 DEBUG: Style pointer-events:', this.style.pointerEvents);
        guardarCajaGeneral();
      });
    }
    
    // Botón editar Caja General
    const editCajaGeneralBtn = document.getElementById('editCajaGeneralBtn');
    if (editCajaGeneralBtn) {
      editCajaGeneralBtn.addEventListener('click', function() {
        console.log('🔍 DEBUG: Click en editCajaGeneralBtn detectado');
        console.log('🔍 DEBUG: Elemento button:', this);
        editarCajaGeneral();
      });
    }

    // ===============================
    // EVENT LISTENERS PARA CUENTA CORRIENTE
    // ===============================
    
    // Botón guardar Cuenta Corriente
    const saveCuentaCorrienteBtn = document.getElementById('saveCuentaCorrienteBtn');
    if (saveCuentaCorrienteBtn) {
      saveCuentaCorrienteBtn.addEventListener('click', function() {
        console.log('🔍 DEBUG: Click en saveCuentaCorrienteBtn detectado');
        guardarCuentaCorriente();
      });
    }
    
    // Botón editar Cuenta Corriente
    const editCuentaCorrienteBtn = document.getElementById('editCuentaCorrienteBtn');
    if (editCuentaCorrienteBtn) {
      editCuentaCorrienteBtn.addEventListener('click', function() {
        console.log('🔍 DEBUG: Click en editCuentaCorrienteBtn detectado');
        editarCuentaCorriente();
      });
    }

    // ===============================
    // EVENT LISTENERS PARA CUENTA DE AHORRO
    // ===============================
    
    // Botón guardar Cuenta de Ahorro
    const saveCuentaAhorroBtn = document.getElementById('saveCuentaAhorroBtn');
    if (saveCuentaAhorroBtn) {
      saveCuentaAhorroBtn.addEventListener('click', guardarCuentaAhorro);
    }
    
    // Botón editar Cuenta de Ahorro
    const editCuentaAhorroBtn = document.getElementById('editCuentaAhorroBtn');
    if (editCuentaAhorroBtn) {
      editCuentaAhorroBtn.addEventListener('click', editarCuentaAhorro);
    }
    
    // Event listeners para botones de eliminar cuenta (cuentas básicas)
    document.querySelectorAll('.remove-account-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const accountType = this.dataset.account;
        eliminarCuenta(accountType);
      });
    });
    
    // Validar campos dinámicamente
    document.addEventListener('input', function(e) {
      if (e.target.matches('#cuentasInicialesForm input[type="number"]')) {
        const value = parseFloat(e.target.value);
        if (value < 0) {
          e.target.style.borderColor = '#dc3545';
          mostrarNotificacion('⚠️ El saldo no puede ser negativo', 'warning');
        } else {
          e.target.style.borderColor = '#ddd';
        }
      }
    });
    
    // Formatear números de cuenta automáticamente
    document.addEventListener('input', function(e) {
      if (e.target.matches('#cuentasInicialesForm input[type="text"]') && 
          e.target.placeholder.includes('Ej: 12345678-9')) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length > 8) {
          value = value.substring(0, 8) + '-' + value.substring(8, 9);
        }
        e.target.value = value;
      }
    });
    
    // Configurar eventos para botones principales
    console.log('🔍 DEBUG: Configurando event listeners para botones principales');
    
    const ingresosBtn = document.getElementById('ingresosBtn');
    if (ingresosBtn) {
      console.log('🔍 DEBUG: Botón ingresosBtn encontrado:', ingresosBtn);
      console.log('🔍 DEBUG: ingresosBtn estado inicial:', {
        disabled: ingresosBtn.disabled,
        display: ingresosBtn.style.display,
        pointerEvents: getComputedStyle(ingresosBtn).pointerEvents,
        visibility: getComputedStyle(ingresosBtn).visibility,
        classList: Array.from(ingresosBtn.classList)
      });
      
      ingresosBtn.addEventListener('click', (e) => {
        console.log('🔍 DEBUG: ¡¡¡ CLICK DETECTADO EN INGRESOS !!!');
        console.log('🔍 DEBUG: Event object:', e);
        console.log('🔍 DEBUG: Target:', e.target);
        console.log('🔍 DEBUG: CurrentTarget:', e.currentTarget);
        
        document.getElementById('ingresosModal').classList.add('show');
        const nroDctoInput = document.getElementById('nro-dcto-ingresos');
        if (nroDctoInput) {
          nroDctoInput.value = generarNumeroComprobante('ingreso');
          nroDctoInput.readOnly = true;
        }
        const fechaInput = document.getElementById('fecha-ingresos');
        if (fechaInput) {
          fechaInput.value = obtenerFechaActual();
          fechaInput.readOnly = true;
        }
      });
      
      // Forzar propiedades del botón
      ingresosBtn.style.pointerEvents = 'auto';
      ingresosBtn.style.cursor = 'pointer';
      ingresosBtn.disabled = false;
      
    } else {
      console.error('❌ DEBUG: Elemento ingresosBtn NO encontrado');
    }
    
    const egresosBtn = document.getElementById('egresosBtn');
    if (egresosBtn) {
      console.log('🔍 DEBUG: Botón egresosBtn encontrado:', egresosBtn);
      console.log('🔍 DEBUG: egresosBtn estado inicial:', {
        disabled: egresosBtn.disabled,
        display: egresosBtn.style.display,
        pointerEvents: getComputedStyle(egresosBtn).pointerEvents,
        visibility: getComputedStyle(egresosBtn).visibility,
        classList: Array.from(egresosBtn.classList)
      });
      
      egresosBtn.addEventListener('click', (e) => {
        console.log('🔍 DEBUG: ¡¡¡ CLICK DETECTADO EN EGRESOS !!!');
        console.log('🔍 DEBUG: Event object:', e);
        
        document.getElementById('egresosModal').classList.add('show');
        const nroDctoInput = document.getElementById('nro-dcto-egresos');
        if (nroDctoInput) {
          nroDctoInput.value = generarNumeroComprobante('egreso');
          nroDctoInput.readOnly = true;
        }
        const fechaInput = document.getElementById('fecha-egresos');
        if (fechaInput) {
          fechaInput.value = obtenerFechaActual();
          fechaInput.readOnly = true;
        }
      });
      
      // Forzar propiedades del botón
      egresosBtn.style.pointerEvents = 'auto';
      egresosBtn.style.cursor = 'pointer';
      egresosBtn.disabled = false;
      
    } else {
      console.error('❌ DEBUG: Elemento egresosBtn NO encontrado');
    }

    const giroDepositosBtn = document.getElementById('giroDepositosBtn');
    if (giroDepositosBtn) {
      giroDepositosBtn.addEventListener('click', () => {
        document.getElementById('girosDepositosSection').style.display = 'block';
        document.getElementById('registroSection').style.display = 'none';
        // Establecer fecha actual y no editable en ambos formularios
        const fechaGiro = document.getElementById('fecha-giro');
        if (fechaGiro) {
          fechaGiro.value = obtenerFechaActual();
          fechaGiro.readOnly = true;
        }
        const fechaDeposito = document.getElementById('fecha-deposito');
      if (fechaDeposito) {
        fechaDeposito.value = obtenerFechaActual();
        fechaDeposito.readOnly = true;
      }
      // Comprobante Giro
      const nroDctoGiro = document.getElementById('nro-dcto-giro');
      if (nroDctoGiro) {
        nroDctoGiro.value = 'G-' + comprobanteCounter.toString().padStart(4, '0');
        nroDctoGiro.readOnly = true;
        comprobanteCounter++;
        localStorage.setItem('comprobanteCounter', comprobanteCounter);
      }
      // Comprobante Depósito
      const nroDctoDeposito = document.getElementById('nro-dcto-deposito');
      if (nroDctoDeposito) {
        nroDctoDeposito.value = 'D-' + comprobanteCounter.toString().padStart(4, '0');
        nroDctoDeposito.readOnly = true;
        comprobanteCounter++;
        localStorage.setItem('comprobanteCounter', comprobanteCounter);
      }
    });
    } else {
      console.error('❌ DEBUG: Elemento giroDepositosBtn NO encontrado');
    }

    const libroCajaBtn = document.getElementById('libroCajaBtn');
    if (libroCajaBtn) {
      libroCajaBtn.addEventListener('click', () => {
        document.getElementById('registroSection').style.display = 'none';
        document.getElementById('libroCajaSection').style.display = 'block';

      // Configurar fechas por defecto (primer y último día del mes actual) y no editables
      const hoy = new Date();
      const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
      const ultimoDiaMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

      const fechaDesde = document.getElementById('fechaDesde');
      const fechaHasta = document.getElementById('fechaHasta');
      if (fechaDesde) {
        fechaDesde.valueAsDate = primerDiaMes;
        fechaDesde.readOnly = true;
      }
      if (fechaHasta) {
        fechaHasta.valueAsDate = ultimoDiaMes;
        fechaHasta.readOnly = true;
      }
    });
    } else {
      console.error('❌ DEBUG: Elemento libroCajaBtn NO encontrado');
    }

    const balanceBtn = document.getElementById('balanceBtn');
    if (balanceBtn) {
      console.log('🔍 DEBUG: Botón balanceBtn encontrado:', balanceBtn);
      console.log('🔍 DEBUG: balanceBtn estado inicial:', {
        disabled: balanceBtn.disabled,
        display: balanceBtn.style.display,
        pointerEvents: getComputedStyle(balanceBtn).pointerEvents,
        visibility: getComputedStyle(balanceBtn).visibility,
        classList: Array.from(balanceBtn.classList)
      });
      
      balanceBtn.addEventListener('click', (e) => {
        console.log('🔍 DEBUG: ¡¡¡ CLICK DETECTADO EN BALANCE !!!');
        console.log('🔍 DEBUG: Event object:', e);
        
        document.getElementById('registroSection').style.display = 'none';
        document.getElementById('balanceSection').style.display = 'block';
        inicializarGraficos();
      });
      
      // Forzar propiedades del botón
      balanceBtn.style.pointerEvents = 'auto';
      balanceBtn.style.cursor = 'pointer';
      balanceBtn.disabled = false;
      
    } else {
      console.error('❌ DEBUG: Elemento balanceBtn NO encontrado');
    }

    const conciliacionBtn = document.getElementById('conciliacionBtn');
    if (conciliacionBtn) {
      console.log('🔍 DEBUG: Botón conciliacionBtn encontrado:', conciliacionBtn);
      console.log('🔍 DEBUG: conciliacionBtn estado inicial:', {
        disabled: conciliacionBtn.disabled,
        display: conciliacionBtn.style.display,
        pointerEvents: getComputedStyle(conciliacionBtn).pointerEvents,
        visibility: getComputedStyle(conciliacionBtn).visibility,
        classList: Array.from(conciliacionBtn.classList)
      });
      
      conciliacionBtn.addEventListener('click', (e) => {
        console.log('🔍 DEBUG: ¡¡¡ CLICK DETECTADO EN CONCILIACIÓN !!!');
        console.log('🔍 DEBUG: Event object:', e);
        document.getElementById('registroSection').style.display = 'none';
        document.getElementById('conciliacionSection').style.display = 'block';
      });
      
      // Forzar propiedades del botón
      conciliacionBtn.style.pointerEvents = 'auto';
      conciliacionBtn.style.cursor = 'pointer';
      conciliacionBtn.disabled = false;
      
    } else {
      console.error('❌ DEBUG: Elemento conciliacionBtn NO encontrado');
      console.error('Elemento conciliacionBtn no encontrado');
    }

    const informeRubroBtn = document.getElementById('informeRubroBtn');
    if (informeRubroBtn) {
      informeRubroBtn.addEventListener('click', () => {
        document.getElementById('registroSection').style.display = 'none';
        document.getElementById('informeRubroSection').style.display = 'block';
      });
    } else {
      console.error('Elemento informeRubroBtn no encontrado');
    }

    const movimientosBtn = document.getElementById('movimientosBtn');
    if (movimientosBtn) {
      movimientosBtn.addEventListener('click', () => {
        document.getElementById('registroSection').style.display = 'none';
        document.getElementById('movimientosSection').style.display = 'block';

      // Llenar el select de categorías
      const selectCategoria = document.getElementById('filtroCategoria');
      selectCategoria.innerHTML = '<option value="">Todas</option>';

      // Agregar categorías de ingresos
      Object.values(categoriasIngresos).forEach(cat => {
        const option = document.createElement('option');
        option.value = cat;
        option.textContent = cat;
        selectCategoria.appendChild(option);
      });

      // Agregar categorías de egresos
      Object.values(categoriasEgresos).forEach(cat => {
        const option = document.createElement('option');
        option.value = cat;
        option.textContent = cat;
        selectCategoria.appendChild(option);
      });

      // Configurar fechas por defecto (primer y último día del mes actual) y no editables
      const hoy = new Date();
      const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
      const ultimoDiaMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

      const filtroFechaDesde = document.getElementById('filtroFechaDesde');
      const filtroFechaHasta = document.getElementById('filtroFechaHasta');
      if (filtroFechaDesde) {
        filtroFechaDesde.valueAsDate = primerDiaMes;
        filtroFechaDesde.readOnly = true;
      }
      if (filtroFechaHasta) {
        filtroFechaHasta.valueAsDate = ultimoDiaMes;
        filtroFechaHasta.readOnly = true;
      }

      // Actualizar tabla de movimientos
      actualizarTablaMovimientos();
    });
    } else {
      console.error('Elemento movimientosBtn no encontrado');
    }

    // Event listeners para cierre de modales
    const closeIngresosModal = document.getElementById('closeIngresosModal');
    if (closeIngresosModal) {
      closeIngresosModal.addEventListener('click', () => {
        document.getElementById('ingresosModal').classList.remove('show');
        movimientoEditando = null;
        document.getElementById('ingresosForm').reset();
      });
    } else {
      console.error('Elemento closeIngresosModal no encontrado');
    }

    const closeEgresosModal = document.getElementById('closeEgresosModal');
    if (closeEgresosModal) {
      closeEgresosModal.addEventListener('click', () => {
        document.getElementById('egresosModal').classList.remove('show');
        document.getElementById('egresosForm').reset();
      });
    } else {
      console.error('Elemento closeEgresosModal no encontrado');
    }

    // Event listeners para formularios
    const ingresosForm = document.getElementById('ingresosForm');
    if (ingresosForm) {
      ingresosForm.addEventListener('submit', registrarIngreso);
    } else {
      console.error('Elemento ingresosForm no encontrado');
    }
    
    const egresosForm = document.getElementById('egresosForm');
    if (egresosForm) {
      egresosForm.addEventListener('submit', registrarEgreso);
    } else {
      console.error('Elemento egresosForm no encontrado');
    }
    
    const girosForm = document.getElementById('girosForm');
    if (girosForm) {
      girosForm.addEventListener('submit', registrarGiro);
    } else {
      console.error('Elemento girosForm no encontrado');
    }
    
    const depositosForm = document.getElementById('depositosForm');
    if (depositosForm) {
      depositosForm.addEventListener('submit', registrarDeposito);
    } else {
      console.error('Elemento depositosForm no encontrado');
    }


    // Cargar datos iniciales
    actualizarSaldosCuentas();
    actualizarTotales();
    actualizarTablaMovimientos();
  });
</script>
@php
    $isCrc = Auth::user()->isCrc();
@endphp

<script>
    const sesionEspecialActiva = @json($isCrc);
    console.log("sesion activa", sesionEspecialActiva);

    const volverBtn = document.getElementById('volverBtn');
    const volverBtnTexto = document.getElementById('volverBtnTexto');
    const volverBtnIcon = document.getElementById('volverBtnIcon');

    if (volverBtn && sesionEspecialActiva) {
        if (volverBtnTexto) {
            volverBtnTexto.textContent = 'Cerrar Sesión';
        }
        if (volverBtnIcon) {
            volverBtnIcon.classList.remove('bi-arrow-left');
            volverBtnIcon.classList.add('bi-box-arrow-right'); // ícono de logout
        }
    }

    if (volverBtn) {
        volverBtn.addEventListener('click', () => {
            if (sesionEspecialActiva) {
                window.location.href = '/logout';
            } else {
                volverARegistro();
            }
        });
    }
</script>

<!-- rutaje web libro caja tabular -->
@php
    $mostrarLibroCaja = $mostrarLibroCaja ?? false;
@endphp

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const mostrarLibroCaja = @json($mostrarLibroCaja);

        if (mostrarLibroCaja) {
            // Oculta todas las secciones
            var secciones = [
                'girosDepositosSection',
                'registroSection',
                'balanceSection',
                'conciliacionSection',
                'informeRubroSection',
                'movimientosSection'
            ];
            secciones.forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });

            // Muestra la sección de Libro de Caja
            document.getElementById('libroCajaSection')?.style.display = 'block';

            // Setea las fechas por defecto
            const hoy = new Date();
            const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            const ultimoDiaMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

            document.getElementById('fechaDesde').valueAsDate = primerDiaMes;
            document.getElementById('fechaHasta').valueAsDate = ultimoDiaMes;
        }
        
        // Función para eliminar completamente las restricciones del modal
        function eliminarTodasLasRestriccionesModal() {
            console.log('🔍 DEBUG: Iniciando eliminación de restricciones del modal');
            
            // Eliminar clases de bloqueo
            const items = document.querySelectorAll('[data-account-type]');
            console.log('🔍 DEBUG: Elementos con data-account-type encontrados:', items.length);
            items.forEach(item => {
                console.log('🔍 DEBUG: Procesando item:', item);
                item.classList.remove('locked', 'disabled');
            });
            
            // Habilitar todos los botones del modal
            const botones = document.querySelectorAll('#modalConfiguracionCuentas button, #modalConfiguracionCuentas .btn');
            console.log('🔍 DEBUG: Botones encontrados en modal:', botones.length);
            botones.forEach(btn => {
                console.log('🔍 DEBUG: Procesando botón:', btn.id, btn);
                btn.disabled = false;
                btn.style.pointerEvents = 'auto';
                btn.style.cursor = 'pointer';
                btn.style.opacity = '1';
                btn.style.visibility = 'visible';
                btn.style.display = 'inline-flex';
            });
            
            // Habilitar todos los campos del modal
            const campos = document.querySelectorAll('#modalConfiguracionCuentas input, #modalConfiguracionCuentas select');
            console.log('🔍 DEBUG: Campos encontrados en modal:', campos.length);
            campos.forEach(campo => {
                console.log('🔍 DEBUG: Procesando campo:', campo.id, campo);
                campo.disabled = false;
                campo.style.backgroundColor = '#fff';
                campo.style.color = '#212529';
                campo.style.cursor = 'text';
                campo.style.pointerEvents = 'auto';
            });
            
            console.log('✅ DEBUG: Todas las restricciones del modal han sido eliminadas');
        }
        
        // Ejecutar la función al cargar la página
        eliminarTodasLasRestriccionesModal();
        
        // Función para diagnosticar el estado de los elementos
        function diagnosticarEstadoModal() {
            console.log('🔍 DEBUG: === DIAGNÓSTICO COMPLETO DEL MODAL ===');
            
            // Verificar modal principal
            const modal = document.getElementById('modalConfiguracionCuentas');
            console.log('🔍 DEBUG: Modal principal encontrado:', !!modal);
            
            // Verificar botones específicos
            const botonesClave = [
                'saveCajaGeneralBtn',
                'editCajaGeneralBtn', 
                'saveCuentaCorrienteBtn',
                'editCuentaCorrienteBtn'
            ];
            
            botonesClave.forEach(id => {
                const btn = document.getElementById(id);
                if (btn) {
                    console.log(`🔍 DEBUG: Botón ${id}:`, {
                        encontrado: true,
                        disabled: btn.disabled,
                        display: btn.style.display,
                        pointerEvents: btn.style.pointerEvents,
                        visibility: btn.style.visibility,
                        opacity: btn.style.opacity,
                        classList: Array.from(btn.classList)
                    });
                } else {
                    console.log(`❌ DEBUG: Botón ${id} NO encontrado`);
                }
            });
            
            // Verificar campos específicos
            const camposClave = [
                'saldo-caja-general',
                'banco-caja-general',
                'numero-caja-general'
            ];
            
            camposClave.forEach(id => {
                const campo = document.getElementById(id);
                if (campo) {
                    console.log(`🔍 DEBUG: Campo ${id}:`, {
                        encontrado: true,
                        disabled: campo.disabled,
                        value: campo.value,
                        backgroundColor: campo.style.backgroundColor,
                        color: campo.style.color
                    });
                } else {
                    console.log(`❌ DEBUG: Campo ${id} NO encontrado`);
                }
            });
            
            console.log('🔍 DEBUG: === FIN DIAGNÓSTICO ===');
        }
        
        // Función específica para diagnosticar x-boton-protegido
        function diagnosticarBotonesProtegidos() {
            console.log('🔍 DEBUG: === DIAGNÓSTICO BOTONES PROTEGIDOS ===');
            
            const botonesProtegidos = ['ingresosBtn', 'egresosBtn', 'balanceBtn', 'conciliacionBtn'];
            
            botonesProtegidos.forEach(id => {
                const btn = document.getElementById(id);
                if (btn) {
                    const computedStyle = getComputedStyle(btn);
                    console.log(`🔍 DEBUG: Botón protegido ${id}:`, {
                        elemento: btn,
                        tagName: btn.tagName,
                        className: btn.className,
                        disabled: btn.disabled,
                        tabIndex: btn.tabIndex,
                        style: {
                            display: computedStyle.display,
                            visibility: computedStyle.visibility,
                            opacity: computedStyle.opacity,
                            pointerEvents: computedStyle.pointerEvents,
                            cursor: computedStyle.cursor,
                            position: computedStyle.position,
                            zIndex: computedStyle.zIndex
                        },
                        atributos: {
                            'data-disabled': btn.getAttribute('data-disabled'),
                            'aria-disabled': btn.getAttribute('aria-disabled'),
                            habilitado: btn.getAttribute('habilitado')
                        },
                        parentElement: btn.parentElement,
                        offsetParent: btn.offsetParent,
                        boundingRect: btn.getBoundingClientRect()
                    });
                    
                    // Verificar si hay overlays bloqueando
                    const rect = btn.getBoundingClientRect();
                    const elementFromPoint = document.elementFromPoint(
                        rect.left + rect.width / 2, 
                        rect.top + rect.height / 2
                    );
                    
                    console.log(`🔍 DEBUG: Elemento en punto central de ${id}:`, elementFromPoint);
                    
                    if (elementFromPoint !== btn) {
                        console.log(`⚠️ DEBUG: ¡¡¡ POSIBLE OVERLAY BLOQUEANDO ${id} !!!`);
                        console.log(`🔍 DEBUG: Elemento bloqueador:`, elementFromPoint);
                    }
                } else {
                    console.log(`❌ DEBUG: Botón protegido ${id} NO encontrado`);
                }
            });
            
            console.log('🔍 DEBUG: === FIN DIAGNÓSTICO BOTONES PROTEGIDOS ===');
        }
        
        // Ejecutar diagnóstico inicial
        setTimeout(diagnosticarEstadoModal, 1000);
        
        // Ejecutar diagnóstico de botones protegidos
        setTimeout(diagnosticarBotonesProtegidos, 1500);
        
        // Función para forzar habilitación de botones principales
        function forzarHabilitacionBotonesPrincipales() {
            console.log('🔧 DEBUG: Forzando habilitación de botones principales');
            
            const botonesPrincipales = ['ingresosBtn', 'egresosBtn', 'balanceBtn', 'conciliacionBtn'];
            
            botonesPrincipales.forEach(id => {
                const btn = document.getElementById(id);
                if (btn) {
                    // Eliminar todas las restricciones posibles
                    btn.disabled = false;
                    btn.style.pointerEvents = 'auto !important';
                    btn.style.cursor = 'pointer !important';
                    btn.style.opacity = '1 !important';
                    btn.style.visibility = 'visible !important';
                    btn.style.display = 'inline-flex !important';
                    btn.style.zIndex = '1000 !important';
                    
                    // Remover atributos que puedan bloquear
                    btn.removeAttribute('disabled');
                    btn.removeAttribute('aria-disabled');
                    btn.removeAttribute('data-disabled');
                    
                    // Remover clases que puedan bloquear
                    btn.classList.remove('disabled', 'blocked', 'inactive');
                    
                    console.log(`✅ DEBUG: Botón ${id} forzado a estar habilitado`);
                } else {
                    console.log(`❌ DEBUG: Botón ${id} no encontrado para forzar`);
                }
            });
        }
        
        // Ejecutar forzado de habilitación
        setTimeout(forzarHabilitacionBotonesPrincipales, 2000);
        
        // También ejecutar cuando se abra el modal
        const modal = document.getElementById('modalConfiguracionCuentas');
        if (modal) {
            modal.addEventListener('shown.bs.modal', eliminarTodasLasRestriccionesModal);
        }
        
        // Interceptar TODOS los clicks para debugging
        document.addEventListener('click', function(e) {
            if (e.target.id && (e.target.id.includes('Caja') || e.target.id.includes('Cuenta') || e.target.id.includes('save') || e.target.id.includes('edit'))) {
                console.log('🔍 DEBUG: Click interceptado en:', e.target.id, e.target);
                console.log('🔍 DEBUG: Event details:', e);
                console.log('🔍 DEBUG: Target disabled:', e.target.disabled);
                console.log('🔍 DEBUG: Target pointer-events:', getComputedStyle(e.target).pointerEvents);
            }
        });
        
        // Interceptar eventos de submit del formulario
        const form = document.getElementById('cuentasInicialesForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('🔍 DEBUG: Submit del formulario interceptado:', e);
            });
        }
    });
</script>
@endif

@include('orgs.contable.libroCajaTabular', ['id' => $org->id ?? $orgId ?? null])
</body>
</html>