<?php

namespace App\Http\Controllers;

use App\CrmCotizacion;
use App\CrmCotizacionDetalle;
use App\CrmProspecto;
use App\Persona;
use App\Comprobante;
use App\LineaComprobante;
use App\Articulo;
use App\Ordentrabajo;
use App\Detalletrabajo;
use App\CostoProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Barryvdh\DomPDF\Facade\Pdf;

class CrmCotizacionController extends Controller
{
    public function index(Request $request)
    {
        $vendedor_id = $request->vendedor_id;
        $buscar = $request->buscar;
        $estado = $request->estado;

        $query = CrmCotizacion::with([
            'vendedor:id,usuario,email',
            'prospecto:id,nombre,empresa,email,telefono',
            'cliente:id,razonsocial,num_documento,telefono,email',
            'oportunidad:id,codigo,nombre'
        ])->crmScope($vendedor_id);

        if ($buscar != '') {
            $query->where(function($q) use ($buscar) {
                $q->where('numero_cotizacion', 'like', '%' . $buscar . '%')
                  ->orWhereHas('prospecto', function($p) use ($buscar) {
                      $p->where('nombre', 'like', '%' . $buscar . '%')->orWhere('empresa', 'like', '%' . $buscar . '%');
                  })
                  ->orWhereHas('cliente', function($c) use ($buscar) {
                      $c->where('razonsocial', 'like', '%' . $buscar . '%')->orWhere('num_documento', 'like', '%' . $buscar . '%');
                  });
            });
        }

        if ($estado != '') {
            $query->where('estado', $estado);
        }

        $cotizaciones = $query->orderBy('id', 'desc')->paginate(20);

        return [
            'pagination' => [
                'total'        => $cotizaciones->total(),
                'current_page' => $cotizaciones->currentPage(),
                'per_page'     => $cotizaciones->perPage(),
                'last_page'    => $cotizaciones->lastPage(),
                'from'         => $cotizaciones->firstItem(),
                'to'           => $cotizaciones->lastItem(),
            ],
            'cotizaciones' => $cotizaciones
        ];
    }

    private static function checkCotizadorColumns()
    {
        if (!Schema::hasColumn('crm_cotizacion_detalles', 'cotizador_tipo')) {
            try {
                Schema::table('crm_cotizacion_detalles', function (Blueprint $table) {
                    $table->string('cotizador_tipo', 20)->nullable();
                    $table->longText('cotizador_data')->nullable();
                });
            } catch (\Exception $e) {
                // Ignore if already created
            }
        }
    }

    public function show($id)
    {
        self::checkCotizadorColumns();

        $cotizacion = CrmCotizacion::with([
            'vendedor:id,usuario,email',
            'prospecto',
            'cliente',
            'oportunidad',
            'detalles.articulo'
        ])->crmScope()->findOrFail($id);

        foreach ($cotizacion->detalles as $det) {
            $det->_cotizadorTipo = $det->cotizador_tipo ?: 'M';
            if ($det->cotizador_data) {
                try {
                    $det->_productoRaw = json_decode($det->cotizador_data, true);
                } catch (\Exception $e) {
                    $det->_productoRaw = null;
                }
            }
        }

        return ['cotizacion' => $cotizacion];
    }

    public static function getSiguienteNumeroCotizacion()
    {
        $year = date('Y');
        $prefix = 'COT-' . $year . '-';

        $cotizaciones = CrmCotizacion::pluck('numero_cotizacion')->toArray();

        $takenNumbers = [];
        $exactTaken = [];

        foreach ($cotizaciones as $num) {
            $numStr = trim((string)$num);
            $exactTaken[strtoupper($numStr)] = true;

            if (preg_match('/^COT\-' . $year . '\-(\d+)$/i', $numStr, $matches)) {
                $takenNumbers[intval($matches[1])] = true;
            } elseif (preg_match('/^(\d+)$/', $numStr, $matches)) {
                $takenNumbers[intval($matches[1])] = true;
            }
        }

        $consecutivo = 1;
        while (true) {
            $formatted = $prefix . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
            if (!isset($takenNumbers[$consecutivo]) && !isset($exactTaken[strtoupper($formatted)])) {
                return $formatted;
            }
            $consecutivo++;
        }
    }

    public function siguienteNumero()
    {
        return response()->json([
            'siguiente' => self::getSiguienteNumeroCotizacion()
        ]);
    }

