<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Cuenta;
use App\Models\Categoria;
use Illuminate\Http\Request;

class MovimientoController extends Controller
{
    // ...existing code...

    // Mostrar un movimiento específico
    public function show($id)
    {
        $movimiento = Movimiento::with(['categoria', 'cuentaOrigen', 'cuentaDestino'])->findOrFail($id);
        return view('orgs.contable.show', compact('movimiento'));
    }

    // Formulario de edición
    public function edit($id)
    {
        $movimiento = Movimiento::findOrFail($id);
        $cuentas = Cuenta::all();
        $categorias = Categoria::all();
        return view('orgs.contable.edit', compact('movimiento', 'cuentas', 'categorias'));
    }

    // ...existing code...
    // Listar todos los movimientos
    public function index()
    {
        $movimientos = Movimiento::with(['categoria', 'cuentaOrigen', 'cuentaDestino'])
            ->orderBy('fecha', 'desc')
            ->get();
        $bancos = \App\Models\Banco::orderBy('nombre')->get();
        $configuraciones = \App\Models\ConfiguracionInicial::with(['cuenta', 'banco'])->get();
        $cuentas = \App\Models\Cuenta::all();

        // Saldos iniciales por nombre de cuenta
        $saldosIniciales = [];
        foreach ($configuraciones as $conf) {
            if ($conf->cuenta && $conf->cuenta->nombre) {
                $saldosIniciales[$conf->cuenta->nombre] = $conf->saldo_inicial;
            }
        }
        // Saldos actuales por nombre de cuenta
        foreach ($cuentas as $cuenta) {
            if (!isset($saldosIniciales[$cuenta->nombre])) {
                $saldosIniciales[$cuenta->nombre] = 0;
            }
            $saldosIniciales[$cuenta->nombre] += $cuenta->saldo_actual;
        }
        // Suma total inicial y actual
        $totalSaldoInicial = $configuraciones->sum('saldo_inicial');
        $totalSaldoActual = $cuentas->sum('saldo_actual');
        // Suma total por cuenta
        $saldosIniciales['Saldo Total'] = $totalSaldoInicial + $totalSaldoActual;

        $categoriasIngresos = \App\Models\Categoria::where('tipo', 'ingreso')->orderBy('subcategoria')->get();
        $categoriasEgresos = \App\Models\Categoria::where('tipo', 'egreso')->orderBy('subcategoria')->get();

        return view('orgs.contable.index', compact('movimientos', 'bancos', 'configuraciones', 'categoriasIngresos', 'categoriasEgresos', 'saldosIniciales', 'totalSaldoInicial', 'totalSaldoActual'));
    }

