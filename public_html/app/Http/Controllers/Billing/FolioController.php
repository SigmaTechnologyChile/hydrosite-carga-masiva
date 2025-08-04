<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
// use App\Http\Controllers\SimpleFacturaController; // COMENTADO - SimpleFactura deshabilitado
use Illuminate\Http\Request;
use App\Models\Org;
use App\Models\Folio;
use App\Models\Document;

class FolioController extends Controller
{
    
	function index()
	{
	    $folios = Folio::paginate(20);
	    return view('billings.folios.index',compact('folios'));
	}
	
	function create()
	{
	    $orgs = Org::where('active',1)->get();
	    return view('billings.folios.create',compact('orgs'));
	}
	
	function store(Request $request)
	{
	    $org_id = $request->input('org_id');
	    $qxt = $request->input('qxt');
	    $typedte = $request->input('typeDte');
	    
	    try {
	        
	        // COMENTADO - SimpleFactura deshabilitado temporalmente
	        // $token = (new SimpleFacturaController)->token($org_id);
	        
	        // Retornar mensaje informativo en lugar de procesar
	        return redirect()->back()->with('error', 'La funcionalidad de folios está temporalmente deshabilitada. SimpleFactura en mantenimiento.');
	        
	        // Retornar mensaje informativo en lugar de procesar
	        return redirect()->back()->with('error', 'La funcionalidad de folios está temporalmente deshabilitada. SimpleFactura en mantenimiento.');
	        
	        /*
	        // CÓDIGO ORIGINAL COMENTADO - SimpleFactura
	        if($token){
                $data='{
                          "credenciales": {
                            "rutEmisor": "76269769-6",
                            "nombreSucursal": "Casa Matriz"
                          },
                          "cantidad": '.$qxt.',
                          "codigoTipoDte": '.$typedte.'
                        }';
                $endpoint="folios/solicitar";
                $method="POST";
    
                // $response = (new SimpleFacturaController)->get_ws($data,$token,$method,"sandbox",$endpoint);
                
                /*
                // CÓDIGO ORIGINAL COMENTADO
                $desde = $response->data->desde;
                $hasta = $response->data->hasta;
                
                $folio = new Folio();
                $folio->org_id = $org_id;
                $folio->message = $response->message;
                $folio->codigoSii = $response->data->codigoSii;
                $folio->fechaIngreso = $response->data->fechaIngreso;
                $folio->fechaCaf = $response->data->fechaCaf;
                $folio->desde = $response->data->desde;
                $folio->hasta = $response->data->hasta;
                $folio->fechaVencimiento = $response->data->fechaVencimiento;
                $folio->tipoDte = $response->data->tipoDte;
                $folio->foliosDisponibles = $response->data->foliosDisponibles;
                $folio->ambiente = $response->data->ambiente;
                $folio->errors = $response->errors;;
                $folio->save();
                    
                for($x=$desde;$x<=$hasta;$x++){
                    $dte = new Document();
                    $dte->org_id = $org_id;
                    $dte->folio = $x;
                    $dte->folio = $x;
                    $dte->save();
                }
                dd($response);
                
	        }else{
	            return null;
	        }
	        */
        } catch (\Exception $e) {
            return response()->json(['message'=>'payment not found!'.$e], 404);
        }
	}
}