    public function store(Request $request)
    {
        self::checkCotizadorColumns();

        $this->validate($request, [
            'fecha_emision' => 'required|date',
            'numero_cotizacion' => 'nullable|string|unique:crm_cotizaciones,numero_cotizacion',
            'detalles' => 'required|array|min:1',
            'detalles.*.concepto' => 'required|string',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ], [
            'numero_cotizacion.unique' => 'El número de cotización ya existe en el sistema.',
        ]);

        try {
            DB::beginTransaction();

            if ($request->filled('numero_cotizacion')) {
                $numeroCotizacion = trim($request->numero_cotizacion);
            } else {
                $numeroCotizacion = self::getSiguienteNumeroCotizacion();
            }

            $cotizacion = new CrmCotizacion();
            $cotizacion->numero_cotizacion = $numeroCotizacion;
            $cotizacion->prospecto_id = $request->filled('prospecto_id') ? $request->prospecto_id : null;
            $cotizacion->cliente_id = $request->filled('cliente_id') ? $request->cliente_id : null;
            $cotizacion->oportunidad_id = $request->oportunidad_id;
            $cotizacion->fecha_emision = $request->fecha_emision;
            $cotizacion->fecha_vencimiento = $request->fecha_vencimiento ?: date('Y-m-d', strtotime($request->fecha_emision . ' + 15 days'));
            $cotizacion->subtotal = $request->input('subtotal', 0);
            $cotizacion->descuento = $request->input('descuento', 0);
            $cotizacion->iva = $request->input('iva', 0);
            $cotizacion->total = $request->input('total', 0);
            $cotizacion->estado = $request->input('estado', 'Borrador');
            $cotizacion->condiciones_pago = $request->condiciones_pago;
            $cotizacion->observaciones = $request->observaciones;

            if ($request->filled('user_id')) {
                $cotizacion->user_id = $request->user_id;
            } else {
                $cotizacion->user_id = Auth::id();
            }

            $cotizacion->save();

            foreach ($request->detalles as $det) {
                $detalle = new CrmCotizacionDetalle();
                $detalle->cotizacion_id = $cotizacion->id;
                $detalle->articulo_id = isset($det['articulo_id']) ? $det['articulo_id'] : null;
                $detalle->concepto = $det['concepto'];
                $detalle->descripcion = isset($det['descripcion']) ? $det['descripcion'] : null;
                $detalle->cantidad = $det['cantidad'];
                $detalle->precio_unitario = $det['precio_unitario'];
                $detalle->descuento_porcentaje = isset($det['descuento_porcentaje']) ? $det['descuento_porcentaje'] : 0;
                $detalle->subtotal = isset($det['subtotal']) ? $det['subtotal'] : ($det['cantidad'] * $det['precio_unitario']);
                $detalle->iva = isset($det['iva']) ? $det['iva'] : 0;
                $detalle->total = isset($det['total']) ? $det['total'] : $detalle->subtotal + $detalle->iva;
                $detalle->cotizador_tipo = isset($det['_cotizadorTipo']) ? $det['_cotizadorTipo'] : (isset($det['cotizador_tipo']) ? $det['cotizador_tipo'] : 'M');
                $detalle->cotizador_data = isset($det['_productoRaw']) && !empty($det['_productoRaw']) ? (is_string($det['_productoRaw']) ? $det['_productoRaw'] : json_encode($det['_productoRaw'])) : null;
                $detalle->save();
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Cotización registrada con éxito', 'cotizacion' => $cotizacion]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar cotización: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        self::checkCotizadorColumns();

        $this->validate($request, [
            'id' => 'required|exists:crm_cotizaciones,id',
            'fecha_emision' => 'required|date',
            'numero_cotizacion' => 'nullable|string|unique:crm_cotizaciones,numero_cotizacion,' . $request->id,
            'detalles' => 'required|array|min:1',
        ], [
            'numero_cotizacion.unique' => 'El número de cotización ya existe en el sistema.',
        ]);

        try {
            DB::beginTransaction();

            $cotizacion = CrmCotizacion::crmScope()->findOrFail($request->id);
            if ($request->filled('numero_cotizacion')) {
                $cotizacion->numero_cotizacion = trim($request->numero_cotizacion);
            }
            $cotizacion->prospecto_id = $request->filled('prospecto_id') ? $request->prospecto_id : null;
            $cotizacion->cliente_id = $request->filled('cliente_id') ? $request->cliente_id : null;
            $cotizacion->oportunidad_id = $request->oportunidad_id;
            $cotizacion->fecha_emision = $request->fecha_emision;
            $cotizacion->fecha_vencimiento = $request->fecha_vencimiento;
            $cotizacion->subtotal = $request->subtotal;
            $cotizacion->descuento = $request->descuento;
            $cotizacion->iva = $request->iva;
            $cotizacion->total = $request->total;
            $cotizacion->estado = $request->estado;
            $cotizacion->condiciones_pago = $request->condiciones_pago;
            $cotizacion->observaciones = $request->observaciones;

            if ($request->filled('user_id')) {
                $cotizacion->user_id = $request->user_id;
            }

            $cotizacion->save();

            // Re-create details
            CrmCotizacionDetalle::where('cotizacion_id', $cotizacion->id)->delete();

            foreach ($request->detalles as $det) {
                $detalle = new CrmCotizacionDetalle();
                $detalle->cotizacion_id = $cotizacion->id;
                $detalle->articulo_id = isset($det['articulo_id']) ? $det['articulo_id'] : null;
                $detalle->concepto = $det['concepto'];
                $detalle->descripcion = isset($det['descripcion']) ? $det['descripcion'] : null;
                $detalle->cantidad = $det['cantidad'];
                $detalle->precio_unitario = $det['precio_unitario'];
                $detalle->descuento_porcentaje = isset($det['descuento_porcentaje']) ? $det['descuento_porcentaje'] : 0;
                $detalle->subtotal = isset($det['subtotal']) ? $det['subtotal'] : ($det['cantidad'] * $det['precio_unitario']);
                $detalle->iva = isset($det['iva']) ? $det['iva'] : 0;
                $detalle->total = isset($det['total']) ? $det['total'] : $detalle->subtotal + $detalle->iva;
                $detalle->cotizador_tipo = isset($det['_cotizadorTipo']) ? $det['_cotizadorTipo'] : (isset($det['cotizador_tipo']) ? $det['cotizador_tipo'] : 'M');
                $detalle->cotizador_data = isset($det['_productoRaw']) && !empty($det['_productoRaw']) ? (is_string($det['_productoRaw']) ? $det['_productoRaw'] : json_encode($det['_productoRaw'])) : null;
                $detalle->save();
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Cotización actualizada con éxito']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar cotización: ' . $e->getMessage()], 500);
        }
    }

    public function cambiarEstado(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:crm_cotizaciones,id',
            'estado' => 'required|string',
        ]);

