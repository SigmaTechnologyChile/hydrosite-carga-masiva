<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderTicket;
use App\Models\Service;
use App\Models\Reading;
use App\Models\Member;
use App\Models\Org;
use Illuminate\Support\Str;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Http\Controllers\Org\DB;
use App\Http\Controllers\Org\Log;

class OrdersController extends Controller
{
    protected $_param;
    public $org;

    public function __construct()
    {
        $this->middleware('auth');
        // El parámetro 'id' debe obtenerse dentro de los métodos, no en el constructor
        $this->org = null;
    }


    public function store(Request $request, $org_id)
    {
        //tienes que revisar store para corregir show(la vista del voucher, es problema de como se guarda la orden)
        $validated = $request->validate([
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
            'payment_method_id' => 'required|string|in:1,2,3',
        ]);
        if (!$request->has('services') || empty($request->services)) {
            return redirect()->back()->with('error', 'Debes seleccionar al menos un servicio');
        }

        // Obtener todos los servicios seleccionados primero
        $selectedServices = Service::whereIn('id', $validated['services'])->get();
        
        if ($selectedServices->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron los servicios seleccionados');
        }

        \Log::info('=== SERVICIOS SELECCIONADOS ===');
        \Log::info('Servicios seleccionados: ', $selectedServices->toArray());

        // Obtener todos los RUTs únicos de los servicios seleccionados
        $ruts = $selectedServices->pluck('rut')->filter()->unique();
        $memberIds = $selectedServices->pluck('member_id')->filter()->unique();

        \Log::info('RUTs encontrados en servicios: ', $ruts->toArray());
        \Log::info('Member IDs encontrados: ', $memberIds->toArray());

        // Buscar el member principal por cualquiera de las formas disponibles
        $member = null;
        
        // Primer intento: por member_id si existe
        if ($memberIds->isNotEmpty()) {
            $member = Member::find($memberIds->first());
        }
        
        // Segundo intento: por RUT si no se encontró por member_id
        if (!$member && $ruts->isNotEmpty()) {
            $member = Member::where('rut', $ruts->first())->first();
        }

        if (!$member) {
            return redirect()->back()->with('error', 'No se encontró el socio asociado a los servicios seleccionados');
        }

        \Log::info('=== MEMBER ENCONTRADO ===');
        \Log::info('Member: ', $member->toArray());

        $payment_method_id = $request->input('payment_method_id');
        $payment_status = in_array($payment_method_id, [1, 2, 3]) ? 1 : 0;

        $order = new Order();
        $order->order_code = Str::upper(Str::random(9));
        $order->dni = $member->rut;
        $order->name = $member->first_name . ' ' . $member->last_name;
        $order->email = $member->email;
        $order->phone = $member->phone;
        $order->status = 1;
        $order->payment_method_id = $payment_method_id;
        $order->payment_status = $payment_status;
        $order->save();

        $order_id = $order->id;

        $sumTotal = 0;
        $qty = 0;
        $invoice_type = 'boleta'; // Valor por defecto

        foreach ($selectedServices as $service) {
            \Log::info('=== PROCESANDO SERVICIO ===');
            \Log::info('Service ID: ' . $service->id);
            \Log::info('Service data: ', $service->toArray());
            
            // Buscar readings para este servicio específico que estén pendientes de pago
            $readings = Reading::where('service_id', $service->id)
                ->where('payment_status', 0)
                ->get();
                
            \Log::info('Readings encontradas: ' . $readings->count());
            \Log::info('Readings data: ', $readings->toArray());

            foreach ($readings as $reading) {
                \Log::info('=== PROCESANDO READING ===');
                \Log::info('Reading ID: ' . $reading->id . ', Total: ' . $reading->total);
                
                $qty++;
                // Capturar el tipo de factura del primer reading
                if ($qty == 1) {
                    $invoice_type = $reading->invoice_type;
                }
                
                $iva =  $reading->total * 0.19;
                $total_con_iva =  $reading->total + $iva;
                $orderItem = new OrderItem;
                $orderItem->order_id = $order_id;
                $orderItem->org_id = $reading->org_id;
                $orderItem->member_id = $reading->member_id;
                $orderItem->service_id = $reading->service_id;
                $orderItem->reading_id = $reading->id;
                $orderItem->locality_id = $reading->locality_id;
                $orderItem->folio = $reading->folio;
                $orderItem->type_dte = ($reading->invoice_type == 'factura') ? 'Factura' : 'Boleta';
                $orderItem->price = $reading->total;
                $orderItem->total =  ($reading->invoice_type == 'factura')? $total_con_iva :$reading->total;
                $orderItem->status = 1;
                $orderItem->payment_method_id = $payment_method_id;
                $orderItem->description = "Pago de servicio nro <b>" . Str::padLeft($service->nro, 5, 0) . "</b>, Periodo <b>" . $reading->period . "</b>, lectura <b>" . $reading->id . "</b>";
                $orderItem->payment_status = $payment_status;
                
                \Log::info('Guardando OrderItem: ', $orderItem->toArray());
                $orderItem->save();
                \Log::info('OrderItem guardado con ID: ' . $orderItem->id);

                $sumTotal += $reading->total;

                $reading->payment_status = $payment_status;
                $reading->save();
                \Log::info('Reading actualizada, payment_status: ' . $reading->payment_status);
            }
            
            // Si no se encontraron readings para este servicio, loguearlo
            if ($readings->count() == 0) {
                \Log::warning('No se encontraron readings pendientes para el servicio ID: ' . $service->id);
                
                // Verificar si existen readings para este servicio (con cualquier payment_status)
                $allReadings = Reading::where('service_id', $service->id)->get();
                \Log::info('Total readings para este servicio (cualquier status): ' . $allReadings->count());
                if ($allReadings->count() > 0) {
                    \Log::info('Payment status de readings existentes: ', $allReadings->pluck('payment_status')->toArray());
                }
            }
        }

        \Log::info('=== RESUMEN PROCESAMIENTO ===');
        \Log::info('Total servicios procesados: ' . $selectedServices->count());
        \Log::info('Total readings encontradas: ' . $qty);
        \Log::info('Suma total: ' . $sumTotal);
        \Log::info('Tipo de factura: ' . $invoice_type);

        // Calcular comisión por servicio
        $commission_rate = 500; // $500 pesos por servicio procesado
        $commission = $commission_rate * $qty;
        
        // Calcular totales con IVA si corresponde
        $subtotal = $sumTotal;
        $iva = $sumTotal * 0.19;
        $total_con_iva = $sumTotal + $iva;
        $total_before_commission = ($invoice_type == 'factura') ? $total_con_iva : $sumTotal;
        $final_total = $total_before_commission + $commission;

        // Guardar todos los cálculos en la orden usando los campos de la tabla
        $order->qty = $qty;
        $order->sub_total = $subtotal;
        $order->commission_rate = $commission_rate;
        $order->commission = $commission;
        $order->total = $final_total;
        // Guardamos información adicional en payment_detail
        $order->payment_detail = json_encode([
            'iva' => ($invoice_type == 'factura') ? $iva : 0,
            'invoice_type' => $invoice_type,
            'total_before_commission' => $total_before_commission
        ]);
        $order->save();

        return redirect()->route('orgs.orders.show', ['id' => $org_id, 'order_code' => $order->order_code]);
    }


