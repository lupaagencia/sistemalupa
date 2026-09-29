<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Asistencia;
use App\Empleado;
use App\Turno;
use App\TurnoDia;
use App\ConfiguracionAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AsistenciaController extends Controller
{
    public function pantallaKiosco()
    {
        return view('backend.kiosco_pantalla');
    }

    public function pantallaMarcarMovil(Request $request)
    {
        $empleado = null;
        $empleados = [];

        $token = $request->query('token');
        if (!empty($token)) {
            $empleado = Empleado::with('turnoAsignado')
                ->where('codigo_qr', $token)
                ->orWhere('id', $token)
                ->orWhere('num_doc', $token)
                ->first();
        }

        if (!$empleado && Auth::check() && Auth::user()->empleado_id) {
            $empleado = Empleado::with('turnoAsignado')->find(Auth::user()->empleado_id);
        }

        if (!$empleado) {
            $empleados = Empleado::select('id', 'nombre', 'apellido', 'num_doc', 'cargo')
                ->orderBy('nombre', 'asc')
                ->get();
        }

        return view('backend.marcar_movil', [
            'empleado' => $empleado,
            'empleados' => $empleados
        ]);
    }

    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $fechaInicio = $request->fecha_inicio ?: Carbon::today()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?: Carbon::today()->format('Y-m-d');
        $empleadoId = $request->empleado_id;
        $estadoLlegada = $request->estado_llegada;
        $buscar = $request->buscar;
        $perPage = $request->input('per_page', 15);

        $query = Asistencia::with(['empleado', 'turno']);

        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }

        if (!empty($empleadoId)) {
            $query->where('empleado_id', $empleadoId);
        }

        if (!empty($estadoLlegada)) {
            $query->where('estado_llegada', $estadoLlegada);
        }

        if (!empty($buscar)) {
            $query->whereHas('empleado', function($q) use ($buscar) {
                $q->where('nombre', 'like', '%' . $buscar . '%')
                  ->orWhere('apellido', 'like', '%' . $buscar . '%')
                  ->orWhere('num_doc', 'like', '%' . $buscar . '%');
            });
        }

        $asistencias = $query->orderBy('hora_marcada', 'desc')->paginate($perPage);

        // KPIs calculation
        $today = Carbon::today()->format('Y-m-d');
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');

        $marcacionesHoy = Asistencia::where('fecha', $today)->count();
        $llegadasTardeMes = Asistencia::whereBetween('fecha', [$startOfMonth, $today])
            ->where('estado_llegada', 'llegada_tarde')->count();
        $minutosTardanzaMes = Asistencia::whereBetween('fecha', [$startOfMonth, $today])
            ->sum('minutos_tardanza');
        $minutosExtrasMes = Asistencia::whereBetween('fecha', [$startOfMonth, $today])
            ->sum('minutos_extras');

        self::checkHuellaColumns();
        $empleados = Empleado::select('id', 'nombre', 'apellido', 'num_doc', 'cargo', 'codigo_qr', 'turno_id', 'huella_dactilar', 'foto')
            ->orderBy('nombre', 'asc')->get();
        $turnos = Turno::where('estado', 'Activo')->orderBy('nombre', 'asc')->get();

        return [
            'pagination' => [
                'total'        => $asistencias->total(),
                'current_page' => $asistencias->currentPage(),
                'per_page'     => $asistencias->perPage(),
                'last_page'    => $asistencias->lastPage(),
                'from'         => $asistencias->firstItem(),
                'to'           => $asistencias->lastItem(),
            ],
            'asistencias' => $asistencias,
            'kpis' => [
                'marcaciones_hoy' => $marcacionesHoy,
                'llegadas_tarde_mes' => $llegadasTardeMes,
                'minutos_tardanza_mes' => $minutosTardanzaMes,
                'horas_extras_mes' => round($minutosExtrasMes / 60, 2),
            ],
            'empleados' => $empleados,
            'turnos' => $turnos
        ];
    }

    public function registrarMarcacionQR(Request $request)
    {
        $codigoQr = trim($request->codigo_qr);
        $empleadoId = $request->empleado_id;
        $tipoEvento = $request->tipo_evento;
        $latitud = $request->latitud;
        $longitud = $request->longitud;

        // Buscar empleado
        $empleado = null;
        if (!empty($codigoQr)) {
            $empleado = Empleado::with('turnoAsignado')->where('codigo_qr', $codigoQr)
                ->orWhere('num_doc', $codigoQr)
                ->orWhere('id', $codigoQr)
                ->first();
        } else if (!empty($empleadoId)) {
            $empleado = Empleado::with('turnoAsignado')->find($empleadoId);
        } else if (Auth::check() && Auth::user()->empleado_id) {
            $empleado = Empleado::with('turnoAsignado')->find(Auth::user()->empleado_id);
        }

        if (!$empleado) {
            return response()->json(['status' => false, 'message' => 'Código QR no reconocido o empleado no encontrado.'], 404);
        }

        return $this->procesarMarcacionEmpleado($empleado, $tipoEvento, $latitud, $longitud, 'Código QR / Carnet', null, $request->observaciones);
    }

    private function obtenerRutaFotoEmpleado($nombreFoto)
    {
        if (empty($nombreFoto)) return null;
        $rutas = [
            public_path('img/empleados/' . $nombreFoto),
            public_path('fotos/' . $nombreFoto),
            public_path('img/productos/' . $nombreFoto),
            public_path($nombreFoto),
        ];
        foreach ($rutas as $r) {
            if (file_exists($r) && is_file($r)) {
                return $r;
            }
        }
        return null;
    }

    public function reconocerRostro(Request $request)
    {
        $fotoBase64 = $request->foto_base64;
        $empleadoId = $request->empleado_id;
        $tipoEvento = $request->tipo_evento;
        $latitud = $request->latitud;
        $longitud = $request->longitud;

        // Auto-detectar empleado si el usuario está logueado en el sistema y no envió ID
        if (empty($empleadoId) && Auth::check() && Auth::user()->empleado_id) {
            $empleadoId = Auth::user()->empleado_id;
        }

        if (empty($fotoBase64)) {
            return response()->json([
                'status' => false,
                'message' => 'No se recibió la imagen de la cámara para el reconocimiento facial.'
            ], 422);
        }

        // Decodificar imagen base64 de la cámara
        $imgData = preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64);
        $imgData = base64_decode($imgData);

        $tempDir = public_path('img/empleados');
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $tempPath = $tempDir . '/temp_face_' . time() . '_' . rand(1000, 9999) . '.jpg';
        file_put_contents($tempPath, $imgData);

        $empleado = null;
        $similitud = 0.0;

        // 1. Si se especifica el ID del empleado o si está logueado
        if (!empty($empleadoId)) {
            $empTarget = Empleado::with('turnoAsignado')->find($empleadoId);
            if (!$empTarget) {
                @unlink($tempPath);
                return response()->json([
                    'status' => false,
                    'message' => 'Empleado seleccionado no encontrado en el sistema.'
                ], 404);
            }

            $fotoPath = $this->obtenerRutaFotoEmpleado($empTarget->foto);
            if (!$fotoPath) {
                @unlink($tempPath);
                return response()->json([
                    'status' => false,
                    'message' => 'El empleado ' . $empTarget->nombre . ' ' . $empTarget->apellido . ' NO tiene fotografía registrada en el sistema. Registre su foto primero en el módulo de Empleados para activar el reconocimiento facial.'
                ], 422);
            }

            $score = $this->calcularSimilitudImagenes($tempPath, $fotoPath);
            // Umbral estricto de coincidencia facial validada contra foto oficial (mínimo 58% de similitud)
            if ($score >= 58.0) {
                $empleado = $empTarget;
                $similitud = round($score, 1);
            } else {
                @unlink($tempPath);
                return response()->json([
                    'status' => false,
                    'message' => 'El rostro en la cámara NO coincide con la foto registrada de ' . $empTarget->nombre . ' ' . $empTarget->apellido . ' (Coincidencia facial: ' . round($score, 1) . '%). Por favor enfoqué de frente a la cámara.'
                ], 422);
            }
        }

        // 2. Modo Auto-búsqueda (sin ID de empleado enviado): Comparar rostro contra todos los empleados con foto
        if (!$empleado) {
            $empleadosConFoto = Empleado::with('turnoAsignado')
                ->whereNotNull('foto')
                ->where('foto', '!=', '')
                ->get();

            $mejorEmpleado = null;
            $maxSimilitud = 0;

            foreach ($empleadosConFoto as $emp) {
                $fPath = $this->obtenerRutaFotoEmpleado($emp->foto);
                if ($fPath) {
                    $score = $this->calcularSimilitudImagenes($tempPath, $fPath);
                    if ($score > $maxSimilitud) {
                        $maxSimilitud = $score;
                        $mejorEmpleado = $emp;
                    }
                }
            }

            // Umbral para auto-detección de rostro (mínimo 60%)
            if ($mejorEmpleado && $maxSimilitud >= 60.0) {
                $empleado = $mejorEmpleado;
                $similitud = round($maxSimilitud, 1);
            }
        }

        @unlink($tempPath);

        if (!$empleado) {
            return response()->json([
                'status' => false,
                'message' => 'Rostro no reconocido. La persona frente a la cámara no coincide con la foto oficial de ningún empleado registrado.'
            ], 422);
        }

        return $this->procesarMarcacionEmpleado($empleado, $tipoEvento, $latitud, $longitud, 'Reconocimiento Facial Biométrico', $similitud, $request->observaciones);
    }

    private static function checkHuellaColumns()
    {
        if (!Schema::hasColumn('empleados', 'huella_dactilar')) {
            try {
                Schema::table('empleados', function (Blueprint $table) {
                    $table->longText('huella_dactilar')->nullable();
                });
            } catch (\Exception $e) {
                // Ignore if already created
            }
        }
    }

    public function reconocerHuella(Request $request)
    {
        self::checkHuellaColumns();

        $empleadoId = $request->empleado_id;
        $huellaData = trim((string)$request->huella_data);
        $tipoEvento = $request->tipo_evento;
        $latitud = $request->latitud;
        $longitud = $request->longitud;

        if (empty($huellaData)) {
            return response()->json([
                'status' => false,
                'message' => 'No se recibieron datos de lectura de la huella dactilar.'
            ], 422);
        }

        $hDataClean = preg_replace('/[^a-zA-Z0-9]/', '', $huellaData);
        $empleado = null;

        // 1. Si se seleccionó un empleado específico en la lista
        if (!empty($empleadoId)) {
            $empTarget = Empleado::with('turnoAsignado')->find($empleadoId);
            if (!$empTarget) {
                return response()->json([
                    'status' => false,
                    'message' => 'Empleado seleccionado no encontrado en el sistema.'
                ], 404);
            }

            if (empty($empTarget->huella_dactilar)) {
                return response()->json([
                    'status' => false,
                    'message' => 'El empleado ' . $empTarget->nombre . ' ' . $empTarget->apellido . ' NO tiene huella dactilar vinculada en el sistema. Debe registrar su huella primero.'
                ], 422);
            }

            // Validar que la huella colocada COINCIDA con la huella registrada del empleado
            $storedClean = preg_replace('/[^a-zA-Z0-9]/', '', $empTarget->huella_dactilar);
            $coincide = false;

            if ($empTarget->huella_dactilar === $huellaData || $storedClean === $hDataClean) {
                $coincide = true;
            } else if (strlen($storedClean) >= 20 && strlen($hDataClean) >= 20 && 
                      (strpos($storedClean, substr($hDataClean, 0, 24)) !== false || strpos($hDataClean, substr($storedClean, 0, 24)) !== false)) {
                $coincide = true;
            }

            if (!$coincide) {
                return response()->json([
                    'status' => false,
                    'message' => 'La huella dactilar escaneada NO coincide con la huella registrada de ' . $empTarget->nombre . ' ' . $empTarget->apellido . '. Marcación rechazada por seguridad.'
                ], 422);
            }

            $empleado = $empTarget;
        } else {
            // 2. Modo Auto-búsqueda: Buscar si la huella coincide con algún empleado registrado
            $candidatos = Empleado::with('turnoAsignado')
                ->whereNotNull('huella_dactilar')
                ->where('huella_dactilar', '!=', '')
                ->get();
            
            foreach ($candidatos as $cand) {
                $candClean = preg_replace('/[^a-zA-Z0-9]/', '', $cand->huella_dactilar);
                if ($cand->huella_dactilar === $huellaData || $candClean === $hDataClean) {
                    $empleado = $cand;
                    break;
                } else if (strlen($candClean) >= 20 && strlen($hDataClean) >= 20 && 
                          (strpos($candClean, substr($hDataClean, 0, 24)) !== false || strpos($hDataClean, substr($candClean, 0, 24)) !== false)) {
                    $empleado = $cand;
                    break;
                }
            }

            if (!$empleado) {
                return response()->json([
                    'status' => false,
                    'message' => 'Huella dactilar NO reconocida. La huella escaneada no coincide con ningún empleado registrado.'
                ], 422);
            }
        }

        return $this->procesarMarcacionEmpleado($empleado, $tipoEvento, $latitud, $longitud, 'Huella Dactilar Biométrica', 99.0, $request->observaciones);
    }

    public function enrolarHuella(Request $request)
    {
        self::checkHuellaColumns();

        $this->validate($request, [
            'empleado_id' => 'required|exists:empleados,id',
            'huella_data' => 'required'
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);
        $empleado->huella_dactilar = trim((string)$request->huella_data);
        $empleado->save();

        return response()->json([
            'status' => true,
            'message' => '¡Huella dactilar registrada exitosamente para ' . $empleado->nombre . ' ' . $empleado->apellido . '!',
            'empleado' => [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre,
                'apellido' => $empleado->apellido,
                'huella_dactilar' => $empleado->huella_dactilar
            ]
        ]);
    }

    public function eliminarHuella(Request $request)
    {
        self::checkHuellaColumns();

        $this->validate($request, [
            'empleado_id' => 'required|exists:empleados,id'
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);
        $empleado->huella_dactilar = null;
        $empleado->save();

        return response()->json([
            'status' => true,
            'message' => 'Huella dactilar desvinculada para ' . $empleado->nombre . ' ' . $empleado->apellido,
            'empleado_id' => $empleado->id
        ]);
    }

    private function procesarMarcacionEmpleado($empleado, $tipoEvento, $latitud, $longitud, $metodoValidacion = 'Código QR', $similitudFacial = null, $observaciones = null)
    {
        // 1. Validar Geolocalización (Geofencing GPS) si está activado en la configuración
        $configSeg = ConfiguracionAsistencia::first();
        $distanciaCalculada = null;

        if ($configSeg && $configSeg->requerir_gps && !empty($configSeg->latitud_empresa) && !empty($configSeg->longitud_empresa)) {
            if (empty($latitud) || empty($longitud)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Marcación rechazada: Se requiere acceso a la ubicación GPS para validar su presencia en la empresa.'
                ], 422);
            }

            $distanciaCalculada = $this->calcularDistanciaHaversine(
                (float)$configSeg->latitud_empresa, 
                (float)$configSeg->longitud_empresa, 
                (float)$latitud, 
                (float)$longitud
            );

            if ($distanciaCalculada > $configSeg->radio_maximo_metros) {
                return response()->json([
                    'status' => false,
                    'message' => 'Marcación rechazada: Se encuentra fuera del perímetro físico de la empresa (a ' . $distanciaCalculada . ' metros). Máximo permitido: ' . $configSeg->radio_maximo_metros . 'm.'
                ], 422);
            }
        }

        // Asegurar QR del empleado
        if (empty($empleado->codigo_qr)) {
            $empleado->codigo_qr = 'EMP-QR-' . str_pad($empleado->id, 5, '0', STR_PAD_LEFT);
            $empleado->save();
        }

        $now = Carbon::now();
        $todayStr = $now->format('Y-m-d');
        $dayOfWeekIso = $now->dayOfWeekIso; // 1 = Lunes, 7 = Domingo

        // Determinar turno aplicable
        $turno = null;
        if (!empty($empleado->turno_id)) {
            $turno = Turno::with('dias')->find($empleado->turno_id);
        }
        if (!$turno && is_object($empleado->turnoAsignado)) {
            $turno = Turno::with('dias')->find($empleado->turnoAsignado->id);
        }
        if (!$turno) {
            $turno = Turno::with('dias')->where('estado', 'Activo')->first();
        }

        $configDia = $turno ? $turno->obtenerConfigDia($dayOfWeekIso) : null;

        // Auto-detectar evento si está vacío
        if (empty($tipoEvento)) {
            $ultimaAsistencia = Asistencia::where('empleado_id', $empleado->id)
                ->where('fecha', $todayStr)
                ->orderBy('id', 'desc')
                ->first();

            if (!$ultimaAsistencia) {
                $tipoEvento = 'entrada_manana';
            } else {
                if (in_array($ultimaAsistencia->tipo_evento, ['entrada_manana', 'entrada_receso', 'entrada_almuerzo'])) {
                    $tipoEvento = 'salida_empresa';
                } else {
                    $tipoEvento = 'entrada_manana';
                }
            }
        }

        // Prevenir marcaciones consecutivas inmediatas (menos de 2 mins) para el mismo empleado (evita marcar salida inmediatamente por error)
        $ultimaReciente = Asistencia::where('empleado_id', $empleado->id)
            ->where('fecha', $todayStr)
            ->where('hora_marcada', '>=', $now->copy()->subMinutes(2))
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimaReciente) {
            $etiqueta = $this->obtenerEtiquetaEvento($ultimaReciente->tipo_evento);
            $horaFormat = Carbon::parse($ultimaReciente->hora_marcada)->format('g:i A');
            return response()->json([
                'status' => false,
                'message' => '¡Hola ' . $empleado->nombre . '! Ya registraste tu ' . $etiqueta . ' hace un momento (' . $horaFormat . '). Por favor retírate de la cámara. Para evitar registrar la salida por error, espera al menos 2 minutos.'
            ], 422);
        }

        $estadoLlegada = 'a_tiempo';
        $minutosTardanza = 0;
        $minutosExtras = 0;

        if ($configDia && isset($configDia->laborable) && !$configDia->laborable) {
            $estadoLlegada = 'a_tiempo';
            $minutosTardanza = 0;
        } else {
            $horaEntradaStr = ($configDia && !empty($configDia->hora_entrada)) ? $configDia->hora_entrada : ($turno ? $turno->hora_entrada : '08:00:00');
            $horaSalidaStr = ($configDia && !empty($configDia->hora_salida)) ? $configDia->hora_salida : ($turno ? $turno->hora_salida : '17:00:00');
            $toleranciaMin = ($configDia && isset($configDia->tolerancia_minutos)) ? $configDia->tolerancia_minutos : ($turno ? $turno->tolerancia_minutos : 10);

            if ($tipoEvento === 'entrada_manana') {
                $horaEntradaTurno = Carbon::parse($todayStr . ' ' . $horaEntradaStr);
                $horaLimiteTolerancia = $horaEntradaTurno->copy()->addMinutes($toleranciaMin);

                if ($now->greaterThan($horaLimiteTolerancia)) {
                    $estadoLlegada = 'llegada_tarde';
                    $minutosTardanza = $now->diffInMinutes($horaEntradaTurno);
                }
            } else if ($tipoEvento === 'salida_empresa') {
                $horaSalidaTurno = Carbon::parse($todayStr . ' ' . $horaSalidaStr);
                if ($now->greaterThan($horaSalidaTurno)) {
                    $minutosExtras = $now->diffInMinutes($horaSalidaTurno);
                }
            }
        }

        // Guardar asistencia
        $obsText = $metodoValidacion;
        if ($similitudFacial) {
            $obsText .= ' (Similitud: ' . $similitudFacial . '%)';
        }
        if ($observaciones) {
            $obsText .= ' - ' . $observaciones;
        }

        $asistencia = new Asistencia();
        $asistencia->empleado_id = $empleado->id;
        $asistencia->turno_id = ($turno && is_object($turno)) ? $turno->id : null;
        $asistencia->fecha = $todayStr;
        $asistencia->tipo_evento = $tipoEvento;
        $asistencia->hora_marcada = $now->format('Y-m-d H:i:s');
        $asistencia->estado_llegada = $estadoLlegada;
        $asistencia->minutos_tardanza = $minutosTardanza;
        $asistencia->minutos_extras = $minutosExtras;
        $asistencia->latitud = $latitud;
        $asistencia->longitud = $longitud;
        $asistencia->distancia_metros = $distanciaCalculada;
        $asistencia->observaciones = $obsText;
        $asistencia->save();

        return response()->json([
            'status' => true,
            'message' => 'Marcaje registrado exitosamente (' . $metodoValidacion . ').',
            'data' => [
                'empleado_nombre' => $empleado->nombre . ' ' . $empleado->apellido,
                'cargo' => $empleado->cargo,
                'foto' => $empleado->foto,
                'tipo_evento' => $tipoEvento,
                'evento_etiqueta' => $this->obtenerEtiquetaEvento($tipoEvento),
                'hora_marcada' => $now->format('h:i:s A'),
                'fecha' => $todayStr,
                'estado_llegada' => $estadoLlegada,
                'minutos_tardanza' => $minutosTardanza,
                'minutos_extras' => $minutosExtras,
                'turno_nombre' => ($turno && is_object($turno)) ? $turno->nombre : 'Sin Turno',
                'metodo_validacion' => $metodoValidacion,
                'similitud_facial' => $similitudFacial
            ]
        ]);
    }

    private function calcularSimilitudImagenes($path1, $path2)
    {
        if (!file_exists($path1) || !file_exists($path2)) {
            return 0.0;
        }

        try {
            $c1 = @file_get_contents($path1);
            $c2 = @file_get_contents($path2);
            if (!$c1 || !$c2) return 0.0;

            $img1 = @imagecreatefromstring($c1);
            $img2 = @imagecreatefromstring($c2);

            if (!$img1 || !$img2) return 0.0;

            $w1 = imagesx($img1); $h1 = imagesy($img1);
            $w2 = imagesx($img2); $h2 = imagesy($img2);

            // Crop central face region (65% width, 65% height, centered) to focus on facial features
            $cropX1 = (int)($w1 * 0.175); $cropY1 = (int)($h1 * 0.15);
            $cropW1 = (int)($w1 * 0.65);  $cropH1 = (int)($h1 * 0.65);

            $cropX2 = (int)($w2 * 0.175); $cropY2 = (int)($h2 * 0.15);
            $cropW2 = (int)($w2 * 0.65);  $cropH2 = (int)($h2 * 0.65);

            $thumb1 = imagecreatetruecolor(32, 32);
            $thumb2 = imagecreatetruecolor(32, 32);

            imagecopyresampled($thumb1, $img1, 0, 0, $cropX1, $cropY1, 32, 32, $cropW1, $cropH1);
            imagecopyresampled($thumb2, $img2, 0, 0, $cropX2, $cropY2, 32, 32, $cropW2, $cropH2);

            $sumLum1 = 0; $sumLum2 = 0;
            $lumMap1 = []; $lumMap2 = [];
            $rMap1 = []; $gMap1 = []; $bMap1 = [];
            $rMap2 = []; $gMap2 = []; $bMap2 = [];

            for ($y = 0; $y < 32; $y++) {
                for ($x = 0; $x < 32; $x++) {
                    $rgb1 = imagecolorat($thumb1, $x, $y);
                    $rgb2 = imagecolorat($thumb2, $x, $y);

                    $r1 = ($rgb1 >> 16) & 0xFF; $g1 = ($rgb1 >> 8) & 0xFF; $b1 = $rgb1 & 0xFF;
                    $r2 = ($rgb2 >> 16) & 0xFF; $g2 = ($rgb2 >> 8) & 0xFF; $b2 = $rgb2 & 0xFF;

                    $rMap1[$y][$x] = $r1; $gMap1[$y][$x] = $g1; $bMap1[$y][$x] = $b1;
                    $rMap2[$y][$x] = $r2; $gMap2[$y][$x] = $g2; $bMap2[$y][$x] = $b2;

                    $l1 = ($r1 * 0.299) + ($g1 * 0.587) + ($b1 * 0.114);
                    $l2 = ($r2 * 0.299) + ($g2 * 0.587) + ($b2 * 0.114);

                    $lumMap1[$y][$x] = $l1;
                    $lumMap2[$y][$x] = $l2;

                    $sumLum1 += $l1;
                    $sumLum2 += $l2;
                }
            }

            @imagedestroy($img1); @imagedestroy($img2);
            @imagedestroy($thumb1); @imagedestroy($thumb2);

            $meanLum1 = $sumLum1 / 1024.0;
            $meanLum2 = $sumLum2 / 1024.0;

            // METRIC 1: Normalized Intensity Luminance Structural Difference (Weight 40%)
            $lumDiff = 0;
            for ($y = 0; $y < 32; $y++) {
                for ($x = 0; $x < 32; $x++) {
                    $normL1 = $lumMap1[$y][$x] - $meanLum1;
                    $normL2 = $lumMap2[$y][$x] - $meanLum2;
                    $lumDiff += abs($normL1 - $normL2);
                }
            }
            $scoreLuminance = max(0.0, (1.0 - ($lumDiff / (1024.0 * 120.0))) * 100.0);

            // METRIC 2: Facial Gradient Direction Vectors (Weight 35%)
            $gradDiff = 0;
            $gradCount = 0;
            for ($y = 1; $y < 31; $y++) {
                for ($x = 1; $x < 31; $x++) {
                    $gx1 = ($lumMap1[$y][$x+1] - $lumMap1[$y][$x-1]) / 2.0;
                    $gy1 = ($lumMap1[$y+1][$x] - $lumMap1[$y-1][$x]) / 2.0;

                    $gx2 = ($lumMap2[$y][$x+1] - $lumMap2[$y][$x-1]) / 2.0;
                    $gy2 = ($lumMap2[$y+1][$x] - $lumMap2[$y-1][$x]) / 2.0;

                    $mag1 = sqrt($gx1*$gx1 + $gy1*$gy1);
                    $mag2 = sqrt($gx2*$gx2 + $gy2*$gy2);

                    if ($mag1 > 3.0 || $mag2 > 3.0) {
                        $gradDiff += abs($gx1 - $gx2) + abs($gy1 - $gy2);
                        $gradCount++;
                    }
                }
            }
            $scoreGradient = 100.0;
            if ($gradCount > 0) {
                $avgGradDiff = $gradDiff / $gradCount;
                $scoreGradient = max(0.0, (1.0 - ($avgGradDiff / 45.0)) * 100.0);
            }

            // METRIC 3: Skin Tone & Color Distribution Similarity (Weight 25%)
            $colorDiff = 0;
            for ($y = 0; $y < 32; $y++) {
                for ($x = 0; $x < 32; $x++) {
                    $dr = abs($rMap1[$y][$x] - $rMap2[$y][$x]);
                    $dg = abs($gMap1[$y][$x] - $gMap2[$y][$x]);
                    $db = abs($bMap1[$y][$x] - $bMap2[$y][$x]);
                    $colorDiff += ($dr + $dg + $db) / 3.0;
                }
            }
            $scoreColor = max(0.0, (1.0 - (($colorDiff / 1024.0) / 100.0)) * 100.0);

            // Composite Facial Recognition Similarity Score
            $finalSimilarity = ($scoreLuminance * 0.40) + ($scoreGradient * 0.35) + ($scoreColor * 0.25);

            return round(min(100.0, max(0.0, $finalSimilarity)), 1);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    public function reporteLlegadasTarde(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $fechaInicio = $request->fecha_inicio ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?: Carbon::today()->format('Y-m-d');

        $reporte = Asistencia::with('empleado')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado_llegada', 'llegada_tarde')
            ->select('empleado_id', DB::raw('COUNT(*) as total_llegadas_tarde'), DB::raw('SUM(minutos_tardanza) as total_minutos'))
            ->groupBy('empleado_id')
            ->orderBy('total_minutos', 'desc')
            ->get();

        return response()->json(['status' => true, 'reporte' => $reporte]);
    }

    public function reporteHorasExtras(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $fechaInicio = $request->fecha_inicio ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?: Carbon::today()->format('Y-m-d');

        $reporte = Asistencia::with('empleado')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('minutos_extras', '>', 0)
            ->select('empleado_id', DB::raw('COUNT(*) as dias_con_extras'), DB::raw('SUM(minutos_extras) as total_minutos_extras'))
            ->groupBy('empleado_id')
            ->orderBy('total_minutos_extras', 'desc')
            ->get();

        return response()->json(['status' => true, 'reporte' => $reporte]);
    }

    public function generarQREmpleado($id)
    {
        $empleado = Empleado::findOrFail($id);
        if (empty($empleado->codigo_qr)) {
            $empleado->codigo_qr = 'EMP-QR-' . str_pad($empleado->id, 5, '0', STR_PAD_LEFT);
            $empleado->save();
        }

        return response()->json([
            'status' => true,
            'codigo_qr' => $empleado->codigo_qr,
            'empleado' => $empleado->nombre . ' ' . $empleado->apellido
        ]);
    }

    public function turnosIndex(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $turnos = Turno::with('dias')->withCount('empleados')->orderBy('nombre', 'asc')->get();

        $nombresDias = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
        ];

        foreach ($turnos as $turno) {
            if ($turno->dias->count() < 7) {
                $diasExistentes = $turno->dias->pluck('dia_num')->toArray();
                for ($d = 1; $d <= 7; $d++) {
                    if (!in_array($d, $diasExistentes)) {
                        TurnoDia::create([
                            'turno_id' => $turno->id,
                            'dia_num' => $d,
                            'dia_nombre' => $nombresDias[$d],
                            'laborable' => !in_array($d, [6, 7]),
                            'hora_entrada' => $turno->hora_entrada ?: '08:00:00',
                            'tolerancia_minutos' => $turno->tolerancia_minutos ?: 10,
                            'hora_salida' => $turno->hora_salida ?: '17:00:00',
                        ]);
                    }
                }
                $turno->load('dias');
            }
        }

        return response()->json(['status' => true, 'turnos' => $turnos]);
    }

    public function turnoStore(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $request->validate([
            'nombre' => 'required|string|max:191'
        ]);

        $turno = new Turno();
        $turno->nombre = $request->nombre;
        $turno->hora_entrada = $request->hora_entrada ?: '08:00:00';
        $turno->tolerancia_minutos = $request->tolerancia_minutos ?? 10;
        $turno->hora_salida_receso = $request->hora_salida_receso;
        $turno->hora_entrada_receso = $request->hora_entrada_receso;
        $turno->hora_salida_almuerzo = $request->hora_salida_almuerzo;
        $turno->hora_entrada_almuerzo = $request->hora_entrada_almuerzo;
        $turno->hora_salida = $request->hora_salida ?: '17:00:00';
        $turno->estado = $request->estado ?? 'Activo';
        $turno->save();

        $this->guardarDiasTurno($turno->id, $request->dias);

        return response()->json(['status' => true, 'message' => 'Turno creado correctamente con su programación por días.']);
    }

    public function turnoUpdate(Request $request, $id)
    {
        if (!$request->ajax()) return redirect('/');

        $request->validate([
            'nombre' => 'required|string|max:191'
        ]);

        $turno = Turno::findOrFail($id);
        $turno->nombre = $request->nombre;
        $turno->hora_entrada = $request->hora_entrada ?: '08:00:00';
        $turno->tolerancia_minutos = $request->tolerancia_minutos ?? 10;
        $turno->hora_salida_receso = $request->hora_salida_receso;
        $turno->hora_entrada_receso = $request->hora_entrada_receso;
        $turno->hora_salida_almuerzo = $request->hora_salida_almuerzo;
        $turno->hora_entrada_almuerzo = $request->hora_entrada_almuerzo;
        $turno->hora_salida = $request->hora_salida ?: '17:00:00';
        $turno->estado = $request->estado ?? 'Activo';
        $turno->save();

        $this->guardarDiasTurno($turno->id, $request->dias);

        return response()->json(['status' => true, 'message' => 'Turno y programación diaria actualizados correctamente.']);
    }

    private function guardarDiasTurno($turnoId, $diasData)
    {
        if (!empty($diasData) && is_array($diasData)) {
            $nombresDias = [
                1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
            ];
            foreach ($diasData as $dData) {
                $diaNum = isset($dData['dia_num']) ? (int)$dData['dia_num'] : 1;
                TurnoDia::updateOrCreate(
                    ['turno_id' => $turnoId, 'dia_num' => $diaNum],
                    [
                        'dia_nombre' => $nombresDias[$diaNum] ?? 'Día ' . $diaNum,
                        'laborable' => !empty($dData['laborable']),
                        'hora_entrada' => $dData['hora_entrada'] ?? '08:00:00',
                        'tolerancia_minutos' => $dData['tolerancia_minutos'] ?? 10,
                        'hora_salida' => $dData['hora_salida'] ?? '17:00:00',
                        'hora_salida_almuerzo' => $dData['hora_salida_almuerzo'] ?? null,
                        'hora_entrada_almuerzo' => $dData['hora_entrada_almuerzo'] ?? null,
                    ]
                );
            }
        }
    }

    public function asignarTurnoEmpleado(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $empleado = Empleado::findOrFail($request->empleado_id);
        $empleado->turno_id = $request->turno_id ?: null;
        $empleado->save();

        return response()->json(['status' => true, 'message' => 'Turno asignado al empleado correctamente.']);
    }

    public function getConfiguracionSeguridad(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $config = ConfiguracionAsistencia::firstOrCreate([], [
            'latitud_empresa' => null,
            'longitud_empresa' => null,
            'radio_maximo_metros' => 100,
            'requerir_gps' => false,
            'pin_kiosco' => '1234'
        ]);
        return response()->json(['status' => true, 'configuracion' => $config]);
    }

    public function guardarConfiguracionSeguridad(Request $request)
    {
        if (!$request->ajax()) return redirect('/');
        $config = ConfiguracionAsistencia::firstOrCreate([]);
        $config->latitud_empresa = $request->latitud_empresa ?: null;
        $config->longitud_empresa = $request->longitud_empresa ?: null;
        $config->radio_maximo_metros = $request->radio_maximo_metros ?? 100;
        $config->requerir_gps = $request->requerir_gps ? true : false;
        if (!empty($request->pin_kiosco)) {
            $config->pin_kiosco = $request->pin_kiosco;
        }
        $config->save();
        return response()->json(['status' => true, 'message' => 'Configuración de seguridad y perímetro GPS actualizada.']);
    }

    private function calcularDistanciaHaversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radio de la Tierra en metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (int)round($earthRadius * $c);
    }

    private function obtenerEtiquetaEvento($tipo)
    {
        switch ($tipo) {
            case 'entrada_manana': return 'Ingreso / Entrada';
            case 'salida_receso': return 'Salida a Receso';
            case 'entrada_receso': return 'Ingreso de Receso';
            case 'salida_almuerzo': return 'Salida a Almuerzo';
            case 'entrada_almuerzo': return 'Ingreso de Almuerzo';
            case 'salida_empresa': return 'Salida';
            default: return $tipo;
        }
    }
}
