@extends('layouts.nice', ['active'=>'orgs.notifications.index', 'title'=>'Notificaciones'])

@section('content')
    <div class="pagetitle">
        <h1>{{$org->name}}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="{{route('orgs.index')}}">Organizaciones</a></li>
                <li class="breadcrumb-item"><a href="{{route('orgs.dashboard',$org->id)}}">{{$org->name}}</a></li>
                <li class="breadcrumb-item active">Notificaciones</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <!-- DEBUG INFO -->
        <div class="alert alert-info mb-3">
            <strong>Debug:</strong> Org ID: {{ $org->id }} | 
            Notificaciones para esta org: {{ $stats['total'] }} |
            Variable notifications: {{ $notifications->count() }} registros |
            Tipo: {{ gettype($notifications) }}
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0 text-white">Total</h6>
                                <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                            </div>
                            <i class="bi bi-bell fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0 text-white">Enviadas</h6>
                                <h3 class="mb-0">{{ $stats['enviadas'] ?? 0 }}</h3>
                            </div>
                            <i class="bi bi-check-circle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0 text-white">Pendientes</h6>
                                <h3 class="mb-0">{{ $stats['pendientes'] ?? 0 }}</h3>
                            </div>
                            <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0 text-white">Fallidas</h6>
                                <h3 class="mb-0">{{ $stats['fallidas'] ?? 0 }}</h3>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de selección y envío -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Enviar Notificación</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#notificationForm">
                        <i class="bi bi-chevron-up"></i>
                    </button>
                </div>
                
                <div class="collapse show" id="notificationForm">
                    <form action="{{ route('orgs.notifications.store', ['id' => $org->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notification_title" class="form-label">Título de la notificación:</label>
                                <input type="text" id="notification_title" name="title" class="form-control" required placeholder="Ingrese un título descriptivo">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notification_message" class="form-label">Mensaje:</label>
                                <textarea id="notification_message" name="message" class="form-control" rows="4" required placeholder="Escriba el contenido detallado de la notificación..."></textarea>
                                <div class="form-text">Describe el contenido de la notificación detalladamente.</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="send_to_all" name="send_to_all">
                                    <label class="form-check-label" for="send_to_all">
                                        Enviar a todos los usuarios
                                    </label>
                                </div>
                                
                                <label for="sectors" class="form-label">Sectores destinatarios:</label>
                                <select id="sectors" name="sectors[]" class="form-select" multiple>
                                    @foreach($activeLocations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Selecciona los sectores a los que enviar la notificación (mantén Ctrl para seleccionar múltiples).</div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" onclick="showSendingMessage()">
                                <i class="bi bi-send"></i> Enviar Notificación
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Listado de notificaciones -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Listado de Notificaciones</h5>
                    <div>
                        <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                            <i class="bi bi-filter"></i> Filtrar
                        </button>
                        <a href="#" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Nueva Notificación
                        </a>
                    </div>
                </div>

                <!-- Buscador -->
                <div class="row mb-3">
                    <div class="col-md-6 offset-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar notificaciones...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros colapsables -->
                <div class="collapse mb-3" id="collapseFilters">
                    <div class="card card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Fecha desde</label>
                                <input type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha hasta</label>
                                <input type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="sent">Enviados</option>
                                    <option value="pending">Pendientes</option>
                                    <option value="failed">Fallidos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Método de envío</label>
                                <select class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="app">Aplicación</option>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button class="btn btn-sm btn-outline-secondary me-2">Limpiar</button>
                                <button class="btn btn-sm btn-secondary">Aplicar filtros</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <!-- DEBUG TABLA -->
                    @if($notifications->count() > 0)
                        <div class="alert alert-warning">
                            <strong>Debug Tabla:</strong> Se encontraron {{ $notifications->count() }} notificaciones para mostrar.
                            <br>Primera notificación: ID={{ $notifications->first()->id }}, Título="{{ $notifications->first()->title }}"
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <strong>Debug Tabla:</strong> No se encontraron notificaciones para mostrar (variable $notifications está vacía)
                        </div>
                    @endif
                    
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Título</th>
                                <th>Mensaje</th>
                                <th>Destinatarios</th>
                                <th>Métodos</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $index => $notification)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $notification->title }}</td>
                                <td title="{{ $notification->message }}">
                                    {{ Str::limit($notification->message, 50) }}
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $notification->recipient_name }}</span>
                                    <br><small class="text-muted">{{ $notification->recipient_email }}</small>
                                </td>
                                <td>
                                    @if($notification->send_method == 'email')
                                        <span class="badge rounded-pill bg-info text-white">
                                            <i class="bi bi-envelope me-1"></i>Email
                                        </span>
                                    @elseif($notification->send_method == 'app')
                                        <span class="badge rounded-pill bg-light text-dark border">
                                            <i class="bi bi-app me-1"></i>App
                                        </span>
                                    @elseif($notification->send_method == 'sms')
                                        <span class="badge rounded-pill bg-warning text-dark">
                                            <i class="bi bi-chat-text me-1"></i>SMS
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($notification->email_status == 'sent')
                                        <span class="badge bg-success">Enviado</span>
                                    @elseif($notification->email_status == 'failed')
                                        <span class="badge bg-danger">Fallido</span>
                                    @elseif($notification->email_status == 'pending')
                                        <span class="badge bg-warning">Pendiente</span>
                                    @else
                                        <span class="badge bg-secondary">Desconocido</span>
                                    @endif
                                </td>
                                <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Acciones
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="showNotificationDetails({{ $notification->id }})"><i class="bi bi-eye"></i> Ver detalles</a></li>
                                            @if($notification->email_status == 'failed')
                                                <li><a class="dropdown-item" href="#"><i class="bi bi-arrow-repeat"></i> Reenviar</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No hay notificaciones registradas
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-end">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Anterior</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    /* ESTILOS PARA SELECT DESHABILITADO */
    select:disabled {
        background-color: #e9ecef;
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    /* Estilos generales */
    .badge {
        font-weight: 500;
    }
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.75rem;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-title {
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del formulario
        const sendToAllCheckbox = document.getElementById('send_to_all');
        const sectorsSelect = document.getElementById('sectors');

        // Función para bloquear/desbloquear los sectores
        function toggleSectors() {
            if (sendToAllCheckbox.checked) {
                // 1. Deshabilitar el elemento select
                sectorsSelect.disabled = true;
                
                // 2. Limpiar las selecciones existentes
                sectorsSelect.selectedIndex = -1;
                Array.from(sectorsSelect.options).forEach(option => {
                    option.selected = false;
                });
            } else {
                // Habilitar el select si no está marcado "Enviar a todos"
                sectorsSelect.disabled = false;
            }
        }

        // Aplicar el estado inicial
        toggleSectors();

        // Escuchar cambios en el checkbox
        sendToAllCheckbox.addEventListener('change', toggleSectors);

        // Configuración de tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Función para mostrar detalles de notificación
    function showNotificationDetails(notificationId) {
        // Aquí puedes implementar la lógica para mostrar detalles
        // Por ejemplo, usando un modal o redirigiendo a una página de detalles
        console.log('Mostrando detalles de notificación ID:', notificationId);
    }
    
    // Función para mostrar mensaje al enviar
    function showSendingMessage() {
        console.log('Enviando notificación...');
        // Opcional: agregar un loader o mensaje
    }
</script>
@endpush
