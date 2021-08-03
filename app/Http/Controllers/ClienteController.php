<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Cliente;
use App\Persona;


class ClienteController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        
        if ($buscar==''){
            $personas = Cliente::join('personas','clientes.id','=','personas.id')
            ->select('personas.id','personas.nombre','personas.tipo_documento',
            'personas.num_documento','personas.direccion','personas.telefono',
            'personas.email','clientes.tipo_cliente','clientes.ciudad','clientes.departamento','clientes.pais',
            'clientes.contacto','clientes.telefono_contacto','clientes.email_contacto')
            ->orderBy('personas.id', 'desc')->paginate(25);
        }
        else{
            $personas = Cliente::join('personas','clientes.id','=','personas.id')
            ->select('personas.id','personas.nombre','personas.tipo_documento',
            'personas.num_documento','personas.direccion','personas.telefono',
            'personas.email','clientes.tipo_cliente','clientes.ciudad','clientes.departamento','clientes.pais',
            'clientes.contacto','clientes.telefono_contacto','clientes.email_contacto')      
            ->where('personas.'.$criterio, 'like', '%'. $buscar . '%')
            ->orderBy('personas.id', 'desc')->paginate(25);
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
    public function selectClientes(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $filtro = $request->filtro;
        $clientes = Cliente::join('personas','clientes.id','=','personas.id')
        ->where('personas.nombre', 'like', '%'. $filtro . '%')
        ->orWhere('personas.num_documento', 'like', '%'. $filtro . '%')
        ->select('personas.id','personas.nombre','personas.tipo_documento',
        'personas.num_documento','personas.direccion','personas.telefono',
        'personas.email','clientes.tipo_cliente','clientes.ciudad','clientes.departamento','clientes.pais',
        'clientes.contacto','clientes.telefono_contacto','clientes.email_contacto')
        ->orderBy('personas.nombre', 'asc')->get();

        return ['clientes' => $clientes];

    }
    public function selectCliente(Request $request){
        //if (!$request->ajax()) return redirect('/');
        $id = $request->id;
        $clientes = Cliente::join('personas','clientes.id','=','personas.id')
        ->where('personas.id', '=', $id)->select('*')->get();

        return $clientes;

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
            $cliente->ciudad = $request->ciudad;
            $cliente->departamento = $request->departamento;
            $cliente->pais = $request->pais;
            $cliente->contacto = $request->contacto;
            $cliente->telefono_contacto = $request->telefono_contacto;
            $cliente->email_contacto = $request->email_contacto;
            $cliente->save();

            DB::commit();
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
                $persona = Persona::findOrFail($request->id);
                $cliente = Cliente::findOrFail($request->id);
            }else{
                $cliente = new Cliente();
                $persona = new Persona();
            }
            
            $persona->nombre = $request->nombre;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento = $request->documento;
            $persona->direccion = $request->direccion;
            $persona->telefono = $request->telefono;
            $persona->email = $request->email;
            $persona->save();
    
            $cliente->tipo_cliente = $request->tipo_cliente;
            $cliente->ciudad = $request->ciudad;
            $cliente->departamento = $request->departamento;
            $cliente->pais = $request->pais;
            $cliente->contacto = $request->contacto;
            $cliente->telefono_contacto = $request->telefono_contacto;
            $cliente->email_contacto = $request->email_contacto;
            $cliente->save();
            
            $newCliente = Cliente::join('personas','clientes.id','=','personas.id')->select('*')->get()->last();
            DB::commit();
            return $newCliente;
    
        } catch (Exception $e){
            DB::rollBack();
        }
        
    }
}
