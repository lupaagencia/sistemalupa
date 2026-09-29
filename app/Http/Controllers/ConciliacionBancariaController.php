<?php

namespace App\Http\Controllers;

use App\ExtractoBancario;
use App\ComprobanteContable;
use App\AsientoDetalle;
use App\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConciliacionBancariaController extends Controller
{
    /**
     * List bank statement entries.
     */
    public function index(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $estado = $request->input('estado'); // 'Pendiente', 'Conciliado'
        $buscar = $request->input('buscar');
        $perPage = $request->input('per_page', 15);

        $query = ExtractoBancario::with(['comprobante.detalles.cuenta']);

        if (!empty($fecha_desde) && !empty($fecha_hasta)) {
            $query->whereBetween('fecha', [$fecha_desde, $fecha_hasta]);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('descripcion', 'like', '%' . $buscar . '%')
                  ->orWhere('referencia', 'like', '%' . $buscar . '%');
            });
        }

        $extractos = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'pagination' => [
                'total'        => $extractos->total(),
                'current_page' => $extractos->currentPage(),
                'per_page'     => $extractos->perPage(),
                'last_page'    => $extractos->lastPage(),
                'from'         => $extractos->firstItem(),
                'to'           => $extractos->lastItem(),
            ],
            'extractos' => $extractos
        ]);
    }

    /**
     * Import bank statement CSV.
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('archivo');
        $path = $file->getRealPath();

        $imported = 0;
        $duplicates = 0;

        try {
            DB::beginTransaction();

            $handle = fopen($path, 'r');
            if ($handle === false) {
                return response()->json(['error' => 'No se pudo abrir el archivo CSV.'], 422);
            }

            // Read header
            $header = fgetcsv($handle, 1000, ',');
            
            // If comma returns 1 element, try semicolon
            if (count($header) == 1) {
                rewind($handle);
                $header = fgetcsv($handle, 1000, ';');
                $delimiter = ';';
            } else {
                $delimiter = ',';
            }

            // Normalize header names to uppercase/trimmed
            $headerMap = array_flip(array_map(function($h) {
                return strtolower(trim(str_replace([' ', '_', '-'], '', $h)));
            }, $header));

            // Map columns
            $colFecha = isset($headerMap['fecha']) ? $headerMap['fecha'] : (isset($headerMap['date']) ? $headerMap['date'] : 0);
            $colDesc = isset($headerMap['descripcion']) ? $headerMap['descripcion'] : (isset($headerMap['description']) ? $headerMap['description'] : 1);
            $colMonto = isset($headerMap['monto']) ? $headerMap['monto'] : (isset($headerMap['amount']) ? $headerMap['amount'] : (isset($headerMap['valor']) ? $headerMap['valor'] : 2));
            $colRef = isset($headerMap['referencia']) ? $headerMap['referencia'] : (isset($headerMap['reference']) ? $headerMap['reference'] : -1);

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (count($row) < 3) continue;

                $fechaRaw = isset($row[$colFecha]) ? trim($row[$colFecha]) : '';
                $descripcion = isset($row[$colDesc]) ? trim($row[$colDesc]) : '';
                $montoRaw = isset($row[$colMonto]) ? trim($row[$colMonto]) : '0';
                $referencia = ($colRef !== -1 && isset($row[$colRef])) ? trim($row[$colRef]) : null;

                if (empty($fechaRaw) || empty($descripcion)) continue;

                // Try parsing date
                try {
                    $fecha = Carbon::parse(str_replace('/', '-', $fechaRaw))->toDateString();
                } catch (\Exception $e) {
                    continue; // Skip invalid date format rows
                }

                // Clean amount
                $monto = (float)str_replace(['$', ' ', ',', 'points', '.'], ['', '', '', '', ''], $montoRaw);
                // Handle standard European format: 1.250,50 -> 1250.50
                // If there's a comma in the original raw, let's treat it correctly:
                if (strpos($montoRaw, ',') !== false && strpos($montoRaw, '.') !== false) {
                    // formats like 1,250.50 or 1.250,50
                    if (strpos($montoRaw, ',') < strpos($montoRaw, '.')) {
                        // 1,250.50 -> replace comma
                        $monto = (float)str_replace(',', '', $montoRaw);
                    } else {
                        // 1.250,50 -> replace dot and change comma to dot
                        $monto = (float)str_replace(['.', ','], ['', '.'], $montoRaw);
                    }
                } elseif (strpos($montoRaw, ',') !== false) {
                    // 1250,50 -> change comma to dot
                    $monto = (float)str_replace(',', '.', $montoRaw);
                } else {
                    $monto = (float)$montoRaw;
                }

                if ($monto == 0) continue;

                // Prevent inserting exact duplicates (same date, description, and amount)
                $exists = ExtractoBancario::where('fecha', $fecha)
                    ->where('descripcion', $descripcion)
                    ->where('monto', $monto)
                    ->exists();

                if ($exists) {
                    $duplicates++;
                    continue;
                }

                ExtractoBancario::create([
                    'fecha' => $fecha,
                    'descripcion' => $descripcion,
                    'monto' => $monto,
                    'referencia' => $referencia,
                    'estado' => 'Pendiente'
                ]);

                $imported++;
            }

            fclose($handle);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Importación completada. Registros creados: {$imported}. Duplicados omitidos: {$duplicates}."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al importar extracto: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Automatically match statement entries with ledger bank records.
     */
    public function conciliarAutomatica(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');

        $query = ExtractoBancario::where('estado', 'Pendiente');

        if (!empty($fecha_desde) && !empty($fecha_hasta)) {
            $query->whereBetween('fecha', [$fecha_desde, $fecha_hasta]);
        }

        $pendientes = $query->get();
        $matchedCount = 0;

        try {
            DB::beginTransaction();

            $cuentaBanco = Cuenta::where('codigo', '111005')->first();
            if (!$cuentaBanco) {
                return response()->json(['error' => 'Cuenta contable auxiliar de bancos (111005) no definida en el PUC.'], 422);
            }

            foreach ($pendientes as $ext) {
                $fechaCarbon = Carbon::parse($ext->fecha);
                $fechaInicio = $fechaCarbon->copy()->subDays(3)->toDateString();
                $fechaFin = $fechaCarbon->copy()->addDays(3)->toDateString();
                $monto = (float)$ext->monto;

                // Find matching AsientoDetalle for account 111005
                $match = AsientoDetalle::join('comprobantes_contables', 'asientos_detalles.comprobante_id', '=', 'comprobantes_contables.id')
                    ->where('asientos_detalles.cuenta_id', $cuentaBanco->id)
                    ->whereBetween('comprobantes_contables.fecha', [$fechaInicio, $fechaFin])
                    ->where(function($q) use ($monto) {
                        if ($monto > 0) {
                            // Statement deposit = Debit in ledger (Debe)
                            $q->where('asientos_detalles.debe', '=', $monto);
                        } else {
                            // Statement withdrawal = Credit in ledger (Haber)
                            $q->where('asientos_detalles.haber', '=', abs($monto));
                        }
                    })
                    ->whereNotIn('comprobantes_contables.id', function($q) {
                        $q->select('comprobante_id')->from('extractos_bancarios')->whereNotNull('comprobante_id');
                    })
                    ->select('comprobantes_contables.id as comp_id')
                    ->first();

                if ($match) {
                    $ext->comprobante_id = $match->comp_id;
                    $ext->estado = 'Conciliado';
                    $ext->save();
                    $matchedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Conciliación automática finalizada. Transacciones conciliadas: {$matchedCount}."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error en conciliación automática: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Manually pair a statement entry with a journal entry.
     */
    public function conciliarManual(Request $request)
    {
        $request->validate([
            'extracto_id' => 'required|exists:extractos_bancarios,id',
            'comprobante_id' => 'required|exists:comprobantes_contables,id'
        ]);

        try {
            DB::beginTransaction();

            $ext = ExtractoBancario::findOrFail($request->extracto_id);

            // Verify if the comprobante is already linked
            $alreadyLinked = ExtractoBancario::where('comprobante_id', $request->comprobante_id)
                ->where('id', '!=', $request->extracto_id)
                ->exists();

            if ($alreadyLinked) {
                return response()->json(['error' => 'El comprobante seleccionado ya está conciliado con otro registro de extracto.'], 422);
            }

            $ext->comprobante_id = $request->comprobante_id;
            $ext->estado = 'Conciliado';
            $ext->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transacción conciliada manualmente de forma correcta.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al conciliar manualmente: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Unpair a reconciled statement entry.
     */
    public function desconciliar(Request $request)
    {
        $request->validate([
            'extracto_id' => 'required|exists:extractos_bancarios,id'
        ]);

        try {
            $ext = ExtractoBancario::findOrFail($request->extracto_id);
            $ext->comprobante_id = null;
            $ext->estado = 'Pendiente';
            $ext->save();

            return response()->json([
                'success' => true,
                'message' => 'Transacción desconciliada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al desconciliar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Retrieve matching candidate journal entries for manual reconciliation.
     */
    public function buscarComprobantesPendientes(Request $request)
    {
        $monto = (float)$request->input('monto');
        $fecha = $request->input('fecha');

        $cuentaBanco = Cuenta::where('codigo', '111005')->first();
        if (!$cuentaBanco) {
            return response()->json(['error' => 'Cuenta contable auxiliar de bancos (111005) no definida en el PUC.'], 422);
        }

        $query = ComprobanteContable::with(['detalles.cuenta'])
            ->whereHas('detalles', function($q) use ($cuentaBanco, $monto) {
                $q->where('cuenta_id', $cuentaBanco->id)
                  ->where(function($q2) use ($monto) {
                      if ($monto > 0) {
                          $q2->where('debe', '=', $monto);
                      } else {
                          $q2->where('haber', '=', abs($monto));
                      }
                  });
            })
            ->whereNotIn('id', function($q) {
                $q->select('comprobante_id')->from('extractos_bancarios')->whereNotNull('comprobante_id');
            });

        if (!empty($fecha)) {
            // Suggest sorting by proximity to date
            $query->orderByRaw("ABS(DATEDIFF(fecha, ?)) ASC", [$fecha]);
        }

        $comprobantes = $query->limit(20)->get();

        return response()->json($comprobantes);
    }
}
