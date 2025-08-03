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



            // Obtener destinatarios (members)

            if ($sendToAll) {

                // Obtener todos los members de esta org que tienen email
                $members = Member::whereHas('orgs', function($query) use ($org) {
                        $query->where('org_id', $org->id);
                    })
                    ->whereNotNull('email')
                    ->get();
                    
                Log::info('Enviando a todos los members. Total:', ['count' => $members->count()]);

            } else {

                $sectorIds = $request->input('sectors', []);

                // Obtener members que tienen services en los sectores seleccionados
                $members = Member::whereHas('orgs', function($query) use ($org) {
                        $query->where('org_id', $org->id);
                    })
                    ->whereNotNull('email')
                    ->whereHas('services', function($serviceQuery) use ($sectorIds) {
                        $serviceQuery->whereIn('location_id', $sectorIds);
                    })
                    ->get();

                Log::info('Enviando a sectores específicos:', [

                    'sectors' => $sectorIds,

                    'memberCount' => $members->count()

                ]);

            }



            // Enviar notificación por email

            Log::info('Iniciando envío de notificaciones');

            Log::info('Iniciando envío de notificaciones', ['members' => $members]);


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
        Log::info('Correo enviado exitosamente a: ' . $member->email);
    } catch (\Exception $e) {
        Log::error('Error enviando correo a ' . $member->email, [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

            }

            Log::info('Proceso de envío finalizado');



            return Redirect::back()->with('success', 'Notificación enviada correctamente.');



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
