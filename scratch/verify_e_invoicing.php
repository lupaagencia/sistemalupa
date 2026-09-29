<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Comprobante;
use App\Cliente;
use App\Persona;
use App\Services\FacturacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "=== STARTING ELECTRONIC INVOICING VERIFICATION SCRIPT ===\n";

DB::beginTransaction();

try {
    // Try to find an existing sales order (pedido)
    $comprobante = Comprobante::where('tipo', 'pedido')->first();

    if ($comprobante) {
        echo "✅ Found existing Comprobante (Pedido) ID: {$comprobante->id}, Number: {$comprobante->num_comprobante}\n";
        
        // Ensure its client is set
        $cliente = $comprobante->cliente;
        if (!$cliente) {
            $cliente = Cliente::first();
            $comprobante->cliente_id = $cliente->id;
            $comprobante->save();
        }
        
        $persona = $cliente->persona ?? $cliente;
        if (empty($persona->email)) {
            $persona->email = 'cliente.dian@example.com';
            $persona->save();
        }
        if (empty($persona->num_documento)) {
            $persona->num_documento = '12345678-9';
            $persona->save();
        }
        
    } else {
        echo "ℹ️ No existing Pedido found. Creating a dummy one...\n";
        
        // Get a valid Facturacion ID if available
        $facturacion = \App\Facturacion::first();
        $datos_factura_id = $facturacion ? $facturacion->id : 1; 

        // 1. Get or create a client persona
        $persona = Persona::first();
        if (!$persona) {
            $persona = new Persona();
            $persona->nombre = 'Cliente de Prueba DIAN';
            $persona->tipo_documento = 'NIT';
            $persona->num_documento = '900800700-1';
            $persona->email = 'cliente.dian@example.com';
            $persona->save();
        } else {
            if (empty($persona->email)) {
                $persona->email = 'cliente.dian@example.com';
            }
            if (empty($persona->num_documento)) {
                $persona->num_documento = '900800700-1';
            }
            $persona->save();
        }

        // 2. Get or create a client
        $cliente = Cliente::where('id', $persona->id)->first();
        if (!$cliente) {
            $cliente = new Cliente();
            $cliente->id = $persona->id;
            $cliente->razonsocial = 'Empresa de Prueba S.A.S.';
            $cliente->email = 'cliente.dian@example.com';
            $cliente->telefono = '1234567';
            $cliente->direccionf = 'Calle Falsa 123';
            $cliente->save();
        }

        // 3. Create a dummy sales order (pedido)
        $comprobante = new Comprobante();
        $comprobante->tipo = 'pedido';
        $comprobante->num_comprobante = 'OT-TEST-' . rand(1000, 9999);
        $comprobante->cliente_id = $cliente->id;
        $comprobante->datos_factura_id = $datos_factura_id;
        $comprobante->user_id = 1;
        $comprobante->fecha = date('Y-m-d H:i:s');
        $comprobante->forma_pago = 'Contado';
        $comprobante->subtotal = 500000;
        $comprobante->descuento = 0;
        $comprobante->impuestos = 95000;
        $comprobante->total = 595000;
        $comprobante->abono = 200000;
        $comprobante->saldo = 395000;
        $comprobante->estado = 'A';
        $comprobante->save();

        echo "✅ Dummy Comprobante (Pedido) created. ID: {$comprobante->id}, Number: {$comprobante->num_comprobante}\n";
    }

    // Load relationships as needed
    $comprobante->load('cliente');

    // 4. Instantiate Service and execute transmission
    $service = app(FacturacionService::class);
    echo "📡 Transmitting to DIAN (Mock Driver)...\n";
    $fe = $service->transmitir($comprobante);

    echo "✅ Transmission completed!\n";
    echo "   Factura Electronica ID: {$fe->id}\n";
    echo "   Estado DIAN: {$fe->estado_dian}\n";
    echo "   CUFE: {$fe->cufe}\n";
    echo "   UUID Proveedor: {$fe->uuid_proveedor}\n";
    echo "   XML Path: {$fe->xml_path}\n";
    echo "   PDF Path: {$fe->pdf_path}\n";
    echo "   QR Code Link: {$fe->qr_code}\n";

    // 5. Verify physical files were generated
    if (Storage::exists($fe->xml_path)) {
        echo "✅ XML File generated successfully.\n";
        // Check standard elements are inside
        $xmlContent = Storage::get($fe->xml_path);
        if (strpos($xmlContent, '<cbc:CustomizationID>DIAN 2.1</cbc:CustomizationID>') !== false) {
            echo "   - CustomizationID DIAN 2.1 found.\n";
        }
        if (strpos($xmlContent, '<cbc:UUID schemeName="CUFE">') !== false) {
            echo "   - CUFE tag found.\n";
        }
    } else {
        echo "❌ XML File NOT found!\n";
    }

    if (Storage::exists($fe->pdf_path)) {
        echo "✅ PDF File generated successfully.\n";
        $pdfContent = Storage::get($fe->pdf_path);
        if (strpos($pdfContent, 'FACTURA ELECTRÓNICA DE VENTA') !== false) {
            echo "   - PDF Title found.\n";
        }
    } else {
        echo "❌ PDF File NOT found!\n";
    }

    // Clean up files created in Storage to keep sandbox clean
    Storage::delete($fe->xml_path);
    Storage::delete($fe->pdf_path);
    echo "🗑️ Temporary XML and PDF storage files deleted.\n";

} catch (\Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
} finally {
    DB::rollback();
    echo "🔄 Database changes rolled back cleanly.\n";
}

echo "=== VERIFICATION COMPLETE ===\n";
