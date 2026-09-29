<?php

namespace App\Http\Controllers;

use App\ClienteEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Cliente;
use App\Detalletrabajo;
use App\Persona;
use App\Datosenvio;
use App\Facturacion;
use App\Contacto;
use App\Ordentrabajo;
use App\Comprobante;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;


class ClienteController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        return $buscar; 
        
        if ($buscar==''){
            $personas = Cliente::join('personas','clientes.id','=','personas.id')
            ->select('*','personas.id','personas.nombre','personas.tipo_documento',
            'personas.num_documento','personas.direccion','personas.telefono',
            'personas.email','clientes.tipo_cliente','clientes.razonsocial', 'clientes.direccionf','clientes.ciudad','clientes.departamento','clientes.pais',
            'clientes.contacto','clientes.sitio_web','clientes.redes_sociales')
            ->orderBy('personas.id', 'asc')->paginate(1000);
        }
        else{
            $personas = Cliente::join('personas','clientes.id','=','personas.id')
            ->select('personas.id','personas.nombre','personas.tipo_documento',
            'personas.num_documento','personas.direccion','personas.telefono',
            'personas.email','clientes.tipo_cliente','clientes.razonsocial', 'clientes.direccionf','clientes.ciudad','clientes.departamento','clientes.pais',
            'clientes.contacto','clientes.sitio_web','clientes.redes_sociales')      
            ->where('clientes.'.$criterio, 'like', '%'. $buscar . '%')
            ->orderBy('personas.id', 'asc')->paginate(1000);

        }
        
        return [
            'pagination' => [
                'total'        => $personas->total(),
                'current_page' => $personas->currentPage(),
                'per_page'     => $personas->perPage(),
                'last_page'    => $personas->lastPage(),
                'from'         => $personas->firstItem(),
                'to'           => $personas->lastItem(),
            ],
            'personas' => $personas
        ];
    }
    public function actualizarClientes(){
        $clientes=Cliente::all();
        foreach ($clientes as $cli) {
            $cli->razonsocial= $cli->personas->nombre;
            $cli->direccionf= $cli->personas->direccion;
            $cli->save();
        }
        return $clientes;
    }
    public function contactos(){
        $contactos=Contacto::all();
        foreach ($contactos as $con) {
            $con->clientes;
        }
        return $contactos;
    }
    public function empresas(){
        $empresas=Facturacion::all();
        foreach ($empresas as $emp) {
            $emp->clientes;
        }
        return $empresas;
    }
    public function envios(){
        $envios=Datosenvio::all();
        foreach ($envios as $env) {
            $env->clientes;
        }
        return $envios;
    }
    public function clientes(Request $request){
        $buscar=$request->buscar;
        $criterio=$request->criterio;
        if($buscar==''){
        $clientes=Cliente::all();
           
        }else{
            $clientes=Cliente::where($criterio, 'LIKE', '%'.$buscar.'%')->get();   
        }
        foreach ($clientes as $cli) {
            $cli->contactos;
            $cli->empresas;
            $cli->envios;
        }
        return $clientes;
    }
    public function crearCuenta(Request $request){
        DB::beginTransaction();
        if($request->id==0){
            $cliente=new Cliente();
        }else{
            $cliente=Cliente::find($request->id);
        }
       
        $cliente->razonsocial=$request->razonsocial;
        $cliente->direccionf=$request->direccionf;
        $cliente->ciudad=$request->ciudad;
        $cliente->departamento=$request->departamento;
        $cliente->pais=$request->pais;
        $cliente->redes_sociales=$request->redes_sociales;
        $cliente->sitio_web=$request->sitio_web;
        $cliente->save();
        DB::commit();
        $contactos = json_decode($request->contactos);
        $empresas=json_decode($request->empresas);
        $envios=json_decode($request->envios);
        if(count($contactos)){
            foreach($contactos as $cont){
                if(isset($cont->id)){
                    $cliente->contactos()->syncWithoutDetaching($cont->id);
                }else{
                    $contacto=new Contacto();
                    $contacto->nombre=$cont->nombre;
                    $contacto->telefono=$cont->telefono;
                    $contacto->telefono_particular=$cont->telefono_particular;
                    $contacto->correo=$cont->correo;
                    $contacto->cargo=$cont->cargo;
                    $contacto->nombre_asistente=$cont->nombre_asistente;
                    $contacto->telefono_asistente=$cont->telefono_asistente;
                    $contacto->fecha_nacimiento='2023-05-24';
                    $contacto->save();
                    $cliente->contactos()->syncWithoutDetaching($contacto->id);
                }
            }
        }
        if(!empty($empresas)){
            foreach($empresas as $emp){
                if(isset($emp->id)){
                    $cliente->empresas()->syncWithoutDetaching($emp->id);
                }else{
                    $empresa=new Facturacion();
                    $empresa->razonsocial=$emp->razonsocial;
                    $empresa->tipo_persona=$emp->tipo_persona;
                    $empresa->tipo_documento=$emp->tipo_documento;
                    $empresa->numero=$emp->numero;
                    $empresa->digito=$emp->digito   ;
                    $empresa->direccion=$emp->direccion;
                    $empresa->telefono=$emp->telefono;
                    $empresa->correo=$emp->correo;
                    $empresa->ciudad=$emp->ciudad;
                    $empresa->departamento=$emp->departamento;
                    $empresa->pais=$emp->pais;
                    $empresa->actividad=$emp->actividad;
                    $empresa->responsable=$emp->responsable;
                    $empresa->save();
                    $cliente->empresas()->syncWithoutDetaching($empresa->id);
                }
            }
        }
        if(!empty($envios)){
            
            foreach($envios as $env){
                
                if(isset($env->id)){
                    $cliente->envios()->syncWithoutDetaching($env->id);
                }else{

                    $envio=new Datosenvio();
                    $envio->idcliente=0;
                    $envio->contacto=$env->contacto;
                    $envio->tipo_documento=$env->tipo_documento;
                    $envio->documento=$env->documento;
                    $envio->telefono=$env->telefono;
                    $envio->direccion=$env->direccion;
                    $envio->ciudad=$env->ciudad;
                    $envio->pais=$env->pais;
                    $envio->save();
                    $cliente->envios()->syncWithoutDetaching($envio->id);
                }
            }
        }

    }
    public function crearContacto(Request $request){
        DB::beginTransaction();
        if($request->id==0){
            $contacto=new Contacto();
        }else{
            $contacto=Contacto::find($request->id);
        }
        $contacto->favorito=0;
        $contacto->nombre=$request->nombre;
        $contacto->telefono=$request->telefono;
        $contacto->telefono_particular=$request->telefono_particular;
        $contacto->correo=$request->correo;
        $contacto->cargo=$request->cargo;
        $contacto->nombre_asistente=$request->nombre_asistente;
        $contacto->telefono_asistente=$request->telefono_asistente;
        $contacto->fecha_nacimiento='2023-05-24';
        $contacto->save();
        DB::commit();
       
        $clientes = json_decode($request->clientes);
        if(count($clientes)){
            foreach($clientes as $cli){
                if(isset($cli->id)){
                    $contacto->clientes()->syncWithoutDetaching($cli->id);
                }else{
                    $persona=new Persona();
                    $cliente=new Cliente();
                    $persona->nombre = $request->razonsocial;
                    $persona->direccion = $request->direccionf;
                    $persona->telefono = $request->telefono;
                    $persona->save();
                   
                    $cliente->razonsocial=$request->razonsocial;
                    $cliente->direccionf=$request->direccionf;
                    $cliente->ciudad=$request->ciudad;
                    $cliente->departamento=$request->departamento;
                    $cliente->pais=$request->pais;
                    $cliente->redes_sociales=$request->redes_sociales;
                    $cliente->sitio_web=$request->sitio_web;
                    $cliente->save();
                    $contacto->clientes()->syncWithoutDetaching($contacto->id);
                }
            }
        }
        
    }
    public function crearEnvios(){
        $envios=Datosenvio::all();
        foreach ($envios as $en) {
            $clienteenvio=new ClienteEnvio();
            $clienteenvio->cliente_id=$en->idcliente;
            $clienteenvio->datosenvio_id=$en->id;
            $clienteenvio->save();
        }   
        
    }
    public function crearEnvio(Request $request){
        DB::beginTransaction();
        if($request->id==0){
            $envio=new Datosenvio();
        }else{
            $envio=Datosenvio::find($request->id);
        }
        $envio->contacto=$request->contacto;
        $envio->favorito=0;
        $envio->empresa=$request->empresa;
        $envio->tipo_documento=$request->tipo_documento;
        $envio->documento=$request->documento;
        $envio->telefono=$request->telefono;
        $envio->direccion=$request->direccion;
        $envio->ciudad=$request->ciudad;
        $envio->pais=$request->pais;
        $envio->save();
        DB::commit();
       
        $clientes = json_decode($request->clientes);
        if(count($clientes)){
            foreach($clientes as $cli){
                if(isset($cli->id)){
                    $envio->clientes()->syncWithoutDetaching($cli->id);
                }else{
                    $persona=new Persona();
                    $cliente=new Cliente();
                    $persona->nombre = $request->razonsocial;
                    $persona->direccion = $request->direccionf;
                    $persona->telefono = $request->telefono;
                    $persona->save();
                   
                    $cliente->razonsocial=$request->razonsocial;
                    $cliente->direccionf=$request->direccionf;
                    $cliente->ciudad=$request->ciudad;
                    $cliente->departamento=$request->departamento;
                    $cliente->pais=$request->pais;
                    $cliente->redes_sociales=$request->redes_sociales;
                    $cliente->sitio_web=$request->sitio_web;
                    $cliente->save();
                    $envio->clientes()->syncWithoutDetaching($envio->id);
                }
            }
        }
        
    }
    public function crearFacturacion(Request $request){
        DB::beginTransaction();
        if($request->id==0){
            $facturacion=new Facturacion();
        }else{
            $facturacion=Facturacion::find($request->id);
        }
        $facturacion->razonsocial=$request->razonsocial;
        $facturacion->favorito=0;
        $facturacion->tipo_persona=$request->tipo_persona;
        $facturacion->tipo_documento=$request->tipo_documento;
        $facturacion->digito=$request->digito;
        $facturacion->direccion=$request->direccion;
        $facturacion->telefono=$request->telefono;
        $facturacion->correo=$request->correo;
        $facturacion->ciudad=$request->ciudad;
        $facturacion->departamento=$request->departamento;
        $facturacion->pais=$request->pais;
        $facturacion->actividad=$request->actividad;
        $facturacion->responsable=$request->responsable;
        $facturacion->save();
        DB::commit();
       
        $clientes = json_decode($request->clientes);
        if(count($clientes)){
            foreach($clientes as $cli){
                if(isset($cli->id)){
                    $facturacion->clientes()->syncWithoutDetaching($cli->id);
                }else{
                    $persona=new Persona();
                    $cliente=new Cliente();
                    $persona->nombre = $request->razonsocial;
                    $persona->direccion = $request->direccionf;
                    $persona->telefono = $request->telefono;
                    $persona->save();
                   
                    $cliente->razonsocial=$request->razonsocial;
                    $cliente->direccionf=$request->direccionf;
                    $cliente->ciudad=$request->ciudad;
                    $cliente->departamento=$request->departamento;
                    $cliente->pais=$request->pais;
                    $cliente->redes_sociales=$request->redes_sociales;
                    $cliente->sitio_web=$request->sitio_web;
                    $cliente->save();
                    $facturacion->clientes()->syncWithoutDetaching($facturacion->id);
                }
            }
        }
        
    }
    public function selectOrdenesCliente(Request $request){
        $ordenes=Ordentrabajo::where('estado','OSP')->Where('cliente_id','=',$request->cliente_id)->get();
        $result = array();
        foreach($ordenes as $orden){
            $orden->cliente;
            $orden->detalles;
            $orden->articulo;
        }
        foreach ($ordenes as $element) {
            $result[$element['fecha']][] = $element;
        }
        return $ordenes;
    }
    public function asignarFavorito(Request $request){

        $clientes=Cliente::find($request->id);
        switch($request->tabla){
            case 'contacto':
                $clientes->contactos();
                foreach ($clientes->contactos as $cont) {
                    if($cont->id==$request->id2){
                        $cont->favorito=1;
                        $cont->save();
                    }else{
                        $cont->favorito=0;
                        $cont->save();
                    }
                }
                return $clientes->contactos;
            break;
            case 'empresa':
                $clientes->empresas();
                foreach ($clientes->empresas as $cont) {
                    if($cont->id==$request->id2){
                        $cont->favorito=1;
                        $cont->save();
                    }else{
                        $cont->favorito=0;
                        $cont->save();
                    }
                }
                return $clientes->empresas;
            break;
            case 'envio':
                $clientes->envios();
                foreach ($clientes->envios as $cont) {
                    if($cont->id==$request->id2){
                        $cont->favorito=1;
                        $cont->save();
                    }else{
                        $cont->favorito=0;
                        $cont->save();
                    }
                }
                return $clientes->envios;
            break;

        }
        
    }    
    public function selectCliente(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $filtro = $request->filtro;
        $clientes = Cliente::join('personas','clientes.id','=','personas.id')
        ->where('personas.nombre', 'like', '%'. $filtro . '%')
        ->orWhere('personas.num_documento', 'like', '%'. $filtro . '%')
        ->select('personas.id','personas.nombre','personas.tipo_documento',
        'personas.num_documento','personas.direccion','personas.telefono',
        'personas.email','clientes.tipo_cliente','clientes.ciudad','clientes.departamento','clientes.pais',
        'clientes.contacto','clientes.sitio_web','clientes.redes_sociales')
        ->orderBy('personas.nombre', 'asc')->get();

        return ['clientes' => $clientes];
    }
    public function selectClientes(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $filtro = $request->filtro;
        $clientes = Cliente::where('razonsocial', 'like', '%'.$filtro.'%')->get();
        foreach ($clientes as $cliente) {
            $cliente->empresas;
            $cliente->contactos;
            $cliente->envios;
        }
        return $clientes;

    }
    public function selectClientebyId(Request $request){
        $id = $request->id;
        $clientes = Cliente::find($id); 
        return $clientes ;
    }
    // public function guias(Request $request){
    //     //if (!$request->ajax()) return redirect('/');
    //     $filtro = $request->filtro;
    //     $clientes = Cliente::join('personas','clientes.id','=','personas.id')->join('ordentrabajos','clientes.id','=','ordentrabajos.cliente_id')
    //     ->whereIn('ordentrabajos.produccion',['EM','E'])
    //     ->select('personas.id','personas.nombre','personas.tipo_documento',
    //     'personas.num_documento','personas.direccion','personas.telefono',
    //     'personas.email','clientes.tipo_cliente','clientes.ciudad','clientes.razonsocial','clientes.direccionf','clientes.departamento','clientes.pais',
    //     'clientes.contacto','clientes.sitio_web','clientes.redes_sociales')
    //     ->orderBy('personas.nombre', 'asc')->get();
    //     $idclientes= array();
       

    //     $result = array();
        
        
    //     $guias=[];
    //     foreach($clientes as $t) {
    //         $idclientes[] = array('id' => $t['id'], 'nombre' => $t['nombre']);
    //         array_push($guias, $t->envios);
    //     }
    //     // for ($i=0; $i < count($idclientes) ; $i++) { 
    //     //     $datos = DB::table('datosenvio')
    //     //     ->select('*')
    //     //     ->where('idcliente', '=', $idclientes[$i]['id'])
    //     //     ->get();
    //     //     if(count($datos)>0){
    //     //         $datos[0]->empresa=$idclientes[$i]['nombre'];
    //     //     }
    //     // }
    //     return $guias;
    // }
    public function guias(Request $request){
        //if (!$request->ajax()) return redirect('/');

        $filtro = $request->filtro;
        $guias=[];
        $clientes = Cliente::all();
        $comprobantes=Comprobante::where('estado',4)->get(0);
        return $comprobantes;
        foreach ($clientes as $cliente) {
            if(count($cliente->comprobante)>0){
                $cliente->envios;
                array_push($guias, $cliente);
            }
        }
        return $guias;

        $result = array();
        
        
        $guias=[];
        foreach($clientes as $t) {
            $idclientes[] = array('id' => $t['id'], 'nombre' => $t['nombre']);
            array_push($guias, $t->envios);
        }
        // for ($i=0; $i < count($idclientes) ; $i++) { 
        //     $datos = DB::table('datosenvio')
        //     ->select('*')
        //     ->where('idcliente', '=', $idclientes[$i]['id'])
        //     ->get();
        //     if(count($datos)>0){
        //         $datos[0]->empresa=$idclientes[$i]['nombre'];
        //     }
        // }
        return $guias;
    }
    public function generarguia(Request $request){
        // $data = [
        //     'title' => 'Welcome to ItSolutionStuff.com',
        //     'date' => date('m/d/Y')
        // ];
          
        // $pdf = PDF::loadHTML('<h1>Test</h1>');
    
        // return $pdf->download('itsolutionstuff.pdf');
        // return $request->guia;
        $guia=json_decode($request->guia);
        
        $path = public_path() . '/reportes/guia.pdf';

        $pdf = PDF::loadView('pdf.guia', compact('guia'));
        $pdf->setPaper('A5', 'landscape');

        $pdf->save($path);
        
        return $pdf->stream();
        // return response()->download($path);
        // $pdf = App::make('dompdf.wrapper');
        // $pdf->loadHTML('<h1>Test</h1>');
        // return $pdf->stream();
    }
   
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();
               
                $persona = new Persona();
                $persona->nombre = $request->nombre;
                $persona->tipo_documento = $request->tipo_documento;
                $persona->num_documento = $request->num_documento;
                $persona->direccion = $request->direccion;
                $persona->telefono = $request->telefono;
                $persona->email = $request->email;
                $persona->save();
                
                $cliente = new Cliente();
                $cliente->id = $persona->id;
                $cliente->tipo_cliente = $request->tipo_cliente;
                $cliente->razonsocial = $request->razonsocial;
                $cliente->direccionf = $request->direccionf;
                $cliente->ciudad = $request->ciudad;
                $cliente->departamento = $request->departamento;
                $cliente->pais = $request->pais;
                $cliente->contacto = $request->contacto;
                $cliente->sitio_web = $request->sitio_web;
                $cliente->redes_sociales = $request->redes_sociales;
                $cliente->save();
                DB::commit();

                if(isset($datos)){
                $datosenvio=new Datosenvio();
                $datosenvio->idcliente=$cliente->id;
                $datosenvio->contacto=$datos[0]['contacto'];
                $datosenvio->empresa=$datos[0]['empresa'];
                $datosenvio->tipo_documento=$datos[0]['tipo_documento'];
                $datosenvio->documento=$datos[0]['documento'];
                $datosenvio->direccion=$datos[0]['direccion'];
                $datosenvio->telefono=$datos[0]['telefono'];
                $datosenvio->ciudad=$datos[0]['ciudad'];
                $datosenvio->pais=$datos[0]['pais'];
                $datosenvio->save();
                }
         

        } catch (Exception $e){
            DB::rollBack();
        }
        
        
    }
   
    
    public function verificarPersona(Request $request){
        $nombre=$request->nombre;
        $persona = Persona::where('nombre', $nombre)->get();
        $resultado=0;
        if(count($persona)>0){
            $resultado=1;
            return $resultado;
        }else{
            $resultado=0;
            return $resultado;
        }
    }
    public function llenarsocial(){
        $persona = Cliente::join('personas','clientes.id','=','personas.id')
            ->select('*','personas.id','personas.nombre','personas.tipo_documento',
            'personas.num_documento','personas.direccion','personas.telefono',
            'personas.email','clientes.tipo_cliente','clientes.razonsocial', 'clientes.direccionf','clientes.ciudad','clientes.departamento','clientes.pais',
            'clientes.contacto','clientes.sitio_web','clientes.redes_sociales')->get();
        foreach($persona as $valor){
            $valor->razonsocial=$valor->nombre;
            $valor->save();
        }
    }

    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();
            if($request->id>0){
                $persona = Persona::findOrFail($request->id);
                $cliente = Cliente::findOrFail($request->id);
            }else{
                $cliente = new Cliente();
                $persona = new Persona();
            }
            $datos=$request->datosenvio;
            if($datos[0]['id']>0){
                $datosenvio = Datosenvio::findOrFail($datos[0]['id']);
            }else{
                $datosenvio=new Datosenvio();
            }
            
            $persona->nombre = $request->nombre;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento = $request->num_documento;
            $persona->direccion = $request->direccion;
            $persona->telefono = $request->telefono;
            $persona->email = $request->email;
            $persona->save();
    
            $cliente->tipo_cliente = $request->tipo_cliente;
            $cliente->razonsocial = $request->razonsocial;
            $cliente->direccionf = $request->direccionf;            
            $cliente->ciudad = $request->ciudad;
            $cliente->departamento = $request->departamento;
            $cliente->pais = $request->pais;
            $cliente->contacto = $request->contacto;
            $cliente->sitio_web = $request->sitio_web;
            $cliente->redes_sociales = $request->redes_sociales;
            $cliente->save();
            
            $newCliente = Cliente::join('personas','clientes.id','=','personas.id')->select('*')->get()->last();
            DB::commit();
            
            $datosenvio->idcliente=$cliente->id;
            $datosenvio->contacto=$datos[0]['contacto'];
            $datosenvio->empresa=$datos[0]['empresa'];
            $datosenvio->tipo_documento=$datos[0]['tipo_documento'];
            $datosenvio->documento=$datos[0]['documento'];
            $datosenvio->direccion=$datos[0]['direccion'];
            $datosenvio->telefono=$datos[0]['telefono'];
            $datosenvio->ciudad=$datos[0]['ciudad'];
            $datosenvio->pais=$datos[0]['pais'];
            $datosenvio->save();

            return $newCliente;
    
        } catch (Exception $e){
            DB::rollBack();
        }
        
    }
    public function devincularCuenta(Request $request)
    {
        $contacto=Contacto::find($request->id);
        $contacto->clientes()->detach([$request->cliente_id]);
    }
    public function devincularContacto(Request $request)
    {
        $cliente=Cliente::find($request->id);
        $cliente->contactos()->detach([$request->contacto_id]);
    }
    public function devincularEmpresa(Request $request)
    {
        $cliente=Cliente::find($request->id);
        $cliente->empresas()->detach([$request->empresa_id]);
    }
    public function devincularEnvio(Request $request)
    {
        $cliente=Cliente::find($request->id);
        $cliente->envios()->detach([$request->envio_id]);
    }
   
}
