<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comprobante;
use App\FacturaElectronica;
use App\Services\FacturacionService;
use Illuminate\Support\Facades\Storage;

class FacturaElectronicaController extends Controller
{
    protected $facturacionService;

    public function __construct(FacturacionService $facturacionService)
    {
        $this->facturacionService = $facturacionService;
    }

    /**
     * Transmitir factura a la DIAN
     */
    public function transmitir(Request $request)
    {
        $this->validate($request, [
            'comprobante_id' => 'required|integer|exists:comprobantes,id'
        ]);

        try {
            $comprobante = Comprobante::with('cliente.persona')->findOrFail($request->comprobante_id);
            $fe = $this->facturacionService->transmitir($comprobante);

            return response()->json([
                'success' => true,
                'message' => 'Factura electrónica transmitida y aceptada por la DIAN.',
                'factura_electronica' => $fe
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtener estado de la factura electrónica
     */
    public function getEstado($comprobanteId)
    {
        $fe = FacturaElectronica::where('comprobante_id', $comprobanteId)->first();
        
        return response()->json([
            'emitido' => $fe ? true : false,
            'factura_electronica' => $fe
        ]);
    }

    /**
     * Descargar documento XML o representación gráfica PDF
     */
    public function descargar($tipo, $id)
    {
        $fe = FacturaElectronica::findOrFail($id);
        
        if ($tipo === 'xml') {
            $path = $fe->xml_path;
            $filename = "factura_{$fe->comprobante_id}.xml";
            $contentType = 'application/xml';
        } elseif ($tipo === 'pdf') {
            $path = $fe->pdf_path;
            $filename = "factura_{$fe->comprobante_id}.pdf";
            $contentType = 'text/html'; // HTML mock represented as PDF locally
        } else {
            abort(404);
        }

        if (!Storage::exists($path)) {
            abort(404, 'Archivo no encontrado en almacenamiento.');
        }

        $content = Storage::get($path);
        
        return response($content, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
