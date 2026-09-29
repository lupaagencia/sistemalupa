<?php

namespace App\Http\Controllers;

use App\ClienteEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Cliente;
use App\LineaComprobante;
use App\Detalletrabajo;

use App\Datosenvio;
use App\Facturacion;
use App\Contacto;
use App\Ordentrabajo;
use App\Comprobante;
use App\InventariosMateriaPrima;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use App\ReciboPago;
use App\Costois;


use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $personas = Cliente::select(
                    '*'
                )
                ->orderBy('clientes.id', 'desc')->paginate(1000);
        } else {
            $personas = Cliente::select(
                    '*'
                )
                ->where('clientes.' . $criterio, 'like', '%' . $buscar . '%')
                ->orderBy('clientes.id', 'desc')->paginate(1000);
        }

        return [
            'pagination' => [
                'total' => $personas->total(),
                'current_page' => $personas->currentPage(),
                'per_page' => $personas->perPage(),
                'last_page' => $personas->lastPage(),
                'from' => $personas->firstItem(),
                'to' => $personas->lastItem(),
            ],
            'personas' => $personas
        ];
    }
    public function actualizarClientes()
    {
        $clientes = Cliente::all();
        foreach ($clientes as $cli) {
            $cli->save();
        }
        return $clientes;
    }
    public function contactos(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $contactos = Contacto::with('clientes')->orderBy('id', 'desc')->paginate(10);
        } else {
            if ($criterio == 'cuenta') {
                $contactos = Contacto::whereHas('clientes', function ($query) use ($buscar) {
                    $query->where('razonsocial', 'like', '%' . $buscar . '%');
                })->with('clientes')->orderBy('id', 'desc')->paginate(10);
            } else {
                $contactos = Contacto::with('clientes')
                    ->where($criterio, 'like', '%' . $buscar . '%')
                    ->orderBy('id', 'desc')->paginate(10);
            }
        }

        return [
            'pagination' => [
                'total' => $contactos->total(),
                'current_page' => $contactos->currentPage(),
                'per_page' => $contactos->perPage(),
                'last_page' => $contactos->lastPage(),
                'from' => $contactos->firstItem(),
                'to' => $contactos->lastItem(),
            ],
            'contactos' => $contactos
        ];
    }
    public function empresas(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $empresas = Facturacion::with('clientes')->orderBy('id', 'desc')->paginate(10);
        } else {
            if ($criterio == 'cuenta') {
                $empresas = Facturacion::whereHas('clientes', function ($query) use ($buscar) {
                    $query->where('razonsocial', 'like', '%' . $buscar . '%');
                })->with('clientes')->orderBy('id', 'desc')->paginate(10);
            } else {
                $empresas = Facturacion::with('clientes')
                    ->where($criterio, 'like', '%' . $buscar . '%')
                    ->orderBy('id', 'desc')->paginate(10);
            }
        }

        return [
            'pagination' => [
                'total' => $empresas->total(),
                'current_page' => $empresas->currentPage(),
                'per_page' => $empresas->perPage(),
                'last_page' => $empresas->lastPage(),
                'from' => $empresas->firstItem(),
                'to' => $empresas->lastItem(),
            ],
            'empresas' => $empresas
        ];
    }
    public function envios(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $envios = Datosenvio::with('clientes')->orderBy('id', 'desc')->paginate(10);
        } else {
            if ($criterio == 'cuenta') {
                $envios = Datosenvio::whereHas('clientes', function ($query) use ($buscar) {
                    $query->where('razonsocial', 'like', '%' . $buscar . '%');
                })->with('clientes')->orderBy('id', 'desc')->paginate(10);
            } else {
                $envios = Datosenvio::with('clientes')
                    ->where($criterio, 'like', '%' . $buscar . '%')
                    ->orderBy('id', 'desc')->paginate(10);
            }
        }

        return [
            'pagination' => [
                'total' => $envios->total(),
                'current_page' => $envios->currentPage(),
                'per_page' => $envios->perPage(),
                'last_page' => $envios->lastPage(),
                'from' => $envios->firstItem(),
                'to' => $envios->lastItem(),
            ],
            'envios' => $envios
        ];
    }
    public function clientes(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $clientes = Cliente::with(['contactos', 'empresas', 'envios'])->orderBy('id', 'desc')->paginate(10);
        } else {
            $clientes = Cliente::with(['contactos', 'empresas', 'envios'])
                ->where($criterio, 'like', '%' . $buscar . '%')
                ->orderBy('id', 'desc')->paginate(10);
        }

        return [
            'pagination' => [
                'total' => $clientes->total(),
                'current_page' => $clientes->currentPage(),
                'per_page' => $clientes->perPage(),
                'last_page' => $clientes->lastPage(),
                'from' => $clientes->firstItem(),
                'to' => $clientes->lastItem(),
            ],
            'personas' => $clientes
        ];
    }
    public function crearCuenta(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!isset($request->id) || $request->id == 0) {
                $cliente = new Cliente();
            } else {
                $cliente = Cliente::find($request->id);
            }

            $cliente->razonsocial = $request->razonsocial;
            $cliente->telefono = $request->telefono;
            $cliente->direccionf = $request->direccionf;
            $cliente->ciudad = $request->ciudad;
            $cliente->departamento = $request->departamento;
            $cliente->pais = $request->pais;
            $cliente->redes_sociales = $request->redes_sociales;
            $cliente->sitio_web = $request->sitio_web;
            $cliente->tipo_documento = $request->nit ?? '';
            $cliente->num_documento = $request->nit ?? '';
            $cliente->email = $request->email ?? '';
            $cliente->save();

            $contactos = json_decode($request->contactos);
            if (!empty($contactos)) {
                foreach ($contactos as $cont) {
                    if (isset($cont->id) && $cont->id != 0) {
                        $cliente->contactos()->syncWithoutDetaching($cont->id);
                    } else {
                        $contacto = new Contacto();
                        $contacto->nombre = $cont->nombre;
                        $contacto->telefono = $cont->telefono;
                        $contacto->correo = $cont->correo ?? '';
                        $contacto->cargo = $cont->cargo ?? '';
                        $contacto->save();
                        $cliente->contactos()->syncWithoutDetaching($contacto->id);
                    }
                }
            }

            $empresas = json_decode($request->empresas);
            if (!empty($empresas)) {
                foreach ($empresas as $emp) {
                    if (isset($emp->id) && $emp->id != 0) {
                        $cliente->empresas()->syncWithoutDetaching($emp->id);
                    } else {
                        $facturacion = new Facturacion();
                        $facturacion->razonsocial = $emp->razonsocial;
                        $facturacion->tipo_persona = $emp->tipo_persona ?? '';
                        $facturacion->tipo_documento = $emp->tipo_documento ?? '';
                        $facturacion->numero = $emp->numero ?? '';
                        $facturacion->digito = $emp->digito ?? '';
                        $facturacion->direccion = $emp->direccion ?? '';
                        $facturacion->telefono = $emp->telefono ?? '';
                        $facturacion->correo = $emp->correo ?? '';
                        $facturacion->save();
                        $cliente->empresas()->syncWithoutDetaching($facturacion->id);
                    }
                }
            }

            $envios = json_decode($request->envios);
            if (!empty($envios)) {
                foreach ($envios as $env) {
                    if (isset($env->id) && $env->id != 0) {
                        $cliente->envios()->syncWithoutDetaching($env->id);
                    } else {
                        $datosenvio = new Datosenvio();
                        $datosenvio->contacto = $env->contacto;
                        $datosenvio->empresa = $env->empresa ?? '';
                        $datosenvio->direccion = $env->direccion ?? '';
                        $datosenvio->telefono = $env->telefono ?? '';
                        $datosenvio->ciudad = $env->ciudad ?? '';
                        $datosenvio->save();
                        $cliente->envios()->syncWithoutDetaching($datosenvio->id);
                    }
                }
            }
            DB::commit();
            return $cliente;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error en crearCuenta: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function crearContacto(Request $request)
    {
        DB::beginTransaction();
        if ($request->id == 0) {
            $contacto = new Contacto();
        } else {
            $contacto = Contacto::find($request->id);
        }
        $contacto->favorito = 0;
        $contacto->nombre = $request->nombre;
        $contacto->telefono = $request->telefono;
        $contacto->telefono_particular = $request->telefono_particular;
        $contacto->correo = $request->correo;
        $contacto->cargo = $request->cargo;
        $contacto->nombre_asistente = $request->nombre_asistente;
        $contacto->telefono_asistente = $request->telefono_asistente;
        $contacto->fecha_nacimiento = '2023-05-24';
        $contacto->save();
        DB::commit();

        $clientes = json_decode($request->clientes);
        if (count($clientes)) {
            foreach ($clientes as $cli) {
                if (isset($cli->id)) {
                    $contacto->clientes()->syncWithoutDetaching($cli->id);
                } else {
                    $cliente = new Cliente();
                    $cliente->razonsocial = $request->razonsocial;
                    $cliente->telefono = $request->telefono;
                    $cliente->direccionf = $request->direccionf;
                    $cliente->ciudad = $request->ciudad;
                    $cliente->departamento = $request->departamento;
                    $cliente->pais = $request->pais;
                    $cliente->redes_sociales = $request->redes_sociales;
                    $cliente->sitio_web = $request->sitio_web;
                    $cliente->save();
                    $contacto->clientes()->syncWithoutDetaching($cliente->id);
                }
            }
        }

    }
    public function crearEnvios()
    {
        $envios = Datosenvio::all();
        foreach ($envios as $en) {
            $clienteenvio = new ClienteEnvio();
            $clienteenvio->cliente_id = $en->idcliente;
            $clienteenvio->datosenvio_id = $en->id;
            $clienteenvio->save();
        }

    }
    public function crearEnvio(Request $request)
    {
        DB::beginTransaction();
        if ($request->id == 0) {
            $envio = new Datosenvio();
        } else {
            $envio = Datosenvio::find($request->id);
        }
        $envio->contacto = $request->contacto;
        $envio->favorito = 0;
        $envio->empresa = $request->empresa;
        $envio->tipo_documento = $request->tipo_documento;
        $envio->documento = $request->documento;
        $envio->telefono = $request->telefono;
        $envio->direccion = $request->direccion;
        $envio->ciudad = $request->ciudad;
        $envio->pais = $request->pais;
        $envio->save();
        DB::commit();

        $clientes = json_decode($request->clientes);
        if (count($clientes)) {
            foreach ($clientes as $cli) {
                if (isset($cli->id)) {
                    $envio->clientes()->syncWithoutDetaching($cli->id);
                } else {
                    $cliente = new Cliente();
                    $cliente->razonsocial = $request->razonsocial;
                    $cliente->telefono = $request->telefono;
                    $cliente->direccionf = $request->direccionf;
                    $cliente->ciudad = $request->ciudad;
                    $cliente->departamento = $request->departamento;
                    $cliente->pais = $request->pais;
                    $cliente->redes_sociales = $request->redes_sociales;
                    $cliente->sitio_web = $request->sitio_web;
                    $cliente->save();
                    $envio->clientes()->syncWithoutDetaching($cliente->id);
                }
            }
        }

    }
    public function crearFacturacion(Request $request)
    {
        DB::beginTransaction();
        if ($request->id == 0) {
            $facturacion = new Facturacion();
        } else {
            $facturacion = Facturacion::find($request->id);
        }
        $facturacion->razonsocial = $request->razonsocial;
        $facturacion->favorito = 0;
        $facturacion->tipo_persona = $request->tipo_persona;
        $facturacion->tipo_documento = $request->tipo_documento;
        $facturacion->numero = $request->numero;
        $facturacion->digito = $request->digito;
        $facturacion->direccion = $request->direccion;
        $facturacion->telefono = $request->telefono;
        $facturacion->correo = $request->correo;
        $facturacion->ciudad = $request->ciudad;
        $facturacion->departamento = $request->departamento;
        $facturacion->pais = $request->pais;
        $facturacion->actividad = $request->actividad;
        $facturacion->responsable = $request->responsable;
        $facturacion->save();
        DB::commit();

        $clientes = json_decode($request->clientes);
        if (count($clientes)) {
            foreach ($clientes as $cli) {
                if (isset($cli->id)) {
                    $facturacion->clientes()->syncWithoutDetaching($cli->id);
                } else {
                    $cliente = new Cliente();
                    $cliente->razonsocial = $request->razonsocial;
                    $cliente->telefono = $request->telefono;
                    $cliente->direccionf = $request->direccionf;
                    $cliente->ciudad = $request->ciudad;
                    $cliente->departamento = $request->departamento;
                    $cliente->pais = $request->pais;
                    $cliente->redes_sociales = $request->redes_sociales;
                    $cliente->sitio_web = $request->sitio_web;
                    $cliente->save();
                    $facturacion->clientes()->syncWithoutDetaching($cliente->id);
                }
            }
        }

    }
    public function selectOrdenesCliente(Request $request)
    {
        $ordenes = Ordentrabajo::Where('cliente_id', '=', $request->cliente_id)->get();
        $ultimoPorArticulo = collect($ordenes)
            ->groupBy('articulo_id')
            ->map(function ($grupo) {
                return $grupo->last(); // devuelve el último del grupo
            });
        $result = collect();
        foreach ($ultimoPorArticulo as $orden) {
            $linea = new LineaComprobante();
            if ($linea = $orden->linea) {
                $linea->orden->detalles;
                $linea->orden->costos;
                $linea->orden->costos->id = 0;
                $linea->articulo;
                $linea->orden->planchas = InventariosMateriaPrima::where('asignado_id', $linea->orden->cliente_id)->where('tipo', 'Plancha')->get();
                foreach ($linea->orden->detalles as $detalle) {
                    if ($detalle->titulo == 'Tinta' || $detalle->titulo == 'tinta') {
                        if (is_array(json_decode($detalle->valor))) {

                            $detalle->valor = json_decode($detalle->valor);
                        }
                    }

                    $detalle->costo;
                    if (!is_null($detalle->costos_id) || $detalle->costos_id != 0) {
                        if (isset($detalle->costo->costois)) {

                        }

                    }
                    if ($detalle->costo) {
                        $detalle->costo->id = 0;
                        $detalle->costos_id = 0;
                    }
                    $detalle->id = 0;
                }
                $linea->orden->id = 0;
                $result->push($linea);
            }
        }
        $arrayPlano = $result->toArray();
        return $arrayPlano;
        foreach ($ordenes as $element) {
            $result[$element['fecha']][] = $element;
        }
    }
    public function asignarFavorito(Request $request)
    {

        $clientes = Cliente::find($request->id);
        switch ($request->tabla) {
            case 'contacto':
                $clientes->contactos();
                foreach ($clientes->contactos as $cont) {
                    if ($cont->id == $request->id2) {
                        $cont->favorito = 1;
                        $cont->save();
                    } else {
                        $cont->favorito = 0;
                        $cont->save();
                    }
                }
                return $clientes->contactos;
                break;
            case 'empresa':
                $clientes->empresas();
                foreach ($clientes->empresas as $cont) {
                    if ($cont->id == $request->id2) {
                        $cont->favorito = 1;
                        $cont->save();
                    } else {
                        $cont->favorito = 0;
                        $cont->save();
                    }
                }
                return $clientes->empresas;
                break;
            case 'envio':
                $clientes->envios();
                foreach ($clientes->envios as $cont) {
                    if ($cont->id == $request->id2) {
                        $cont->favorito = 1;
                        $cont->save();
                    } else {
                        $cont->favorito = 0;
                        $cont->save();
                    }
                }
                return $clientes->envios;
                break;

        }

    }
    public function selectCliente(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');
        $filtro = $request->filtro;
        $clientes = Cliente::where('razonsocial', 'like', '%' . $filtro . '%')
            ->orWhere('num_documento', 'like', '%' . $filtro . '%')
            ->select('id', 'razonsocial', 'razonsocial as nombre', 'num_documento', 'ciudad')
            ->orderBy('razonsocial', 'asc')->get();

        return ['clientes' => $clientes];
    }
    public function selectClientes(Request $request)
    {
        $filtro = $request->filtro;

        // Búsqueda robusta por Razón Social o por Nombre de Contactos relacionados
        $clientes = Cliente::with(['contactos', 'empresas', 'envios'])
            ->select('id', 'razonsocial', 'razonsocial as nombre', 'num_documento', 'ciudad', 'telefono', 'email', 'tipo_cliente')
            ->where('razonsocial', 'like', '%' . $filtro . '%')
            ->orWhereHas('contactos', function ($query) use ($filtro) {
                $query->where('nombre', 'like', '%' . $filtro . '%');
            })
            ->limit(50)
            ->get();

        return ['clientes' => $clientes];
    }
    public function selectClientebyId(Request $request)
    {
        $id = $request->id;
        $clientes = Cliente::find($id);
        return $clientes;
    }

    public function selectFacturacion(Request $request)
    {
        $filtro = $request->filtro;
        $facturaciones = Facturacion::where('razonsocial', 'like', '%' . $filtro . '%')
            ->orWhere('nit', 'like', '%' . $filtro . '%')
            ->orderBy('razonsocial', 'asc')->get();
        return ['facturaciones' => $facturaciones];
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
    public function guias(Request $request)
    {
        //if (!$request->ajax()) return redirect('/');

        $filtro = $request->filtro;
        $guias = [];
        $clientes = Cliente::all();
        $comprobantes = Comprobante::where('estado', 4)->get();

        foreach ($comprobantes as $comprobante) {
            $comprobante->cliente->envios;
            array_push($guias, $comprobante->cliente);
        }
        return $guias;

        $result = array();


        $guias = [];
        // foreach($clientes as $t) {
        //     $idclientes[] = array('id' => $t['id'], 'nombre' => $t['nombre']);
        //     array_push($guias, $t->envios);
        // }
        // // for ($i=0; $i < count($idclientes) ; $i++) { 
        // //     $datos = DB::table('datosenvio')
        // //     ->select('*')
        // //     ->where('idcliente', '=', $idclientes[$i]['id'])
        // //     ->get();
        // //     if(count($datos)>0){
        // //         $datos[0]->empresa=$idclientes[$i]['nombre'];
        // //     }
        // // }
        // return $guias;
    }
    public function generarguia(Request $request)
    {
        // $data = [
        //     'title' => 'Welcome to ItSolutionStuff.com',
        //     'date' => date('m/d/Y')
        // ];

        // $pdf = PDF::loadHTML('<h1>Test</h1>');

        // return $pdf->download('itsolutionstuff.pdf');
        // return $request->guia;
        $guia = json_decode($request->guia);
        $path = public_path() . '/reportes/guia.pdf';

        $pdf = PDF::loadView('pdf.guia', compact('guia'));
        $pdf->setPaper('carta', 'portrait');

        $pdf->save($path);

        return $pdf->stream();
        // return response()->download($path);
        // $pdf = App::make('dompdf.wrapper');
        // $pdf->loadHTML('<h1>Test</h1>');
        // return $pdf->stream();
    }

    public function store(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        try {
            DB::beginTransaction();

            $cliente = new Cliente();
            $cliente->tipo_cliente = $request->tipo_cliente;
            $cliente->razonsocial = $request->razonsocial;
            $cliente->telefono = $request->telefono;
            $cliente->direccionf = $request->direccionf;
            $cliente->ciudad = $request->ciudad;
            $cliente->departamento = $request->departamento;
            $cliente->pais = $request->pais;
            $cliente->contacto = $request->contacto;
            $cliente->sitio_web = $request->sitio_web;
            $cliente->redes_sociales = $request->redes_sociales;
            $cliente->tipo_documento = $request->tipo_documento;
            $cliente->num_documento = $request->num_documento;
            $cliente->email = $request->email;
            $cliente->save();
            DB::commit();

            if (isset($datos)) {
                $datosenvio = new Datosenvio();
                $datosenvio->idcliente = $cliente->id;
                $datosenvio->contacto = $datos[0]['contacto'];
                $datosenvio->empresa = $datos[0]['empresa'];
                $datosenvio->tipo_documento = $datos[0]['tipo_documento'];
                $datosenvio->documento = $datos[0]['documento'];
                $datosenvio->direccion = $datos[0]['direccion'];
                $datosenvio->telefono = $datos[0]['telefono'];
                $datosenvio->ciudad = $datos[0]['ciudad'];
                $datosenvio->pais = $datos[0]['pais'];
                $datosenvio->save();
            }


        } catch (Exception $e) {
            DB::rollBack();
        }


    }


    public function verificarPersona(Request $request)
    {
        $nombre = $request->nombre;
        $persona = Cliente::where('razonsocial', $nombre)->get();
        $resultado = 0;
        if (count($persona) > 0) {
            $resultado = 1;
            return $resultado;
        } else {
            $resultado = 0;
            return $resultado;
        }
    }


    public function update(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');
        try {
            DB::beginTransaction();
            if ($request->id > 0) {
                $cliente = Cliente::findOrFail($request->id);
            } else {
                $cliente = new Cliente();
            }
            $datos = $request->datosenvio;
            if ($datos[0]['id'] > 0) {
                $datosenvio = Datosenvio::findOrFail($datos[0]['id']);
            } else {
                $datosenvio = new Datosenvio();
            }

            $cliente->tipo_cliente = $request->tipo_cliente;
            $cliente->razonsocial = $request->razonsocial;
            $cliente->tipo_documento = $request->tipo_documento;
            $cliente->num_documento = $request->num_documento;
            $cliente->direccionf = $request->direccion;
            $cliente->telefono = $request->telefono;
            $cliente->email = $request->email;
            $cliente->ciudad = $request->ciudad;
            $cliente->departamento = $request->departamento;
            $cliente->pais = $request->pais;
            $cliente->contacto = $request->contacto;
            $cliente->sitio_web = $request->sitio_web;
            $cliente->redes_sociales = $request->redes_sociales;
            $cliente->save();

            $newCliente = Cliente::all()->last();
            DB::commit();

            $datosenvio->idcliente = $cliente->id;
            $datosenvio->contacto = $datos[0]['contacto'];
            $datosenvio->empresa = $datos[0]['empresa'];
            $datosenvio->tipo_documento = $datos[0]['tipo_documento'];
            $datosenvio->documento = $datos[0]['documento'];
            $datosenvio->direccion = $datos[0]['direccion'];
            $datosenvio->telefono = $datos[0]['telefono'];
            $datosenvio->ciudad = $datos[0]['ciudad'];
            $datosenvio->pais = $datos[0]['pais'];
            $datosenvio->save();

            return $newCliente;

        } catch (Exception $e) {
            DB::rollBack();
        }

    }
    public function devincularCuenta(Request $request)
    {
        $contacto = Contacto::find($request->id);
        $contacto->clientes()->detach([$request->cliente_id]);
    }
    public function devincularContacto(Request $request)
    {
        $cliente = Cliente::find($request->id);
        $cliente->contactos()->detach([$request->contacto_id]);
    }
    public function devincularEmpresa(Request $request)
    {
        $cliente = Cliente::find($request->id);
        $cliente->empresas()->detach([$request->empresa_id]);
    }
    public function devincularEnvio(Request $request)
    {
        $cliente = Cliente::find($request->id);
        $cliente->envios()->detach([$request->envio_id]);
    }

    public function eliminarContacto(Request $request)
    {
        $id = $request->id;
        $contacto = Contacto::findOrFail($id);

        // Desvincular de todos los clientes antes de eliminar
        $contacto->clientes()->detach();
        $contacto->delete();
    }

    public function eliminarFacturacion(Request $request)
    {
        $id = $request->id;

        // Verificar si está vinculado a algún comprobante
        $comprobantes = Comprobante::where('datos_factura_id', $id)->limit(5)->get(['id', 'num_comprobante', 'tipo']);

        if ($comprobantes->count() > 0) {
            $lista = $comprobantes->map(function ($c) {
                return $c->tipo . " #" . ($c->num_comprobante ?? $c->id);
            })->implode(', ');
            return response()->json([
                'error' => 'No se puede eliminar: Registro de facturación en uso.',
                'detalles' => 'Vinculado a: ' . $lista . (count($comprobantes) >= 5 ? '...' : ''),
                'solucion' => 'Solución: Cambie los datos de facturación en los comprobantes mencionados o elimínelos primero.'
            ], 422);
        }

        $facturacion = Facturacion::findOrFail($id);
        $facturacion->clientes()->detach();
        $facturacion->delete();
    }

    public function eliminarEnvio(Request $request)
    {
        $id = $request->id;
        $envio = Datosenvio::findOrFail($id);

        // Desvincular de clientes
        $envio->clientes()->detach();
        $envio->delete();
    }

    public function eliminarCliente(Request $request)
    {
        $id = $request->id;
        $cliente = Cliente::with(['contactos', 'empresas', 'envios'])->findOrFail($id);

        // Verificar órdenes de trabajo y comprobantes (Causa bloqueo fuerte)
        $ordenes = Ordentrabajo::where('cliente_id', $id)->limit(5)->pluck('id')->toArray();
        $comprobantes = Comprobante::where('cliente_id', $id)->limit(5)->get(['id', 'num_comprobante', 'tipo']);

        // Construir detalles de conexiones
        $detalles = "";
        if (!empty($ordenes)) {
            $detalles .= "<b>Órdenes:</b> " . implode(', ', $ordenes) . ".<br>";
        }
        if ($comprobantes->count() > 0) {
            $compStr = $comprobantes->map(function ($c) {
                return $c->tipo . " #" . ($c->num_comprobante ?? $c->id);
            })->implode(', ');
            $detalles .= "<b>Comprobantes:</b> " . $compStr . ".<br>";
        }

        if ($cliente->contactos->count() > 0) {
            $detalles .= "<b>Contactos:</b> " . $cliente->contactos->count() . " vinculado(s) (" . $cliente->contactos->take(3)->pluck('nombre')->implode(', ') . ").<br>";
        }
        if ($cliente->empresas->count() > 0) {
            $detalles .= "<b>Facturación:</b> " . $cliente->empresas->count() . " registro(s) (" . $cliente->empresas->take(3)->pluck('razonsocial')->implode(', ') . ").<br>";
        }
        if ($cliente->envios->count() > 0) {
            $detalles .= "<b>Envíos:</b> " . $cliente->envios->count() . " dirección(es) (" . $cliente->envios->take(3)->pluck('contacto')->implode(', ') . ").<br>";
        }

        // Si hay cualquier conexión, mostramos la ventana de aviso/reasignación
        if ($detalles != "") {
            Log::info("Intento de eliminación de cliente $id bloqueado por: " . strip_tags($detalles));
            $hasHistory = !empty($ordenes) || $comprobantes->count() > 0;
            return response()->json([
                'error' => $hasHistory ? 'No se puede eliminar: La cuenta tiene historial contable.' : 'Atención: La cuenta tiene registros vinculados.',
                'detalles' => $detalles,
                'solucion' => 'Solución: Puede usar el botón <b>Reasignar</b> para mover estos datos a otra cuenta, o cancelarlos manualmente antes de borrar.'
            ], 422);
        }

        $cliente->contactos()->detach();
        $cliente->empresas()->detach();
        $cliente->envios()->detach();

        $cliente->delete();
    }
    public function reasignarCliente(Request $request)
    {
        $id_origen = $request->id_origen;
        $id_destino = $request->id_destino;

        if ($id_origen == $id_destino) {
            return response()->json(['error' => 'El destino no puede ser el mismo origen.'], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Reasignar Ordentrabajo
            Ordentrabajo::where('cliente_id', $id_origen)->update(['cliente_id' => $id_destino]);
            
            // 2. Reasignar Comprobantes
            Comprobante::where('cliente_id', $id_origen)->update(['cliente_id' => $id_destino]);

            // 2.1 Reasignar Comprobantes en tablas de respaldo (comprobantes2) si existen
            if (DB::getSchemaBuilder()->hasTable('comprobantes2')) {
                DB::table('comprobantes2')->where('cliente_id', $id_origen)->update(['cliente_id' => $id_destino]);
            }
            
            // 3. Reasignar Recibos de Pago
            ReciboPago::where('cliente_id', $id_origen)->update(['cliente_id' => $id_destino]);
            
            // 4. Reasignar Costos (idpersona)
            Costois::where('idpersona', $id_origen)->update(['idpersona' => $id_destino]);
            
            // 5. Reasignar Inventarios (Planchas, etc)
            InventariosMateriaPrima::where('asignado_id', $id_origen)->update(['asignado_id' => $id_destino]);
            
            // 6. Reasignar Ingresos (Raw DB update as column names might vary)
            DB::table('ingresos')->where('cliente_id', $id_origen)->update(['cliente_id' => $id_destino]);
            
            // 7. Reasignar Datosenvio (Legacy direct link)
            DB::table('datosenvio')->where('idcliente', $id_origen)->update(['idcliente' => $id_destino]);

            // 8. Reasignar Contactos (Pivot) - Manejando duplicados
            $contactosOrigen = DB::table('cliente_contacto')->where('cliente_id', $id_origen)->pluck('contacto_id');
            foreach ($contactosOrigen as $contactoId) {
                $exists = DB::table('cliente_contacto')->where('cliente_id', $id_destino)->where('contacto_id', $contactoId)->exists();
                if (!$exists) {
                    DB::table('cliente_contacto')->insert(['cliente_id' => $id_destino, 'contacto_id' => $contactoId]);
                }
            }

            // 9. Reasignar Facturaciones (Pivot) - Manejando duplicados
            $facturacionesOrigen = DB::table('cliente_factura')->where('cliente_id', $id_origen)->pluck('facturacion_id');
            foreach ($facturacionesOrigen as $factId) {
                $exists = DB::table('cliente_factura')->where('cliente_id', $id_destino)->where('facturacion_id', $factId)->exists();
                if (!$exists) {
                    DB::table('cliente_factura')->insert(['cliente_id' => $id_destino, 'facturacion_id' => $factId]);
                }
            }

            // 10. Reasignar Envíos (Pivot) - Manejando duplicados
            $enviosOrigen = DB::table('cliente_envio')->where('cliente_id', $id_origen)->pluck('datosenvio_id');
            foreach ($enviosOrigen as $envioId) {
                $exists = DB::table('cliente_envio')->where('cliente_id', $id_destino)->where('datosenvio_id', $envioId)->exists();
                if (!$exists) {
                    DB::table('cliente_envio')->insert(['cliente_id' => $id_destino, 'datosenvio_id' => $envioId]);
                }
            }

            // Ahora eliminar el origen (limpio)
            $this->eliminarRelacionesYBorrarCliente($id_origen);

            DB::commit();
            return ['status' => 'success'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error en reasignarCliente: " . $e->getMessage());
            return response()->json(['error' => 'Error al reasignar registros: ' . $e->getMessage()], 500);
        }
    }

    protected function eliminarRelacionesYBorrarCliente($id)
    {
        $cliente = Cliente::find($id);

        if ($cliente) {
            $cliente->contactos()->detach();
            $cliente->empresas()->detach();
            $cliente->envios()->detach();
            $cliente->delete();
        }
    }

    public function reasignarFacturacion(Request $request)
    {
        $id_origen = $request->id_origen;
        $id_destino = $request->id_destino;

        if ($id_origen == $id_destino) {
            return response()->json(['error' => 'El destino no puede ser el mismo origen.'], 422);
        }

        DB::beginTransaction();
        try {
            Comprobante::where('datos_factura_id', $id_origen)->update(['datos_factura_id' => $id_destino]);

            $facturacion = Facturacion::findOrFail($id_origen);
            $facturacion->clientes()->detach();
            $facturacion->delete();

            DB::commit();
            return ['status' => 'success'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error en reasignarFacturacion: " . $e->getMessage());
            return response()->json(['error' => 'Error al reasignar registros: ' . $e->getMessage()], 500);
        }
    }
}
