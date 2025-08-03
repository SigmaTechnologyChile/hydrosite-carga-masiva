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
        $org = Org::findOrFail($id);
        $activeLocations = Location::where('org_id', $org->id)->get();
        
        // Obtener notificaciones recientes de la organización
        $notifications = Notification::where('org_id', $org->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // Calcular estadísticas
        $stats = [
            'total' => Notification::where('org_id', $org->id)->count(),
            'enviadas' => Notification::where('org_id', $org->id)->where('email_status', 'sent')->count(),
            'pendientes' => Notification::where('org_id', $org->id)->where('email_status', 'pending')->count(),
            'fallidas' => Notification::where('org_id', $org->id)->where('email_status', 'failed')->count(),
        ];
        
        return View::make('orgs.notifications.index', compact('org', 'activeLocations', 'notifications', 'stats'));
    }

    public function store(Request $request, $id)
    {
        try {
            Log::info('Iniciando proceso de notificación', ["data" => $request->all()]);

            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'sectors' => 'required_without:send_to_all|array',
            ]);

            $org = Org::findOrFail($id);
            $title = $request->input('title');
            $message = $request->input('message');
            $sendToAll = $request->has('send_to_all');

            Log::info('Parámetros recibidos:', [
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

            // Enviar notificación por email
            Log::info('Iniciando envío de notificaciones');
            $sentCount = 0;
            $errorCount = 0;

            foreach ($users as $user) {
                try {
                    Log::info('Enviando correo a: ' . $user->email);
                    
                    // Verificar configuración SMTP antes de enviar
                    Log::info('Configuración SMTP:', [
                        'host' => Config::get('mail.mailers.smtp.host'),
                        'port' => Config::get('mail.mailers.smtp.port'),
                        'encryption' => Config::get('mail.mailers.smtp.encryption'),
                        'from_address' => Config::get('mail.from.address')
                    ]);

                    Mail::to($user->email)->send(new NotificationMail($title, $message, $org, $user));
                    Log::info('Correo enviado exitosamente a: ' . $user->email);
                    $sentCount++;
                    
                    // Guardar notificación exitosa
                    Notification::create([
                        'org_id' => $org->id,
                        'title' => $title,
                        'message' => $message,
                        'recipient_email' => $user->email,
                        'recipient_name' => $user->name,
                        'send_method' => 'email',
                        'email_status' => 'sent',
                        'email_sent_at' => now(),
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Error enviando correo a ' . $user->email, [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    $errorCount++;
                    
                    // Guardar notificación fallida
                    Notification::create([
                        'org_id' => $org->id,
                        'title' => $title,
                        'message' => $message,
                        'recipient_email' => $user->email,
                        'recipient_name' => $user->name,
                        'send_method' => 'email',
                        'email_status' => 'failed',
                        'email_error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Proceso de envío finalizado', [
                'sent' => $sentCount,
                'errors' => $errorCount,
                'total' => $users->count()
            ]);

            $message = "Notificación enviada. Enviados: {$sentCount}, Errores: {$errorCount}";
            
            if ($errorCount == 0) {
                return Redirect::back()->with('success', $message);
            } else {
                return Redirect::back()->with('warning', $message);
            }

        } catch (\Exception $e) {
            Log::error('Error general en el proceso:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return Redirect::back()
                ->with('error', 'Error al enviar la notificación: ' . $e->getMessage())
                ->withInput();
        }
    }
}
