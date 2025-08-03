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

use App\Models\Org;

use App\Models\Member;

use App\Models\Service;

use Illuminate\Support\Facades\Redirect;



class NotificationController extends Controller

{

    public function index($id)

    {

        $org = Org::findOrFail($id);

        $activeLocations = Location::where('org_id', $org->id)->get();

        return View::make('orgs.notifications.index', compact('org', 'activeLocations'));

    }



    public function store(Request $request, $id)

    {

        try {

            Log::info('Iniciando proceso de notificación',["data"=>$request]);



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



            // Obtener destinatarios (members) - VERSIÓN SIMPLIFICADA

            if ($sendToAll) {

                // Obtener todos los members de esta org que tienen email
                $members = Member::join('orgs_members', 'members.id', '=', 'orgs_members.member_id')
                    ->where('orgs_members.org_id', $org->id)
                    ->whereNotNull('members.email')
                    ->select('members.*')
                    ->get();
                    
                Log::info('Enviando a todos los members. Total:', ['count' => $members->count()]);

            } else {

                $sectorIds = $request->input('sectors', []);

                // APPROACH ALTERNATIVO: usar relaciones Eloquent en lugar de JOINs complejos
                $members = collect();
                
                // Primero obtener todos los services de los sectores seleccionados
                $services = Service::whereIn('locality_id', $sectorIds)
                    ->where('org_id', $org->id)
                    ->get();
                
                // Luego obtener los RUTs únicos de esos services
                $rutsList = $services->pluck('rut')->unique()->filter();
                
                // Finalmente obtener los members que tengan esos RUTs y pertenezcan a la org
                if ($rutsList->count() > 0) {
                    $members = Member::join('orgs_members', 'members.id', '=', 'orgs_members.member_id')
                        ->where('orgs_members.org_id', $org->id)
                        ->whereIn('members.rut', $rutsList)
                        ->whereNotNull('members.email')
                        ->select('members.*')
                        ->distinct()
                        ->get();
                }
                
                Log::info('Enviando a sectores específicos:', [
                    'sectors' => $sectorIds,
                    'services_found' => $services->count(),
                    'ruts_found' => $rutsList->count(),
                    'memberCount' => $members->count()
                ]);

            }

            // Verificar si encontramos members antes de continuar
            if ($members->count() == 0) {
                Log::warning('⚠️ No se encontraron members para enviar notificaciones');
                return Redirect::back()->with('warning', '⚠️ No se encontraron destinatarios válidos para enviar la notificación. Verifica que los miembros tengan email configurado.');
            }

            // Enviar notificación por email

            Log::info('Iniciando envío de notificaciones');
            Log::info('Iniciando envío de notificaciones', ['members' => $members]);

            $sentCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($members as $member) {
                Log::info('Iniciando envío de notificaciones lista members', ['member_email' => $member->email]);
                
                try {
                    // Verificar configuración SMTP antes de enviar
                    Log::info('Configuración SMTP:', [
                        'host' => Config::get('mail.mailers.smtp.host'),
                        'port' => Config::get('mail.mailers.smtp.port'),
                        'encryption' => Config::get('mail.mailers.smtp.encryption'),
                        'from_address' => Config::get('mail.from.address')
                    ]);

                    Mail::to($member->email)->send(new NotificationMail($title, $message, $org, $member));
                    Log::info('✅ Correo enviado exitosamente a: ' . $member->email);
                    $sentCount++;
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $errorMessage = $e->getMessage();
                    $errors[] = "Error enviando a {$member->email}: {$errorMessage}";
                    
                    Log::error('❌ Error enviando correo a ' . $member->email, [
                        'error' => $errorMessage,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info('📊 Proceso de envío finalizado:', [
                'total_members' => $members->count(),
                'sent_successfully' => $sentCount,
                'failed' => $failedCount
            ]);

            // Preparar mensaje de respuesta basado en resultados
            if ($sentCount > 0 && $failedCount == 0) {
                // Todos los envíos exitosos
                return Redirect::back()->with('success', "✅ Notificación enviada exitosamente a {$sentCount} destinatario(s).");
            } elseif ($sentCount > 0 && $failedCount > 0) {
                // Algunos exitosos, algunos fallaron
                $errorDetails = implode(' | ', array_slice($errors, 0, 3)); // Mostrar solo los primeros 3 errores
                return Redirect::back()
                    ->with('warning', "⚠️ Parcialmente enviado: {$sentCount} exitosos, {$failedCount} fallidos.")
                    ->with('error_details', $errorDetails);
            } else {
                // Todos fallaron
                $errorDetails = implode(' | ', array_slice($errors, 0, 3));
                return Redirect::back()
                    ->with('error', "❌ Error: No se pudo enviar a ningún destinatario. Detalles: {$errorDetails}")
                    ->withInput();
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