    // Guardar nuevo movimiento
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:ingreso,egreso,transferencia,giros_depositos',
            'fecha' => 'required|date',
            'monto' => 'required|numeric',
        ]);

        $data = [
            'tipo' => $request->tipo,
            'fecha' => $request->fecha,
            'monto' => intval($request->monto),
        ];

        if ($request->tipo === 'ingreso') {
            $data['cuenta_destino_id'] = $this->getCuentaId($request->cuenta_destino);
            $data['descripcion'] = $request->descripcion;
            $data['nro_dcto'] = $request->nro_comprobante;
            $data['categoria_id'] = $request->categoria_id;
        } elseif ($request->tipo === 'egreso') {
            $data['cuenta_origen_id'] = $this->getCuentaId($request->cuenta_origen);
            $data['descripcion'] = $request->descripcion;
            $data['nro_dcto'] = $request->nro_comprobante;
            $data['categoria_id'] = $request->categoria_id;
            $data['proveedor'] = $request->razon_social ?? null;
            $data['rut_proveedor'] = $request->rut_proveedor ?? null;
        } elseif ($request->tipo === 'giros_depositos') {
            // GIROS: desde cuenta_corriente_1/2/ahorro hacia caja-general
            // DEPOSITOS: desde caja-general hacia cuenta_corriente_1/2/ahorro
            if ($request->modal === 'giro') {
                $data['tipo'] = 'transferencia';
                $data['subtipo'] = 'giro';
                $data['cuenta_origen_id'] = $this->getCuentaId($request->cuenta);
                $data['cuenta_destino_id'] = $this->getCuentaId('caja_general');
                $data['descripcion'] = $request->detalle;
            } elseif ($request->modal === 'deposito') {
                $data['tipo'] = 'transferencia';
                $data['subtipo'] = 'deposito';
                $data['cuenta_origen_id'] = $this->getCuentaId('caja_general');
                $data['cuenta_destino_id'] = $this->getCuentaId($request->cuenta);
                $data['descripcion'] = $request->detalle;
            }
            // Siempre asignar nro_dcto, aunque no venga del request
            $data['nro_dcto'] = $request->nro_comprobante ?? 'CPB-0001';
        }

        // Validar saldo suficiente en cuenta origen (solo si aplica)
        if (isset($data['cuenta_origen_id'])) {
            $cuentaOrigen = Cuenta::find($data['cuenta_origen_id']);
            if ($cuentaOrigen && $cuentaOrigen->saldo_actual < $data['monto']) {
                return redirect()->route('movimientos.index')->with('error', 'Saldo insuficiente en la cuenta origen.');
            }
        }

        Movimiento::create($data);

        // Actualizar saldos de cuentas involucradas
        if (isset($data['cuenta_origen_id'])) {
            $cuentaOrigen = Cuenta::find($data['cuenta_origen_id']);
            if ($cuentaOrigen) {
                $cuentaOrigen->saldo_actual -= $data['monto'];
                $cuentaOrigen->save();
            }
        }
        if (isset($data['cuenta_destino_id'])) {
            $cuentaDestino = Cuenta::find($data['cuenta_destino_id']);
            if ($cuentaDestino) {
                $cuentaDestino->saldo_actual += $data['monto'];
                $cuentaDestino->save();
            }
        }

        return redirect()->route('movimientos.index')->with('success', 'Movimiento registrado correctamente.');
    }

    // Helper para obtener el id de la cuenta por nombre
    private function getCuentaId($nombre)
    {
        if (!$nombre) return null;
        $cuenta = Cuenta::where('tipo', $nombre)->first();
        return $cuenta ? $cuenta->id : null;
    }

    // Actualizar movimiento
    public function update(Request $request, $id)
    {
        $movimiento = Movimiento::findOrFail($id);

        $request->validate([
            'tipo' => 'required|in:ingreso,egreso,transferencia',
            'fecha' => 'required|date',
            'monto' => 'required|numeric',
            'nro_comprobante' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'descripcion' => 'required|string',
        ]);

        $data = [
            'tipo' => $request->tipo,
            'fecha' => $request->fecha,
            'monto' => $request->monto,
            'descripcion' => $request->descripcion,
            'nro_dcto' => $request->nro_comprobante,
            'categoria_id' => $request->categoria_id,
        ];

        if ($request->tipo === 'ingreso') {
            $data['cuenta_destino_id'] = $this->getCuentaId($request->cuenta_destino);
        } elseif ($request->tipo === 'egreso') {
            $data['cuenta_origen_id'] = $this->getCuentaId($request->cuenta_origen);
            $data['proveedor'] = $request->razon_social ?? null;
            $data['rut_proveedor'] = $request->rut_proveedor ?? null;
        }

        $movimiento->update($data);

        return redirect()->route('movimientos.index')->with('success', 'Movimiento actualizado correctamente.');
    }

    // Eliminar movimiento
    public function destroy($id)
    {
        $movimiento = Movimiento::findOrFail($id);
        $movimiento->delete();

        return redirect()->route('movimientos.index')->with('success', 'Movimiento eliminado correctamente.');
    }

    /**
     * Guardar ingreso desde modal
     */
    public function storeIngreso(Request $request, $orgId)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'fecha' => 'required|date',
                'nro_dcto' => 'nullable|string|max:50',
                'categoria' => 'required|string|max:100',
                'cuenta_destino' => 'required|string|max:50',
                'descripcion' => 'nullable|string|max:255',
                'monto' => 'required|numeric|min:0.01'
            ]);

            // Crear el movimiento
            $movimiento = Movimiento::create([
                'org_id' => $orgId,
                'fecha' => $request->fecha,
                'tipo' => 'ingreso',
                'nro_dcto' => $request->nro_dcto,
                'categoria' => $request->categoria, // Guardar como texto
                'cuenta_destino' => $request->cuenta_destino,
                'descripcion' => $request->descripcion,
                'monto' => $request->monto,
            ]);

            // Actualizar o crear la cuenta si no existe
            $cuenta = Cuenta::firstOrCreate(
                ['nombre' => $request->cuenta_destino, 'org_id' => $orgId],
                ['saldo' => 0]
            );

            // Sumar al saldo
            $cuenta->saldo += $request->monto;
            $cuenta->save();

            return response()->json([
                'success' => true,
                'message' => 'Ingreso registrado correctamente',
                'movimiento_id' => $movimiento->id,
                'nuevo_saldo' => $cuenta->saldo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el ingreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar egreso desde modal
     */
    public function storeEgreso(Request $request, $orgId)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'nro_dcto' => 'required|string|max:50',
            'categoria' => 'required|string|max:100',
            'cuenta_origen' => 'required|string',
            'razon_social' => 'required|string|max:100',
            'rut_proveedor' => 'nullable|string|max:20',
            'descripcion' => 'required|string',
            'monto' => 'required|numeric|min:0.01',
        ]);

        // Obtener o crear la cuenta origen
        $cuenta = Cuenta::firstOrCreate(
            ['tipo' => $data['cuenta_origen']],
            ['nombre' => $data['cuenta_origen'], 'saldo_actual' => 0]
        );

        // Verificar saldo suficiente
        if ($cuenta->saldo_actual < $data['monto']) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo insuficiente en la cuenta seleccionada. Saldo actual: $' . number_format($cuenta->saldo_actual, 0, ',', '.')
            ], 400);
        }

        // Crear el movimiento
        $movimiento = Movimiento::create([
            'tipo' => 'egreso',
            'fecha' => $data['fecha'],
            'monto' => $data['monto'],
            'descripcion' => $data['descripcion'],
            'nro_dcto' => $data['nro_dcto'],
            'categoria' => $data['categoria'], // Cambio aquí: de categoria_id a categoria
            'cuenta_origen_id' => $cuenta->id,
            'proveedor' => $data['razon_social'],
            'rut_proveedor' => $data['rut_proveedor'],
            'creado_en' => now(),
        ]);

        // Actualizar saldo de la cuenta origen
        $cuenta->saldo_actual -= $data['monto'];
        $cuenta->save();

        return response()->json([
            'success' => true,
            'message' => 'Egreso registrado correctamente',
            'movimiento' => $movimiento,
            'nuevo_saldo' => $cuenta->saldo_actual
        ]);
    }
}