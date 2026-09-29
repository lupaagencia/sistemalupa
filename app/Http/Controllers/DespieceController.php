<?php

namespace App\Http\Controllers;

use App\DespieceProyecto;
use App\DespieceMueble;
use App\DespiecePieza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DespieceController extends Controller
{
    public function indexProyectos(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '') {
            $proyectos = DespieceProyecto::orderBy('id', 'desc')->paginate(10);
        } else {
            $proyectos = DespieceProyecto::where($criterio, 'like', '%' . $buscar . '%')
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        return [
            'pagination' => [
                'total'        => $proyectos->total(),
                'current_page' => $proyectos->currentPage(),
                'per_page'     => $proyectos->perPage(),
                'last_page'    => $proyectos->lastPage(),
                'from'         => $proyectos->firstItem(),
                'to'           => $proyectos->lastItem(),
            ],
            'proyectos' => $proyectos->items()
        ];
    }

    public function storeProyecto(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $proyecto = DespieceProyecto::updateOrCreate(
            ['id' => $request->id],
            [
                'cliente' => $request->cliente,
                'descripcion' => $request->descripcion,
                'fecha' => $request->fecha
            ]
        );

        return ['id' => $proyecto->id];
    }

    public function destroyProyecto($id)
    {
        $proyecto = DespieceProyecto::findOrFail($id);
        $proyecto->delete();
        return ['status' => 'success'];
    }

    public function getMuebles($proyecto_id)
    {
        $muebles = DespieceMueble::with(['piezas', 'divisiones'])
            ->where('proyecto_id', $proyecto_id)
            ->orderBy('id', 'asc')
            ->get();

        return $muebles;
    }

    public function storeMueble(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        try {
            DB::beginTransaction();

            $mueble = DespieceMueble::updateOrCreate(
                ['id' => $request->id],
                [
                    'proyecto_id' => $request->proyecto_id,
                    'nombre' => $request->nombre,
                    'tipo_mueble' => $request->tipo_mueble,
                    'ancho' => $request->ancho,
                    'alto' => $request->alto,
                    'profundidad' => $request->profundidad,
                    'espesor_material' => $request->espesor_material,
                    'material' => $request->material,
                    'material_interno' => $request->material_interno,
                    'material_externo' => $request->material_externo,
                    'tipo_meson' => $request->tipo_meson,
                    'costados_vistos' => $request->costados_vistos,
                    'sistema_apertura' => $request->sistema_apertura,
                    'tipo_tirador' => $request->tipo_tirador,
                    'alto_meson' => $request->alto_meson ?? 0,
                    'tiene_fondo' => filter_var($request->tiene_fondo ?? true, FILTER_VALIDATE_BOOLEAN),
                    'material_fondo' => $request->material_fondo ?? 'Durolac Blanco / MDF 3mm',
                    'ancho_derecho' => $request->ancho_derecho ?? null,
                    'hueco_alto' => $request->hueco_alto ?? null,
                    'hueco_ancho' => $request->hueco_ancho ?? null,
                    'espacio_ciego' => $request->espacio_ciego ?? null,
                    'notas' => $request->notas ?? $request->notes ?? ''
                ]
            );

            // Delete old pieces if it's an update
            DespiecePieza::where('mueble_id', $mueble->id)->delete();
            \App\DespieceDivision::where('mueble_id', $mueble->id)->delete();

            // Insert new divisiones
            $divisiones = $request->divisiones;
            if (is_array($divisiones)) {
                foreach ($divisiones as $index => $div) {
                    \App\DespieceDivision::create([
                        'mueble_id' => $mueble->id,
                        'posicion' => $index + 1,
                        'tipo' => $div['tipo'] ?? 'Cajones',
                        'cantidad' => $div['cantidad'] ?? 1
                    ]);
                }
            }

            // Insert new pieces
            $piezas = $request->piezas;
            if (is_array($piezas)) {
                foreach ($piezas as $p) {
                    DespiecePieza::create([
                        'mueble_id' => $mueble->id,
                        'nombre_pieza' => $p['nombre_pieza'],
                        'material' => $p['material'] ?? $mueble->material_interno ?? $mueble->material,
                        'cantidad' => $p['cantidad'],
                        'largo' => $p['largo'],
                        'ancho' => $p['ancho'],
                        'canto_l1' => filter_var($p['canto_l1'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'canto_l2' => filter_var($p['canto_l2'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'canto_a1' => filter_var($p['canto_a1'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'canto_a2' => filter_var($p['canto_a2'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ]);
                }
            }

            DB::commit();
            return ['status' => 'success', 'mueble_id' => $mueble->id];
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyMueble($id)
    {
        $mueble = DespieceMueble::findOrFail($id);
        $mueble->delete();
        return ['status' => 'success'];
    }

    public function descargarPDF($proyecto_id)
    {
        $proyecto = DespieceProyecto::findOrFail($proyecto_id);
        $muebles = DespieceMueble::with(['piezas'])
            ->where('proyecto_id', $proyecto_id)
            ->get();

        // Calculate totals for materials
        $totalAreaMelamina = 0; // m2
        $totalMetrosCanto = 0; // linear meters

        foreach ($muebles as $mueble) {
            foreach ($mueble->piezas as $pieza) {
                // Area in m2 = (Largo_mm * Ancho_mm * Cantidad) / 1,000,000
                $area = ($pieza->largo * $pieza->ancho * $pieza->cantidad) / 1000000;
                $totalAreaMelamina += $area;

                // Edge banding length: check which edges are banded
                $perimetroEnchapado = 0;
                if ($pieza->canto_l1) $perimetroEnchapado += $pieza->largo;
                if ($pieza->canto_l2) $perimetroEnchapado += $pieza->largo;
                if ($pieza->canto_a1) $perimetroEnchapado += $pieza->ancho;
                if ($pieza->canto_a2) $perimetroEnchapado += $pieza->ancho;

                // in meters
                $totalMetrosCanto += ($perimetroEnchapado * $pieza->cantidad) / 1000;
            }
        }

        $pdf = Pdf::loadView('pdf.despiece_proyecto', compact('proyecto', 'muebles', 'totalAreaMelamina', 'totalMetrosCanto'));
        return $pdf->stream('despiece_proyecto_' . str_pad($proyecto->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function getPiezasGlobales($proyecto_id)
    {
        $muebles = DespieceMueble::with(['piezas'])
            ->where('proyecto_id', $proyecto_id)
            ->get();
            
        $piezasGlobales = collect();
        foreach ($muebles as $mueble) {
            foreach ($mueble->piezas as $pieza) {
                // Add the furniture name to the piece for reference in the optimizer
                $pieza['mueble_nombre'] = $mueble->nombre;
                $piezasGlobales->push($pieza);
            }
        }
        
        return $piezasGlobales;
    }
}
