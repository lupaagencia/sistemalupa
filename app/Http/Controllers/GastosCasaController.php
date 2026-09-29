<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use App\GastosCasa\GhPersona;
use App\GastosCasa\GhCategoria;
use App\GastosCasa\GhGasto;
use App\GastosCasa\GhIngreso;
use App\GastosCasa\GhDistribucion;
use App\GastosCasa\GhReserva;

class GastosCasaController extends Controller
{
    private function ensureTablesExist()
    {
        try {
            if (!Schema::hasTable('gh_personas')) {
                $sqlPath = base_path('gastos_casa_schema.sql');
                if (File::exists($sqlPath)) {
                    $sql = File::get($sqlPath);
                    DB::unprepared($sql);
                }
            }

            if (Schema::hasTable('gh_personas') && !Schema::hasColumn('gh_personas', 'user_id')) {
                Schema::table('gh_personas', function ($table) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('nombre');
                });
            }

            if (Schema::hasTable('gh_ingresos') && !Schema::hasColumn('gh_ingresos', 'tipo_ingreso')) {
                Schema::table('gh_ingresos', function ($table) {
                    $table->string('tipo_ingreso', 50)->default('arriendo')->after('id');
                });
            }

            if (Schema::hasTable('gh_gastos') && !Schema::hasColumn('gh_gastos', 'origen_pago')) {
                Schema::table('gh_gastos', function ($table) {
                    $table->string('origen_pago', 50)->default('persona')->after('categoria_id');
                });
                try {
                    DB::statement('ALTER TABLE gh_gastos MODIFY persona_id bigint(20) UNSIGNED NULL');
                } catch (\Exception $eEx) {}
            }
        } catch (\Exception $e) {
            // Silently swallow or log error if SQL execution fails
            \Log::error('Error checking/creating gh_ tables: ' . $e->getMessage());
        }
    }

    private function getCurrentUserPersonaInfo()
    {
        $currentUser = \Auth::user();
        $isAdmin = false;
        $userPersonaId = null;

        if ($currentUser) {
            if ($currentUser->idrol == 1 || $currentUser->idrol == '1' || strtolower($currentUser->usuario) === 'admin') {
                $isAdmin = true;
            }

            $linkedPersona = GhPersona::where('user_id', $currentUser->id)
                ->orWhere(function ($query) use ($currentUser) {
                    $query->whereNull('user_id')
                          ->whereRaw('LOWER(nombre) = ?', [strtolower($currentUser->usuario)]);
                })->first();

            if ($linkedPersona) {
                $userPersonaId = $linkedPersona->id;
                if (empty($linkedPersona->user_id)) {
                    try {
                        $linkedPersona->user_id = $currentUser->id;
                        $linkedPersona->save();
                    } catch (\Exception $e) {}
                }
            }
        }

        return [
            'user' => $currentUser,
            'is_admin' => $isAdmin,
            'persona_id' => $userPersonaId
        ];
    }

    public function index()
    {
        $this->ensureTablesExist();
        return view('gastos_casa.index');
    }

    public function getData()
    {
        $this->ensureTablesExist();

        try {
            $userPersonaInfo = $this->getCurrentUserPersonaInfo();
            $systemUsers = DB::table('users')->select('id', 'usuario', 'idrol')->get();

            $personas = GhPersona::where('activo', true)->get();
            $categorias = GhCategoria::all();
            
            $gastos = GhGasto::with(['persona', 'categoria'])
                ->orderBy('fecha', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $ingresos = GhIngreso::with(['distribuciones.persona', 'distribuciones.gasto'])
                ->orderBy('fecha', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $reservas = GhReserva::orderBy('fecha', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            // Calculate KPI totals
            $totalGastos = $gastos->where('estado', '!=', 'anulado')->sum('valor');
            $totalPendiente = $gastos->where('estado', '!=', 'anulado')->sum('saldo_pendiente');
            
            $totalArriendos = $ingresos->filter(function ($ing) {
                return empty($ing->tipo_ingreso) || $ing->tipo_ingreso === 'arriendo';
            })->sum('valor_total');

            $totalDonaciones = $ingresos->filter(function ($ing) {
                return $ing->tipo_ingreso === 'donacion_aporte';
            })->sum('valor_total');

            $totalIngresosGenerales = $ingresos->sum('valor_total');

            $fondoPredial = $reservas->where('tipo_reserva', 'predial')->reduce(function ($acc, $item) {
                return $acc + ($item->tipo_movimiento === 'ingreso' ? $item->valor : -$item->valor);
            }, 0);

            $fondoArreglos = $reservas->where('tipo_reserva', 'arreglos')->reduce(function ($acc, $item) {
                return $acc + ($item->tipo_movimiento === 'ingreso' ? $item->valor : -$item->valor);
            }, 0);

            // Person Stats
            $personasStats = $personas->map(function ($p) use ($gastos) {
                $pGastos = $gastos->where('persona_id', $p->id)->where('estado', '!=', 'anulado');
                $totalAportado = $pGastos->sum('valor');
                $saldoPendiente = $pGastos->sum('saldo_pendiente');
                $totalReembolsado = $totalAportado - $saldoPendiente;

                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'user_id' => $p->user_id,
                    'porcentaje' => $p->porcentaje_participacion,
                    'total_aportado' => $totalAportado,
                    'total_reembolsado' => $totalReembolsado,
                    'saldo_pendiente' => $saldoPendiente
                ];
            });

            return response()->json([
                'success' => true,
                'personas' => $personasStats,
                'categorias' => $categorias,
                'gastos' => $gastos,
                'ingresos' => $ingresos,
                'reservas' => $reservas,
                'kpis' => [
                    'total_gastos' => $totalGastos,
                    'total_pendiente' => $totalPendiente,
                    'total_arriendos' => $totalArriendos,
                    'total_donaciones' => $totalDonaciones,
                    'total_ingresos_generales' => $totalIngresosGenerales,
                    'fondo_predial' => $fondoPredial,
                    'fondo_arreglos' => $fondoArreglos
                ],
                'user_info' => [
                    'logged_in' => \Auth::check(),
                    'username' => $userPersonaInfo['user'] ? $userPersonaInfo['user']->usuario : null,
                    'user_id' => $userPersonaInfo['user'] ? $userPersonaInfo['user']->id : null,
                    'persona_id' => $userPersonaInfo['persona_id'],
                    'is_admin' => $userPersonaInfo['is_admin']
                ],
                'system_users' => $systemUsers
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function storePersona(Request $request)
    {
        $this->ensureTablesExist();

        $userPersonaInfo = $this->getCurrentUserPersonaInfo();
        if (\Auth::check() && !$userPersonaInfo['is_admin']) {
            return response()->json(['success' => false, 'error' => 'Seguridad: Solo los administradores pueden registrar o editar integrantes.'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:191',
            'porcentaje_participacion' => 'nullable|numeric|min:0|max:100'
        ]);

        try {
            if ($request->filled('id')) {
                $persona = GhPersona::findOrFail($request->id);
                $persona->update([
                    'nombre' => $request->nombre,
                    'user_id' => $request->user_id ?: $persona->user_id,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'porcentaje_participacion' => $request->porcentaje_participacion ?? 33.33
                ]);
            } else {
                $persona = GhPersona::create([
                    'nombre' => $request->nombre,
                    'user_id' => $request->user_id ?: null,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'porcentaje_participacion' => $request->porcentaje_participacion ?? 33.33,
                    'activo' => true
                ]);
            }

            return response()->json(['success' => true, 'persona' => $persona]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deletePersona($id)
    {
        $this->ensureTablesExist();

        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['success' => false, 'error' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar integrantes.'], 403);
        }

        try {
            $persona = GhPersona::findOrFail($id);
            
            $gastosCount = GhGasto::where('persona_id', $id)->count();
            if ($gastosCount > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se puede eliminar a ' . $persona->nombre . ' porque tiene ' . $gastosCount . ' gasto(s) registrado(s) a su nombre.'
                ], 400);
            }

            $persona->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function storeGasto(Request $request)
    {
        $this->ensureTablesExist();

        $request->validate([
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
            'valor' => 'required|numeric|min:0',
        ]);

        try {
            $userPersonaInfo = $this->getCurrentUserPersonaInfo();
            $isEdit = $request->filled('id');
            $gasto = $isEdit ? GhGasto::findOrFail($request->id) : new GhGasto();

            // Authorization check
            if (\Auth::check() && !$userPersonaInfo['is_admin'] && $userPersonaInfo['persona_id']) {
                if ($isEdit) {
                    if ($gasto->persona_id && $gasto->persona_id != $userPersonaInfo['persona_id']) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Seguridad: Solo puedes modificar los gastos registrados por tu propio usuario.'
                        ], 403);
                    }
                } else {
                    if ($request->origen_pago === 'persona' || empty($request->origen_pago)) {
                        $request->merge(['persona_id' => $userPersonaInfo['persona_id']]);
                    }
                }
            }

            $uploadDir = public_path('uploads/gastos_casa');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $origenPago = $request->origen_pago ?: 'persona';
            $gasto->origen_pago = $origenPago;

            if ($origenPago === 'persona') {
                $gasto->persona_id = $request->persona_id;
            } else {
                $gasto->persona_id = $request->persona_id ?: null;
            }

            $gasto->categoria_id = $request->categoria_id ?: null;
            $gasto->fecha = $request->fecha;
            $gasto->descripcion = $request->descripcion;
            $gasto->valor = $request->valor;
            $gasto->notas = $request->notas;

            if ($origenPago === 'persona') {
                if (!$isEdit) {
                    $gasto->estado = 'pendiente';
                    $gasto->saldo_pendiente = $request->valor;
                } else {
                    $reembolsadoHastaAhora = $gasto->valor - $gasto->saldo_pendiente;
                    $gasto->saldo_pendiente = max(0, $request->valor - $reembolsadoHastaAhora);
                    if ($gasto->saldo_pendiente <= 0) {
                        $gasto->estado = 'reembolsado_total';
                    } else if ($reembolsadoHastaAhora > 0) {
                        $gasto->estado = 'reembolsado_parcial';
                    } else {
                        $gasto->estado = 'pendiente';
                    }
                }
            } else {
                // Direct house / fund payment (no debt or reimbursement generated to any person)
                $gasto->saldo_pendiente = 0;
                $gasto->estado = 'reembolsado_total';

                if (!$isEdit) {
                    if ($origenPago === 'fondo_arreglos') {
                        GhReserva::create([
                            'tipo_reserva' => 'arreglos',
                            'tipo_movimiento' => 'egreso',
                            'fecha' => $request->fecha,
                            'concepto' => 'Pago directo gasto: ' . $request->descripcion,
                            'valor' => $request->valor
                        ]);
                    } else if ($origenPago === 'fondo_predial') {
                        GhReserva::create([
                            'tipo_reserva' => 'predial',
                            'tipo_movimiento' => 'egreso',
                            'fecha' => $request->fecha,
                            'concepto' => 'Pago directo gasto: ' . $request->descripcion,
                            'valor' => $request->valor
                        ]);
                    }
                }
            }

            // Handle Soporte File Upload
            if ($request->hasFile('soporte')) {
                $file = $request->file('soporte');
                $filename = time() . '_soporte_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $gasto->soporte_path = 'uploads/gastos_casa/' . $filename;
                $gasto->soporte_nombre_orig = $file->getClientOriginalName();
            }

            // Handle Comprobante File Upload
            if ($request->hasFile('comprobante')) {
                $file = $request->file('comprobante');
                $filename = time() . '_comprobante_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $gasto->comprobante_path = 'uploads/gastos_casa/' . $filename;
                $gasto->comprobante_nombre_orig = $file->getClientOriginalName();
            }

            $gasto->save();

            return response()->json(['success' => true, 'gasto' => $gasto]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteGasto($id)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['success' => false, 'error' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar gastos.'], 403);
        }
        try {
            $gasto = GhGasto::findOrFail($id);
            $gasto->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function storeIngreso(Request $request)
    {
        $this->ensureTablesExist();

        $request->validate([
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
            'valor_total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $uploadDir = public_path('uploads/gastos_casa');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $ingreso = new GhIngreso();
            $ingreso->tipo_ingreso = $request->tipo_ingreso ?: 'arriendo';
            $ingreso->fecha = $request->fecha;
            $ingreso->periodo_mes = $request->periodo_mes;
            $ingreso->inquilino_nombre = $request->inquilino_nombre;
            $ingreso->descripcion = $request->descripcion;
            $ingreso->valor_total = $request->valor_total;
            $ingreso->notas = $request->notas;

            if ($request->hasFile('comprobante')) {
                $file = $request->file('comprobante');
                $filename = time() . '_ingreso_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $ingreso->comprobante_path = 'uploads/gastos_casa/' . $filename;
            }

            $ingreso->save();

            // Process Distribuciones
            $distribucionesInput = json_decode($request->distribuciones, true) ?? [];

            foreach ($distribucionesInput as $item) {
                $valor = floatval($item['valor'] ?? 0);
                if ($valor <= 0) continue;

                $dist = GhDistribucion::create([
                    'ingreso_id' => $ingreso->id,
                    'tipo_destino' => $item['tipo_destino'],
                    'persona_id' => $item['persona_id'] ?? null,
                    'gasto_id' => $item['gasto_id'] ?? null,
                    'valor' => $valor,
                    'observaciones' => $item['observaciones'] ?? null,
                    'fecha' => $request->fecha
                ]);

                if (!empty($item['gasto_id'])) {
                    $gasto = GhGasto::find($item['gasto_id']);
                    if ($gasto) {
                        $gasto->saldo_pendiente = max(0, $gasto->saldo_pendiente - $valor);
                        $gasto->estado = ($gasto->saldo_pendiente <= 0) ? 'reembolsado_total' : 'reembolsado_parcial';
                        $gasto->save();
                    }
                } else if (!empty($item['persona_id']) && $item['tipo_destino'] === 'reembolso_persona') {
                    $gastosPersona = GhGasto::where('persona_id', $item['persona_id'])
                        ->where('saldo_pendiente', '>', 0)
                        ->where('estado', '!=', 'anulado')
                        ->orderBy('fecha', 'asc')
                        ->get();

                    $rem = $valor;
                    foreach ($gastosPersona as $g) {
                        if ($rem <= 0) break;
                        $descuento = min($rem, $g->saldo_pendiente);
                        $g->saldo_pendiente -= $descuento;
                        $g->estado = ($g->saldo_pendiente <= 0) ? 'reembolsado_total' : 'reembolsado_parcial';
                        $g->save();
                        $rem -= $descuento;
                    }
                }

                if ($item['tipo_destino'] === 'reserva_predial') {
                    GhReserva::create([
                        'tipo_reserva' => 'predial',
                        'tipo_movimiento' => 'ingreso',
                        'distribucion_id' => $dist->id,
                        'fecha' => $request->fecha,
                        'concepto' => 'Aporte desde Arriendo (' . ($request->periodo_mes ?? $request->fecha) . ')',
                        'valor' => $valor
                    ]);
                } else if ($item['tipo_destino'] === 'reserva_arreglos') {
                    GhReserva::create([
                        'tipo_reserva' => 'arreglos',
                        'tipo_movimiento' => 'ingreso',
                        'distribucion_id' => $dist->id,
                        'fecha' => $request->fecha,
                        'concepto' => 'Aporte desde Arriendo (' . ($request->periodo_mes ?? $request->fecha) . ')',
                        'valor' => $valor
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'ingreso' => $ingreso]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function storeReservaMovimiento(Request $request)
    {
        $this->ensureTablesExist();

        $request->validate([
            'tipo_reserva' => 'required|in:predial,arreglos,otro',
            'tipo_movimiento' => 'required|in:ingreso,egreso',
            'fecha' => 'required|date',
            'concepto' => 'required|string',
            'valor' => 'required|numeric|min:0.01'
        ]);

        try {
            $uploadDir = public_path('uploads/gastos_casa');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $reserva = new GhReserva();
            $reserva->tipo_reserva = $request->tipo_reserva;
            $reserva->tipo_movimiento = $request->tipo_movimiento;
            $reserva->fecha = $request->fecha;
            $reserva->concepto = $request->concepto;
            $reserva->valor = $request->valor;

            if ($request->hasFile('soporte')) {
                $file = $request->file('soporte');
                $filename = time() . '_reserva_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $reserva->soporte_path = 'uploads/gastos_casa/' . $filename;
            }

            $reserva->save();

            return response()->json(['success' => true, 'reserva' => $reserva]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function normalizarTexto($str)
    {
        if (!$str) return '';
        $str = mb_strtolower(trim($str), 'UTF-8');
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ã' => 'a', 'õ' => 'o', 'ñ' => 'n'
        ];
        return strtr($str, $replacements);
    }

    public function deleteGastosMasivo(Request $request)
    {
        if (\Auth::check() && \Auth::user()->idrol !== 'Superadministrador') {
            return response()->json(['success' => false, 'error' => 'Acceso denegado: Solo el usuario Superadministrador tiene permisos para eliminar gastos.'], 403);
        }
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        try {
            $count = GhGasto::whereIn('id', $request->ids)->delete();
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function descargarPlantilla()
    {
        $csvHeader = "Fecha,Persona,Categoria,Descripcion,Valor,Notas\n";
        $csvSample1 = "2026-09-20,Julián,Arreglos y Remodelación,Compra de bultos de cemento y arena,150000,Factura #1024\n";
        $csvSample2 = "2026-09-21,Óscar,Servicios Públicos,Pago recibo de agua potable,85000,Pago PSE\n";
        $csvContent = $csvHeader . $csvSample1 . $csvSample2;

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="plantilla_importar_gastos.csv"');
    }

    public function importarGastos(Request $request)
    {
        $this->ensureTablesExist();

        $request->validate([
            'archivo' => 'required|file'
        ]);

        try {
            $file = $request->file('archivo');
            $extension = strtolower($file->getClientOriginalExtension());
            $rows = [];

            if (in_array($extension, ['xlsx', 'xls']) && class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
            } else {
                $handle = fopen($file->getRealPath(), 'r');
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    if (count($data) === 1 && strpos($data[0], ';') !== false) {
                        $data = explode(';', $data[0]);
                    }
                    $rows[] = $data;
                }
                fclose($handle);
            }

            if (empty($rows) || count($rows) <= 1) {
                return response()->json(['success' => false, 'error' => 'El archivo está vacío o solo contiene el encabezado.'], 400);
            }

            $personasMap = GhPersona::all()->keyBy(function ($item) {
                return $this->normalizarTexto($item->nombre);
            });

            $categoriasMap = GhCategoria::all()->keyBy(function ($item) {
                return $this->normalizarTexto($item->nombre);
            });

            $importedCount = 0;
            $skippedCount = 0;

            DB::beginTransaction();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row) || count($row) < 4) continue;

                $fechaRaw = trim($row[0] ?? '');
                $personaNombre = trim($row[1] ?? '');
                $categoriaNombre = trim($row[2] ?? '');
                $descripcion = trim($row[3] ?? '');
                $valorRaw = trim($row[4] ?? '0');
                $notas = trim($row[5] ?? '');

                if (empty($personaNombre) || empty($descripcion)) {
                    $skippedCount++;
                    continue;
                }

                $fecha = date('Y-m-d');
                if (!empty($fechaRaw)) {
                    $timestamp = strtotime(str_replace('/', '-', $fechaRaw));
                    if ($timestamp !== false) {
                        $fecha = date('Y-m-d', $timestamp);
                    }
                }

                $valor = floatval(preg_replace('/[^\d.]/', '', str_replace(',', '.', $valorRaw)));
                if ($valor <= 0) {
                    $skippedCount++;
                    continue;
                }

                $personaKey = $this->normalizarTexto($personaNombre);
                if (isset($personasMap[$personaKey])) {
                    $personaId = $personasMap[$personaKey]->id;
                } else {
                    $skippedCount++;
                    continue;
                }

                $categoriaId = null;
                if (!empty($categoriaNombre)) {
                    $catKey = $this->normalizarTexto($categoriaNombre);
                    if (isset($categoriasMap[$catKey])) {
                        $categoriaId = $categoriasMap[$catKey]->id;
                    }
                }

                GhGasto::create([
                    'persona_id' => $personaId,
                    'categoria_id' => $categoriaId,
                    'fecha' => $fecha,
                    'descripcion' => $descripcion,
                    'valor' => $valor,
                    'estado' => 'pendiente',
                    'saldo_pendiente' => $valor,
                    'notas' => $notas ?: 'Importación Masiva'
                ]);

                $importedCount++;
            }

            DB::commit();

            $message = "Se importaron exitosamente {$importedCount} gastos.";
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} fila(s) omitida(s) por no coincidir la persona con los integrantes existentes).";
            }

            return response()->json([
                'success' => true,
                'imported_count' => $importedCount,
                'skipped_count' => $skippedCount,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Error procesando archivo: ' . $e->getMessage()], 500);
        }
    }
}
