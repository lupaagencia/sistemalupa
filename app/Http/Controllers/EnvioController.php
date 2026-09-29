<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Cliente;

use App\Datosenvio;
use App\Facturacion;
use App\Contacto;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;


class EnvioController extends Controller
{
    public function index(Request $request){
        if (!$request->ajax()) return redirect('/');
        $contacto=new Contacto();
        $clientes=$contacto->clientes();
        return $contacto;
    }

    public function contacto(){
        $contactos=Contacto::all();
        foreach ($contactos as $con) {
            return $con->clientes;
        }
        $cliente=$contactos[0]->clientes;
        return $cliente;
    }
    public function actualizarClientes(){
        $clientes=Cliente::all();
        foreach ($clientes as $cli) {
            $cli->save();
        }
        return $clientes;
    }
    public function clientes(){
       
        $clientes=Cliente::all();
        foreach ($clientes as $cli) {
            $cli->contactos;
        }
        return $clientes;
    }

    public function crearContacto(Request $request){
        DB::beginTransaction();
        $cliente=new Cliente();
        $cliente->razonsocial=$request->nombre;
        $cliente->direccionf=$request->direccion;
        $cliente->ciudad=$request->ciudad;
        $cliente->departamento=$request->departamento;
        $cliente->pais=$request->pais;
        $cliente->redes_sociales=$request->redes_sociales;
        $cliente->sitio_web=$request->sitio_web;
        $cliente->tipo_documento='NIT';
        $cliente->num_documento='';
        $cliente->telefono=$request->telefono;
        $cliente->save();
        DB::commit();
        if(!empty($request->nombre_contacto)){
            $contacto=new Contacto();
            $contacto->nombre=$request->nombre_contacto;
            $contacto->telefono=$request->telefono_contacto;
            $contacto->telefono_particular=$request->telefono_particular;
            $contacto->correo=$request->correo;
            $contacto->tipo_contacto=$request->tipo;
            $contacto->cargo=$request->cargo;
            $contacto->nombre_asistente=$request->nombre_asistente;
            $contacto->telefono_asistente=$request->telefono_asistente;
            $contacto->fecha_nacimiento=$request->fecha_nacimiento;
            $contacto->save();
            $cliente->contactos()->syncWithoutDetaching($contacto->id);
        }

    }
   
    public function selectEnvios(Request $request){
            //if (!$request->ajax()) return redirect('/');
            $filtro = $request->filtro;
            $envios = Datosenvio::with('clientes')->where('contacto', 'like', '%'. $filtro . '%')->get();
    
            return $envios;
        

    }
   
   
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();
                
                $cliente = new Cliente();
                $cliente->tipo_cliente = $request->tipo_cliente;
                $cliente->razonsocial = $request->razonsocial;
                $cliente->direccionf = $request->direccionf;
                $cliente->ciudad = $request->ciudad;
                $cliente->departamento = $request->departamento;
                $cliente->pais = $request->pais;
                $cliente->contacto = $request->contacto;
                $cliente->sitio_web = $request->sitio_web;
                $cliente->redes_sociales = $request->redes_sociales;
                $cliente->tipo_documento = $request->tipo_documento;
                $cliente->num_documento = $request->num_documento;
                $cliente->telefono = $request->telefono;
                $cliente->email = $request->email;
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
   
   
 

    public function update(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();
            if($request->id>0){
                $cliente = Cliente::findOrFail($request->id);
            }else{
                $cliente = new Cliente();
            }
            $datos=$request->datosenvio;
            if($datos[0]['id']>0){
                $datosenvio = Datosenvio::findOrFail($datos[0]['id']);
            }else{
                $datosenvio=new Datosenvio();
            }
            
            $cliente->tipo_cliente = $request->tipo_cliente;
            $cliente->razonsocial = $request->razonsocial;
            $cliente->direccionf = $request->direccionf;            
            $cliente->ciudad = $request->ciudad;
            $cliente->departamento = $request->departamento;
            $cliente->pais = $request->pais;
            $cliente->contacto = $request->contacto;
            $cliente->sitio_web = $request->sitio_web;
            $cliente->redes_sociales = $request->redes_sociales;
            $cliente->tipo_documento = $request->tipo_documento;
            $cliente->num_documento = $request->num_documento;
            $cliente->telefono = $request->telefono;
            $cliente->email = $request->email;
            $cliente->save();
            
            $newCliente = Cliente::select('*')->get()->last();
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
   
}
