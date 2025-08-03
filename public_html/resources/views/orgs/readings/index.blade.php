@extends('layouts.nice', ['active'=>'orgs.readings.index','title'=>'Ingreso de Lecturas'])

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@push('css')
<style>
    .enhanced-btn {
        position: relative;
        overflow: hidden;
        border: none;
        transition: all 0.4s ease;
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 30%, #0a58ca 70%, #084298 100%);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
        color: white;
        font-weight: 500;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    }

    .enhanced-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image:
            radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.15) 1px, transparent 1px),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.7;
        pointer-events: none;
    }

    .enhanced-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15), 0 6px 6px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 20%, #0a58ca 60%, #084298 100%);
    }

    .enhanced-btn:active {
        transform: translateY(1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
        }
    }

    /* Estilos para los inputs y selects */
    .form-select, .form-control {
        height: 38px;
    }

    .input-group-text {
        height: 38px;
    }

    /* Estilos para la tabla */
    .table-responsive {
        overflow-x: auto;
    }

    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    /* Estilos para el modal de carga masiva */
    .file-upload {
        border: 2px dashed #4a6491;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        background-color: rgba(74, 100, 145, 0.05);
        cursor: pointer;
        transition: all 0.3s;
    }

    .file-upload:hover {
        background-color: rgba(74, 100, 145, 0.1);
        transform: scale(1.01);
    }

    .file-upload i {
        font-size: 3rem;
        color: #4a6491;
        margin-bottom: 15px;
    }

    .preview-table {
        max-height: 300px;
        overflow-y: auto;
    }

    .preview-table th {
        background-color: #2c3e50;
        color: white;
        position: sticky;
        top: 0;
    }

    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(90deg, #2c3e50, #4a6491);
        color: white;
    }

    .modal-title {
        font-weight: 600;
    }

    .modal-footer {
        background-color: #f8f9fa;
    }

    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }

    .step-indicator::before {
        content: "";
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: #dee2e6;
        z-index: 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 1;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .step.active .step-number {
        background-color: #4a6491;
        color: white;
    }

    .step-label {
        font-size: 0.9rem;
        color: #6c757d;
        text-align: center;
    }

    .step.active .step-label {
        color: #2c3e50;
        font-weight: 600;
    }

    .success-icon {
        font-size: 5rem;
        color: #28a745;
        margin: 20px 0;
    }

    /* Estilos para lecturas mensuales */
    .current-reading-input-monthly {
        border: 2px solid #0d6efd;
        border-radius: 5px;
        text-align: center;
        font-weight: bold;
    }

    .current-reading-input-monthly:focus {
        border-color: #0a58ca;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .current-reading-input-monthly.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .table-warning td {
        background-color: rgba(255, 193, 7, 0.2) !important;
    }

    .table-success td {
        background-color: rgba(25, 135, 84, 0.2) !important;
    }

    .badge.fs-6 {
        font-size: 1rem !important;
        padding: 0.5rem 0.75rem;
    }

    /* Animación para transiciones */
    .table tbody tr {
        transition: background-color 0.3s ease;
    }

    /* Toast personalizado */
    .toast {
        margin-bottom: 0.5rem;
    }
</style>
@endpush


@section('content')

    <div class="pagetitle">

      <h1>{{$org->name}}</h1>

      <nav>

        <ol class="breadcrumb">

          <li class="breadcrumb-item"><a href="/">Home</a></li>

          <li class="breadcrumb-item"><a href="{{route('orgs.index')}}">Organizaciones</a></li>

          <li class="breadcrumb-item"><a href="{{route('orgs.dashboard',$org->id)}}">{{$org->name}}</a></li>

          <li class="breadcrumb-item active">Lecturas</li>

        </ol>

      </nav>

    </div><!-- End Page Title -->



    <section class="section dashboard">

    <div class="card top-selling overflow-auto">

        <div class="card-body pt-2">

            <form method="GET" id="filterForm" action="{{ route('orgs.readings.index', $org->id) }}">
                <div class="row g-3 align-items-end">
                    <!-- Periodo Actual al margen izquierdo -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold d-block">Periodo Actual</label>
                        <div class="form-control text-center bg-light fw-bold" style="height:38px;">
                            {{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}-{{ date('Y') }}
                        </div>
                    </div>
                    <!-- Selector de Sector -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Sector</label>
                        <select class="form-select" id="sectorSelect" name="sector">
                            <option value="">Todos</option>
                            @foreach($sectores as $sector)
                                <option value="{{ $sector->id }}" {{ request('sector') == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Botón Facturar -->
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Acción</label>
                        <div class="d-grid">
                            <button type="button" class="btn btn-danger" id="btnFacturar" style="background-color: #dc6868; border-color: #dc6868;">
                                <i class="ri-file-text-line"></i> Facturar
                            </button>
                        </div>
                    </div>
                    <!-- Buscador por nombre, apellido o RUT -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Buscar</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Nombre, Apellido o RUT">
                            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
                        </div>
                    </div>
                    <!-- Botón Exportar -->
                    <div class="col-md-auto d-flex align-items-center ms-2">
                        <a href="#" class="btn btn-primary pulse-btn p-1 px-2 rounded-2 enhanced-btn disabled" tabindex="-1" aria-disabled="true">
                            <i class="bi bi-box-arrow-right me-2"></i>Exportar
                        </a>
                    </div>
                    <!-- Botón Ingreso Masivo -->
                    <div class="col-md-auto d-flex align-items-center ms-2">
                        <button type="button" class="btn btn-primary pulse-btn p-1 px-2 rounded-2 enhanced-btn" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-file-upload me-2"></i>Ingreso Masivo
                        </button>
                    </div>
                </div>

            </form>

            <!-- Sección de Lecturas del Mes Actual -->
            <div class="card mt-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Lecturas del Mes Actual - {{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}/{{ date('Y') }}
                        @if(request('mode', 'current') == 'pending')
                            <span class="badge bg-warning ms-2">Pendientes por Ingresar</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center">Nro Serv.</th>
                                    <th scope="col" class="text-center">Sector</th>
                                    <th scope="col" class="text-center">RUT/RUN</th>
                                    <th scope="col" class="text-center">Nombre/Apellido</th>
                                    <th scope="col" class="text-center">Lectura Anterior</th>
                                    <th scope="col" class="text-center">Lectura Actual</th>
                                    <th scope="col" class="text-center">Estado</th>
                                    <th scope="col" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($lecturasMesActual) && count($lecturasMesActual) > 0)
                                    @foreach($lecturasMesActual as $lectura)
                                    <tr class="{{ $lectura->current_reading ? 'table-success' : 'table-warning' }}">
                                        <td class="text-center fw-bold">{{ str_pad($lectura->service_number ?? $lectura->nro, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td class="text-center">{{ $lectura->sector_name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <a href="{{route('orgs.members.edit',[$org->id,$lectura->member_id ?? $lectura->id])}}">
                                                {{ $lectura->rut }}
                                            </a>
                                        </td>
                                        <td class="text-center">{{ $lectura->full_name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $lectura->previous_reading ?? 0 }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if(!$lectura->current_reading || $lectura->current_reading == 0)
                                            <form method="POST" action="{{ route('orgs.readings.current_reading_update', $org->id) }}"
                                                class="current-reading-form-monthly" 
                                                data-reading-id="{{ $lectura->id ?? 'new' }}"
                                                data-form-id="form-{{ $lectura->member_id }}-{{ date('Y-m') }}">
                                                @csrf
                                                @if($lectura->id)
                                                    <input type="hidden" name="reading_id" value="{{ $lectura->id }}">
                                                @else
                                                    <input type="hidden" name="member_id" value="{{ $lectura->member_id }}">
                                                    <input type="hidden" name="period" value="{{ date('Y-m') }}">
                                                @endif
                                                <div class="input-group input-group-sm">
                                                    <input type="number"
                                                        name="current_reading"
                                                        class="form-control current-reading-input-monthly"
                                                        value="{{ $lectura->current_reading ?? '' }}"
                                                        placeholder="Ingrese lectura"
                                                        min="0"
                                                        step="1"
                                                        data-row-index="{{ $loop->index }}"
                                                        data-previous="{{ $lectura->previous_reading ?? 0 }}"
                                                        data-service-id="{{ $lectura->service_id }}"
                                                        data-member-id="{{ $lectura->member_id }}"
                                                        data-service-number="{{ $lectura->nro ?? 'unknown' }}">
                                                    <button class="btn btn-outline-success btn-sm" type="submit">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                </div>
                                            </form>
                                            @else
                                            <span class="badge bg-success fs-6">{{ $lectura->current_reading }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($lectura->current_reading && $lectura->current_reading > 0)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Registrada
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <!-- Botón DTE -->
                                                @if($lectura->current_reading && $lectura->current_reading > 0)
                                                    <a href="{{ route('orgs.readings.boleta', [$org->id, $lectura->reading_id]) }}" class="btn btn-dark btn-sm me-2" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="Ver DTE">
                                                        <i class="ri-file-2-line"></i> DTE
                                                    </a>
                                                @else
                                                    <a href="#" class="btn btn-dark btn-sm me-2 disabled" tabindex="-1" aria-disabled="true" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="Sin lectura registrada">
                                                        <i class="ri-file-2-line"></i> DTE
                                                    </a>
                                                @endif
                                                <!-- Botón Editar -->
                                                <button class="btn btn-sm btn-success edit-btn"
                                                        data-bs-id="{{ $lectura->id ?? $lectura->member_id }}"
                                                        data-bs-current="{{ $lectura->current_reading ?? 0 }}"
                                                        data-bs-previous="{{ $lectura->previous_reading ?? 0 }}"
                                                        data-bs-corte_reposicion="0"
                                                        data-bs-other="0"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editReadingModal"
                                                        data-bs-placement="top"
                                                        title="Editar">
                                                    <i class="ri-edit-box-line"></i> Editar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mb-0 mt-2">No hay lecturas pendientes para el mes actual</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


    </div>

    </div>

    </section>



    <!-- Modal de Edición de Lectura -->

    <div class="modal fade" id="editReadingModal" tabindex="-1" aria-labelledby="editReadingModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title" id="editReadingModalLabel">Editar Lectura N° <span id="idReadingModal"></span></h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>

                <div class="modal-body">

                    <form method="POST" action="{{ route('orgs.readings.update', $org->id) }}" id="editReadingForm">

                        @csrf

                        @method('POST')



                        <!-- Input para el ID de la lectura -->

                        <input type="hidden" id="reading_id" name="reading_id">



                        <div class="mb-3">

                            <label for="previous_reading" class="form-label">Lectura Anterior</label>

                            <input type="number" class="form-control" id="previous_reading" name="previous_reading" readonly>

                        </div>



                        <div class="mb-3">

                            <label for="current_reading" class="form-label">Lectura Actual</label>

                            <input type="number" class="form-control" id="current_reading" name="current_reading" required>

                        </div>
                        <div class="mb-3">

                            <div class="form-check mb-2">

                                <input

                                    class="form-check-input"

                                    type="checkbox"

                                    id="cargo_corte_reposicion"

                                    name="cargo_corte_reposicion"

                                  >

                                <label class="form-check-label" for="cargo_corte_reposicion">

                                    Cargo Corte Reposición

                                </label>

                            </div>

                        </div>
                        <div class="mb-3">

                            <label for="other" class="form-label">Otros Cargos</label>

                            <input type="number" class="form-control" id="other" name="other" >

                        </div>



                        <div class="mb-3">

                            <button type="submit" class="btn btn-success enhanced-btn">Guardar Cambios</button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>



    <!-- Modal para carga masiva -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header py-3 px-4" style="background: linear-gradient(90deg, #4a6491 0%, #2c3e50 100%); color: #fff;">
                    <h4 class="modal-title fw-bold d-flex align-items-center gap-2" id="uploadModalLabel">
                        <i class="fas fa-file-import fa-lg"></i> Ingreso Masivo de Lecturas
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-3">
                    <div class="step-indicator mb-4 px-2 py-3 rounded-3 bg-white shadow-sm d-flex flex-row align-items-center justify-content-center" style="position:relative;">
                        <div class="step text-center position-relative flex-grow-1" style="min-width:160px;">
                            <div class="step-number d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width:48px;height:48px;font-size:1.5rem;background:#0d6efd;color:#fff;border-radius:50%;box-shadow:0 2px 8px rgba(13,110,253,.15);font-weight:700;">1</div>
                            <div class="step-label fw-semibold" style="font-size:1rem;color:#0d6efd;">Cargar Archivo</div>
                        </div>
                        <div class="step-bar flex-grow-0" style="height:4px;width:60px;background:#dee2e6;margin:0 8px;"></div>
                        <div class="step text-center position-relative flex-grow-1" style="min-width:160px;">
                            <div class="step-number d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width:48px;height:48px;font-size:1.5rem;background:#6c757d;color:#fff;border-radius:50%;box-shadow:0 2px 8px rgba(108,117,125,.15);font-weight:700;">2</div>
                            <div class="step-label fw-semibold" style="font-size:1rem;color:#6c757d;">Verificar Datos</div>
                        </div>
                        <div class="step-bar flex-grow-0" style="height:4px;width:60px;background:#dee2e6;margin:0 8px;"></div>
                        <div class="step text-center position-relative flex-grow-1" style="min-width:160px;">
                            <div class="step-number d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width:48px;height:48px;font-size:1.5rem;background:#28a745;color:#fff;border-radius:50%;box-shadow:0 2px 8px rgba(40,167,69,.15);font-weight:700;">3</div>
                            <div class="step-label fw-semibold" style="font-size:1rem;color:#28a745;">Confirmar</div>
                        </div>
                    </div>

                    <div id="step1">
                        <div class="mb-4 p-3 rounded-3 bg-light border">
                            <h5 class="fw-semibold mb-2"><i class="fas fa-info-circle text-primary me-2"></i>Instrucciones</h5>
                            <ul class="mb-0 ps-3">
                                <li>El archivo debe estar en formato <span class="fw-bold">Excel (.xlsx)</span> o <span class="fw-bold">CSV (.csv)</span></li>
                                <li>Debe contener <span class="fw-bold">únicamente las siguientes columnas</span> en este orden:<br>
                                    <span class="text-primary">numero_servicio, rut, lectura_actual, periodo</span>
                                </li>
                                <li><span class="fw-bold">numero_servicio</span>: Debe coincidir con el número de servicio registrado en el sistema (puede tener ceros a la izquierda).</li>
                                <li><span class="fw-bold">rut</span>: Sin puntos, solo guion antes del dígito verificador (ejemplo: 12345678-9).</li>
                                <li><span class="fw-bold">lectura_actual</span>: Valor numérico correspondiente a la lectura actual del medidor.</li>
                                <li><span class="fw-bold">periodo</span>: Formato Año-Mes (ejemplo: 2025-08).</li>
                                <li>Se validarán todos los datos antes de procesar.</li>
                                <li><a href="{{ asset('storage/templates/plantilla_carga_lecturas_masiva.csv') }}" class="text-primary fw-bold" download><i class="fas fa-download me-1"></i>Descargar plantilla de ejemplo</a></li>
                            </ul>
                        </div>
                        <div class="file-upload mb-4 p-4 rounded-3 border border-primary bg-white d-flex flex-column align-items-center justify-content-center" id="dropZone" style="min-height: 160px; cursor: pointer;">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <h5 class="mb-1 fw-bold">Arrastra tu archivo Excel/CSV aquí</h5>
                            <p class="text-muted mb-2">o haz clic para seleccionar</p>
                            <input type="file" id="fileInput" class="d-none" accept=".xlsx, .xls, .csv">
                        </div>
                        <div class="alert alert-warning d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <span>Ejemplo de formato requerido:</span>
                        </div>
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr class="table-primary">
                                        <th>Numero de Servicio</th>
                                        <th>RUT</th>
                                        <th>Lectura Actual</th>
                                        <th>Período</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>00001</td>
                                        <td>12345678-9</td>
                                        <td>1350</td>
                                        <td>2025-07</td>
                                    </tr>
                                    <tr>
                                        <td>00002</td>
                                        <td>98765432-1</td>
                                        <td>2150</td>
                                        <td>2025-07</td>
                                    </tr>
                                    <tr>
                                        <td>00003</td>
                                        <td>23456789-0</td>
                                        <td>3480</td>
                                        <td>2025-07</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="step2" class="d-none">
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                            <i class="fas fa-check-circle fa-lg"></i>
                            <span>Archivo cargado correctamente. Verifique los datos a continuación.</span>
                        </div>
                        <h5 class="fw-semibold mb-3"><i class="fas fa-eye text-success me-2"></i>Vista previa de archivos:</h5>
                        <div class="preview-table mb-4 rounded-3 border">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr class="table-primary">
                                        <th>RUT</th>
                                        <th>Nombre</th>
                                        <th>Lectura Actual</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>12345678-9</td>
                                        <td>Juan Ejemplo</td>
                                        <td>1350</td>
                                        <td><span class="badge bg-success">Válido</span></td>
                                    </tr>
                                    <tr>
                                        <td>98765432-1</td>
                                        <td>María Ejemplo</td>
                                        <td>2150</td>
                                        <td><span class="badge bg-success">Válido</span></td>
                                    </tr>
                                    <tr>
                                        <td>23456789-0</td>
                                        <td>Carlos Ejemplo</td>
                                        <td>3480</td>
                                        <td><span class="badge bg-success">Válido</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle fa-lg"></i>
                            <span>Se han detectado 3 registros válidos listos para importar.</span>
                        </div>
                    </div>

                    <div id="step3" class="d-none text-center">
                        <i class="fas fa-check-circle success-icon mb-3"></i>
                        <h3 class="mb-3 fw-bold text-success">¡Importación completada con éxito!</h3>
                        <p class="fs-5">Se han registrado <span id="importedCount">0</span> lecturas correctamente.</p>
                        <div class="alert alert-success mt-4 d-inline-block text-start">
                            <i class="fas fa-clipboard-list me-2"></i> <span class="fw-semibold">Resumen:</span>
                            <ul class="mt-2 mb-0 ps-3">
                                <li>Total de registros en archivo: <span id="totalToImport">0</span></li>
                                <li>Registros importados: <span id="importedCount2">0</span></li>
                                <li>Registros con error: <span id="errorCount">0</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary enhanced-btn px-4 py-2" onclick="procesarCargaMasiva()">
                        <i class="fas fa-upload me-2"></i>Procesar Archivo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Selección de Tipo de Documento -->
    <div class="modal fade" id="seleccionDocumentoModal" tabindex="-1" aria-labelledby="seleccionDocumentoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header py-3 px-4" style="background: linear-gradient(90deg, #dc6868 0%, #b84a4a 100%); color: #fff;">
                    <h4 class="modal-title fw-bold d-flex align-items-center gap-2" id="seleccionDocumentoModalLabel">
                        <i class="fas fa-file-invoice fa-lg"></i> Seleccionar Tipo de Documento
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-3">
                    <div class="mb-4 p-3 rounded-3 bg-light border">
                        <h6 class="fw-semibold mb-2"><i class="fas fa-info-circle text-primary me-2"></i>Información</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Período:</strong> {{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</p>
                                <p class="mb-0"><strong>Sector:</strong> <span id="sectorInfoSeleccion">Todos los sectores</span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Lecturas completas:</strong> <span id="lecturasCompletasSeleccion" class="badge bg-success">0</span></p>
                                <p class="mb-0"><strong>Documentos disponibles:</strong> <span id="documentosDisponibles" class="badge bg-primary">0</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Opción Boletas -->
                        <div class="col-12">
                            <div class="card border-success h-100">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input fs-5" type="radio" name="tipoDocumento" id="radioBoletas" value="boletas">
                                        <label class="form-check-label w-100" for="radioBoletas">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="fas fa-receipt fa-2x text-success"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1 text-success fw-bold">Generar Boletas</h5>
                                                    <p class="mb-0 text-muted">Boletas exentas (Código 41) - Sin IVA</p>
                                                    <small class="text-success">✓ Para servicios básicos y exentos</small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-success fs-6" id="contadorBoletas">0</span>
                                                    <br><small class="text-muted">disponibles</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opción Facturas -->
                        <div class="col-12">
                            <div class="card border-primary h-100">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input fs-5" type="radio" name="tipoDocumento" id="radioFacturas" value="facturas">
                                        <label class="form-check-label w-100" for="radioFacturas">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="fas fa-file-invoice fa-2x text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1 text-primary fw-bold">Generar Facturas</h5>
                                                    <p class="mb-0 text-muted">Facturas afectas (Código 33) - Con IVA</p>
                                                    <small class="text-primary">✓ Para servicios comerciales e industriales</small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary fs-6" id="contadorFacturas">0</span>
                                                    <br><small class="text-muted">disponibles</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                            <i class="fas fa-lightbulb"></i>
                            <span>Seleccione el tipo de documento que desea generar. Solo se procesarán los servicios que cumplan con las condiciones específicas de cada tipo.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger enhanced-btn px-4 py-2" id="btnContinuarSeleccion" disabled>
                        <i class="fas fa-arrow-right me-2"></i>Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Generar Boletas -->
    <div class="modal fade" id="facturarModal" tabindex="-1" aria-labelledby="facturarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header py-3 px-4" style="background: linear-gradient(90deg, #28a745 0%, #1e7e34 100%); color: #fff;">
                    <h4 class="modal-title fw-bold d-flex align-items-center gap-2" id="facturarModalLabel">
                        <i class="fas fa-receipt fa-lg"></i> Generar Boletas
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-3">
                    <!-- Información del período y sector -->
                    <div class="mb-4 p-3 rounded-3 bg-light border">
                        <h5 class="fw-semibold mb-2"><i class="fas fa-info-circle text-success me-2"></i>Información de Generación</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Período:</strong> {{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</p>
                                <p class="mb-0"><strong>Sector:</strong> <span id="sectorInfo">Todos los sectores</span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Lecturas completas:</strong> <span id="lecturasCompletasCount" class="badge bg-success fs-6">0</span></p>
                                <p class="mb-0"><strong>Boletas a generar:</strong> <span id="boletasCount" class="badge bg-primary fs-6">0</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de las boletas a generar -->
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>Boletas Exentas (Código 41)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="fw-bold text-success">Características del documento:</h6>
                                    <ul class="list-unstyled mb-3">
                                        <li><i class="fas fa-check text-success me-2"></i>Boletas no afectas a IVA</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Código tributario: 41</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Formato DTE oficial SII</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Solo lecturas con ambos valores registrados</li>
                                    </ul>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h2 class="text-success mb-2" id="boletasCountDisplay">0</h2>
                                        <p class="mb-0 fw-bold">Boletas</p>
                                        <small class="text-muted">a procesar</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info d-flex align-items-center gap-2 mt-3">
                                <i class="fas fa-info-circle"></i>
                                <span>Solo se procesarán los servicios que tengan <strong>lectura anterior</strong> y <strong>lectura actual</strong> registradas.</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-warning d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span><strong>Importante:</strong> Una vez generadas las boletas, no podrán ser modificadas. Verifique que todas las lecturas estén correctas antes de proceder.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-success enhanced-btn px-4 py-2" id="btnProcesarBoletas">
                        <i class="fas fa-cog me-2"></i>Generar <span id="btnBoletasCount">0</span> Boletas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Generar Facturas -->
    <div class="modal fade" id="facturasModal" tabindex="-1" aria-labelledby="facturasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header py-3 px-4" style="background: linear-gradient(90deg, #0d6efd 0%, #0842a0 100%); color: #fff;">
                    <h4 class="modal-title fw-bold d-flex align-items-center gap-2" id="facturasModalLabel">
                        <i class="fas fa-file-invoice fa-lg"></i> Generar Facturas
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 pt-3">
                    <!-- Información del período y sector -->
                    <div class="mb-4 p-3 rounded-3 bg-light border">
                        <h5 class="fw-semibold mb-2"><i class="fas fa-info-circle text-primary me-2"></i>Información de Generación</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Período:</strong> {{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</p>
                                <p class="mb-0"><strong>Sector:</strong> <span id="sectorInfoFacturas">Todos los sectores</span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Lecturas completas:</strong> <span id="lecturasCompletasCountFacturas" class="badge bg-success fs-6">0</span></p>
                                <p class="mb-0"><strong>Facturas a generar:</strong> <span id="facturasCount" class="badge bg-primary fs-6">0</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de las facturas a generar -->
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-file-invoice me-2"></i>Facturas Afectas (Código 33)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="fw-bold text-primary">Características del documento:</h6>
                                    <ul class="list-unstyled mb-3">
                                        <li><i class="fas fa-check text-primary me-2"></i>Facturas afectas a IVA (19%)</li>
                                        <li><i class="fas fa-check text-primary me-2"></i>Código tributario: 33</li>
                                        <li><i class="fas fa-check text-primary me-2"></i>Formato DTE oficial SII</li>
                                        <li><i class="fas fa-check text-primary me-2"></i>Solo servicios con tipo "Factura"</li>
                                    </ul>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h2 class="text-primary mb-2" id="facturasCountDisplay">0</h2>
                                        <p class="mb-0 fw-bold">Facturas</p>
                                        <small class="text-muted">a procesar</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info d-flex align-items-center gap-2 mt-3">
                                <i class="fas fa-info-circle"></i>
                                <span>Solo se procesarán los servicios que tengan <strong>lectura anterior</strong>, <strong>lectura actual</strong> registradas y <strong>tipo de documento = Factura</strong>.</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-warning d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span><strong>Importante:</strong> Una vez generadas las facturas, no podrán ser modificadas. Verifique que todas las lecturas estén correctas antes de proceder.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary enhanced-btn px-4 py-2" id="btnProcesarFacturas">
                        <i class="fas fa-cog me-2"></i>Generar <span id="btnFacturasCount">0</span> Facturas
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection



@section('js')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== INICIANDO APLICACIÓN ===');
    console.log('SweetAlert2 disponible:', typeof Swal !== 'undefined');
    
    // === FILTRO POR SECTOR ===
    const sectorSelect = document.getElementById('sectorSelect');
    if (sectorSelect) {
        sectorSelect.addEventListener('change', function() {
            // Actualizar el estado del botón Facturar cuando cambie el sector
            if (window.actualizarEstadoBoton) {
                window.actualizarEstadoBoton();
            }
            document.getElementById('filterForm').submit();
        });
    }

    // === BOTÓN FACTURAR ===
    const btnFacturar = document.getElementById('btnFacturar');
    console.log('Botón Facturar encontrado:', btnFacturar !== null);
    
    if (btnFacturar) {
        // Función para verificar si todas las lecturas están completadas
        function verificarLecturasCompletas() {
            const inputsLecturas = document.querySelectorAll('.current-reading-input-monthly');
            
            // Si no hay inputs de lectura pendientes, significa que todas están registradas
            return inputsLecturas.length === 0;
        }
        
        // Función para actualizar estado del botón (hacer global)
        window.actualizarEstadoBoton = function() {
            const inputsLecturas = document.querySelectorAll('.current-reading-input-monthly');
            const lecturasCompletas = verificarLecturasCompletas();
            
            // Obtener el sector seleccionado para el título
            const sectorSelect = document.getElementById('sectorSelect');
            const sectorSeleccionado = sectorSelect ? sectorSelect.value : '';
            const nombreSector = sectorSelect && sectorSeleccionado ? 
                sectorSelect.options[sectorSelect.selectedIndex].text : '';
            
            if (lecturasCompletas && inputsLecturas.length === 0) {
                // Todas las lecturas están registradas - Habilitar botón
                btnFacturar.disabled = false;
                btnFacturar.style.backgroundColor = '#dc6868';
                btnFacturar.style.borderColor = '#dc6868';
                btnFacturar.style.opacity = '1';
                const tituloSector = sectorSeleccionado ? ` del sector "${nombreSector}"` : '';
                btnFacturar.title = `Todas las lecturas${tituloSector} están registradas - Click para facturar`;
                console.log('Botón habilitado - Todas las lecturas registradas');
            } else {
                // Hay lecturas pendientes - Mantener habilitado pero con título informativo
                btnFacturar.disabled = false;
                btnFacturar.style.backgroundColor = '#dc6868';
                btnFacturar.style.borderColor = '#dc6868';
                btnFacturar.style.opacity = '1';
                const mensajeSector = sectorSeleccionado ? ` en el sector "${nombreSector}"` : '';
                btnFacturar.title = inputsLecturas.length > 0 ? 
                    `Hay ${inputsLecturas.length} lecturas pendientes${mensajeSector}` : 
                    `No hay lecturas para facturar${mensajeSector}`;
                console.log('Botón disponible -', inputsLecturas.length, 'lecturas pendientes');
            }
        };
        
        // Verificar estado inicial
        window.actualizarEstadoBoton();
        
        // Test inicial de SweetAlert
        console.log('Configurando event listener para btnFacturar');
        
        // TEST SIMPLE: Agregar un evento de test
        btnFacturar.addEventListener('mouseenter', function() {
            console.log('Mouse sobre el botón Facturar');
        });
        
        // Actualizar estado cuando se registran lecturas
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    window.actualizarEstadoBoton();
                }
            });
        });
        
        // Observar cambios en la tabla
        const tablaLecturas = document.querySelector('.table tbody');
        if (tablaLecturas) {
            observer.observe(tablaLecturas, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class']
            });
        }
        
        // Evento click del botón facturar
        btnFacturar.addEventListener('click', function() {
            console.log('=== CLICK EN BOTÓN FACTURAR ===');
            
            // Obtener el sector seleccionado
            const sectorSelect = document.getElementById('sectorSelect');
            const sectorSeleccionado = sectorSelect ? sectorSelect.value : '';
            const nombreSector = sectorSelect && sectorSeleccionado ? 
                sectorSelect.options[sectorSelect.selectedIndex].text : 'Todos los sectores';
            
            // Obtener estado actual de las lecturas - solo las que tienen badge de success (completas)
            const lecturasCompletas = document.querySelectorAll('.badge.bg-success.fs-6');
            
            console.log('Estado actual:', {
                lecturasCompletas: lecturasCompletas.length,
                sectorSeleccionado: sectorSeleccionado,
                nombreSector: nombreSector
            });
            
            // Verificar que hay lecturas completas para facturar
            if (lecturasCompletas.length === 0) {
                const mensajeSector = sectorSeleccionado ? ` para el sector "${nombreSector}"` : ' en este período';
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin Lecturas Completas',
                    html: `
                        <p>No hay lecturas completas para procesar${mensajeSector}.</p>
                        <p class="text-muted">Para generar documentos, se requiere que los servicios tengan tanto la <strong>lectura anterior</strong> como la <strong>lectura actual</strong> registradas.</p>
                    `,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#dc6868'
                });
                return;
            }
            
            // Actualizar información del modal de selección
            document.getElementById('sectorInfoSeleccion').textContent = nombreSector;
            document.getElementById('lecturasCompletasSeleccion').textContent = lecturasCompletas.length;
            document.getElementById('documentosDisponibles').textContent = lecturasCompletas.length;
            
            // Por ahora, simular conteos - en producción estos vendrían del servidor
            document.getElementById('contadorBoletas').textContent = lecturasCompletas.length;
            document.getElementById('contadorFacturas').textContent = Math.floor(lecturasCompletas.length * 0.3); // Simular que 30% son facturas
            
            // Mostrar el modal de selección
            const seleccionModal = new bootstrap.Modal(document.getElementById('seleccionDocumentoModal'));
            seleccionModal.show();
        });
    }

    // ===== CÓDIGO PARA LECTURAS =====

    const currentReadingInputs = document.querySelectorAll('.current-reading-input');



    currentReadingInputs.forEach(input => {

        input.addEventListener('focus', function() {

            this.dataset.originalValue = this.value;

        });



        input.addEventListener('keydown', function(e) {

            if (e.key === 'Enter') {

                e.preventDefault();



                const form = this.closest('form');

                const inputElement = this;



                if (this.value !== this.dataset.originalValue) {

                    const xhr = new XMLHttpRequest();

                    xhr.open('POST', form.action, true);

                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');



                    // Manejar la respuesta

                    xhr.onload = function() {

                        if (xhr.status === 200) {

                            // Bloquear el input actual

                            inputElement.setAttribute('readonly', true);

                            inputElement.classList.add('read-only');

                            inputElement.disabled = true;



                            // Buscar el siguiente input vacío y disponible

                            let found = false;

                            for (let i = 0; i < currentReadingInputs.length; i++) {

                                const nextInput = currentReadingInputs[i];

                                if (!nextInput.disabled && nextInput.value === '') {

                                    nextInput.focus();

                                    nextInput.select();

                                    found = true;

                                    break;

                                }

                            }

                            // Si no hay input vacío, no hacer nada

                        }

                    };



                    // Enviar el formulario

                    const formData = new FormData(form);

                    xhr.send(formData);

                }

            }

        });



        // Restaurar valor original al perder el foco sin cambios

        input.addEventListener('blur', function() {

            if (this.value === '') {

                this.value = this.dataset.originalValue;

            }

        });

    });

    // ===== CÓDIGO PARA LECTURAS MENSUALES =====
    const monthlyReadingInputs = document.querySelectorAll('.current-reading-input-monthly');
    
    monthlyReadingInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.dataset.originalValue = this.value;
        });

        // Validación en tiempo real
        input.addEventListener('input', function() {
            const previousReading = parseInt(this.dataset.previous) || 0;
            const currentValue = parseInt(this.value) || 0;
            
            // Validar que la lectura actual sea mayor o igual a la anterior
            if (currentValue < previousReading && currentValue > 0) {
                this.setCustomValidity('La lectura actual no puede ser menor a la anterior (' + previousReading + ')');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const form = this.closest('form');
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.click();
                }
            }
        });
    });

    // Añadir calculadora de consumo en tiempo real para inputs mensuales
    const monthlyInputs = document.querySelectorAll('.current-reading-input-monthly');
    monthlyInputs.forEach(input => {
        // Crear elemento para mostrar el consumo calculado
        const consumoDisplay = document.createElement('small');
        consumoDisplay.className = 'text-muted ms-2 consumo-preview';
        consumoDisplay.style.display = 'none';
        input.parentNode.appendChild(consumoDisplay);
        
        input.addEventListener('input', function() {
            const previousReading = parseInt(this.dataset.previous) || 0;
            const currentValue = parseInt(this.value) || 0;
            const consumo = currentValue - previousReading;
            
            if (currentValue > 0) {
                consumoDisplay.textContent = `Consumo: ${Math.max(0, consumo)} m³`;
                consumoDisplay.style.display = 'inline';
                
                // Cambiar color según el consumo
                if (consumo < 0) {
                    consumoDisplay.className = 'text-danger ms-2 consumo-preview';
                } else if (consumo === 0) {
                    consumoDisplay.className = 'text-warning ms-2 consumo-preview';
                } else {
                    consumoDisplay.className = 'text-success ms-2 consumo-preview';
                }
            } else {
                consumoDisplay.style.display = 'none';
            }
        });
    });
    
    // Sistema de control de formularios pendientes
    const pendingForms = new Set();
    
    // Manejar envío de formularios de lecturas mensuales
    const monthlyForms = document.querySelectorAll('.current-reading-form-monthly');
    monthlyForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formId = this.dataset.formId;
            const input = this.querySelector('.current-reading-input-monthly');
            const currentValue = parseInt(input.value) || 0;
            const previousReading = parseInt(input.dataset.previous) || 0;
            const serviceNumber = input.dataset.serviceNumber;
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Prevenir envíos múltiples usando el ID único del formulario
            if (pendingForms.has(formId)) {
                console.log(`Formulario ${formId} ya está siendo procesado`);
                return;
            }
            
            // Validar lectura
            if (currentValue <= 0) {
                alert('Por favor ingrese una lectura válida mayor a 0');
                input.focus();
                return;
            }
            
            if (currentValue < previousReading) {
                if (!confirm(`La lectura actual (${currentValue}) es menor a la anterior (${previousReading}). ¿Está seguro de continuar?`)) {
                    input.focus();
                    return;
                }
            }
            
            // Marcar formulario como pendiente
            pendingForms.add(formId);
            
            // Deshabilitar botón y mostrar loading
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            input.disabled = true;
            
            // Obtener datos del formulario
            const formData = new FormData(this);
            
            // Debug para servicio específico
            const serviceId = input.dataset.serviceId;
            const memberId = input.dataset.memberId;
            if (serviceNumber === '216') {
                console.log('DEBUG SERVICIO 216 - Enviando formulario:', {
                    formData: Object.fromEntries(formData),
                    serviceId: serviceId,
                    memberId: memberId,
                    serviceNumber: serviceNumber,
                    currentValue: currentValue,
                    formId: formId,
                    action: this.action
                });
            }
            
            // Función para limpiar el estado del formulario
            const resetFormState = () => {
                pendingForms.delete(formId);
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-check"></i>';
                input.disabled = false;
            };
            
            // Enviar formulario vía AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', this.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                try {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Éxito: actualizar la interfaz
                            const row = input.closest('tr');
                            if (row) {
                                row.classList.remove('table-warning');
                                row.classList.add('table-success');
                                
                                // Reemplazar el input con un badge
                                const td = input.closest('td');
                                td.innerHTML = `<span class="badge bg-success fs-6">${currentValue}</span>`;
                                
                                // Actualizar el estado
                                const statusCell = row.querySelector('td:last-child');
                                if (statusCell) {
                                    statusCell.innerHTML = `
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Registrada
                                        </span>
                                    `;
                                }
                            }
                            
                            // Limpiar estado del formulario
                            pendingForms.delete(formId);
                            
                            // Mostrar mensaje de éxito
                            showToast('Lectura registrada correctamente', 'success');
                            
                            // Actualizar estado del botón facturar
                            if (window.actualizarEstadoBoton) {
                                window.actualizarEstadoBoton();
                            }
                        } else {
                            resetFormState();
                            showToast(response.message || 'Error al registrar la lectura', 'error');
                        }
                    } else {
                        resetFormState();
                        showToast('Error de conexión. Intente nuevamente.', 'error');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e, xhr.responseText);
                    resetFormState();
                    showToast('Error inesperado. Intente nuevamente.', 'error');
                }
            };
            
            xhr.onerror = function() {
                resetFormState();
                showToast('Error de conexión. Verifique su conexión a internet.', 'error');
            };
            
            xhr.ontimeout = function() {
                resetFormState();
                showToast('Tiempo de espera agotado. Intente nuevamente.', 'error');
            };
            
            // Establecer timeout de 30 segundos
            xhr.timeout = 30000;
            
            xhr.send(formData);
        });
    });

    // Función para mostrar notificaciones toast
    function showToast(message, type = 'info') {
        // Crear elemento toast
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        // Agregar al container de toasts
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        
        // Mostrar toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Eliminar después de 5 segundos
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }



    // ===== CÓDIGO PARA MODAL DE EDICIÓN =====

    const editModal = document.getElementById('editReadingModal');

    if (editModal) {

        const editModalInstance = new bootstrap.Modal(editModal);



        editModal.addEventListener('show.bs.modal', function(event) {

            const button = event.relatedTarget;

            const readingId = button.getAttribute('data-bs-id');

            const currentReading = button.getAttribute('data-bs-current');

            const previousReading = button.getAttribute('data-bs-previous');

            const corte_reposicion = button.getAttribute('data-bs-corte_reposicion') || 0;

            const other = button.getAttribute('data-bs-other');



            document.getElementById('cargo_corte_reposicion').checked = parseInt(corte_reposicion) > 0;

            document.getElementById('other').value = other;

            document.getElementById('idReadingModal').textContent = readingId;

            document.getElementById('reading_id').value = readingId;

            document.getElementById('current_reading').value = currentReading;

            document.getElementById('previous_reading').value = previousReading;

        });

    }

    // ===== CÓDIGO PARA MODAL DE SELECCIÓN DE DOCUMENTOS =====
    const seleccionDocumentoModal = document.getElementById('seleccionDocumentoModal');
    if (seleccionDocumentoModal) {
        const radioBoletas = document.getElementById('radioBoletas');
        const radioFacturas = document.getElementById('radioFacturas');
        const btnContinuarSeleccion = document.getElementById('btnContinuarSeleccion');
        
        // Manejar cambios en los radio buttons
        function actualizarBotonContinuar() {
            const tipoSeleccionado = document.querySelector('input[name="tipoDocumento"]:checked');
            btnContinuarSeleccion.disabled = !tipoSeleccionado;
            
            if (tipoSeleccionado) {
                btnContinuarSeleccion.classList.remove('disabled');
                btnContinuarSeleccion.innerHTML = `<i class="fas fa-arrow-right me-2"></i>Continuar con ${tipoSeleccionado.value === 'boletas' ? 'Boletas' : 'Facturas'}`;
            } else {
                btnContinuarSeleccion.classList.add('disabled');
                btnContinuarSeleccion.innerHTML = '<i class="fas fa-arrow-right me-2"></i>Continuar';
            }
        }
        
        radioBoletas.addEventListener('change', actualizarBotonContinuar);
        radioFacturas.addEventListener('change', actualizarBotonContinuar);
        
        // Manejar click del botón continuar
        btnContinuarSeleccion.addEventListener('click', function() {
            const tipoSeleccionado = document.querySelector('input[name="tipoDocumento"]:checked');
            if (!tipoSeleccionado) return;
            
            // Cerrar modal de selección
            const modalInstance = bootstrap.Modal.getInstance(seleccionDocumentoModal);
            modalInstance.hide();
            
            // Obtener información común
            const sectorInfo = document.getElementById('sectorInfoSeleccion').textContent;
            const lecturasCompletas = parseInt(document.getElementById('lecturasCompletasSeleccion').textContent);
            
            // Abrir el modal correspondiente
            if (tipoSeleccionado.value === 'boletas') {
                // Actualizar información del modal de boletas
                document.getElementById('sectorInfo').textContent = sectorInfo;
                
                // Mostrar modal de boletas
                setTimeout(() => {
                    const boletasModal = new bootstrap.Modal(document.getElementById('facturarModal'));
                    boletasModal.show();
                }, 300);
                
            } else if (tipoSeleccionado.value === 'facturas') {
                // Verificar si hay facturas disponibles
                const facturasDisponibles = parseInt(document.getElementById('contadorFacturas').textContent);
                
                if (facturasDisponibles === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin Facturas Disponibles',
                        html: `
                            <p>No hay servicios configurados para generar facturas en este período.</p>
                            <div class="alert alert-info text-start mt-3">
                                <strong>Para generar facturas se requiere:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Lecturas completas (anterior y actual)</li>
                                    <li>Tipo de documento = "Factura"</li>
                                    <li>Servicio habilitado para facturación</li>
                                </ul>
                            </div>
                        `,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }
                
                // Actualizar información del modal de facturas
                document.getElementById('sectorInfoFacturas').textContent = sectorInfo;
                
                // Mostrar modal de facturas
                setTimeout(() => {
                    const facturasModal = new bootstrap.Modal(document.getElementById('facturasModal'));
                    facturasModal.show();
                }, 300);
            }
        });
        
        // Limpiar selección al cerrar modal
        seleccionDocumentoModal.addEventListener('hidden.bs.modal', function() {
            radioBoletas.checked = false;
            radioFacturas.checked = false;
            actualizarBotonContinuar();
        });
    }



    // ===== CÓDIGO PARA MODAL DE BOLETAS =====
    const facturarModal = document.getElementById('facturarModal');
    if (facturarModal) {
        const btnProcesarBoletas = document.getElementById('btnProcesarBoletas');
        
        // Función para contar lecturas completas (con lectura anterior y actual)
        function contarLecturasCompletas() {
            // Contar las filas que tienen badge bg-success (lecturas registradas)
            const lecturasRegistradas = document.querySelectorAll('.badge.bg-success.fs-6');
            return lecturasRegistradas.length;
        }
        
        // Actualizar contadores del modal cuando se abre
        facturarModal.addEventListener('show.bs.modal', function() {
            const lecturasCompletas = contarLecturasCompletas();
            
            // Actualizar todos los elementos que muestran el conteo
            document.getElementById('lecturasCompletasCount').textContent = lecturasCompletas;
            document.getElementById('boletasCount').textContent = lecturasCompletas;
            document.getElementById('boletasCountDisplay').textContent = lecturasCompletas;
            document.getElementById('btnBoletasCount').textContent = lecturasCompletas;
            
            console.log('Modal abierto - Lecturas completas encontradas:', lecturasCompletas);
            
            // Habilitar/deshabilitar botón según si hay lecturas para procesar
            if (lecturasCompletas > 0) {
                btnProcesarBoletas.disabled = false;
                btnProcesarBoletas.classList.remove('disabled');
            } else {
                btnProcesarBoletas.disabled = true;
                btnProcesarBoletas.classList.add('disabled');
            }
        });
        
        // Manejar click del botón procesar boletas
        btnProcesarBoletas.addEventListener('click', function() {
            if (this.disabled) return;
            
            const lecturasCompletas = contarLecturasCompletas();
            const sectorInfo = document.getElementById('sectorInfo').textContent;
            
            console.log('=== INICIANDO PROCESO DE BOLETAS ===');
            console.log('Lecturas completas:', lecturasCompletas);
            console.log('Sector:', sectorInfo);
            
            // Confirmar procesamiento
            Swal.fire({
                title: '¿Confirmar Generación de Boletas?',
                html: `
                    <div class="text-start">
                        <p><strong>Se generarán ${lecturasCompletas} boletas exentas</strong></p>
                        <p><strong>Período:</strong> {{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</p>
                        <p><strong>Sector:</strong> ${sectorInfo}</p>
                        <p><strong>Código tributario:</strong> 41 (Boletas exentas)</p>
                        <hr>
                        <p class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Una vez generadas, las boletas no podrán ser modificadas.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check me-2"></i>Sí, Generar Boletas',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Cancelar',
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Cerrar modal de confirmación y procesar
                    procesarGeneracionBoletas(lecturasCompletas, sectorInfo);
                }
            });
        });
        
        // Función para procesar la generación de boletas
        function procesarGeneracionBoletas(cantidad, sector) {
            console.log('Procesando generación de boletas...');
            
            // Mostrar indicador de procesamiento
            Swal.fire({
                title: 'Generando Boletas...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-success mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Procesando ${cantidad} boletas exentas</p>
                        <p class="text-muted">Por favor espere...</p>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    // Aquí iría la llamada AJAX al controlador
                    // Por ahora simulamos el proceso
                    setTimeout(() => {
                        // Cerrar modal de procesamiento
                        Swal.close();
                        
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Proceso Completo!',
                            html: `
                                <div class="text-center">
                                    <h5 class="text-success mb-3">Boletas generadas exitosamente</h5>
                                    <div class="alert alert-success text-start">
                                        <strong>Resumen del proceso:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Boletas generadas: <strong>${cantidad}</strong></li>
                                            <li>Código tributario: <strong>41 (Exentas)</strong></li>
                                            <li>Sector: <strong>${sector}</strong></li>
                                            <li>Período: <strong>{{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            `,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#28a745',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                            // Cerrar el modal principal después del mensaje de éxito
                            const modalInstance = bootstrap.Modal.getInstance(facturarModal);
                            modalInstance.hide();
                            
                            // Recargar la página para actualizar el estado
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        });
                    }, 2500); // Simular 2.5 segundos de procesamiento
                }
            });
        }
        
        // Limpiar estado al cerrar modal
        facturarModal.addEventListener('hidden.bs.modal', function() {
            console.log('Modal de boletas cerrado');
        });
    }

    // ===== CÓDIGO PARA MODAL DE FACTURAS =====
    const facturasModal = document.getElementById('facturasModal');
    if (facturasModal) {
        const btnProcesarFacturas = document.getElementById('btnProcesarFacturas');
        
        // Función para contar facturas disponibles (lecturas completas + tipo factura)
        function contarFacturasDisponibles() {
            // Por ahora simular que 30% de las lecturas completas son facturas
            // En producción esto vendría del servidor basado en el tipo de servicio
            const lecturasRegistradas = document.querySelectorAll('.badge.bg-success.fs-6');
            return Math.floor(lecturasRegistradas.length * 0.3);
        }
        
        // Actualizar contadores del modal cuando se abre
        facturasModal.addEventListener('show.bs.modal', function() {
            const facturasDisponibles = contarFacturasDisponibles();
            const lecturasCompletas = document.querySelectorAll('.badge.bg-success.fs-6').length;
            
            // Actualizar todos los elementos que muestran el conteo
            document.getElementById('lecturasCompletasCountFacturas').textContent = lecturasCompletas;
            document.getElementById('facturasCount').textContent = facturasDisponibles;
            document.getElementById('facturasCountDisplay').textContent = facturasDisponibles;
            document.getElementById('btnFacturasCount').textContent = facturasDisponibles;
            
            console.log('Modal de facturas abierto - Facturas disponibles:', facturasDisponibles);
            
            // Habilitar/deshabilitar botón según si hay facturas para procesar
            if (facturasDisponibles > 0) {
                btnProcesarFacturas.disabled = false;
                btnProcesarFacturas.classList.remove('disabled');
            } else {
                btnProcesarFacturas.disabled = true;
                btnProcesarFacturas.classList.add('disabled');
            }
        });
        
        // Manejar click del botón procesar facturas
        btnProcesarFacturas.addEventListener('click', function() {
            if (this.disabled) return;
            
            const facturasDisponibles = contarFacturasDisponibles();
            const sectorInfo = document.getElementById('sectorInfoFacturas').textContent;
            
            console.log('=== INICIANDO PROCESO DE FACTURAS ===');
            console.log('Facturas disponibles:', facturasDisponibles);
            console.log('Sector:', sectorInfo);
            
            // Confirmar procesamiento
            Swal.fire({
                title: '¿Confirmar Generación de Facturas?',
                html: `
                    <div class="text-start">
                        <p><strong>Se generarán ${facturasDisponibles} facturas afectas</strong></p>
                        <p><strong>Período:</strong> {{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</p>
                        <p><strong>Sector:</strong> ${sectorInfo}</p>
                        <p><strong>Código tributario:</strong> 33 (Facturas afectas)</p>
                        <p><strong>IVA:</strong> 19% incluido</p>
                        <hr>
                        <p class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Una vez generadas, las facturas no podrán ser modificadas.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check me-2"></i>Sí, Generar Facturas',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Cancelar',
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Cerrar modal de confirmación y procesar
                    procesarGeneracionFacturas(facturasDisponibles, sectorInfo);
                }
            });
        });
        
        // Función para procesar la generación de facturas
        function procesarGeneracionFacturas(cantidad, sector) {
            console.log('Procesando generación de facturas...');
            
            // Mostrar indicador de procesamiento
            Swal.fire({
                title: 'Generando Facturas...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Procesando ${cantidad} facturas afectas</p>
                        <p class="text-muted">Calculando IVA y totales...</p>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    // Aquí iría la llamada AJAX al controlador
                    // Por ahora simulamos el proceso
                    setTimeout(() => {
                        // Cerrar modal de procesamiento
                        Swal.close();
                        
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Proceso Completo!',
                            html: `
                                <div class="text-center">
                                    <h5 class="text-primary mb-3">Facturas generadas exitosamente</h5>
                                    <div class="alert alert-success text-start">
                                        <strong>Resumen del proceso:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Facturas generadas: <strong>${cantidad}</strong></li>
                                            <li>Código tributario: <strong>33 (Afectas)</strong></li>
                                            <li>IVA aplicado: <strong>19%</strong></li>
                                            <li>Sector: <strong>${sector}</strong></li>
                                            <li>Período: <strong>{{ str_pad(date('m'), 2, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            `,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#0d6efd',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                            // Cerrar el modal principal después del mensaje de éxito
                            const modalInstance = bootstrap.Modal.getInstance(facturasModal);
                            modalInstance.hide();
                            
                            // Recargar la página para actualizar el estado
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        });
                    }, 2500); // Simular 2.5 segundos de procesamiento
                }
            });
        }
        
        // Limpiar estado al cerrar modal
        facturasModal.addEventListener('hidden.bs.modal', function() {
            console.log('Modal de facturas cerrado');
        });
    }

    // ===== CÓDIGO PARA MODAL DE CARGA MASIVA (EXISTENTE) =====
    const uploadModal = document.getElementById('uploadModal');

    if (uploadModal) {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        let selectedFile = null;

        // Función para mostrar el estado del archivo seleccionado
        function showFileStatus(file, isValid = true) {
            dropZone.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-file-${isValid ? 'check' : 'times'} fa-3x ${isValid ? 'text-success' : 'text-danger'} mb-3"></i>
                    <h5 class="mb-1">${file.name}</h5>
                    <p class="text-muted mb-2">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    ${isValid ? '<p class="text-success">Archivo válido</p>' : '<p class="text-danger">Formato no válido</p>'}
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetDropZone()">
                        <i class="fas fa-times"></i> Cambiar archivo
                    </button>
                </div>
            `;
        }

        // Función para resetear el drop zone
        window.resetDropZone = function() {
            selectedFile = null;
            fileInput.value = '';
            dropZone.innerHTML = `
                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                <h5 class="mb-1 fw-bold">Arrastra tu archivo Excel/CSV aquí</h5>
                <p class="text-muted mb-2">o haz clic para seleccionar</p>
            `;
        };

        // Función para validar el archivo
        function validateFile(file) {
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                                  'application/vnd.ms-excel', 'text/csv'];
            const allowedExtensions = ['xlsx', 'xls', 'csv'];
            const extension = file.name.split('.').pop().toLowerCase();
            
            return allowedTypes.includes(file.type) || allowedExtensions.includes(extension);
        }

        // Función para procesar el archivo
        function handleFile(file) {
            if (!validateFile(file)) {
                showFileStatus(file, false);
                Swal.fire({
                    icon: 'error',
                    title: 'Formato no válido',
                    text: 'Por favor selecciona un archivo Excel (.xlsx, .xls) o CSV (.csv)'
                });
                return;
            }

            selectedFile = file;
            showFileStatus(file, true);
        }

        // Eventos para drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight() {
            dropZone.classList.add('bg-light');
        }

        function unhighlight() {
            dropZone.classList.remove('bg-light');
        }

        // Click en dropZone
        dropZone.addEventListener('click', function() {
            fileInput.click();
        });

        // Cambio en input file
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        // Drop de archivo
        dropZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        // Función para procesar la carga masiva
        window.procesarCargaMasiva = function() {
            if (!selectedFile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivo requerido',
                    text: 'Por favor selecciona un archivo Excel o CSV para continuar.'
                });
                return;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Procesando archivo...',
                text: 'Por favor espera mientras procesamos tu archivo.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Crear FormData para enviar el archivo
            const formData = new FormData();
            formData.append('file', selectedFile);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Enviar archivo al servidor
            fetch(`{{ route('orgs.readings.mass_upload', $org->id) }}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Carga completada!',
                        html: `
                            <div class="text-start">
                                <p><strong>Registros procesados:</strong> ${data.stats.processed}</p>
                                ${data.stats.errors > 0 ? `<p class="text-warning"><strong>Errores:</strong> ${data.stats.errors}</p>` : ''}
                                ${data.stats.skipped > 0 ? `<p class="text-muted"><strong>Omitidos:</strong> ${data.stats.skipped}</p>` : ''}
                            </div>
                        `,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Cerrar modal y recargar página
                        const modal = bootstrap.Modal.getInstance(uploadModal);
                        modal.hide();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en la carga',
                        text: data.message || 'Ocurrió un error durante la carga del archivo.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo procesar el archivo. Por favor intenta nuevamente.'
                });
            });
        };

        // Resetear modal cuando se cierre
        uploadModal.addEventListener('hidden.bs.modal', function() {
            window.resetDropZone();
        });
    }

});

</script>

@endsection