    public function show($org_id, $order_code)
    {
        $org = Org::findOrFail($org_id);
        
        // Carga la orden con la relación 'items'
        $order = Order::with('items')->where('order_code', $order_code)->firstOrFail();

        // Obtiene los ítems de la orden
        $items = $order->items;

        // Debug temporal - quitar después de probar
        \Log::info('=== DEBUG ORDER SHOW ===');
        \Log::info('Order Code: ' . $order_code);
        \Log::info('Order Data: ', $order->toArray());
        \Log::info('Items Count: ' . $items->count());
        \Log::info('Items Data: ', $items->toArray());
        \Log::info('Commission: ' . $order->commission);
        \Log::info('Commission Rate: ' . $order->commission_rate);
        \Log::info('Total: ' . $order->total);

        return view('orgs.orders.show', compact('org', 'order', 'items'));
    }

    private function getPaymentMethodId($paymentMethod)
    {
        if ($paymentMethod = PaymentMethod::where('title', $paymentMethod)->first()) {
            return $paymentMethod->id;
        } else {
            return 0;
        }
    }

    private function createOrderItems($order, $services)
    {
        $total = 0;
        foreach ($services as $service) {
            dd($service->total_amount, $service->price);
            // Verifica si el servicio tiene un precio válido
            $price = $service->total_amount ?? $service->price ?? 0;

            // Si el precio es 0, puedes optar por lanzar un error o hacer algo específico
            if ($price == 0) {
                return redirect()->back()->with('error', 'El servicio ' . $service->sector . ' no tiene un precio válido.');
            }

            $item = new OrderItem();
            $item->order_id = $order->id;
            $item->org_id = $order->org_id;
            $item->service_id = $service->id;
            $item->doc_id = $service->doc_id ?? 1;
            $item->description = "Pago de servicio: " . $service->sector;
            $item->price = $price;
            $item->qty = 1;
            $item->total = $price;
            $item->status = 1;
            $item->save();

            $total += $price;
        }


        $order->qty = $services->count();
        $order->total = $total;
        $order->save();
    }
}