        $cotizacion = CrmCotizacion::crmScope()->findOrFail($request->id);
        $cotizacion->estado = $request->estado;
        $cotizacion->save();

        return response()->json(['status' => 'success', 'message' => 'Estado de la cotización actualizado a ' . $request->estado]);
    }

    public function destroy(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar cotizaciones.'], 403);
        }
        $cotizacion = CrmCotizacion::crmScope()->findOrFail($request->id);
        $cotizacion->delete();

        return response()->json(['status' => 'success', 'message' => 'Cotización eliminada']);
    }

    public function pdf($id)
    {
        $cotizacion = CrmCotizacion::with([
            'vendedor.empleado',
            'vendedor.persona',
            'prospecto',
            'cliente.empresas',
            'cliente.envios',
            'cliente.contactos',
            'oportunidad',
            'detalles.articulo'
        ])->findOrFail($id);

        // 1. Configuración de datos de la empresa desde Ajustes
        $empresaConfig = \App\Ajustes::where('tipo', 'empresa')->pluck('valor', 'detalle')->toArray();
        $defaults = [
            'nombre' => 'LUPACK (AGENCIA LUPA S.A.S.)',
            'slogan' => 'Soluciones Integrales en Empaques e Impresión',
            'nit' => '901086443-7',
            'telefono' => '+57 316 5288931',
            'email' => 'contacto@lupack.com',
            'direccion' => 'Carrera 1 # 23-60, Cali - Colombia',
            'logo' => 'img/LOGO-LUPA.jpg'
        ];
        $empresa = array_merge($defaults, $empresaConfig);

        // 2. Procesamiento del Logo a Base64 para DomPDF
        $logoBase64 = null;
        $relativeLogoPath = ltrim($empresa['logo'], '/');
        $fullLogoPath = public_path($relativeLogoPath);
        if (file_exists($fullLogoPath)) {
            $logoData = file_get_contents($fullLogoPath);
            $type = pathinfo($fullLogoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/' . ($type === 'svg' ? 'svg+xml' : $type) . ';base64,' . base64_encode($logoData);
        }

        // 3. Obtención del nombre completo y teléfono del vendedor
        $vendedorNombreCompleto = 'N/A';
        $vendedorTelefono = '';

        if ($cotizacion->vendedor) {
            $v = $cotizacion->vendedor;

            if ($v->empleado) {
                $nombreComp = trim(($v->empleado->nombre ?? '') . ' ' . ($v->empleado->apellido ?? ''));
                if (!empty($nombreComp)) {
                    $vendedorNombreCompleto = $nombreComp;
                }
                if (!empty($v->empleado->telefono)) {
                    $vendedorTelefono = $v->empleado->telefono;
                }
            }

            if ($vendedorNombreCompleto === 'N/A' && $v->persona) {
                if (!empty($v->persona->nombre)) {
                    $vendedorNombreCompleto = $v->persona->nombre;
                }
                if (empty($vendedorTelefono) && !empty($v->persona->telefono)) {
                    $vendedorTelefono = $v->persona->telefono;
                }
            }

            if ($vendedorNombreCompleto === 'N/A') {
                $vendedorNombreCompleto = $v->usuario;
            }
        }

        // 4. Extracción detallada de Datos del Cliente / Prospecto / Persona (Priorizando Datos de Facturación)
        $clienteData = [
            'nombre' => 'N/A',
            'empresa' => '',
            'documento' => 'N/A',
            'direccion' => 'N/A',
            'ciudad' => '',
            'telefono' => 'N/A',
            'email' => 'N/A',
            'contacto' => ''
        ];

        if ($cotizacion->cliente) {
            $c = $cotizacion->cliente;

            // Priorizar Datos de Facturación (empresas -> datos_factura)
            $facturacion = null;
            if ($c->empresas && $c->empresas->count() > 0) {
                $facturacion = $c->empresas->where('favorito', 1)->first() ?: $c->empresas->first();
            }

            // 1. Documento: Priorizar Datos de Facturación (numero, digito, tipo_documento)
            if ($facturacion && !empty($facturacion->numero)) {
                $docTipo = !empty($facturacion->tipo_documento) ? strtoupper($facturacion->tipo_documento) . ': ' : 'NIT: ';
                $numFull = $facturacion->numero;
                if (isset($facturacion->digito) && $facturacion->digito !== '' && $facturacion->digito !== null) {
                    $numFull .= '-' . $facturacion->digito;
                }
                $clienteData['documento'] = $docTipo . $numFull;
            } else {
                $docTipo = !empty($c->tipo_documento) ? strtoupper($c->tipo_documento) . ': ' : '';
                $clienteData['documento'] = !empty($c->num_documento) ? ($docTipo . $c->num_documento) : 'N/A';
            }

            // 2. Nombre / Razón Social: Priorizar Datos de Facturación
            if ($facturacion && !empty($facturacion->razonsocial)) {
                $clienteData['nombre'] = $facturacion->razonsocial;
                $clienteData['empresa'] = $facturacion->razonsocial;
            } else {
                $clienteData['nombre'] = $c->razonsocial ?: ($c->nombre ?: 'N/A');
                $clienteData['empresa'] = $c->razonsocial ?: '';
            }

            // 3. Dirección: Priorizar Datos de Facturación
            if ($facturacion && !empty($facturacion->direccion)) {
                $clienteData['direccion'] = $facturacion->direccion;
            } else {
                $dir = $c->direccionf ?: ($c->direccion ?: '');
                $clienteData['direccion'] = $dir ?: 'N/A';
            }

            // 4. Ciudad / Depto: Priorizar Datos de Facturación
            if ($facturacion && !empty($facturacion->ciudad)) {
                $ciudad = trim($facturacion->ciudad . (!empty($facturacion->departamento) ? (' - ' . $facturacion->departamento) : ''));
                $clienteData['ciudad'] = $ciudad;
            } else {
                $ciudad = trim(($c->ciudad ?? '') . (!empty($c->departamento) ? (' - ' . $c->departamento) : ''));
                $clienteData['ciudad'] = $ciudad;
            }

            // 5. Teléfono: Priorizar Datos de Facturación
            if ($facturacion && !empty($facturacion->telefono)) {
                $clienteData['telefono'] = $facturacion->telefono;
            } else {
                $clienteData['telefono'] = $c->telefono ?: ($c->telefono_contacto ?: 'N/A');
            }

            // 6. Email: Priorizar Datos de Facturación
            if ($facturacion && !empty($facturacion->correo)) {
                $clienteData['email'] = $facturacion->correo;
            } else {
                $clienteData['email'] = $c->email ?: ($c->email_contacto ?: 'N/A');
            }

            $clienteData['contacto'] = $c->contacto ?: '';
        }

        // Búsqueda directa en pivote cliente_factura / datos_factura si el documento sigue N/A y existe cliente_id
        if ($clienteData['documento'] === 'N/A' && $cotizacion->cliente_id) {
            $facturacionPivot = DB::table('cliente_factura')
                ->join('datos_factura', 'cliente_factura.facturacion_id', '=', 'datos_factura.id')
                ->where('cliente_factura.cliente_id', $cotizacion->cliente_id)
                ->orderBy('cliente_factura.favorito', 'desc')
                ->select('datos_factura.*')
                ->first();

            if ($facturacionPivot && !empty($facturacionPivot->numero)) {
                $docTipo = !empty($facturacionPivot->tipo_documento) ? strtoupper($facturacionPivot->tipo_documento) . ': ' : 'NIT: ';
                $numFull = $facturacionPivot->numero;
                if (isset($facturacionPivot->digito) && $facturacionPivot->digito !== '' && $facturacionPivot->digito !== null) {
                    $numFull .= '-' . $facturacionPivot->digito;
                }
                $clienteData['documento'] = $docTipo . $numFull;
                if ($clienteData['nombre'] === 'N/A' && !empty($facturacionPivot->razonsocial)) {
                    $clienteData['nombre'] = $facturacionPivot->razonsocial;
                    $clienteData['empresa'] = $facturacionPivot->razonsocial;
                }
                if ($clienteData['direccion'] === 'N/A' && !empty($facturacionPivot->direccion)) {
                    $clienteData['direccion'] = $facturacionPivot->direccion;
                }
                if ($clienteData['telefono'] === 'N/A' && !empty($facturacionPivot->telefono)) {
                    $clienteData['telefono'] = $facturacionPivot->telefono;
                }
                if ($clienteData['email'] === 'N/A' && !empty($facturacionPivot->correo)) {
                    $clienteData['email'] = $facturacionPivot->correo;
                }
            } else {
                $persona = \App\Persona::find($cotizacion->cliente_id);
                if ($persona) {
                    if ($clienteData['nombre'] === 'N/A') $clienteData['nombre'] = $persona->nombre ?: 'N/A';
                    if (empty($clienteData['empresa'])) $clienteData['empresa'] = $persona->nombre ?: '';
                    if ($clienteData['documento'] === 'N/A' && !empty($persona->num_documento)) {
                        $docTipo = !empty($persona->tipo_documento) ? strtoupper($persona->tipo_documento) . ': ' : '';
                        $clienteData['documento'] = $docTipo . $persona->num_documento;
                    }
                    if ($clienteData['direccion'] === 'N/A' && !empty($persona->direccion)) {
                        $clienteData['direccion'] = $persona->direccion;
                    }
                    if ($clienteData['telefono'] === 'N/A' && !empty($persona->telefono)) {
                        $clienteData['telefono'] = $persona->telefono;
                    }
                    if ($clienteData['email'] === 'N/A' && !empty($persona->email)) {
                        $clienteData['email'] = $persona->email;
                    }
                }
            }
        }

        if (($clienteData['nombre'] === 'N/A' || $clienteData['telefono'] === 'N/A') && $cotizacion->prospecto) {
            $p = $cotizacion->prospecto;
            if ($clienteData['nombre'] === 'N/A') $clienteData['nombre'] = $p->nombre ?: 'N/A';
            if (empty($clienteData['empresa'])) $clienteData['empresa'] = $p->empresa ?: $p->nombre;
            if ($clienteData['documento'] === 'N/A' && !empty($p->num_documento)) {
                $clienteData['documento'] = $p->num_documento;
            }
            if ($clienteData['direccion'] === 'N/A' && !empty($p->direccion)) {
                $clienteData['direccion'] = $p->direccion;
            }
            if (empty($clienteData['ciudad']) && !empty($p->ciudad)) {
                $clienteData['ciudad'] = $p->ciudad;
            }
            if ($clienteData['telefono'] === 'N/A') {
                $clienteData['telefono'] = $p->telefono ?: ($p->celular ?: 'N/A');
            }
            if ($clienteData['email'] === 'N/A' && !empty($p->email)) {
                $clienteData['email'] = $p->email;
            }
        }

        $pdf = Pdf::loadView('pdf.cotizacion', compact('cotizacion', 'empresa', 'logoBase64', 'vendedorNombreCompleto', 'vendedorTelefono', 'clienteData'));
        return $pdf->download('Cotizacion_' . $cotizacion->numero_cotizacion . '.pdf');
    }

    public function convertirAPedido(Request $request)
    {
        $cotizacion = CrmCotizacion::with(['detalles', 'prospecto', 'cliente'])->crmScope()->findOrFail($request->id);

        if ($cotizacion->estado === 'Convertida') {
            return response()->json(['status' => 'warning', 'message' => 'Esta cotización ya fue convertida anteriormente en pedido.'], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Garantizar / Crear Cliente o Persona
            $clienteId = $cotizacion->cliente_id;
            if (!$clienteId && $cotizacion->prospecto) {
                $prospectoNombre = trim($cotizacion->prospecto->nombre);
                $persona = Persona::where('nombre', $prospectoNombre)->first();
                if (!$persona && $cotizacion->prospecto->email) {
                    $persona = Persona::where('email', $cotizacion->prospecto->email)->first();
                }

                if (!$persona) {
                    $persona = new Persona();
                    $persona->nombre = mb_substr($prospectoNombre, 0, 95);
                    $persona->tipo_documento = 'NIT';
                    $persona->num_documento = '000000000';
                    $persona->direccion = mb_substr($cotizacion->prospecto->direccion ?: 'Ciudad', 0, 65);
                    $persona->telefono = mb_substr($cotizacion->prospecto->telefono ?: $cotizacion->prospecto->celular ?: '0', 0, 18);
                    $persona->email = mb_substr($cotizacion->prospecto->email ?: '', 0, 45);
                    $persona->save();
                }

                $clienteId = $persona->id;
                $cotizacion->cliente_id = $clienteId;
            }

            if (!$clienteId) {
                return response()->json(['status' => 'error', 'message' => 'Debe asociar un cliente o prospecto con datos para generar el pedido.'], 422);
            }

            // 2. Crear Comprobante de tipo 'pedido'
            $maxNum = (int) Comprobante::where('tipo', 'pedido')->max('num_comprobante');
            if ($maxNum == 0) {
                $maxNum = (int) Comprobante::max('num_comprobante');
            }

            $datosFacturaId = 0;
            if ($cotizacion->cliente && method_exists($cotizacion->cliente, 'empresas') && $cotizacion->cliente->empresas && $cotizacion->cliente->empresas->count() > 0) {
                $datosFacturaId = $cotizacion->cliente->empresas->first()->id;
            }

            $comprobante = new Comprobante();
            $comprobante->tipo = 'pedido';
            $comprobante->num_comprobante = max($maxNum + 1, 1);
            $comprobante->cliente_id = (int)$clienteId;
            $comprobante->datos_factura_id = (int)$datosFacturaId;
            $comprobante->user_id = (int)(Auth::id() ?? $cotizacion->user_id ?? 1);
            $comprobante->fecha = date('Y-m-d');
            $comprobante->forma_pago = mb_substr($cotizacion->condiciones_pago ?: 'Contado', 0, 45);
            $comprobante->transportadora = '';
            $comprobante->subtotal = (float)($cotizacion->subtotal ?: 0);
            $comprobante->descuento = (float)($cotizacion->descuento ?: 0);
            $comprobante->impuestos = (float)($cotizacion->iva ?: 0);
            $comprobante->iva = 0.19;
            $comprobante->total = (float)($cotizacion->total ?: 0);
            $comprobante->abono = 0;
            $comprobante->saldo = (float)($cotizacion->total ?: 0);
            $comprobante->fuente_id = (string)$cotizacion->id;
            $comprobante->estado = '1';
            $comprobante->save();

            // 3. Procesar cada detalle: Buscar/Crear Producto (Articulo), Orden de Trabajo, Detalles y Línea de Comprobante
            if ($cotizacion->detalles && count($cotizacion->detalles) > 0) {
                $firstTipoProdId = (int)(\App\TipoProducto::value('id') ?? 1);
                $firstCategoriaId = (int)(\App\Categoria::value('id') ?? 1);

                foreach ($cotizacion->detalles as $det) {
                    // A. Resolver o Crear Artículo
                    $artId = 0;
                    if ($det->articulo_id && $det->articulo_id > 0) {
                        $artExist = Articulo::find($det->articulo_id);
                        if ($artExist) {
                            $artId = $artExist->id;
                        }
                    }

                    $nombreConcepto = trim($det->concepto ?: $det->descripcion ?: 'Producto Personalizado');

                    if ($artId == 0) {
                        // Buscar uno que se asemeje en el catálogo de productos
                        $articuloSemejante = Articulo::where('nombre', 'like', "%{$nombreConcepto}%")->first();
                        if ($articuloSemejante) {
                            $artId = $articuloSemejante->id;
                        } else {
                            // Crear un nuevo producto en el catálogo si no existe uno semejante
                            $nuevoArt = new Articulo();
                            $nuevoArt->nombre = mb_substr($nombreConcepto, 0, 95);
                            $nuevoArt->codigo = 'ART-' . time() . rand(10, 99);
                            $nuevoArt->idcategoria = $firstCategoriaId;
                            $nuevoArt->tipo_producto_id = $firstTipoProdId;
                            $nuevoArt->tipo_cantidad = 'unidad';
                            $nuevoArt->imagen = 'noimagen';
                            $nuevoArt->precio_venta = (float)($det->precio_unitario ?: 0);
                            $nuevoArt->iva = 1;
                            $nuevoArt->descripcion = mb_substr($det->descripcion ?: $nombreConcepto, 0, 250);
                            $nuevoArt->condicion = 1;
                            $nuevoArt->id_item_padre = 0;
                            $nuevoArt->stock = 0;
                            $nuevoArt->ancho_final = 0;
                            $nuevoArt->largo_final = 0;
                            $nuevoArt->save();
                            $artId = $nuevoArt->id;
                        }
                        $det->articulo_id = $artId;
                        $det->save();
                    }

                    // B. Crear la Orden de Trabajo de Producción
                    $orden = new Ordentrabajo();
                    $orden->idcliente = (int)$clienteId;
                    $orden->cliente_id = (int)$clienteId;
                    $orden->articulo_id = (int)$artId;
                    $orden->detalles_diseno = mb_substr($nombreConcepto, 0, 2900);
                    $orden->observaciones = mb_substr($det->descripcion ?: $nombreConcepto, 0, 2900);
                    $orden->fecha = date('Y-m-d');
                    $orden->fecha_entrega = date('Y-m-d', strtotime("+12 days"));
                    $orden->cantidad = (float)($det->cantidad ?: 1);
                    $orden->valor_unitario = (float)($det->precio_unitario ?: 0);
                    $orden->totalParcial = (float)($det->subtotal ?: 0);
                    $orden->descuento = (float)($det->descuento_porcentaje ?: 0);
                    $orden->impuesto = (float)($det->iva ?: 0);
                    $orden->valor_impuesto = 0;
                    $orden->total = (float)($det->total ?: 0);
                    $orden->abono = 0;
                    $orden->saldo = (float)($det->total ?: 0);
                    $orden->estado = 'VC';
                    $orden->produccion = 'EP';
                    $orden->prioridad = '0';
                    $orden->plancha = 0;
                    $orden->pago = 0;
                    $orden->cantidad_entregada = 0;
                    $orden->cantidad_original = (int)($det->cantidad ?: 1);
                    $orden->medida_final = null;
                    $orden->tamano = null;
                    $orden->medida_material = null;
                    $orden->cabida = null;
                    $orden->carpeta_cliente = null;
                    $orden->save();

                    // C. Guardar especificaciones / atributos en Detalletrabajo
                    if (!empty($det->descripcion)) {
                        $detTrabajo = new Detalletrabajo();
                        $detTrabajo->ordentrabajo_id = (int)$orden->id;
                        $detTrabajo->costos_id = 0;
                        $detTrabajo->titulo = 'Descripción';
                        $detTrabajo->valor = mb_substr($det->descripcion, 0, 1900);
                        $detTrabajo->save();
                    }

                    if (!empty($det->opciones) || !empty($det->detalles_json)) {
                        $attrRaw = $det->opciones ?? $det->detalles_json;
                        $detallesArray = json_decode($attrRaw, true);
                        if (is_array($detallesArray)) {
                            foreach ($detallesArray as $key => $val) {
                                if ($key === 'papeles' || $key === 'papel') {
                                    if (is_array($val)) {
                                        foreach ($val as $pItem) {
                                            $costoPapel = new CostoProduccion();
                                            $costoPapel->ordentrabajo_id = (int)$orden->id;
                                            $costoPapel->costois_id = 0;
                                            $costoPapel->titulo = 'papel';
                                            $costoPapel->medida_material = is_array($pItem) ? mb_substr($pItem['medida_material'] ?? $pItem['corte'] ?? '', 0, 45) : null;
                                            $costoPapel->tamano = is_array($pItem) ? mb_substr($pItem['tamano'] ?? '', 0, 45) : null;
                                            $costoPapel->cantidad = is_array($pItem) ? (float)($pItem['cantidad'] ?? $pItem['pliegos'] ?? 0) : 0;
                                            $costoPapel->valor = is_array($pItem) ? (float)($pItem['valor'] ?? 0) : 0;
                                            $costoPapel->total = is_array($pItem) ? (float)($pItem['total'] ?? 0) : 0;
                                            $costoPapel->completado = 0;
                                            $costoPapel->pago = 0;
                                            $costoPapel->terminado = 0;
                                            $costoPapel->save();
                                        }
                                    }
                                } else {
                                    $valStr = is_array($val) ? json_encode($val) : (string)$val;
                                    if ($valStr !== '' && $valStr !== 'null') {
                                        $detTrabajo = new Detalletrabajo();
                                        $detTrabajo->ordentrabajo_id = (int)$orden->id;
                                        $detTrabajo->costos_id = 0;
                                        $detTrabajo->titulo = mb_substr((string)$key, 0, 18);
                                        $detTrabajo->valor = mb_substr($valStr, 0, 1900);
                                        $detTrabajo->save();
                                    }
                                }
                            }
                        }
                    }

                    // D. Crear la Línea de Comprobante
                    $linea = new LineaComprobante();
                    $linea->comprobante_id = (int)$comprobante->id;
                    $linea->articulo_id = (int)$artId;
                    $linea->ordentrabajo_id = (int)$orden->id;
                    $linea->cantidad = (int)($det->cantidad ?: 1);
                    $linea->valor_unitario = (float)($det->precio_unitario ?: 0);
                    $linea->subtotal = (float)($det->subtotal ?: 0);
                    $linea->descuento = 0;
                    $linea->impuesto = (float)($det->iva ?: 0);
                    $linea->valor_total = (float)($det->total ?: 0);
                    $linea->fecha = date('Y-m-d');
                    $linea->fecha_entrega = $orden->fecha_entrega;
                    $linea->estado = '1';
                    $linea->save();
                }
            }

            // 4. Marcar Cotización como Convertida
            $cotizacion->estado = 'Convertida';
            $cotizacion->pedido_id = $comprobante->id;
            $cotizacion->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Cotización convertida en Pedido #' . $comprobante->num_comprobante . ' exitosamente.',
                'pedido_id' => $comprobante->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al convertir cotización: ' . $e->getMessage()], 500);
        }
    }

    public function revertirConversion(Request $request)
    {
        $cotizacion = CrmCotizacion::findOrFail($request->id);

        if ($cotizacion->estado !== 'Convertida') {
            return response()->json(['status' => 'warning', 'message' => 'Esta cotización no se encuentra en estado Convertida.'], 400);
        }

        try {
            DB::beginTransaction();

            $pedidoId = $cotizacion->pedido_id;

            if ($pedidoId) {
                $pedido = Comprobante::find($pedidoId);
                if ($pedido) {
                    // Eliminar las líneas de comprobante y las órdenes de trabajo asociadas
                    $lineas = LineaComprobante::where('comprobante_id', $pedido->id)->get();
                    foreach ($lineas as $linea) {
                        if ($linea->ordentrabajo_id && $linea->ordentrabajo_id > 0) {
                            Detalletrabajo::where('ordentrabajo_id', $linea->ordentrabajo_id)->delete();
                            CostoProduccion::where('ordentrabajo_id', $linea->ordentrabajo_id)->delete();
                            Ordentrabajo::where('id', $linea->ordentrabajo_id)->delete();
                        }
                        $linea->delete();
                    }

                    // Eliminar también las proformas generadas para este pedido
                    $proformas = Comprobante::where('tipo', 'proforma')
                        ->where(function($q) use ($pedidoId) {
                            $q->where('pedido_id', $pedidoId)
                              ->orWhere('fuente_id', (string)$pedidoId);
                        })->get();
                    foreach ($proformas as $prof) {
                        LineaComprobante::where('comprobante_id', $prof->id)->delete();
                        $prof->delete();
                    }

                    // Eliminar el comprobante del pedido
                    $pedido->delete();
                }
            }

            // Restablecer cotización a estado Borrador y desvincular pedido_id
            $cotizacion->estado = 'Borrador';
            $cotizacion->pedido_id = null;
            $cotizacion->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Conversión revertida exitosamente. El pedido y las órdenes asociadas fueron eliminados, y la cotización volvió a estar editable.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al revertir conversión: ' . $e->getMessage()], 500);
        }
    }
}
