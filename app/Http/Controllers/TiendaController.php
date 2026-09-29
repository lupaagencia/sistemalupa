<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ordentrabajo;
use App\Detalletrabajo;
use App\Articulo;
use App\AtributoTienda;
use App\TipoProducto;
use Illuminate\Support\Facades\DB;

class TiendaController extends Controller
{
    public function getConfiguracion()
    {
        $ajustes = \App\Ajustes::where('tipo', 'tienda')->get();
        $config = [
            'modo_vista_producto' => 'ambos', // 'personalizar', 'zoom', 'ambos'
            'numero_whatsapp' => '',
            'incremento_match' => 20,
            'incremento_anual' => 10,
            'iva' => 19,
            'threshold_octavo' => 8,
            'threshold_cuarto' => 4,
            'threshold_medio' => 2
        ];
        foreach ($ajustes as $ajuste) {
            $config[$ajuste->detalle] = $ajuste->valor;
        }
        return response()->json($config);
    }

    public function getTiposProducto()
    {
        $tipos = TipoProducto::where('estado', 1)->get();
        return response()->json($tipos);
    }

    public function listAll()
    {
        return response()->json(\App\AtributoTienda::with('tiposProducto')->where('activo', 1)->orderBy('nombre', 'asc')->get());
    }
    /**
     * Obtiene los atributos de la tienda agrupados por tipo.
     */
    public function getAtributos(Request $request)
    {
        $articuloId = $request->articulo_id;

        if ($articuloId) {
            $articulo = Articulo::find($articuloId);
            if ($articulo && $articulo->tipo_producto_id) {
                $tipoProductoId = $articulo->tipo_producto_id;
                $atributos = AtributoTienda::where('activo', 1)
                    ->where('mostrar_en_producto', 1)
                    ->whereHas('tiposProducto', function ($query) use ($tipoProductoId) {
                        $query->where('tipo_producto.id', $tipoProductoId);
                    })
                    ->get()
                    ->groupBy('tipo');
                return response()->json($atributos);
            }
        }

        $atributos = AtributoTienda::with('tiposProducto')
            ->where('activo', 1)
            ->where('mostrar_en_producto', 1)
            ->get()
            ->groupBy('tipo');
        return response()->json($atributos);
    }

    /**
     * Guarda un nuevo atributo de tienda.
     */
    public function storeAtributo(Request $request)
    {
        $nombreImagen = null;
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $ruta = public_path('img/atributos');
            $imagen->move($ruta, $nombreImagen);
        }

        $atributo = AtributoTienda::create([
            'tipo' => $request->tipo,
            'nombre' => $request->nombre,
            'etiquetas' => $request->etiquetas,
            'imagen' => $nombreImagen,
            'valor_extra' => $request->valor_extra ?? 0,
            'formula' => $request->formula,
            'condicion' => $request->condicion,
            'dependencia' => $request->dependencia,
            'es_buscable' => $request->es_buscable ?? true,
            'seleccion_multiple' => $request->seleccion_multiple ?? false,
            'mostrar_en_producto' => $request->mostrar_en_producto ?? true,
            'activo' => 1
        ]);

        if ($request->has('tipos_producto')) {
            $tipos = json_decode($request->tipos_producto);
            $atributo->tiposProducto()->sync($tipos);
        }

