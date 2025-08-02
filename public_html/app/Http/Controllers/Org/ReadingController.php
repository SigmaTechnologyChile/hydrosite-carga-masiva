<?php
namespace App\Http\Controllers\Org;

use App\Exports\ReadingsExport;
use App\Exports\ReadingsHistoryExport;
use App\Http\Controllers\Controller;
use App\Models\FixedCostConfig;
use App\Models\Location;
use App\Models\Member;
use App\Models\Org;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\Reading;
use App\Models\Section;
use App\Models\Service;
use App\Models\TierConfig;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReadingController extends Controller
{
    protected $_param;

    public function __construct()
    {
        $this->middleware('auth');
        // No accedas a parámetros de ruta aquí. Hazlo en los métodos que lo requieran.
    }

    /**
     * Show the application dashboard.
     * @param int $org_id
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
public function index($org_id, Request $request)
{
    $org = Org::find($org_id);
    if (! $org) {
        return redirect()->back()->with('error', 'Organización no encontrada.');
    }

    $locations = DB::table('locations')
        ->where('org_id', $org_id)
        ->orderBy('order_by', 'ASC')
        ->get();

    // Obtener los sectores para el selector
    $sectores = $locations;

    $sector = $request->input('sector');
    $search = $request->input('search');
    $mode = $request->input('mode', 'current'); // Modo por defecto: current
    $year   = $request->input('year');
    $month  = $request->input('month');

    // Log para debug del modo
    Log::info("Modo de vista seleccionado: {$mode}", [
        'sector' => $sector,
        'search' => $search,
        'request_all' => $request->all()
    ]);

    // Si no se especifica año/mes, usar el actual
    if (!$year || !$month) {
        $year = date('Y');
        $month = date('m');
    }
    if ($month && strlen($month) == 1) {
        $month = '0' . $month;
    }

    $currentPeriod = date('Y-m'); // Período actual
    $previousMonth = date('Y-m', strtotime('-1 month')); // Mes anterior

    $period = null;
    if ($year && $month) {
        $period = "$year-$month";
    }

    // Variable para lecturas del mes actual (para modos current y pending)
    $lecturasMesActual = null;

    // Si el modo es 'current' o 'pending', obtener lecturas del mes actual
    if ($mode === 'current' || $mode === 'pending') {
        // Obtener todos los servicios de la organización primero
        $serviciosBase = DB::table('services')
            ->join('members', 'services.member_id', '=', 'members.id')
            ->leftJoin('locations', 'services.locality_id', '=', 'locations.id')
            ->where('services.org_id', $org_id);

        // Aplicar filtros de sector y búsqueda
        if ($sector && $sector != '0') {
            $serviciosBase->where('services.locality_id', $sector);
        }

        if ($search) {
            $serviciosBase->where(function ($q) use ($search) {
                $q->where('members.rut', 'like', "%{$search}%")
                  ->orWhere('members.full_name', 'like', "%{$search}%");
            });
        }

        $servicios = $serviciosBase->select(
            'services.id as service_id',
            'services.nro as service_number',
            'members.id as member_id',
            'members.rut',
            'members.full_name',
            'locations.name as sector_name'
        )
        ->where('services.nro', '!=', 216) // Excluir servicio 216
        ->orderBy('services.nro', 'asc')
        ->get();

        // Ahora obtener las lecturas para cada servicio
        $lecturasMesActual = collect();
        
        foreach ($servicios as $servicio) {
            // Obtener lectura actual (solo la más reciente si hay múltiples)
            $currentReading = DB::table('readings')
                ->where('service_id', $servicio->service_id)
                ->where('period', $currentPeriod)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            // Obtener lectura anterior
            $prevReading = DB::table('readings')
                ->where('service_id', $servicio->service_id)
                ->where('period', $previousMonth)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            // Si el modo es 'pending', solo incluir servicios sin lectura actual
            if ($mode === 'pending' && $currentReading) {
                continue;
            }

            // Crear objeto resultado
            $resultado = (object) [
                'id' => $currentReading ? $currentReading->id : null, // Agregar campo 'id' para compatibilidad con la vista
                'service_id' => $servicio->service_id,
                'service_number' => $servicio->service_number,
                'member_id' => $servicio->member_id,
                'rut' => $servicio->rut,
                'full_name' => $servicio->full_name,
                'sector_name' => $servicio->sector_name,
                'current_reading' => $currentReading ? $currentReading->current_reading : null,
                'previous_reading' => $prevReading ? $prevReading->current_reading : null,
                'reading_id' => $currentReading ? $currentReading->id : null
            ];

            $lecturasMesActual->push($resultado);
        }
    }

    // Para el historial de lecturas (modo 'all' o cuando no se muestra la sección mensual)
    $readings = null;
    if ($mode === 'all' || $mode === 'current') {
        $query = DB::table('readings')
            ->join('services', 'readings.service_id', '=', 'services.id')
            ->join('members', 'services.member_id', '=', 'members.id')
            ->leftJoin('locations', 'services.locality_id', '=', 'locations.id')
            ->where('readings.org_id', $org_id)
            ->where('services.nro', '!=', 216); // Excluir servicio 216

        if ($sector && $sector != '0') {
            $query->where('services.locality_id', $sector);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('members.rut', 'like', "%{$search}%")
                  ->orWhere('members.full_name', 'like', "%{$search}%");
            });
        }

        if ($period) {
            $query->where('readings.period', 'like', $period . '%');
        }

        $readings = $query->select(
                'readings.id',
                'readings.org_id',
                'readings.period',
                'readings.cm3',
                'readings.invoice_type',
                'readings.vc_water',
                'readings.v_subs',
                'readings.total',
                'readings.payment_status',
                'services.nro',
                'services.member_id',
                'services.id as service_id',
                'members.id as member_id',
                'members.rut',
                'members.full_name',
                'readings.corte_reposicion',
                'readings.other',
                'locations.name as location_name',
                'readings.current_reading',
                'readings.previous_reading',
                DB::raw('(
                    SELECT r2.current_reading
                    FROM readings r2
                    WHERE r2.service_id = readings.service_id
                      AND r2.period < readings.period
                    ORDER BY r2.period DESC
                    LIMIT 1
                ) as previous_month_reading')
            )
            ->orderBy('readings.period', 'desc')
            ->paginate(20);
    }

    // Obtener los sectores para el selector
    $sectores = DB::table('locations')
        ->where('org_id', $org->id)
        ->orderBy('order_by', 'ASC')
        ->get();

    return view('orgs.readings.index', compact('org', 'readings', 'locations', 'sectores', 'lecturasMesActual'));
}

    public function history($org_id, Request $request)
    {
        $org = Org::find($org_id);
        if (! $org) {
            return redirect()->back()->with('error', 'Organización no encontrada.');
        }

        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $sector     = $request->input('sector');
        $search     = $request->input('search');

        if ($start_date) {
            $start_date = \Carbon\Carbon::createFromFormat('Y-m-d', $start_date)->format('Y-m');
        }
        if ($end_date) {
            $end_date = \Carbon\Carbon::createFromFormat('Y-m-d', $end_date)->format('Y-m');
        }

        $readings = Reading::join('services', 'readings.service_id', 'services.id')
            ->join('members', 'services.member_id', 'members.id')
            ->where('readings.org_id', $org_id)
            ->when($start_date && $end_date, function ($q) use ($start_date, $end_date) {
                $q->where('readings.period', '>=', $start_date)
                    ->where('readings.period', '<=', $end_date);
            })
            ->when($sector, function ($q) use ($sector) {
                $q->where('services.locality_id', $sector);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('members.rut', $search)
                    ->orWhere('members.full_name', 'like', '%' . $search . '%');
            })
            ->select('readings.*', 'services.nro', 'members.rut', 'members.full_name', 'services.sector as location_name')
            ->orderBy('period', 'desc')->paginate(20);

        $locations = Location::where('org_id', $org->id)->orderby('order_by', 'ASC')->get();

        return view('orgs.readings.history', compact('org', 'readings', 'locations'));
    }

    public function current_reading_update($org_id, Request $request)
    {
        $request->validate([
            'current_reading' => 'required|numeric|min:0',
            'member_id' => 'nullable|numeric',
            'period' => 'nullable|string',
        ]);

        try {
            $org = Org::find($org_id);
            if (! $org) {
                return redirect()->back()->with('error', 'Organización no encontrada.');
            }

            // Debug específico para el servicio 216
            $debugData = [
                'request_data' => $request->all(),
                'has_reading_id' => $request->has('reading_id'),
                'reading_id_value' => $request->reading_id,
                'has_member_id' => $request->has('member_id'),
                'member_id_value' => $request->member_id,
                'has_period' => $request->has('period'),
                'period_value' => $request->period,
            ];
            
            // Buscar si es el servicio problemático
            if ($request->has('member_id')) {
                $service = DB::table('services')
                    ->where('member_id', $request->member_id)
                    ->where('org_id', $org->id)
                    ->first();
                    
                if ($service && $service->nro == 216) {
                    $debugData['service_216_detected'] = true;
                    $debugData['service_data'] = $service;
                    Log::info('DEBUG SERVICIO 216 - Request recibido', $debugData);
                }
            }

            // Si se proporciona reading_id, actualizar lectura existente
            if ($request->has('reading_id') && $request->reading_id) {
                $reading = Reading::findOrFail($request->reading_id);
                $this->updateReading($org, $reading, $request->only(['current_reading']));
            } 
            // Si no hay reading_id pero sí member_id y period, crear o actualizar lectura
            else if ($request->has('member_id') && $request->has('period')) {
                $this->createOrUpdateReading($org, $request);
            } else {
                return redirect()->back()->with('error', 'Datos insuficientes para procesar la lectura.');
            }

            // Respuesta para peticiones AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Lectura registrada correctamente'
                ]);
            }

            return redirect()->back()->with('success', 'Lectura actualizada correctamente');
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en current_reading_update', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTrace()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Error al procesar lectura: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('danger', 'Error al actualizar lectura: ' . $e->getMessage());
        }
    }

    private function createOrUpdateReading($org, $request)
    {
        // Obtener el servicio del miembro para el período actual
        $service = DB::table('services')
            ->where('member_id', $request->member_id)
            ->where('org_id', $org->id)
            ->first();

        if (!$service) {
            throw new \Exception('Servicio no encontrado para el miembro especificado.');
        }

        // Debug específico para servicio 216
        if ($service->nro == 216) {
            Log::info('DEBUG SERVICIO 216 - En createOrUpdateReading', [
                'service_data' => $service,
                'request_data' => $request->all(),
                'period' => $request->period
            ]);
        }

        // Verificar si ya existe una lectura para este service_id y período
        $existingReading = Reading::where('service_id', $service->id)
            ->where('period', $request->period)
            ->where('org_id', $org->id)
            ->first();

        if ($existingReading) {
            // Si ya existe una lectura, actualizarla en lugar de crear una nueva
            Log::info('Actualizando lectura existente para servicio N° ' . $service->nro, [
                'service_id' => $service->id,
                'period' => $request->period,
                'existing_reading_id' => $existingReading->id,
                'new_current_reading' => $request->current_reading
            ]);
            
            $this->updateReading($org, $existingReading, ['current_reading' => $request->current_reading]);
            return $existingReading;
        }

        // Si no existe, crear nueva lectura
        $previousMonth = date('Y-m', strtotime($request->period . '-01 -1 month'));
        $previousReading = DB::table('readings')
            ->where('service_id', $service->id)
            ->where('period', $previousMonth)
            ->first();

        $previousReadingValue = $previousReading ? $previousReading->current_reading : 0;

        // Crear nueva lectura de forma segura
        $reading = new Reading();
        $reading->org_id = $org->id;
        $reading->member_id = $request->member_id;
        $reading->service_id = $service->id;
        $reading->locality_id = $service->locality_id;
        $reading->period = $request->period;
        $reading->current_reading = $request->current_reading;
        $reading->previous_reading = $previousReadingValue;
        $reading->cm3 = max(0, $request->current_reading - $previousReadingValue);
        $reading->corte_reposicion = 0;
        $reading->other = 0;
        $reading->save();

        // Calcular costos usando la función existente
        $this->updateReading($org, $reading, ['current_reading' => $request->current_reading]);
        
        return $reading;
    }

    public function update($org_id, Request $request)
    {
        $request->validate([
            'reading_id'      => 'required|numeric',
            'current_reading' => 'required|numeric|min:0',
            'multas_vencidas' => 'nullable|numeric|min:0',
        ]);

        try {
            $org     = Org::find($org_id);
            if (! $org) {
                return redirect()->back()->with('error', 'Organización no encontrada.');
            }
            $reading = Reading::findOrFail($request->reading_id);

            $this->updateReading($org, $reading, $request->all());

            return redirect()->back()->with('success', 'Actualización de lectura correcta');
        } catch (\Exception $e) {
            return redirect()->back()->with('danger', 'Error al actualizar la lectura: ' . $e->getMessage());
        }
    }

    private function updateReading($org, $reading, $data)
    {
        $tier       = TierConfig::where('org_id', $org->id)->OrderBy('id', 'ASC')->get();
        $configCost = FixedCostConfig::where('org_id', $org->id)->first();

        $reading->previous_reading = $data['previous_reading'] ?? $reading->previous_reading;
        $reading->current_reading  = $data['current_reading'];

        $reading->cm3 = max(0, $reading->current_reading - $reading->previous_reading);

        $service              = Service::findOrFail($reading->service_id);
        $cargo_fijo           = $configCost->fixed_charge_penalty;
        $subsidio             = $service->meter_plan; // 0 o 1
        $porcentaje_subsidio  = $service->percentage / 100;
        $consumo_agua_potable = 0;
        $subsidioDescuento    = 0;
        $cm3                  = $reading->cm3;
        $consumoNormal        = 0;

        $tramos = [];
        foreach ($tier as $t) {
            $tramos[] = [
                'hasta'  => $t->range_to,
                'precio' => $t->value,
            ];
        }
        if (empty($tramos) || end($tramos)['hasta'] < PHP_INT_MAX) {
            $tramos[] = [
                'hasta'  => PHP_INT_MAX,
                'precio' => end($tramos)['precio'] ?? 0,
            ];
        }

        $anterior = 0;
        $restante = $cm3;

        for ($i = 0; $i < count($tramos) && $restante > 0; $i++) {
            $limite = $tramos[$i]['hasta'];
            $precio = $tramos[$i]['precio'];

            $cantidad = min($restante, $limite - $anterior);

            if ($i === 0 && $subsidio != 0) {
                $cantidadSubvencionada = min($configCost->max_covered_m3, $cantidad);
                $cantidadNormal        = $cantidad - $cantidadSubvencionada;
                $precioConSubsidio     = $precio * (1 - $porcentaje_subsidio); // esto es el 0.4 o 0.6? es lo que se descuenta o el total, deverisa ser el descuento, osea el 0.4 el que se muestra
                $consumo_agua_potable += $cantidadSubvencionada * $precioConSubsidio;
                $consumo_agua_potable += $cantidadNormal * $precio;
                $consumoNormal += $cantidad * $precio;
                $subsidioDescuento = $cantidadSubvencionada * ($precio - $precioConSubsidio);
            } else {
                $consumo_agua_potable += $cantidad * $precio;
                $consumoNormal += $cantidad * $precio;
            }

            $restante -= $cantidad;
            $anterior = $limite;
        }

        $reading->vc_water = $consumoNormal;
        $reading->v_subs   = $subsidioDescuento;

        // Manejar las multas vencidas (800 o 1600)
        $multas_vencidas = 0;
        if (isset($data['cargo_mora'])) {
            $multas_vencidas = max($multas_vencidas, $configCost->late_fee_penalty);
        }
        if (isset($data['cargo_vencido'])) {
            $multas_vencidas = max($multas_vencidas, $configCost->expired_penalty);
        }
        $reading->multas_vencidas = $multas_vencidas;

        $reading->corte_reposicion = isset($data['cargo_corte_reposicion']) ?
        ($configCost->replacement_penalty) : 0;
        $reading->other = $data['other'] ?? $reading->other;

        $subtotal_consumo_mes  = $consumo_agua_potable + $cargo_fijo;
        $reading->total_mounth = $subtotal_consumo_mes;
        $subTotal              = $subtotal_consumo_mes + $reading->multas_vencidas + $reading->corte_reposicion + $reading->other + $reading->s_previous;
        $reading->sub_total    = $subTotal;

        if ($reading->invoice_type && $reading->invoice_type != "boleta") {
            $iva            = $subTotal * 0.19;
            $reading->total = $subTotal + $iva;
        } else {
            $reading->total = $subTotal;
        }

        $reading->save();
    }

public function dte($id, $readingId)
{
    Log::info('Recibiendo solicitud para DTE con ID org: ' . $id . ' y readingId: ' . $readingId);

    // Obtener organización
    $org = DB::table('orgs')->where('id', $id)->first();
    if (!$org) {
        abort(404, 'Organización no encontrada');
    }

    // Obtener lectura con validación de organización
    $reading = DB::table('readings')
        ->where('id', $readingId)
        ->where('org_id', $id)
        ->first();

    if (!$reading) {
        abort(404, 'Lectura no encontrada o no pertenece a esta organización');
    }

    // Obtener servicio asociado
    $service = DB::table('services')
        ->where('id', $reading->service_id)
        ->first();

    if (!$service) {
        abort(404, 'Servicio no encontrado');
    }

    // Obtener miembro asociado
    $member = DB::table('members')
        ->where('id', $service->member_id)
        ->first();

    if (!$member) {
        abort(404, 'Miembro no encontrado');
    }

    // Añadir datos de miembro y servicio al objeto lectura
    $reading->member = $member;
    $reading->service = $service;

    // Obtener configuración de tramos
    $tier = DB::table('tier_config')
        ->where('org_id', $id)
        ->orderBy('id', 'ASC')
        ->get();


    if ($tier->isEmpty()) {
        Log::error("No se encontraron secciones para la organización con ID: {$id}");
        abort(404, 'No se encontraron secciones.');
    }

    // Obtener configuración de costos fijos
    $configCost = DB::table('fixed_costs_config')
        ->where('org_id', $org->id)
        ->first();

    if (!$configCost) {
        Log::error("No se encontró configuración de costos fijos para la organización con ID: {$org->id}");
        abort(404, 'Configuración de costos fijos no encontrada.');
    }

    // Obtener lectura anterior
    $readingAnterior = DB::table('readings')
        ->where('member_id', $member->id)
        ->where('service_id', $service->id)
        ->where('period', '<', $reading->period)
        ->orderBy('period', 'desc')
        ->first();

    // Cálculo de tramos (igual que antes)
    $consumo = $reading->cm3;
    $detalle_sections = [];
    $consumo_restante = $consumo;
    $anterior = 0;

     foreach ($tier as $index => $tierConfig) {
            if ($consumo_restante <= 0) {
                // Si no queda consumo, este tramo tendrá 0 m3
                $tierConfig->section            = $tierConfig->range_from . " Hasta " . $tierConfig->range_to;
                $tierConfig->m3                 = 0;
                $tierConfig->precio             = $tierConfig->value;
                $tierConfig->total              = 0;
                $tierConfig->total_sin_subsidio = 0;
                $tierConfig->subsidio_aplicado  = 0;
            } else {
                $limite_tramo = $tierConfig->range_to;

                // Si es el último tramo, asignar todo el consumo restante
                $es_ultimo_tramo = ($index == count($tier) - 1);

                if ($es_ultimo_tramo) {
                    // En el último tramo, asignar todo el consumo restante
                    $m3_en_este_tramo = $consumo_restante;
                } else {
                    // En tramos intermedios, calcular la capacidad del tramo
                    $capacidad_tramo  = $limite_tramo - $anterior;
                    $m3_en_este_tramo = min($capacidad_tramo, $consumo_restante);
                }

                $tierConfig->section = $tierConfig->range_from . " Hasta " . $tierConfig->range_to;
                $tierConfig->m3      = $m3_en_este_tramo;
                $tierConfig->precio  = $tierConfig->value;

                // SIEMPRE mostrar el precio completo sin subsidio en la tabla de tramos
                $tierConfig->total = $m3_en_este_tramo * $tierConfig->value;

                // Reducir el consumo restante
                $consumo_restante -= $m3_en_este_tramo;
                $anterior = $limite_tramo;

                Log::info("Tramo {$tierConfig->range_from}-{$tierConfig->range_to}: {$m3_en_este_tramo} m3, Total sin subsidio: {$tierConfig->total}, Restante: {$consumo_restante}");
            }

            $detalle_sections[] = $tierConfig;
        }

        // Calcular el total de todos los tramos (sin subsidio aplicado)


    // Cálculos de montos (igual que antes)
    $total_tramos_sin_subsidio = array_sum(array_column($detalle_sections, 'total'));
    $cargo_fijo = $configCost->fixed_charge_penalty;
    $consumo_agua_potable = $total_tramos_sin_subsidio;
    $subsidio_descuento = $reading->v_subs ?? 0;
    $subtotal_consumo = $consumo_agua_potable + $cargo_fijo - $subsidio_descuento;

    $subtotal_con_cargos = $subtotal_consumo +
        ($reading->multas_vencidas ?? 0) +
        ($reading->corte_reposicion ?? 0) +
        ($reading->other ?? 0) +
        ($reading->s_previous ?? 0);

    // Determinar tipo de documento
    $routeName = Route::currentRouteName();
    $docType = str_contains($routeName, 'factura') ? 'factura' : 'boleta';

    if ($docType === 'factura') {
        $iva = $subtotal_con_cargos * 0.19;
        $total_con_iva = $subtotal_con_cargos + $iva;
        return view('orgs.factura', compact('reading', 'org', 'detalle_sections', 'tier', 'configCost', 'subtotal_consumo', 'subtotal_con_cargos', 'iva', 'total_con_iva', 'consumo_agua_potable', 'subsidio_descuento', 'readingAnterior'));
    } else {
        return view('orgs.boleta', compact('reading', 'org', 'detalle_sections', 'tier', 'configCost', 'subtotal_consumo',  'consumo_agua_potable', 'subsidio_descuento', 'readingAnterior'));
    }
}

      public function multiBoletaPrint($id, $readingId)
    {
        Log::info('Recibiendo solicitud para DTE con ID org: ' . $id . ' y readingId: ' . $readingId);

        $org              = Org::findOrFail($id);
        $reading          = Reading::findOrFail($readingId);
        $reading->member  = Member::findOrFail($reading->member_id);
        $reading->service = Service::findOrFail($reading->service_id);
        $tier             = TierConfig::where('org_id', $id)->OrderBy('id', 'ASC')->get();
        $configCost       = FixedCostConfig::where('org_id', $org->id)->first();

        $readingAnterior = Reading::where('member_id', $reading->member_id)
            ->where('service_id', $reading->service_id)
            ->where('period', '<', $reading->period)
            ->orderBy('period', 'desc')
            ->first();

        if ($tier->isEmpty()) {
            Log::error("No se encontraron secciones para la organización con ID: {$id}");
            abort(404, 'No se encontraron secciones.');
        }

        if (! $configCost) {
            \Log::error("No se encontró configuración de costos fijos para la organización con ID: {$org->id}");
            abort(404, 'Configuración de costos fijos no encontrada.');
        }

        // Obtener datos del servicio para el subsidio
        $service             = Service::findOrFail($reading->service_id);
        $subsidio            = $service->meter_plan; // 0 o 1
        $porcentaje_subsidio = $service->percentage / 100;

        // Asegurándonos de que el consumo es mayor que 0
        $consumo = $reading->cm3;
        Log::info("Consumo inicial: " . $consumo);

        $detalle_sections = [];
        $consumo_restante = $consumo;
        $anterior         = 0;

        foreach ($tier as $index => $tierConfig) {
            if ($consumo_restante <= 0) {
                // Si no queda consumo, este tramo tendrá 0 m3
                $tierConfig->section            = $tierConfig->range_from . " Hasta " . $tierConfig->range_to;
                $tierConfig->m3                 = 0;
                $tierConfig->precio             = $tierConfig->value;
                $tierConfig->total              = 0;
                $tierConfig->total_sin_subsidio = 0;
                $tierConfig->subsidio_aplicado  = 0;
            } else {
                $limite_tramo = $tierConfig->range_to;

                // Si es el último tramo, asignar todo el consumo restante
                $es_ultimo_tramo = ($index == count($tier) - 1);

                if ($es_ultimo_tramo) {
                    // En el último tramo, asignar todo el consumo restante
                    $m3_en_este_tramo = $consumo_restante;
                } else {
                    // En tramos intermedios, calcular la capacidad del tramo
                    $capacidad_tramo  = $limite_tramo - $anterior;
                    $m3_en_este_tramo = min($capacidad_tramo, $consumo_restante);
                }

                $tierConfig->section = $tierConfig->range_from . " Hasta " . $tierConfig->range_to;
                $tierConfig->m3      = $m3_en_este_tramo;
                $tierConfig->precio  = $tierConfig->value;

                // SIEMPRE mostrar el precio completo sin subsidio en la tabla de tramos
                $tierConfig->total = $m3_en_este_tramo * $tierConfig->value;

                // Reducir el consumo restante
                $consumo_restante -= $m3_en_este_tramo;
                $anterior = $limite_tramo;

                Log::info("Tramo {$tierConfig->range_from}-{$tierConfig->range_to}: {$m3_en_este_tramo} m3, Total sin subsidio: {$tierConfig->total}, Restante: {$consumo_restante}");
            }

            $detalle_sections[] = $tierConfig;
        }

        // Calcular el total de todos los tramos (sin subsidio aplicado)
        $total_tramos_sin_subsidio = array_sum(array_column($detalle_sections, 'total'));

        Log::info("Total de tramos sin subsidio: {$total_tramos_sin_subsidio}");
        Log::info("vc_water de reading (con subsidio aplicado): {$reading->vc_water}");
        Log::info("v_subs de reading (subsidio a descontar): {$reading->v_subs}");

        // Valores fijos
        $cargo_fijo           = $configCost->fixed_charge_penalty;
        $consumo_agua_potable = $total_tramos_sin_subsidio; // Usar el total de tramos sin subsidio
        $subsidio_descuento   = $reading->v_subs ?? 0;      // Subsidio ya calculado

        $subtotal_consumo = $consumo_agua_potable + $cargo_fijo - $subsidio_descuento;

        // Verificando el subtotal
        Log::info("Consumo agua potable (tramos sin subsidio): " . $consumo_agua_potable);
        Log::info("Cargo fijo: " . $cargo_fijo);
        Log::info("Subsidio a descontar: " . $subsidio_descuento);
        Log::info("Subtotal de consumo (después de restar subsidio): " . $subtotal_consumo);

        $subtotal_con_cargos = $subtotal_consumo +
            ($reading->multas_vencidas ?? 0) +
            ($reading->corte_reposicion ?? 0) +
            ($reading->other ?? 0) +
            ($reading->s_previous ?? 0);

        // Definir el IVA solo si el tipo de documento es factura
        $iva           = 0;
        $total_con_iva = $subtotal_con_cargos;


$routeName = Route::currentRouteName();
Log::info('Ruta actual detectada: ' . $routeName);

if ($routeName === 'orgs.multiBoletaPrint') {
    $docType = 'boleta';
} elseif ($routeName === 'orgs.multiFacturaPrint') {
    $docType = 'factura';
    $iva = $subtotal_con_cargos * 0.19;
    $total_con_iva = $subtotal_con_cargos + $iva;
} else {
    $docType = 'boleta'; // Por defecto
}

        Log::info('Tipo de documento seleccionado: ' . $docType);
        Log::info("IVA Calculado: {$iva}");
        Log::info("Total con IVA: {$total_con_iva}");

        switch (strtolower($docType)) {
            case 'boleta':
                Log::info('Entrando a la vista de Boleta');
                return view('orgs.multiBoletaPrint', compact('reading', 'org', 'detalle_sections', 'tier', 'configCost', 'subtotal_consumo', 'total_con_iva', 'consumo_agua_potable', 'subsidio_descuento', 'readingAnterior'));

            case 'factura':
                Log::info('Entrando a la vista de Factura');
                return view('orgs.multiFacturaPrint', compact('reading', 'org', 'detalle_sections', 'tier', 'configCost', 'subtotal_consumo', 'subtotal_con_cargos', 'iva', 'total_con_iva', 'consumo_agua_potable', 'subsidio_descuento', 'readingAnterior'));

            default:
                abort(404, 'Tipo de documento no reconocido: ' . $docType);
        }
    }

    /*Export Excel*/
    public function export()
    {
        return Excel::download(new ReadingsExport, 'Reading-' . date('Ymdhis') . '.xlsx');
    }

    /**
     * Guarda lecturas enviadas por el frontend (POST /guardar-lecturas)
     * Espera un array 'lecturas' en el body (JSON)
     * Cada lectura debe tener: numero, rut, cliente, sector, lectura, period
     * Busca el service_id por numero y rut, y guarda o actualiza la lectura para ese periodo
     * Devuelve JSON con éxito o error
     */
    public function store(Request $request)
    {
        $lecturas = $request->input('lecturas');
        Log::info('Lecturas recibidas para guardar:', $lecturas);
        if (!is_array($lecturas) || empty($lecturas)) {
            Log::warning('No se recibieron lecturas válidas');
            return response()->json(['message' => 'No se recibieron lecturas válidas'], 400);
        }
        $errores = [];
        $guardadas = 0;
        DB::beginTransaction();
        try {
            foreach ($lecturas as $lectura) {
                Log::info('Procesando lectura:', $lectura);
                // Validar datos mínimos
                if (empty($lectura['numero']) || empty($lectura['rut']) || empty($lectura['lectura']) || empty($lectura['period'])) {
                    $errores[] = 'Faltan datos en una lectura';
                    Log::warning('Lectura con datos faltantes:', $lectura);
                    continue;
                }
                // Buscar el servicio por numero y rut
                $servicio = Service::where('nro', $lectura['numero'])
                    ->with('member')
                    ->first();
                Log::info('Resultado búsqueda servicio:', ['servicio' => $servicio]);
                if ($servicio && $servicio->member) {
                    Log::info('Datos del member asociado:', ['rut' => $servicio->member->rut]);
                }
                if (!$servicio || !$servicio->member || $servicio->member->rut !== $lectura['rut']) {
                    $errores[] = 'No se encontró el servicio para el número ' . $lectura['numero'] . ' y rut ' . $lectura['rut'];
                    Log::warning('No se encontró el servicio para:', ['numero' => $lectura['numero'], 'rut' => $lectura['rut']]);
                    continue;
                }
                // Validar que existan los campos necesarios en el modelo
                $service_id = $servicio->getAttribute('id');
                $org_id = $servicio->getAttribute('org_id');
                $member_id = $servicio->getAttribute('member_id');
                Log::info('IDs detectados:', ['service_id' => $service_id, 'org_id' => $org_id, 'member_id' => $member_id]);
                if (!$service_id || !$org_id || !$member_id) {
                    $errores[] = 'El modelo Service no tiene los campos requeridos (id, org_id, member_id)';
                    Log::warning('Faltan campos en Service:', ['service' => $servicio]);
                    continue;
                }
                // Verificar si ya existe una lectura para este service_id y período (RESTRICCIÓN ESTRICTA)
                $existingServiceReading = Reading::where('service_id', $service_id)
                    ->where('period', $lectura['period'])
                    ->where('org_id', $org_id)
                    ->first();

                if ($existingServiceReading) {
                    $errores[] = 'Ya existe una lectura para el servicio N° ' . $lectura['numero'] . ' en el período ' . $lectura['period'];
                    Log::warning('Lectura duplicada detectada:', ['service_id' => $service_id, 'period' => $lectura['period']]);
                    continue;
                }

                // Crear nueva lectura (sin firstOrNew ya que verificamos duplicados arriba)
                $reading = new Reading();
                $reading->org_id = $org_id;
                $reading->member_id = $member_id;
                $reading->service_id = $service_id;
                $reading->period = $lectura['period'];
                $reading->current_reading = $lectura['lectura'];
                // Asignar locality_id desde el servicio relacionado
                $reading->locality_id = $servicio->getAttribute('locality_id');
                // Buscar lectura anterior para previous_reading
                $prev = Reading::where('service_id', $service_id)
                    ->where('period', '<', $lectura['period'])
                    ->orderBy('period', 'desc')
                    ->first();
                $reading->previous_reading = $prev && isset($prev->current_reading) ? $prev->current_reading : 0;
                $reading->cm3 = max(0, (float)$reading->current_reading - (float)$reading->previous_reading);
                $reading->save();
                $guardadas++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar lecturas: ' . $e->getMessage());
            return response()->json(['message' => 'Error al guardar lecturas: ' . $e->getMessage()], 500);
        }
        if ($guardadas === 0) {
            Log::warning('No se guardó ninguna lectura', ['errores' => $errores]);
            return response()->json(['message' => 'No se guardó ninguna lectura', 'errores' => $errores], 400);
        }
        Log::info('Lecturas guardadas correctamente', ['guardadas' => $guardadas]);
        return response()->json([
            'message' => 'Lecturas guardadas correctamente',
            'guardadas' => $guardadas,
            'errores' => $errores
        ]);
    }

    public function exportHistory($id)
    {
        // Si tu exportador requiere argumentos, pásalos aquí
        return Excel::download(new ReadingsHistoryExport($id), 'Readings-History-' . date('Ymdhis') . '.xlsx');
    }

    /**
     * Elimina registros duplicados de lecturas
     * Mantiene solo la lectura más reciente por service_id y period
     */
    public function cleanDuplicateReadings($org_id)
    {
        try {
            DB::beginTransaction();

            // Buscar duplicados: más de una lectura por service_id y period en la misma organización
            $duplicates = DB::table('readings')
                ->select('service_id', 'period', DB::raw('COUNT(*) as count'), DB::raw('MAX(id) as keep_id'))
                ->where('org_id', $org_id)
                ->groupBy('service_id', 'period')
                ->having('count', '>', 1)
                ->get();

            $deletedCount = 0;
            $affectedServices = [];

            foreach ($duplicates as $duplicate) {
                // Obtener el número de servicio para el log
                $serviceInfo = DB::table('services')
                    ->where('id', $duplicate->service_id)
                    ->first();

                // Eliminar todos los registros excepto el más reciente (keep_id)
                $deleted = DB::table('readings')
                    ->where('org_id', $org_id)
                    ->where('service_id', $duplicate->service_id)
                    ->where('period', $duplicate->period)
                    ->where('id', '!=', $duplicate->keep_id)
                    ->delete();

                $deletedCount += $deleted;
                $affectedServices[] = [
                    'service_id' => $duplicate->service_id,
                    'service_number' => $serviceInfo ? $serviceInfo->nro : 'N/A',
                    'period' => $duplicate->period,
                    'deleted_count' => $deleted,
                    'kept_id' => $duplicate->keep_id
                ];
            }

            DB::commit();

            Log::info("Limpieza de duplicados completada para org {$org_id}", [
                'total_deleted' => $deletedCount,
                'affected_services' => $affectedServices
            ]);

            return response()->json([
                'success' => true,
                'message' => "Limpieza completada. Se eliminaron {$deletedCount} registros duplicados.",
                'deleted_count' => $deletedCount,
                'affected_services' => count($affectedServices)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al limpiar duplicados en org {$org_id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar registros duplicados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina todos los registros de un servicio específico por su número
     */
    public function deleteServiceReadings($org_id, $serviceNumber)
    {
        try {
            DB::beginTransaction();

            // Buscar el servicio por número
            $service = DB::table('services')
                ->where('org_id', $org_id)
                ->where('nro', $serviceNumber)
                ->first();

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => "Servicio número {$serviceNumber} no encontrado en la organización."
                ], 404);
            }

            // Obtener información del miembro para el log
            $member = DB::table('members')->where('id', $service->member_id)->first();
            
            // Eliminar todas las lecturas del servicio
            $deletedReadings = DB::table('readings')
                ->where('org_id', $org_id)
                ->where('service_id', $service->id)
                ->delete();

            DB::commit();

            Log::info("Eliminación completa del servicio {$serviceNumber}", [
                'org_id' => $org_id,
                'service_id' => $service->id,
                'service_number' => $serviceNumber,
                'member_name' => $member ? $member->full_name : 'N/A',
                'member_rut' => $member ? $member->rut : 'N/A',
                'deleted_readings' => $deletedReadings
            ]);

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deletedReadings} registros del servicio número {$serviceNumber}.",
                'deleted_readings' => $deletedReadings,
                'service_info' => [
                    'number' => $serviceNumber,
                    'member_name' => $member ? $member->full_name : 'N/A',
                    'member_rut' => $member ? $member->rut : 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar registros del servicio {$serviceNumber} en org {$org_id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar registros del servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga la plantilla CSV/Excel para carga masiva de lecturas
     */
    public function downloadTemplate($org_id)
    {
        try {
            $org = Org::find($org_id);
            if (!$org) {
                return redirect()->back()->with('error', 'Organización no encontrada.');
            }

            $templatePath = storage_path('templates/plantilla_carga_lecturas_masiva.csv');
            
            if (!file_exists($templatePath)) {
                return redirect()->back()->with('error', 'Plantilla no encontrada.');
            }

            return response()->download($templatePath, 'plantilla_carga_lecturas_masiva.csv', [
                'Content-Type' => 'text/csv',
            ]);

        } catch (\Exception $e) {
            Log::error("Error al descargar plantilla para org {$org_id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al descargar la plantilla: ' . $e->getMessage());
        }
    }

    /**
     * Procesa la carga masiva de lecturas desde archivo Excel/CSV
     */
    public function massUpload($org_id, Request $request)
    {
        try {
            $org = Org::find($org_id);
            if (!$org) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organización no encontrada.'
                ], 404);
            }

            // Validar que se haya enviado un archivo
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Máximo 10MB
            ]);

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            
            // Leer el archivo según su extensión
            if (in_array($extension, ['xlsx', 'xls'])) {
                $data = Excel::toArray(new \stdClass(), $file)[0]; // Obtener la primera hoja
            } else if ($extension === 'csv') {
                $data = array_map('str_getcsv', file($file->getRealPath()));
            }

            if (empty($data) || count($data) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo está vacío o no contiene datos válidos.'
                ], 400);
            }

            // Validar headers (primera fila)
            $headers = array_map('trim', $data[0]);
            $expectedHeaders = ['numero_servicio', 'rut', 'lectura_actual', 'periodo'];
            
            if ($headers !== $expectedHeaders) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las columnas del archivo no coinciden con el formato esperado. Columnas esperadas: ' . implode(', ', $expectedHeaders)
                ], 400);
            }

            $processedRows = 0;
            $errorRows = [];
            $skippedRows = 0;

            DB::beginTransaction();

            // Procesar cada fila de datos (excepto la primera que son los headers)
            for ($i = 1; $i < count($data); $i++) {
                $row = $data[$i];
                
                // Validar que la fila tenga todos los campos necesarios
                if (count($row) < 4 || empty(trim($row[0])) || empty(trim($row[2])) || empty(trim($row[3]))) {
                    $skippedRows++;
                    $errorRows[] = [
                        'row' => $i + 1,
                        'data' => $row,
                        'error' => 'Fila incompleta - faltan datos obligatorios'
                    ];
                    continue;
                }

                $numero_servicio = trim($row[0]);
                $rut = trim($row[1]);
                $lectura_actual = trim($row[2]);
                $periodo = trim($row[3]);

                // Validar que la lectura actual sea numérica
                if (!is_numeric($lectura_actual)) {
                    $errorRows[] = [
                        'row' => $i + 1,
                        'data' => $row,
                        'error' => 'La lectura actual debe ser un valor numérico'
                    ];
                    continue;
                }

                // Buscar el servicio por número
                $service = Service::where('org_id', $org_id)
                    ->where('nro', $numero_servicio)
                    ->first();

                if (!$service) {
                    $errorRows[] = [
                        'row' => $i + 1,
                        'data' => $row,
                        'error' => "Servicio número {$numero_servicio} no encontrado"
                    ];
                    continue;
                }

                // Verificar que el RUT coincida (opcional, pero recomendado)
                if (!empty($rut)) {
                    $member = Member::find($service->member_id);
                    if ($member && $member->rut !== $rut) {
                        $errorRows[] = [
                            'row' => $i + 1,
                            'data' => $row,
                            'error' => "RUT no coincide con el servicio {$numero_servicio}"
                        ];
                        continue;
                    }
                }

                // Buscar o crear la lectura para este período
                $existingReading = Reading::where('org_id', $org_id)
                    ->where('service_id', $service->id)
                    ->where('period', $periodo)
                    ->first();

                if ($existingReading) {
                    // Actualizar lectura existente
                    $existingReading->current_reading = $lectura_actual;
                    $existingReading->save();
                } else {
                    // Crear nueva lectura
                    Reading::create([
                        'org_id' => $org_id,
                        'member_id' => $service->member_id,
                        'service_id' => $service->id,
                        'locality_id' => $service->locality_id,
                        'period' => $periodo,
                        'current_reading' => $lectura_actual,
                        'previous_reading' => 0, // Se puede calcular después
                        'cm3' => 0,
                        'vc_water' => 0,
                        'v_subs' => 0,
                        'total' => 0,
                        'corte_reposicion' => 0,
                        'other' => 0,
                        'invoice_type' => 'factura',
                        'payment_status' => 'pending',
                        'folio' => null,
                    ]);
                }

                $processedRows++;
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => "Carga masiva completada. {$processedRows} registros procesados correctamente.",
                'stats' => [
                    'processed' => $processedRows,
                    'errors' => count($errorRows),
                    'skipped' => $skippedRows,
                    'total_rows' => count($data) - 1 // Excluir header
                ]
            ];

            if (!empty($errorRows)) {
                $response['errors'] = array_slice($errorRows, 0, 10); // Mostrar solo los primeros 10 errores
                $response['message'] .= " " . count($errorRows) . " filas con errores.";
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en carga masiva para org {$org_id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error durante la carga masiva: ' . $e->getMessage()
            ], 500);
        }
    }

}
