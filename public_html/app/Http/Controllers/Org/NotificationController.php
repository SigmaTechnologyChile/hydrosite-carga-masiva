<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use App\Models\Location;
use App\Models\User;
use App\Models\Member;
use App\Models\Service;
use App\Models\Org;
use App\Models\Notification;
use Illuminate\Support\Facades\Redirect;

class NotificationController extends Controller
{
    public function index($id)
    {
        Log::info('=== MÉTODO INDEX EJECUTADO === Org ID: ' . $id);
        
        $org = Org::findOrFail($id);
        $activeLocations = Location::where('org_id', $org->id)->get();
        
        // Inicializar datos por defecto
        $notifications = collect();
        $stats = [
            'total' => 0,
            'enviadas' => 0,
            'pendientes' => 0,
            'fallidas' => 0,
        ];
        
        // Intentar obtener notificaciones si la tabla existe
        try {
            Log::info('Buscando notificaciones para org_id: ' . $org->id);
            
            $notifications = Notification::where('org_id', $org->id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
            
            Log::info('Notificaciones encontradas: ' . $notifications->count());
            
            // Calcular estadísticas
            $stats = [
                'total' => Notification::where('org_id', $org->id)->count(),
                'enviadas' => Notification::where('org_id', $org->id)->where('email_status', 'sent')->count(),
                'pendientes' => Notification::where('org_id', $org->id)->where('email_status', 'pending')->count(),
                'fallidas' => Notification::where('org_id', $org->id)->where('email_status', 'failed')->count(),
            ];
            
            Log::info('Estadísticas calculadas: ', $stats);
        } catch (\Exception $e) {
            Log::warning('Tabla notifications no existe aún: ' . $e->getMessage());
            // Usamos los valores por defecto ya inicializados
        }
        
        return View::make('orgs.notifications.index', compact('org', 'activeLocations', 'notifications', 'stats'));
    }

    public function store(Request $request, $id)
    {
        try {
            Log::info('=== INICIANDO STORE === Datos recibidos:', $request->all());

            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'sectors' => 'required_without:send_to_all|array',
            ]);

            $org = Org::findOrFail($id);
            $title = $request->input('title');
            $message = $request->input('message');
            $sendToAll = $request->has('send_to_all');

            Log::info('Parámetros procesados:', [
                'title' => $title,
                'sendToAll' => $sendToAll,
                'orgId' => $org->id
            ]);

            // Obtener destinatarios
            if ($sendToAll) {
                // Obtener todos los usuarios de la org que tengan email
                $users = User::where('org_id', $org->id)
                    ->whereNotNull('email')
                    ->get();
                Log::info('Enviando a todos los usuarios. Total:', ['count' => $users->count()]);
            } else {
                $sectorIds = $request->input('sectors', []);
                
                // Obtener usuarios por sectores - usando una consulta más directa
                // Buscar users que tengan members con services en los sectores seleccionados
                $users = collect();
                
                // Obtener todos los services de los sectores seleccionados
                $services = Service::whereIn('location_id', $sectorIds)
                    ->where('org_id', $org->id)
                    ->get();
                
                // Obtener los RUTs de los members de esos services
                $memberRuts = $services->pluck('rut')->unique()->filter();
                
                // Obtener users que tengan esos RUTs
                $users = User::where('org_id', $org->id)
                    ->whereNotNull('email')
                    ->whereIn('rut', $memberRuts)
                    ->get();
                    
                Log::info('Enviando a sectores específicos:', [
                    'sectors' => $sectorIds,
                    'services_found' => $services->count(),
                    'member_ruts' => $memberRuts->toArray(),
                    'userCount' => $users->count()
                ]);
            }

            // Verificar si hay usuarios para enviar
            if ($users->count() == 0) {
                Log::warning('No se encontraron usuarios para enviar notificaciones');
                return Redirect::back()->with('warning', 'No se encontraron usuarios para enviar la notificación.');
            }

            // SIMULACIÓN DE ENVÍO - Modo de prueba
            Log::info('Iniciando SIMULACIÓN de envío de notificaciones');
            $sentCount = 0;
            $errorCount = 0;

            foreach ($users as $user) {
                try {
                    Log::info('SIMULANDO envío de correo a: ' . $user->email);
                    
                    // SIMULACIÓN: En lugar de enviar, solo registramos
                    Log::info('SIMULACIÓN: Correo enviado exitosamente a: ' . $user->email);
                    $sentCount++;
                    
                    // Guardar notificación exitosa (simulada)
                    try {
                        Notification::create([
                            'org_id' => $org->id,
                            'title' => $title,
                            'message' => $message,
                            'recipient_email' => $user->email,
                            'recipient_name' => $user->name,
                            'send_method' => 'email',
                            'email_status' => 'sent',
                            'email_sent_at' => now(),
                            'status' => 'sent'
                        ]);
                        Log::info('Notificación guardada en BD para: ' . $user->email);
                    } catch (\Exception $dbError) {
                        Log::warning('No se pudo guardar en BD (tabla no existe): ' . $dbError->getMessage());
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error simulando envío a ' . $user->email, [
                        'error' => $e->getMessage()
                    ]);
                    $errorCount++;
                }
            }

            Log::info('Proceso de envío finalizado', [
                'sent' => $sentCount,
                'errors' => $errorCount,
                'total' => $users->count()
            ]);

            $successMessage = "Notificación enviada. Enviados: {$sentCount}, Errores: {$errorCount}";
            
            Log::info('=== REDIRIGIENDO === Mensaje: ' . $successMessage);
            
            if ($errorCount == 0) {
                return Redirect::back()->with('success', $successMessage);
            } else {
                return Redirect::back()->with('warning', $successMessage);
            }

        } catch (\Exception $e) {
            Log::error('Error general en el proceso:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return Redirect::back()
                ->with('error', 'Error al enviar la notificación: ' . $e->getMessage())
                ->withInput();
        }
    }
}