        return response()->json([
            'status' => 'success',
            'atributo' => $atributo
        ]);
    }

    public function updateAtributo(Request $request)
    {
        $atributo = AtributoTienda::findOrFail($request->id);
        
        if ($request->hasFile('imagen')) {
            if ($atributo->imagen) {
                $rutaAnterior = public_path('img/atributos/' . $atributo->imagen);
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }
            
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
            $ruta = public_path('img/atributos');
            $imagen->move($ruta, $nombreImagen);
            $atributo->imagen = $nombreImagen;
        }

        $atributo->tipo = $request->tipo;
        $atributo->nombre = $request->nombre;
        $atributo->etiquetas = $request->etiquetas;
        $atributo->valor_extra = $request->valor_extra ?? 0;
        $atributo->formula = $request->formula;
        $atributo->condicion = $request->condicion;
        $atributo->dependencia = $request->dependencia;
        $atributo->es_buscable = $request->es_buscable ?? true;
        $atributo->seleccion_multiple = $request->seleccion_multiple ?? false;
        $atributo->mostrar_en_producto = $request->mostrar_en_producto ?? true;
        $atributo->save();

        if ($request->has('tipos_producto')) {
            $tipos = json_decode($request->tipos_producto);
            $atributo->tiposProducto()->sync($tipos);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Desactiva un atributo de tienda.
     */
    public function desactivarAtributo(Request $request)
    {
        $atributo = AtributoTienda::findOrFail($request->id);
        $atributo->activo = 0;
        $atributo->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Activa un atributo de tienda.
     */
    public function activarAtributo(Request $request)
    {
        $atributo = AtributoTienda::findOrFail($request->id);
        $atributo->activo = 1;
        $atributo->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Obtiene el precio mínimo histórico para un artículo.
     */
    public function getMinPrecio($articulo_id)
    {
        $minPrecio = Ordentrabajo::where('articulo_id', $articulo_id)
            ->where('valor_unitario', '>', 0)
            ->min('valor_unitario');

        if (!$minPrecio) {
            $articulo = Articulo::find($articulo_id);
            $minPrecio = $articulo ? $articulo->precio_venta : 0;
        }

        return response()->json([
            'min_precio' => $minPrecio
        ]);
    }

    /**
     * Cotiza un producto basado en el historial de pedidos de otros clientes.
     */
    /**
     * Cotiza un producto basado en el historial de pedidos de otros clientes.
     */
    public function cotizar(Request $request)
    {
        $articuloId = $request->articulo_id;
        $cantidad = $request->cantidad;
        $especificaciones = $request->especificaciones; 
        
        // Pre-cargar todos los atributos para evitar consultas repetitivas en bucles
        $todosLosAtributos = AtributoTienda::where('activo', 1)->get();
        
        // 0. Cargar configuración de incrementos
        $incMatch = \App\Ajustes::where('tipo', 'tienda')->where('detalle', 'incremento_match')->first()->valor ?? 20;
        $incAnual = \App\Ajustes::where('tipo', 'tienda')->where('detalle', 'incremento_anual')->first()->valor ?? 10;
        $ivaValue = \App\Ajustes::where('tipo', 'tienda')->where('detalle', 'iva')->first()->valor ?? 19;
        
        $incMatchFactor = 1 + ($incMatch / 100);
        $incAnualFactor = 1 + ($incAnual / 100);
        $ivaFactor = 1 + ($ivaValue / 100);

        // 0. Identificar atributos que suman valor unitario y separarlos de la búsqueda
        $valorExtraTotal = 0;
        $specsParaBusqueda = [];

        if (is_array($especificaciones)) {
            foreach ($especificaciones as $spec) {
                $tipoBuscado = trim($spec['titulo']);
                $valoresBuscados = is_array($spec['valor']) ? $spec['valor'] : [$spec['valor']];

                foreach ($valoresBuscados as $val) {
                    $nombreBuscado = trim($val);

                    $attrInfo = $todosLosAtributos->filter(function($a) use ($tipoBuscado, $nombreBuscado) {
                        $tipoMatch = strcasecmp($a->tipo, $tipoBuscado) == 0;
                        $nombreMatch = strcasecmp($a->nombre, $nombreBuscado) == 0;
                        $etiquetaMatch = stripos($a->etiquetas, $nombreBuscado) !== false;
                        return $tipoMatch && ($nombreMatch || $etiquetaMatch);
                    })->first();
                    
                    if ($attrInfo) {
                        if ($attrInfo->es_buscable) {
                            $specsParaBusqueda[] = [
                                'titulo' => $tipoBuscado,
                                'valor' => $attrInfo->nombre, // Usar nombre canónico para búsqueda en historial
                                'etiquetas' => $attrInfo->etiquetas
                            ];
                        } else {
                            $valorExtraTotal += $attrInfo->valor_extra;
                        }
                    } else {
                        $specsParaBusqueda[] = [
                            'titulo' => $tipoBuscado,
                            'valor' => $nombreBuscado,
                            'etiquetas' => ''
                        ];
                    }
                }
            }
        }

        // Obtener TODOS los tipos de atributos buscables para poder hacer validación inversa
        $todosTiposBuscables = AtributoTienda::where('activo', 1)
            ->where('es_buscable', true)
            ->pluck('tipo')
            ->unique()
            ->map(fn($t) => strtolower(trim($t)))
            ->toArray();

        // Tipos que el usuario SÍ seleccionó (para búsqueda)
        $tiposSeleccionados = array_map(
            fn($s) => strtolower(trim($s['titulo'])),
            $specsParaBusqueda
        );

        // Tipos buscables que el usuario NO seleccionó (deben estar AUSENTES en la orden)
        $tiposNoSeleccionados = array_diff($todosTiposBuscables, $tiposSeleccionados);

        // Buscamos órdenes del mismo artículo y cantidad cercana
        $margenMin = $cantidad * 0.9;
        $margenMax = $cantidad * 1.1;

        // Límite de 15 para respuesta ultra-rápida
        $ordenesCandidatas = Ordentrabajo::with(['detalles.costo.costois'])
            ->where('articulo_id', $articuloId)
            ->whereBetween('cantidad', [$margenMin, $margenMax])
            ->where('valor_unitario', '>', 0)
            ->orderBy('id', 'desc')
            ->limit(15)
            ->get();

        foreach ($ordenesCandidatas as $orden) {
            $coincideCompletamente = true;
            
            // Los detalles ya vienen cargados en la relación
            $detallesOrden = $orden->detalles;

            // --- PASO 1: Verificar que los atributos seleccionados SÍ estén en la orden ---
            if (count($specsParaBusqueda) > 0) {
                foreach ($specsParaBusqueda as $spec) {
                    $titulo = $spec['titulo'];
                    $valorBuscado = $spec['valor'];
                    $valorBuscadoLower = strtolower(trim($valorBuscado));

                    $encontradoEnDetalle = false;
                    foreach ($detallesOrden as $detalle) {
                        $valorGuardado = strtolower(trim($detalle->valor));
                        $descDetalle = strtolower(trim($detalle->descripcion));
                        $tituloDetalle = strtolower(trim($detalle->titulo));

                        if (stripos($tituloDetalle, $titulo) !== false) {
                            $textosARevisar = [$valorGuardado, $descDetalle];
                            foreach ($textosARevisar as $texto) {
                                if (empty($texto)) continue;

                                if (stripos($texto, '[object object]') !== false) {
                                    $numObjetos = count(explode('[object Object]', $texto)) - 1;
                                    if (stripos($titulo, 'tinta') !== false) {
                                        preg_match('/(\d+)/', $valorBuscadoLower, $m);
                                        $buscadoNum = isset($m[1]) ? $m[1] : (stripos($valorBuscadoLower, 'full') !== false ? 4 : 0);
                                        if ($buscadoNum > 0 && $buscadoNum == $numObjetos) {
                                            $encontradoEnDetalle = true;
                                            break;
                                        }
                                    }
                                }
                                
                                if (!$encontradoEnDetalle && $this->isJson($texto)) {
                                    $dataJson = json_decode($texto, true);
                                    if (is_array($dataJson)) {
                                        // Validación por cantidad de tintas en el array JSON
                                        if (stripos($titulo, 'tinta') !== false) {
                                            preg_match('/(\d+)/', $valorBuscadoLower, $m);
                                            $numPedido = isset($m[1]) ? (int)$m[1] : (stripos($valorBuscadoLower, 'full') !== false ? 4 : 0);
                                            if ($numPedido > 0 && $numPedido == count($dataJson)) {
                                                $encontradoEnDetalle = true;
                                            }
                                        }

                                        if (!$encontradoEnDetalle) {
                                            foreach ($dataJson as $item) {
                                                $textoJson = strtolower(json_encode($item));
                                                if ($this->checkMatch($valorBuscadoLower, $textoJson, $titulo)) {
                                                    $encontradoEnDetalle = true;
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        if (!$encontradoEnDetalle && stripos($titulo, 'tinta') !== false && count($dataJson) >= 4) {
                                            if (stripos($valorBuscadoLower, 'full') !== false || stripos($valorBuscadoLower, '4') !== false) {
                                                $encontradoEnDetalle = true;
                                            }
                                        }
                                    }
                                } 
                                
                                if (!$encontradoEnDetalle) {
                                    if ($this->checkMatch($valorBuscadoLower, $texto, $titulo)) {
                                        $encontradoEnDetalle = true;
                                        break;
                                    }
                                }
                            }
                        }

                        // Para Papel: Revisar insumo asociado
                        if (!$encontradoEnDetalle && strtolower($titulo) == 'papel') {
                            if ($detalle->costo) {
                                $textosCosto = [
                                    strtolower(trim($detalle->costo->valor)),
                                    strtolower(trim($detalle->costo->descripcion))
                                ];
                                if ($detalle->costo->costois) {
                                    $textosCosto[] = strtolower(trim($detalle->costo->costois->nombre));
                                    $textosCosto[] = strtolower(trim($detalle->costo->costois->descripcion));
                                }

                                foreach ($textosCosto as $tc) {
                                    if (empty($tc)) continue;
                                    if ($this->checkMatch($valorBuscadoLower, $tc, $titulo)) {
                                        $encontradoEnDetalle = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($encontradoEnDetalle) break;
                    }

                    // Revisar en la descripción general de la orden
                    if (!$encontradoEnDetalle) {
                        $descGeneral = strtolower($orden->observaciones . ' ' . $orden->detalles_diseno);
                        if ($this->checkMatch($valorBuscadoLower, $descGeneral, $titulo)) {
                            $encontradoEnDetalle = true;
                        }
                    }

                    // --- NUEVO: Revisar etiquetas si no se encontró por nombre ---
                    if (!$encontradoEnDetalle && !empty($spec['etiquetas'])) {
                        $etiquetasArray = array_map('trim', explode(',', $spec['etiquetas']));
                        foreach ($etiquetasArray as $etiqueta) {
                            if (empty($etiqueta)) continue;
                            
                            foreach ($detallesOrden as $detalle) {
                                $valorGuardado = strtolower(trim($detalle->valor));
                                $descDetalle = strtolower(trim($detalle->descripcion));
                                $tituloDetalle = strtolower(trim($detalle->titulo));

                                if (stripos($tituloDetalle, $titulo) !== false) {
                                    if ($this->checkMatch(strtolower($etiqueta), $valorGuardado, $titulo) || 
                                        $this->checkMatch(strtolower($etiqueta), $descDetalle, $titulo)) {
                                        $encontradoEnDetalle = true;
                                        break;
                                    }
                                }
                            }
                            if ($encontradoEnDetalle) break;
                        }
                    }

                    if (!$encontradoEnDetalle) {
                        $coincideCompletamente = false;
                        break;
                    }
                }
            }

            // --- PASO 2 (NUEVO): Verificar que los atributos NO seleccionados NO estén en la orden ---
            // Si el usuario no seleccionó "Terminado" y la orden tiene "plastificado", no debe coincidir.
            if ($coincideCompletamente && count($tiposNoSeleccionados) > 0) {
                foreach ($detallesOrden as $detalle) {
                    $tituloDetalle = strtolower(trim($detalle->titulo));
                    foreach ($tiposNoSeleccionados as $tipoNoSelec) {
                        if (stripos($tituloDetalle, $tipoNoSelec) !== false) {
                            $coincideCompletamente = false;
                            break 2;
                        }
                    }
                }
            }

            if ($coincideCompletamente) {
                $valorBase = $orden->valor_unitario;
                
                // 1. Incremento del 10% por año de antigüedad (si aplica)
                $anioOrden = date('Y', strtotime($orden->created_at));
                $anioActual = date('Y');
                $aniosDiferencia = $anioActual - $anioOrden;
                
                if ($aniosDiferencia > 0) {
                    for ($i = 0; $i < $aniosDiferencia; $i++) {
                        $valorBase *= $incAnualFactor;
                    }
                }

                // 2. Incremento por coincidencia encontrada (configurable)
                $valorBase *= $incMatchFactor;

                $unitarioFinal = $valorBase + $valorExtraTotal;
                $unitarioConIva = $unitarioFinal * $ivaFactor;

                return response()->json([
                    'status' => 'success',
                    'match_found' => true,
                    'valor_unitario' => round($unitarioConIva, 2),
                    'total' => round($unitarioConIva * $cantidad, 2),
                    'orden_referencia' => $orden->id,
                    'valor_extra' => $valorExtraTotal,
                    'anios_antiguedad' => $aniosDiferencia,
                    'iva_aplicado' => $ivaValue
                ]);
            }
        }

        $articulo = $articuloId ? Articulo::find($articuloId) : null;
        if (!$articulo) {
            $tipoId = $request ? $request->input('tipo_producto_id') : null;
            $tipoObj = $tipoId ? \App\TipoProducto::find($tipoId) : null;
            $articulo = new Articulo();
            $articulo->id = 0;
            $articulo->nombre = $tipoObj ? $tipoObj->nombre : 'Producto Personalizado';
            if ($tipoObj) {
                $articulo->setRelation('tipo', $tipoObj);
            }
        }
        
        // --- NUEVO: Cálculo manual de cotización si no hay match histórico ---
        // Pasamos todosLosAtributos para que pueda calcular costos de terminados
        $resultadoCalculo = $this->calcularCotizacion($articulo, $cantidad, $especificaciones, $todosLosAtributos, $request);
        
        $totalConIva = round($resultadoCalculo['valor_total'] * $ivaFactor, 2);
        $valorUnitarioConIva = round($totalConIva / $cantidad, 2);

        return response()->json([
            'status' => 'info',
            'match_found' => false,
            'valor_unitario' => $valorUnitarioConIva,
            'total' => $totalConIva,
            'mensaje' => 'Cálculo de producción aplicado (Costos + Gastos + Rentabilidad + IVA).',
            'detalles_calculo' => [
                'papel' => [
                    'medida_producto' => $resultadoCalculo['medida_final'],
                    'formato_maquina' => $resultadoCalculo['tamano'],
                    'cabida_calculada' => $resultadoCalculo['cabida'],
                    'total_tamanos' => $resultadoCalculo['hojas_totales'],
                    'pliegos' => $resultadoCalculo['pliegos_necesarios'],
                    'valor_unitario' => $resultadoCalculo['precio_pliego'],
                    'costo_total' => $resultadoCalculo['valor_total'],
                    'debug_nombre_recibido' => $resultadoCalculo['debug_nombre'] ?? 'No recibido'
                ],
                'debug_specs' => $resultadoCalculo['debug_specs'] ?? '',
                'tamano' => $resultadoCalculo['tamano'],
                'cabida' => $resultadoCalculo['cabida'],
                'impresion' => [
                    'costo_planchas' => $resultadoCalculo['costo_planchas'],
                    'costo_tiraje' => $resultadoCalculo['costo_tiraje'],
                    'num_tintas' => $resultadoCalculo['num_tintas'],
                    'valor_unitario_plancha' => $resultadoCalculo['precio_plancha']
                ],
                'acabados' => [
                    'costo_total' => $resultadoCalculo['costo_terminados'],
                    'detalles' => $resultadoCalculo['detalles_terminados'],
                    'area_mt2' => $resultadoCalculo['area_total_mt2'],
                    'print_w' => $resultadoCalculo['print_w'],
                    'print_h' => $resultadoCalculo['print_h']
                ],
                'diagrama' => $resultadoCalculo['diagrama'],
                'opciones_cabida' => $resultadoCalculo['opciones_cabida'] ?? []
            ],
            'valor_extra' => $valorExtraTotal
        ]);
    }

    /**
     * Realiza el cálculo detallado de la cotización basado en reglas de negocio.
     */
    private function solveRowStacking($W, $H, $pieceW, $pieceH) {
        if ($pieceW <= 0 || $pieceH <= 0) return ['total' => 0, 'pieces' => []];
        $count1 = floor($W / $pieceW); 
        $count2 = floor($W / $pieceH); 
        
        $best = ['total' => -1, 'n1' => 0, 'n2' => 0];
        
        for ($n1 = 0; $n1 * $pieceH <= $H; $n1++) {
            $remainingH = $H - ($n1 * $pieceH);
            $n2 = floor($remainingH / $pieceW); 
            
            $total = ($n1 * $count1) + ($n2 * $count2);
            if ($total > $best['total']) {
                $best = ['total' => $total, 'n1' => $n1, 'n2' => $n2, 'count1' => $count1, 'count2' => $count2, 'w1' => $pieceW, 'h1' => $pieceH, 'w2' => $pieceH, 'h2' => $pieceW];
            }
        }

        // Generar coordenadas para el mejor resultado
        $pieces = [];
        $currentY = 0;
        // Filas tipo 1
        for ($r = 0; $r < $best['n1']; $r++) {
            for ($c = 0; $c < $best['count1']; $c++) {
                $pieces[] = ['x' => $c * $best['w1'], 'y' => $currentY, 'w' => $best['w1'], 'h' => $best['h1']];
            }
            $currentY += $best['h1'];
        }
        // Filas tipo 2
        for ($r = 0; $r < $best['n2']; $r++) {
            for ($c = 0; $c < $best['count2']; $c++) {
                $pieces[] = ['x' => $c * $best['w2'], 'y' => $currentY, 'w' => $best['w2'], 'h' => $best['h2']];
            }
            $currentY += $best['h2'];
        }

        return ['total' => $best['total'], 'pieces' => $pieces];
    }

    private function calcularCotizacion($articulo, $cantidad, $especificaciones, $todosLosAtributos, $request = null)
    {
        $detalles = [];
        $valorTotal = 0;

        // 1. Identificar variables clave de las especificaciones
        $nombrePapel = '';
        $numTintas = 1;
        $printW = 100; // Por defecto pliego completo
        $printH = 70;
        
        $w = (float)($request ? $request->input('ancho', request('ancho')) : request('ancho'));
        $h = (float)($request ? $request->input('largo', request('largo')) : request('largo'));

        $anchoBox = (float)($request ? $request->input('ancho_box', 0) : 0);
        $largoBox = (float)($request ? $request->input('largo_box', 0) : 0);
        $altoBox = (float)($request ? $request->input('alto_box', 0) : 0);

        // --- EVALUACIÓN DE FÓRMULAS SEGURA ---
        $tipoProducto = ($articulo && $articulo->tipo) ? $articulo->tipo : ($request && $request->input('tipo_producto_id') ? \App\TipoProducto::find($request->input('tipo_producto_id')) : null);
        if ($tipoProducto && ($anchoBox > 0 || $largoBox > 0 || $altoBox > 0)) {
            $fAncho = $tipoProducto->formula_ancho;
            $fLargo = $tipoProducto->formula_largo;

            if ($fAncho || $fLargo) {
                // Función anónima para calcular expresiones simples de forma segura
                $safeEval = function($expr, $vals) {
                    $expr = str_ireplace(['W', 'L', 'A', 'H', 'P1', 'P2', 'PL', 'F'], [$vals['W'], $vals['L'], $vals['A'], $vals['H'], $vals['P1'], $vals['P2'], $vals['PL'], $vals['F']], $expr);
                    // Limpiar la expresión: solo números y operadores básicos
                    $expr = preg_replace('/[^0-9\+\-\*\/\(\)\.]/', '', $expr);
                    if (empty($expr)) return 0;
                    try {
                        return @eval("return $expr;"); 
                    } catch (\Throwable $e) {
                        return 0;
                    }
                };

                $vals = [
                    'W' => $anchoBox,
                    'L' => $largoBox,
                    'A' => $altoBox,
                    'H' => $altoBox,
                    'P1' => 2,
                    'P2' => 2,
                    'PL' => 2,
                    'F' => (float)request('fuelle') ?: 0
                ];

                if ($fAncho) {
                    $resA = $safeEval($fAncho, $vals);
                    if ($resA > 0) $w = (float)$resA;
                }

                if ($fLargo) {
                    $resL = $safeEval($fLargo, $vals);
                    if ($resL > 0) $h = (float)$resL;
                }
            }
        }

        $medidaTexto = $w . "x" . $h;

        if ($w <= 0 || $h <= 0) {
            $medidaFinal = $articulo->medida_final;
            if ($medidaFinal && stripos($medidaFinal, 'x') !== false) {
                $parts = explode('x', strtolower($medidaFinal));
                $w = (float)trim($parts[0]);
                $h = (float)trim($parts[1]);
                $medidaTexto = $medidaFinal;
            }
        }

        // --- FALLBACK: Si sigue en 0, buscar en el troquel de cabida 1 ---
        $usaTroquelReal = false;
        $troquelImg = null;
        if ($articulo && $articulo->id) {
            $medidaFinal = $articulo->medida_final;
            $troquelBase = \App\ArticuloTroquel::where('articulo_id', $articulo->id)->where('cabida', 1)->first();
            if ($troquelBase) {
                $usaTroquelReal = true;
                $troquelImg = $troquelBase->imagen_troquel;
                if ($w == 0 && $h == 0) {
                    $w = (float)$troquelBase->ancho;
                    $h = (float)$troquelBase->largo;
                }
            }
        }

        $cabida = 1;
        $tamanoMaquina = 1; 
        $nombreFormato = 'Personalizado';
        $piecesDiagram = [];
        $diagramW = 100;
        $diagramH = 70;

        $totalTamanos = 0;
        $pliegos = 0;
        $precioPliego = 0;
        $costoPlanchas = 0;
        $costoTiraje = 0;
        $costoTerminados = 0;
        $detallesTerminados = [];
        $areaTotalMt2 = 0;
        $precioUnitarioPlancha = 0;
        $costoProduccionNeto = 0;
        $porcentajeGastos = 0;
        $porcentajeRentabilidad = 0;
        $troquelImg = null;
        $formatoTecnico = $this->getFormatoTecnico($tamanoMaquina);

        if ($w > 0 && $h > 0) {
        // 0. Determinar Pliego Base (Ej: 70x100, 60x90) y cargar sus cortes desde Ajustes
        $pliegoBaseInput = $request ? $request->input('pliego_base', request('pliego_base', '70x100')) : '70x100';
        $pliegoW = 70;
        $pliegoH = 100;
        if ($pliegoBaseInput && strpos($pliegoBaseInput, 'x') !== false) {
            $pParts = explode('x', strtolower($pliegoBaseInput));
            if (count($pParts) >= 2 && (float)$pParts[0] > 0 && (float)$pParts[1] > 0) {
                $pliegoW = (float)$pParts[0];
                $pliegoH = (float)$pParts[1];
            }
        }

        // Consultar cortes configurados en Ajustes para este Pliego Base
        $cortesAjustes = \App\Ajustes::where('tipo', 'medidas_pliego')
            ->where(function($q) use ($pliegoBaseInput) {
                $q->where('categoria', $pliegoBaseInput)
                  ->orWhere('valor', $pliegoBaseInput);
            })->get();

        $fraccionesEstablecidas = [];
        if ($cortesAjustes->count() > 0) {
            foreach ($cortesAjustes as $corte) {
                $parts = explode('x', strtolower($corte->valor));
                if (count($parts) >= 2) {
                    $cw = (float)$parts[0];
                    $ch = (float)$parts[1];
                    if ($cw > 0 && $ch > 0) {
                        $cantCortesEnPliego = max(
                            floor($pliegoW / $cw) * floor($pliegoH / $ch),
                            floor($pliegoW / $ch) * floor($pliegoH / $cw)
                        );
                        $fraccionesEstablecidas[] = [
                            'n' => $corte->detalle ? $corte->detalle : ($corte->valor . ' cm'),
                            'div' => $cantCortesEnPliego > 0 ? $cantCortesEnPliego : 1,
                            'w' => $cw,
                            'h' => $ch
                        ];
                    }
                }
            }
        }

        // Fallback si no hay cortes específicos en Ajustes para esta categoría
        if (empty($fraccionesEstablecidas)) {
            $fraccionesEstablecidas = [
                ['n' => '1/1 Pliego Entero (' . $pliegoW . 'x' . $pliegoH . ' cm)', 'div' => 1, 'w' => $pliegoW, 'h' => $pliegoH],
                ['n' => '1/2 Medio Pliego', 'div' => 2, 'w' => $pliegoW, 'h' => $pliegoH / 2],
                ['n' => '1/3 Tercio Pliego', 'div' => 3, 'w' => $pliegoW, 'h' => $pliegoH / 3],
                ['n' => '1/4 Cuarto Pliego', 'div' => 4, 'w' => $pliegoW / 2, 'h' => $pliegoH / 2],
                ['n' => '1/6 Sexto Pliego', 'div' => 6, 'w' => $pliegoW / 3, 'h' => $pliegoH / 2],
                ['n' => '1/8 Octavo Pliego', 'div' => 8, 'w' => $pliegoW / 4, 'h' => $pliegoH / 2],
            ];
        }

        // --- LÓGICA DE TROQUEL (Prioridad Absoluta) ---
        $troquelImg = null;
        $usaTroquelReal = false;
        if (request('cabida_troquel')) {
            $troquelBase = \App\ArticuloTroquel::where('articulo_id', $articulo->id)
                ->where('cabida', request('cabida_troquel'))
                ->first();
            
            if ($troquelBase) {
                $troquelImg = $troquelBase->imagen;
                $cabida = (int)$troquelBase->cabida;
                $printW = (float)$troquelBase->ancho_impresion;
                $printH = (float)$troquelBase->largo_impresion;
                $medidaTexto = $printW . "x" . $printH . " (Troquel Real)";
                $usaTroquelReal = true;

                if ($troquelBase->tamano) {
                    $nombreFormato = $troquelBase->tamano;
                    if (preg_match('/\/(\d+)/', $nombreFormato, $m)) {
                        $tamanoMaquina = (int)$m[1];
                    } else {
                        preg_match_all('/(\d+)/', $nombreFormato, $ms);
                        $tamanoMaquina = !empty($ms[1]) ? (int)end($ms[1]) : 1;
                    }
                } else {
                    foreach ($fraccionesEstablecidas as $f) {
                        if (($printW <= $f['w'] && $printH <= $f['h']) || ($printH <= $f['w'] && $printW <= $f['h'])) {
                            $tamanoMaquina = $f['div'];
                            $nombreFormato = $f['n'];
                            break;
                        }
                    }
                }
            }
        }

        if (!$usaTroquelReal && $w > 0 && $h > 0) {
            $tipoProducto = $articulo->tipo;
            if ($tipoProducto && (int)$tipoProducto->piezas_por_pliego > 0) {
                $cabida = 1;
                $tamanoMaquina = (int)$tipoProducto->piezas_por_pliego;
                $nombreFormato = "1/" . $tamanoMaquina;
                
                $fraccion = collect($fraccionesEstablecidas)->where('div', $tamanoMaquina)->first();
                if ($fraccion) {
                    $printW = $fraccion['w'];
                    $printH = $fraccion['h'];
                } else {
                    $printW = $pliegoW;
                    $printH = $pliegoH / $tamanoMaquina;
                }
            } else {
                // 1. Evaluación de Opciones de Cabida y Desperdicio para cada Formato / Tamaño
                $opcionesCabidaCalculadas = [];
                $areaTrabajo = $w * $h;

                foreach ($fraccionesEstablecidas as $f) {
                    // Verificación de compatibilidad con máquinas del taller (pinzas, agarre y proceso de impresión)
                    if (!$this->esCompatibleConMaquina($f['w'], $f['h'], $f['div'])) {
                        continue; // Descartar resultado si NO hay compatibilidad con ninguna máquina
                    }

                    $resF1 = $this->solveRowStacking($f['w'], $f['h'], $w, $h);
                    $resF2 = $this->solveRowStacking($f['h'], $f['w'], $w, $h);
                    $cFisica = max($resF1['total'], $resF2['total']);
                    
                    if ($cFisica <= 0) continue; // Descartar si el trabajo no cabe en este formato

                    $areaSheet = $f['w'] * $f['h'];
                    $areaUsada = $cFisica * $areaTrabajo;
                    $aprovechamientoPct = round(($areaUsada / $areaSheet) * 100, 2);
                    $desperdicioPct = round(100 - $aprovechamientoPct, 2);

                    $tamanosBaseReq = ceil($cantidad / $cFisica);
                    $pliegosBaseReq = ceil($tamanosBaseReq / $f['div']);

                    $opcionesCabidaCalculadas[] = [
                        'nombre' => $f['n'],
                        'tamano' => $f['w'] . 'x' . $f['h'] . ' cm',
                        'div' => $f['div'],
                        'cabida' => $cFisica,
                        'aprovechamiento_pct' => $aprovechamientoPct,
                        'desperdicio_pct' => $desperdicioPct,
                        'pliegos_base' => $pliegosBaseReq,
                        'w' => $f['w'],
                        'h' => $f['h']
                    ];
                }

                // --- FILTRADO DE OPCIONES IDEALES (CONSERVAR MEJORES OPCIONES POR PRENSA Y CABIDA) ---
                $opcionesFinales = [];
                foreach ($opcionesCabidaCalculadas as $opA) {
                    $esDominada = false;
                    foreach ($opcionesCabidaCalculadas as $opB) {
                        if ($opA['nombre'] === $opB['nombre']) continue;

                        // Si B requiere estrictamente menos pliegos base y tiene mayor o igual cabida y mejor/igual rendimiento -> descartar A
                        if ($opB['pliegos_base'] < $opA['pliegos_base'] && $opB['cabida'] >= $opA['cabida'] && $opB['aprovechamiento_pct'] >= $opA['aprovechamiento_pct']) {
                            $esDominada = true;
                            break;
                        }
                    }

                    if (!$esDominada) {
                        $opcionesFinales[] = $opA;
                    }
                }

                // Ordenar: 1) Mayor cabida, 2) Mayor aprovechamiento %, 3) Menor pliegos base
                usort($opcionesFinales, function($a, $b) {
                    if ($a['cabida'] != $b['cabida']) {
                        return $b['cabida'] <=> $a['cabida'];
                    }
                    if ($a['aprovechamiento_pct'] != $b['aprovechamiento_pct']) {
                        return $b['aprovechamiento_pct'] <=> $a['aprovechamiento_pct'];
                    }
                    return $a['pliegos_base'] <=> $b['pliegos_base'];
                });

                $opcionesCabida = $opcionesFinales;

                // Rendimiento máximo en el Pliego Base Maestro completo para diagrama
                $resFull1 = $this->solveRowStacking($pliegoW, $pliegoH, $w, $h);
                $resFull2 = $this->solveRowStacking($pliegoH, $pliegoW, $w, $h);
                if ($resFull1['total'] >= $resFull2['total']) {
                    $piecesDiagram = $resFull1['pieces'];
                    $diagramW = $pliegoW; $diagramH = $pliegoH;
                } else {
                    $piecesDiagram = $resFull2['pieces'];
                    $diagramW = $pliegoH; $diagramH = $pliegoW;
                }

                if (!empty($opcionesCabida)) {
                    $topOp = $opcionesCabida[0];

                    // Si el usuario especificó una cabida manual vía request, la respetamos
                    $cabidaUsuario = (int)($request ? $request->input('cabida_troquel', request('cabida_troquel')) : request('cabida_troquel'));
                    if ($cabidaUsuario > 0) {
                        $cabida = $cabidaUsuario;
                        // Buscar el formato que mejor coincida con la cabida pedida
                        $matchOp = collect($opcionesCabida)->where('cabida', $cabidaUsuario)->first();
                        if ($matchOp) {
                            $tamanoMaquina = $matchOp['div'];
                            $nombreFormato = $matchOp['nombre'];
                            $printW = $matchOp['w'];
                            $printH = $matchOp['h'];
                        } else {
                            $tamanoMaquina = $topOp['div'];
                            $nombreFormato = $topOp['nombre'];
                            $printW = $topOp['w'];
                            $printH = $topOp['h'];
                        }
                    } else {
                        $cabida = $topOp['cabida'];
                        $tamanoMaquina = $topOp['div'];
                        $nombreFormato = $topOp['nombre'];
                        $printW = $topOp['w'];
                        $printH = $topOp['h'];
                    }
                } else {
                    $cabida = 1;
                    $tamanoMaquina = 1;
                    $nombreFormato = "Pliego " . $pliegoW . "x" . $pliegoH . " cm";
                    $printW = $pliegoW;
                    $printH = $pliegoH;
                }
            }

        // --- BÚSQUEDA DE TROQUEL ESPECÍFICO (Solo para obtener la IMAGEN si no se usó troquel real arriba) ---
        if (!$usaTroquelReal && $articulo) {
            $troquel = \App\ArticuloTroquel::where('articulo_id', $articulo->id)
                                          ->where('cabida', (int)$cabida)
                                          ->first();
            if ($troquel) {
                // SOBRESCRIBIMOS medidas teóricas con las medidas físicas grabadas
                if ((float)$troquel->ancho_impresion > 0) $printW = (float)$troquel->ancho_impresion;
                if ((float)$troquel->largo_impresion > 0) $printH = (float)$troquel->largo_impresion;
                $troquelImg = $troquel->imagen;

                // SI el troquel tiene un tamaño definido (ej: 1/4), lo usamos como ley
                if ($troquel->tamano) {
                    $nombreFormato = $troquel->tamano;
                    // Buscar el número después del slash (ej: 1/4 -> 4)
                    if (preg_match('/\/(\d+)/', $nombreFormato, $m)) {
                        $tamanoMaquina = (int)$m[1];
                    } else {
                        preg_match_all('/(\d+)/', $nombreFormato, $ms);
                        $tamanoMaquina = !empty($ms[1]) ? (int)end($ms[1]) : $tamanoMaquina;
                    }
                }
            }
        }
        
        if (is_array($especificaciones)) {
            foreach ($especificaciones as $spec) {
                $titulo = strtolower(trim($spec['titulo']));
                if ($titulo == 'papel') $nombrePapel = $spec['valor'];
                if ($titulo == 'tinta' || $titulo == 'tintas') {
                    $valor = strtolower(trim($spec['valor']));
                    if (stripos($valor, 'full') !== false || stripos($valor, '4') !== false) $numTintas = 4;
                    else {
                        preg_match('/(\d+)/', $valor, $m);
                        $numTintas = isset($m[1]) ? (int)$m[1] : 1;
                    }
                }
            }
        }

        // Si el usuario especificó una cabida manual desde el cotizador, respetarla prioritariamente
        $cabidaInputUser = (int)($request ? $request->input('cabida_troquel', request('cabida_troquel')) : request('cabida_troquel'));
        if ($cabidaInputUser > 0) {
            $cabida = $cabidaInputUser;
        }

        // 2. CÁLCULO DE PAPEL (Fórmula WebLupa Pro: Pliegos = ((Cantidad / Cabida) + Sobrante) / Tamaño)
        // Tamaño = Piezas totales del trabajo que salen de 1 Pliego Maestro completo (Divisor * Cabida)
        $tamanoPiezas = ($tamanoMaquina > 0 && $cabida > 0) ? ($tamanoMaquina * $cabida) : ($tamanoMaquina > 0 ? $tamanoMaquina : 1);
        $sobranteBase = 50 + (($numTintas - 1) * 25);
        if ($numTintas >= 4) $sobranteBase = 125;

        $bases = ceil($cantidad / ($cabida ?: 1));
        $sobranteTotal = $sobranteBase;
        if ($bases > 1000) {
            $pasosExtra = floor(($bases - 1) / 1000);
            $sobranteTotal += ($pasosExtra * ($sobranteBase * 0.30));
        }

        $totalTamanos = ceil($bases + $sobranteTotal);
        $pliegos = ceil($totalTamanos / $tamanoPiezas);

        // Precio papel
        $precioPliego = 0;
        if (!empty($nombrePapel)) {
            // 1. Intento por nombre exacto o LIKE completo
            $insumoPapel = \App\Costois::where('nombre', 'LIKE', "%$nombrePapel%")->where('estado', 1)->first();
            
            // 2. Búsqueda progresiva por palabras (eliminando genéricos al inicio)
            if (!$insumoPapel) {
                $palabras = array_filter(explode(' ', str_replace(['(', ')', '-', ',', '.', '/'], ' ', $nombrePapel)));
                
                // Intentamos buscar por combinaciones de palabras de atrás hacia adelante
                // (Ej: "Cartulina blanca calibre 48" -> prueba "blanca calibre 48", luego "calibre 48")
                while (count($palabras) > 1 && !$insumoPapel) {
                    $query = \App\Costois::where('estado', 1);
                    foreach ($palabras as $p) {
                        if (strlen($p) > 2) $query->where('nombre', 'LIKE', "%$p%");
                    }
                    $insumoPapel = $query->first();
                    if (!$insumoPapel) array_shift($palabras); // Quitar la primera palabra y reintentar
                }
            }

            // 3. Último recurso: Buscar solo por la última palabra (si es un número significativo como "48")
            if (!$insumoPapel) {
                $palabras = array_filter(explode(' ', $nombrePapel));
                $ultima = end($palabras);
                if (is_numeric($ultima) || strlen($ultima) > 2) {
                    $insumoPapel = \App\Costois::where('nombre', 'LIKE', "%$ultima%")
                        ->where('estado', 1)
                        ->first();
                }
            }

            if ($insumoPapel) {
                $precioPliego = (float)$insumoPapel->valor;
            }
        }

        $costoPapel = $pliegos * $precioPliego;
        $valorTotal += $costoPapel;

        // 3. CLASIFICACIÓN TÉCNICA DEL FORMATO (Centralizado para todos los procesos)
        $formatoTecnico = $this->getFormatoTecnico($tamanoMaquina);

        // 4. CÁLCULO DE PLANCHAS (Dinámico con Atributos)
        $costoPlanchas = 0;
        $precioUnitarioPlancha = 0;
        
        $formatoTecnico = $this->getFormatoTecnico($tamanoMaquina);
        
        // Buscamos si hay un atributo tipo 'Plancha' que cumpla la condición para este formato
        $attrPlancha = \App\AtributoTienda::where('tipo', 'Plancha')
            ->where('activo', 1)
            ->get()
            ->filter(function($a) use ($tamanoMaquina, $formatoTecnico) {
                return $this->checkCondition($a->condicion, [
                    'divisor' => $tamanoMaquina,
                    'formato' => $formatoTecnico['slug']
                ]);
            })->first();

        if ($attrPlancha) {
            $costoPlanchas = $this->evaluateFormula($attrPlancha->formula, $attrPlancha->valor_extra, [
                'cantidad' => $cantidad,
                'tintas' => $numTintas,
                'divisor' => $tamanoMaquina,
                'valor' => (float)$attrPlancha->valor_extra
            ]);
            $precioUnitarioPlancha = (float)$attrPlancha->valor_extra;
        } else {
            // Fallback al sistema anterior por Insumos
            $insumoPlancha = \App\Costois::where('nombre', 'LIKE', "%" . $formatoTecnico['plancha'] . "%")->where('estado', 1)->first();
            if ($insumoPlancha) {
                $precioUnitarioPlancha = (float)$insumoPlancha->valor;
                $costoPlanchas = $precioUnitarioPlancha * $numTintas;
            }
        }
        $valorTotal += $costoPlanchas;

        // 5. CÁLCULO DE TIRAJE (MANO DE OBRA)
        $costoTiraje = 0;
        $insumoTiraje = \App\Costois::where('nombre', 'LIKE', "%" . $formatoTecnico['tiraje'] . "%")->where('estado', 1)->first();
        if ($insumoTiraje) {
            $valorBaseMillarUnitario = (float)$insumoTiraje->valor;
            $tamañosNetos = $cantidad / ($cabida ?: 1);
            
            $costoPrimerMillar = $valorBaseMillarUnitario * $numTintas;
            $costoTiraje = $costoPrimerMillar;

            if ($tamañosNetos > 1000) {
                $millaresAdicionales = ceil(($tamañosNetos - 1000) / 1000);
                $costoTiraje += ($millaresAdicionales * ($costoPrimerMillar * 0.70));
            }
        }
        $valorTotal += $costoTiraje;

        // 6. CÁLCULO DE TERMINADOS (Área de hoja de impresión x Cantidad de hojas)
        $costoTerminados = 0;
        $detallesTerminados = [];
        // Área en m2 = (Ancho hoja cm / 100) * (Alto hoja cm / 100)
        $areaPorHoja = ($printW / 100) * ($printH / 100); 
        $areaTotalMt2 = $totalTamanos * $areaPorHoja;

        if (is_array($especificaciones)) {
            foreach ($especificaciones as $spec) {
            $titulo = strtolower(trim($spec['titulo'] ?? ''));
            $valorSeleccionado = $spec['valor'] ?? '';

            // Búsqueda en la colección pre-cargada
            $attr = $todosLosAtributos->where('nombre', $valorSeleccionado)->first();
            
            if ($attr) {
                $costoActual = 0;
                
                // Si tiene fórmula, la usamos
                if (!empty($attr->formula)) {
                    $context = [
                        'cantidad' => $cantidad,
                        'tintas' => $numTintas,
                        'divisor' => $tamanoMaquina,
                        'area' => $areaTotalMt2,
                        'valor' => (float)$attr->valor_extra
                    ];
                    
                    // Resolver dependencia si existe
                    if (!empty($attr->dependencia)) {
                        $context['dependencia'] = $this->getSelectedValueForType($attr->dependencia, $especificaciones);
                    }
                    
                    $costoActual = $this->evaluateFormula($attr->formula, $attr->valor_extra, $context);
                } 
                // Si no tiene fórmula pero es de tipo Terminado/Acabado, usamos lógica base por m2
                elseif (in_array(strtolower($attr->tipo), ['terminado', 'acabado', 'terminados', 'acabados'])) {
                    $costoActual = $areaTotalMt2 * (float)$attr->valor_extra;
                }

                if ($costoActual > 0) {
                    $costoTerminados += $costoActual;
                    $detallesTerminados[] = [
                        'nombre' => $valorSeleccionado,
                        'tipo' => $attr->tipo,
                        'valor_m2' => (float)$attr->valor_extra,
                        'costo' => $costoActual
                    ];
                }
            }
        }

        // 7. CÁLCULO ESPECÍFICO DE TROQUELADO (Automático si hay troquel o manual si se solicita)
        $quiereTroquel = false;
        
        // Solo verificamos si el tipo de producto lo permite
        $tipoProducto = $articulo->tipo;
        if ($tipoProducto && (int)$tipoProducto->permite_troquelado == 1) {
            // Si el artículo ya tiene una línea de troquel real configurada, asumimos que se debe troquelar
            if ($troquelImg) {
                $quiereTroquel = true;
            } else {
                foreach ($especificaciones as $spec) {
                    if (stripos($spec['titulo'], 'troquel') !== false || stripos($spec['valor'], 'troquel') !== false) {
                        $quiereTroquel = true;
                        break;
                    }
                }
            }
        }

        if ($quiereTroquel) {
            $slugFormato = $formatoTecnico['slug']; // 'medio', 'cuarto', 'pliego'
            
            // Regla: Si el tamaño de impresión es mayor a 70x50 cm (3500 cm2), es Medio Mayor
            if (($printW * $printH) > 3500) {
                $slugFormato = 'medio mayor';
            }

            $insumoTroquel = \App\Costois::where('estado', 1)
                ->where(function($q) use ($slugFormato) {
                    $q->where('nombre', 'LIKE', "%troquelado%".$slugFormato."%")
                      ->orWhere('nombre', 'LIKE', "%troquelado%".($slugFormato == 'medio' ? 'pliego' : 'X')."%");
                })->first();

            if ($insumoTroquel) {
                $valorBaseTroquel = (float)$insumoTroquel->valor;
                $hojasProduccion = $cantidad / ($cabida ?: 1);
                
                // Primer millar 100%
                $costoTroquelado = $valorBaseTroquel;

                // Millares adicionales al 70%
                if ($hojasProduccion > 1000) {
                    $millaresAdicionales = ceil(($hojasProduccion - 1000) / 1000);
                    $costoTroquelado += ($millaresAdicionales * ($valorBaseTroquel * 0.70));
                }
                
                $costoTerminados += $costoTroquelado;
                $detallesTerminados[] = [
                    'nombre' => 'Troquelado (' . $insumoTroquel->nombre . ')',
                    'tipo' => 'Terminado',
                    'valor_m2' => 0, // No es por m2
                    'costo' => $costoTroquelado
                ];
            }
        }

        // 8. CÁLCULO DE CORTE INICIAL (Basado en gramaje y cantidad de pliegos)
        if ($formatoTecnico['slug'] != 'pliego') {
            $insumoCorte = \App\Costois::where('estado', 1)
                ->where('nombre', 'LIKE', "%Corte %" . $formatoTecnico['slug'] . "%")
                ->first();

            if ($insumoCorte) {
                $valorBaseCorte = (float)$insumoCorte->valor;
                
                // Intentar detectar gramaje del nombre del papel (ej: "75g", "240gr")
                $gramajePapel = 0;
                if (preg_match('/(\d+)\s*(g|gr)/i', $nombrePapel, $matches)) {
                    $gramajePapel = (int)$matches[1];
                } else {
                    // Fallback: Si es cartón o cartulina, asumimos > 150g
                    if (stripos($nombrePapel, 'carton') !== false || stripos($nombrePapel, 'cartulina') !== false || stripos($nombrePapel, 'kraft') !== false) {
                        $gramajePapel = 200; 
                    } else {
                        $gramajePapel = 75; // Papel común delgado
                    }
                }

                $hojasPorPaquete = ($gramajePapel < 150) ? 500 : 100;
                $numPaquetesCorte = ceil($pliegos / $hojasPorPaquete);
                $costoCorteInicial = $numPaquetesCorte * $valorBaseCorte;

                $costoTerminados += $costoCorteInicial;
                $detallesTerminados[] = [
                    'nombre' => 'Corte Inicial (' . $insumoCorte->nombre . ' - ' . $gramajePapel . 'g)',
                    'tipo' => 'Terminado',
                    'valor_m2' => 0,
                    'costo' => $costoCorteInicial
                ];
            }
        }

        // 9. CÁLCULO DE EMPAQUE (Basado en el TAMAÑO FINAL de la pieza individual)
        $areaIndividual = $w * $h;
        $slugEmpaqueFinal = 'mayor';
        
        if ($areaIndividual <= 875) $slugEmpaqueFinal = 'octavo'; // 1/8 de pliego (70x50/8)
        elseif ($areaIndividual <= 1750) $slugEmpaqueFinal = 'cuarto'; // 1/4 de pliego
        elseif ($areaIndividual <= 3500) $slugEmpaqueFinal = 'medio pliego'; // 1/2 pliego
        else $slugEmpaqueFinal = 'mayor';

        $insumoEmpaque = \App\Costois::where('estado', 1)
            ->where('nombre', 'LIKE', "%Empaque %" . $slugEmpaqueFinal . "%")
            ->first();

        if ($insumoEmpaque) {
            $valorBaseEmpaque = (float)$insumoEmpaque->valor;
            // El empaque se calcula por cada millar de productos finales
            $millaresFinales = ceil($cantidad / 1000);
            $costoEmpaque = $millaresFinales * $valorBaseEmpaque;

            $costoTerminados += $costoEmpaque;
            $detallesTerminados[] = [
                'nombre' => 'Empaque (' . $insumoEmpaque->nombre . ' - Tamaño Final)',
                'tipo' => 'Terminado',
                'valor_m2' => 0,
                'costo' => $costoEmpaque
            ];
        }

        // 10. CÁLCULO DE CORTE FINAL (Si aplica)
        $costoCorteFinal = 0;
        // Solo si no fue troquelado, el corte final es necesario para separar piezas
        if (!$quiereTroquel && $cabida > 1) {
            $insumoCorteFinal = \App\Costois::where('estado', 1)
                ->where('nombre', 'LIKE', "%Corte final%")
                ->first();
            
            if ($insumoCorteFinal) {
                $valorBaseCorteFinal = (float)$insumoCorteFinal->valor;
                $numCortesFinales = ceil($cantidad / 500); // Un corte por cada 500 unidades
                $costoCorteFinal = $numCortesFinales * $valorBaseCorteFinal;

                $costoTerminados += $costoCorteFinal;
                $detallesTerminados[] = [
                    'nombre' => 'Corte Final (' . $insumoCorteFinal->nombre . ')',
                    'tipo' => 'Terminado',
                    'valor_m2' => 0,
                    'costo' => $costoCorteFinal
                ];
            }
        }

        // 11. SUMATORIA TOTAL Y APLICACIÓN DE MÁRGENES
        // Sumamos TODO: Papel + Planchas + Tiraje + (Terminados + Troquel + Empaque + Corte Final)
        $costoProduccionNeto = $valorTotal + $costoTerminados;
        
        $porcentajeGastos = 0;
        $porcentajeRentabilidad = 0;

        $tipoProducto = ($articulo && isset($articulo->tipo)) ? $articulo->tipo : null;
        if ($tipoProducto) {
            $porcentajeGastos = (float)$tipoProducto->gastos_fijos;
            $porcentajeRentabilidad = (float)$tipoProducto->rentabilidad;
        }

        // Paso A: Sumar Gastos Fijos sobre el costo neto de producción
        $valorConGastos = $costoProduccionNeto * (1 + ($porcentajeGastos / 100));
        
        // Paso B: Sumar Rentabilidad sobre el valor ya incrementado con gastos
        $valorFinalVenta = $valorConGastos * (1 + ($porcentajeRentabilidad / 100));
        
        $valorTotal = $valorFinalVenta;

        return [
            'medida_final' => $medidaTexto,
            'cabida' => $cabida,
            'tamano' => $tamanoPiezas,
            'formato_maquina' => $nombreFormato,
            'cantidad_pedida' => $cantidad,
            'tamanos_base' => $bases,
            'sobrante' => $sobranteTotal,
            'hojas_totales' => $totalTamanos,
            'pliegos_necesarios' => $pliegos,
            'valor_total' => $valorTotal,
            'costo_neto' => $costoProduccionNeto,
            'porcentaje_gastos' => $porcentajeGastos,
            'porcentaje_rentabilidad' => $porcentajeRentabilidad,
            'precio_pliego' => $precioPliego,
            'costo_planchas' => $costoPlanchas,
            'costo_tiraje' => $costoTiraje,
            'costo_terminados' => $costoTerminados,
            'detalles_terminados' => $detallesTerminados,
            'area_total_mt2' => $areaTotalMt2,
            'print_w' => $printW,
            'print_h' => $printH,
            'num_tintas' => $numTintas,
            'precio_plancha' => $precioUnitarioPlancha,
            'formato_tecnico' => $formatoTecnico['slug'],
            'papel' => [
                'medida_producto' => $medidaTexto,
                'formato_maquina' => $nombreFormato,
                'tamano' => $tamanoPiezas,
                'cabida' => $cabida,
                'cantidad' => $cantidad,
                'tamanos_base' => $bases,
                'sobrante' => $sobranteTotal,
                'total_tamanos' => $totalTamanos,
                'pliegos' => $pliegos,
                'valor_unitario' => $precioPliego,
                'costo_total' => $pliegos * $precioPliego
            ],
            'diagrama' => [
                'pieces' => $piecesDiagram,
                'w' => $diagramW,
                'h' => $diagramH,
                'troquel_img' => $troquelImg
            ],
            'opciones_cabida' => $opcionesCabida ?? []
        ];
        }
    }

    return [
        'medida_final' => $medidaTexto ?? 'N/A',
        'cabida' => $cabida ?? 1,
        'tamano' => $tamanoPiezas ?? 1,
        'formato_maquina' => $nombreFormato ?? 'N/A',
        'cantidad_pedida' => $cantidad,
        'tamanos_base' => $bases ?? 1,
        'sobrante' => $sobranteTotal ?? 0,
        'hojas_totales' => $totalTamanos ?? 1,
        'pliegos_necesarios' => $pliegos ?? 1,
        'valor_total' => $valorTotal ?? 0,
        'costo_neto' => $costoProduccionNeto ?? 0,
        'porcentaje_gastos' => $porcentajeGastos ?? 0,
        'porcentaje_rentabilidad' => $porcentajeRentabilidad ?? 0,
        'precio_pliego' => $precioPliego ?? 0,
        'costo_planchas' => $costoPlanchas ?? 0,
        'costo_tiraje' => $costoTiraje ?? 0,
        'costo_terminados' => $costoTerminados ?? 0,
        'detalles_terminados' => $detallesTerminados ?? [],
        'area_total_mt2' => $areaTotalMt2 ?? 0,
        'print_w' => $printW ?? 100,
        'print_h' => $printH ?? 70,
        'num_tintas' => $numTintas ?? 1,
        'precio_plancha' => $precioUnitarioPlancha ?? 0,
        'formato_tecnico' => $formatoTecnico['slug'] ?? 'pliego',
        'papel' => [],
        'diagrama' => ['pieces' => [], 'w' => 100, 'h' => 70],
        'opciones_cabida' => $opcionesCabida ?? []
    ];
}
}

    /**
     * Clasifica el formato de producción según el divisor
     */
    private function getFormatoTecnico($divisor) {
        // Cargar umbrales desde Ajustes (Configuración Tienda)
        $ajustes = \App\Ajustes::where('tipo', 'tienda')->get();
        $thresholdOctavo = 8;
        $thresholdCuarto = 4;
        $thresholdMedio = 2;

        foreach ($ajustes as $ajuste) {
            if ($ajuste->detalle === 'threshold_octavo') $thresholdOctavo = (float)$ajuste->valor;
            if ($ajuste->detalle === 'threshold_cuarto') $thresholdCuarto = (float)$ajuste->valor;
            if ($ajuste->detalle === 'threshold_medio') $thresholdMedio = (float)$ajuste->valor;
        }

        if ($divisor >= $thresholdOctavo) {
            return [
                'slug' => 'octavo',
                'plancha' => 'Plancha octavo',
                'tiraje' => 'Tiraje octavo'
            ];
        } elseif ($divisor >= $thresholdCuarto) {
            return [
                'slug' => 'cuarto',
                'plancha' => 'Plancha cuarto',
                'tiraje' => 'Tiraje un cuarto'
            ];
        } elseif ($divisor >= $thresholdMedio) {
            return [
                'slug' => 'medio',
                'plancha' => 'Plancha medio pliego',
                'tiraje' => 'Tiraje medio pliego'
            ];
        } else {
            return [
                'slug' => 'pliego',
                'plancha' => 'Plancha pliego completo',
                'tiraje' => 'Tiraje pliego completo'
            ];
        }
    }

    /**
     * Lógica inteligente de coincidencia para tintas y otros atributos.
     */
    private function checkMatch($buscado, $existente, $tipo) {
        $buscado = strtolower(trim($buscado));
        $existente = strtolower(trim($existente));

        if (empty($buscado) || empty($existente)) return false;

        // 1. Coincidencia bidireccional simple
        if (stripos($existente, $buscado) !== false || stripos($buscado, $existente) !== false) return true;

        // 2. Coincidencia por palabras (70% de las palabras deben coincidir)
        $palabrasBuscadas = array_filter(explode(' ', str_replace(['(', ')', ',', '.', '-'], ' ', $buscado)));
        $palabrasExistentes = array_filter(explode(' ', str_replace(['(', ')', ',', '.', '-'], ' ', $existente)));
        
        if (count($palabrasBuscadas) > 0) {
            $coincidencias = 0;
            $palabrasValidas = 0;
            foreach ($palabrasBuscadas as $pb) {
                $esNumero = preg_match('/\d/', $pb);
                if (strlen($pb) < 3 && !$esNumero) continue; // Ignorar palabras cortas que NO sean números
                $palabrasValidas++;
                foreach ($palabrasExistentes as $pe) {
                    if (stripos($pe, $pb) !== false || stripos($pb, $pe) !== false) {
                        $coincidencias++;
                        break;
                    }
                }
            }
            if ($palabrasValidas > 0) {
                $porcentajeMatch = ($coincidencias / $palabrasValidas) * 100;
                if ($porcentajeMatch >= 70) return true;
            }
        }

        // Lógica para Tintas
        if (strtolower($tipo) == 'tinta') {
            // Caso Fullcolor / 4 tintas
            $esFullColorBuscado = (stripos($buscado, 'full') !== false || stripos($buscado, '4') !== false || stripos($buscado, 'policromia') !== false);
            $esFullColorExistente = (stripos($existente, 'full') !== false || stripos($existente, '4') !== false || stripos($existente, 'policromia') !== false);
            
            if ($esFullColorBuscado && $esFullColorExistente) return true;

            // Extraer números si existen (1 tinta, 2 tintas...)
            preg_match('/(\d+)/', $buscado, $matchesBuscado);
            preg_match('/(\d+)/', $existente, $matchesExistente);
            
            $numBuscado = isset($matchesBuscado[1]) ? $matchesBuscado[1] : null;
            $numExistente = isset($matchesExistente[1]) ? $matchesExistente[1] : null;

            if ($numBuscado && $numExistente) {
                if ($numBuscado == $numExistente) return true;
            }

            // Si el cliente busca "1 tinta" y en la base de datos dice un color (ej: "NEGRA", "AZUL", "PANTONE")
            if ($numBuscado == '1' || stripos($buscado, 'una') !== false) {
                // Si no hay números en el existente pero hay texto, y no dice "y", "con", "duo", "doble"
                if (!$numExistente && strlen($existente) > 2) {
                    $multiples = [' y ', ' con ', ' + ', ',', 'duo', 'doble', 'triple', 'full'];
                    $esMultiple = false;
                    foreach ($multiples as $m) {
                        if (stripos($existente, $m) !== false) {
                            $esMultiple = true;
                            break;
                        }
                    }
                    if (!$esMultiple) return true; 
                }
            }
        }

        return false;
    }

    /**
     * Evalúa una condición simple (ej: "divisor >= 8")
     */
    private function checkCondition($condicion, $context) {
        if (empty($condicion)) return true;

        // Limpieza inicial de espacios y normalización
        $condicionOriginal = $condicion;
        $condicion = str_ireplace(['tamaño', 'tamaños', 'impresion', 'impresión'], 'formato', $condicion);
        $condicion = str_ireplace(['cuarto', 'octavo', 'medio', 'pliego'], ["'cuarto'", "'octavo'", "'medio'", "'pliego'"], $condicion);

        foreach ($context as $key => $val) {
            // Reemplazamos solo si la variable existe en el string (evitamos dobles comillas)
            if (strpos($condicion, $key) !== false) {
                $replacement = is_numeric($val) ? $val : "'$val'";
                $condicion = str_replace($key, $replacement, $condicion);
            }
        }
        
        try {
            // Permitimos operadores y caracteres necesarios para la comparación
            $condicion = preg_replace('/[^0-9\s\>\=\<\!\&\|\'\.\,a-zA-Z]/', '', $condicion);
            
            // Si después de la limpieza el string empieza por un operador, es que falta la variable
            if (preg_match('/^\s*[\>\=\<]/', $condicion)) {
                return false; 
            }

            if (empty(trim($condicion))) return true;
            
            $result = eval("return ($condicion);");
            return $result;
        } catch (\Exception $e) {
            \Log::error("Error evaluando condición: [$condicion] Original: [$condicionOriginal] Error: " . $e->getMessage());
            return false;
        } catch (\Throwable $t) {
            \Log::error("Error fatal en condición: [$condicion] Error: " . $t->getMessage());
            return false;
        }
    }

    private function fitsInMachine($cutW, $cutH, $minW, $minH, $maxW, $maxH) {
        $normal = ($cutW >= $minW && $cutH >= $minH && $cutW <= $maxW && $cutH <= $maxH) ||
                  ($cutW >= $minH && $cutH >= $minW && $cutW <= $maxH && $cutH <= $maxW);

        $rotated = ($cutH >= $minW && $cutW >= $minH && $cutH <= $maxW && $cutH <= $maxH) ||
                   ($cutH >= $minH && $cutW >= $minW && $cutH <= $maxH && $cutW <= $maxW);

        return $normal || $rotated;
    }

    private function fitsInGTO($w, $h) {
        return $this->fitsInMachine($w, $h, 22.0, 14.0, 50.0, 35.0);
    }

    private function fitsInSORM($w, $h) {
        return $this->fitsInMachine($w, $h, 35.0, 27.5, 70.0, 52.0);
    }

    /**
     * Valida si un formato de pliego de corte es compatible físicamente con las máquinas del taller
     * utilizando la lógica exacta definida en la tabla de Ajustes
     */
    private function esCompatibleConMaquina($sheetW, $sheetH, $div) {
        $w = (float)$sheetW;
        $h = (float)$sheetH;

        $aptoGTO = $this->fitsInGTO($w, $h);
        $aptoSORM = $this->fitsInSORM($w, $h);

        // Si no entra en los rangos de la GTO (22x14 - 50x35) ni de la SORM (35x27.5 - 70x52), descartar
        if (!$aptoGTO && !$aptoSORM) {
            return false;
        }

        return true;
    }

    /**
     * Evalúa una fórmula matemática básica
     */
    private function evaluateFormula($formula, $valorBase, $context) {
        if (empty($formula)) return $valorBase * ($context['tintas'] ?? 1);

        // Alias comunes para el usuario
        $formula = str_ireplace(['cantidad tintas', 'numero tintas', 'tintas'], '{tintas}', $formula);
        $formula = str_ireplace(['valor extra atributo', 'valor atributo', 'valor extra'], '{valor}', $formula);
        $formula = str_ireplace(['cantidad total', 'unidades'], '{cantidad}', $formula);

        foreach ($context as $key => $val) {
            // Si el valor es texto, intentamos extraer el número (ej: "4 Tintas" -> 4)
            $numericVal = $val;
            if (!is_numeric($val) && is_string($val)) {
                preg_match('/(\d+)/', $val, $m);
                $numericVal = isset($m[1]) ? (float)$m[1] : 0;
            }
            $formula = str_replace('{'.$key.'}', $numericVal, $formula);
        }
        
        // Fallback para valor si no se usó llaves
        $formula = str_replace('valor', (float)$valorBase, $formula);

        try {
            $formula = preg_replace('/[^0-9\s\+\-\*\/\(\)\.]/', '', $formula);
            if (empty(trim($formula))) return (float)$valorBase;
            
            return eval("return ($formula);");
        } catch (\Exception $e) {
            \Log::error("Error en fórmula: " . $formula . " - " . $e->getMessage());
            return (float)$valorBase;
        }
    }

    private function getSelectedValueForType($type, $especificaciones) {
        foreach ($especificaciones as $spec) {
            if (strtolower(trim($spec['titulo'])) == strtolower($type)) {
                return $spec['valor'];
            }
        }
        return 0;
    }

    private function isJson($string) {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}
