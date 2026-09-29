<?php

namespace App\Services;

use App\Comprobante;
use App\FacturaElectronica;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FacturacionService
{
    /**
     * Transmitir la factura electrónica a la DIAN (simulación Mock)
     *
     * @param Comprobante $comprobante
     * @return FacturaElectronica
     * @throws \Exception
     */
    public function transmitir(Comprobante $comprobante)
    {
        // 1. Validar que la venta exista y sea tipo 'pedido' o 'factura'
        if ($comprobante->tipo !== 'pedido' && $comprobante->tipo !== 'factura') {
            throw new \Exception("Solo se pueden facturar electrónicamente los documentos de venta tipo pedido o factura.");
        }

        // 2. Validar que tenga cliente con datos mínimos obligatorios para DIAN
        $cliente = $comprobante->cliente;
        if (!$cliente) {
            throw new \Exception("La venta no tiene un cliente asociado.");
        }

        $persona = $cliente->persona ?? $cliente;
        $nit = $persona->num_documento ?? '';
        $nombre = $persona->nombre ?? '';
        
        // Use cliente email or default fallback
        $email = $persona->email ?? $comprobante->cliente->email ?? '';

        if (empty($nit)) {
            throw new \Exception("El cliente '{$nombre}' no posee un número de documento o NIT válido para facturación electrónica.");
        }

        if (empty($email)) {
            throw new \Exception("El cliente '{$nombre}' no tiene una dirección de correo electrónico configurada para envío de factura electrónica.");
        }

        // 3. Crear o recuperar el registro de factura electrónica
        $fe = FacturaElectronica::where('comprobante_id', $comprobante->id)->first();
        if (!$fe) {
            $fe = new FacturaElectronica();
            $fe->comprobante_id = $comprobante->id;
        }

        $fe->estado_dian = 'Pendiente';
        $fe->save();

        // 4. Calcular CUFE (Código Único de Facturación Electrónica) - Simulación DIAN
        // Formato estándar DIAN: SHA-384 de la combinación de NumFac + NitEmisor + NitAdquiriente + ValorTotal + Fecha
        $nitEmisor = '901234567-8'; // NIT de Lupa Agencia
        $fecha = $comprobante->fecha;
        $total = $comprobante->total;
        $numComprobante = $comprobante->num_comprobante;

        $stringCufe = $numComprobante . $nitEmisor . $nit . $total . $fecha . 'clave_tecnica_dian_test';
        $cufe = hash('sha384', $stringCufe);

        // 5. Generar XML UBL 2.1 (Simulación)
        $xmlContent = $this->generarXMLUBL21($comprobante, $cufe, $nitEmisor, $nit);
        $xmlPath = "public/facturas/factura_{$comprobante->id}.xml";
        Storage::put($xmlPath, $xmlContent);

        // 6. Generar Representación Gráfica PDF (Simulación HTML)
        $pdfContent = $this->generarPDFRepresentacion($comprobante, $cufe, $nitEmisor, $nit);
        $pdfPath = "public/facturas/factura_{$comprobante->id}.pdf";
        Storage::put($pdfPath, $pdfContent);

        // 7. Simular acuse de recibo y aceptación de la DIAN
        $fe->cufe = $cufe;
        $fe->uuid_proveedor = 'PTA-' . uniqid();
        $fe->estado_dian = 'Aceptado';
        $fe->xml_path = $xmlPath;
        $fe->pdf_path = $pdfPath;
        $fe->qr_code = "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$cufe}";
        $fe->dian_response = json_encode([
            'statusCode' => '00',
            'statusMessage' => 'Procesado Bandera de Aceptación Correcta.',
            'dianNotification' => 'La Factura Electrónica ha sido validada y aceptada de conformidad con la resolución de la DIAN.',
            'validationTime' => Carbon::now()->toIso8601String()
        ]);
        $fe->fecha_transmision = Carbon::now();
        $fe->save();

        return $fe;
    }

    /**
     * Genera un contenido XML simulado conforme al estándar UBL 2.1 de la DIAN
     */
    private function generarXMLUBL21(Comprobante $comprobante, $cufe, $nitEmisor, $nitAdquiriente)
    {
        $fecha = Carbon::parse($comprobante->fecha);
        $nombreCliente = $comprobante->cliente->nombre ?? 'Cliente Generico';
        
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n";
        $xml .= "<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\"\n";
        $xml .= "         xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\"\n";
        $xml .= "         xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\">\n";
        $xml .= "    <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>\n";
        $xml .= "    <cbc:CustomizationID>DIAN 2.1</cbc:CustomizationID>\n";
        $xml .= "    <cbc:ProfileID>DIAN 2.1: Factura Electrónica de Venta</cbc:ProfileID>\n";
        $xml .= "    <cbc:ID>{$comprobante->num_comprobante}</cbc:ID>\n";
        $xml .= "    <cbc:UUID schemeName=\"CUFE\">{$cufe}</cbc:UUID>\n";
        $xml .= "    <cbc:IssueDate>{$fecha->toDateString()}</cbc:IssueDate>\n";
        $xml .= "    <cbc:IssueTime>{$fecha->toTimeString()}</cbc:IssueTime>\n";
        $xml .= "    <cac:AccountingSupplierParty>\n";
        $xml .= "        <cac:Party>\n";
        $xml .= "            <cac:PartyIdentification>\n";
        $xml .= "                <cbc:ID schemeAgencyID=\"195\" schemeID=\"4\" schemeName=\"31\">{$nitEmisor}</cbc:ID>\n";
        $xml .= "            </cac:PartyIdentification>\n";
        $xml .= "            <cac:PartyName>\n";
        $xml .= "                <cbc:Name>LUPA AGENCIA S.A.S.</cbc:Name>\n";
        $xml .= "            </cac:PartyName>\n";
        $xml .= "        </cac:Party>\n";
        $xml .= "    </cac:AccountingSupplierParty>\n";
        $xml .= "    <cac:AccountingCustomerParty>\n";
        $xml .= "        <cac:Party>\n";
        $xml .= "            <cac:PartyIdentification>\n";
        $xml .= "                <cbc:ID schemeAgencyID=\"195\" schemeID=\"4\">{$nitAdquiriente}</cbc:ID>\n";
        $xml .= "            </cac:PartyIdentification>\n";
        $xml .= "            <cac:PartyName>\n";
        $xml .= "                <cbc:Name><![CDATA[{$nombreCliente}]]></cbc:Name>\n";
        $xml .= "            </cac:PartyName>\n";
        $xml .= "        </cac:Party>\n";
        $xml .= "    </cac:AccountingCustomerParty>\n";
        $xml .= "    <cac:LegalMonetaryTotal>\n";
        $xml .= "        <cbc:LineExtensionAmount currencyID=\"COP\">{$comprobante->subtotal}</cbc:LineExtensionAmount>\n";
        $xml .= "        <cbc:TaxExclusiveAmount currencyID=\"COP\">{$comprobante->impuestos}</cbc:TaxExclusiveAmount>\n";
        $xml .= "        <cbc:PayableAmount currencyID=\"COP\">{$comprobante->total}</cbc:PayableAmount>\n";
        $xml .= "    </cac:LegalMonetaryTotal>\n";
        $xml .= "</Invoice>\n";

        return $xml;
    }

    /**
     * Genera la representación gráfica simulada (HTML/PDF)
     */
    private function generarPDFRepresentacion(Comprobante $comprobante, $cufe, $nitEmisor, $nitAdquiriente)
    {
        $nombreCliente = $comprobante->cliente->nombre ?? 'Cliente Generico';
        
        $html = "<html>\n<head>\n<style>\n";
        $html .= "body { font-family: Arial, sans-serif; color: #333; margin: 30px; }\n";
        $html .= ".header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }\n";
        $html .= ".details { width: 100%; margin-top: 20px; border-collapse: collapse; }\n";
        $html .= ".details td { padding: 8px; border: 1px solid #ddd; }\n";
        $html .= ".cufe { background: #f5f5f5; font-family: monospace; font-size: 11px; padding: 10px; border: 1px dashed #999; word-break: break-all; }\n";
        $html .= "</style>\n</head>\n<body>\n";
        $html .= "<div class='header'>\n";
        $html .= "  <h2>FACTURA ELECTRÓNICA DE VENTA</h2>\n";
        $html .= "  <h3>LUPA AGENCIA S.A.S.</h3>\n";
        $html .= "  <p>NIT: {$nitEmisor} - Responsable de IVA</p>\n";
        $html .= "  <h4>No. {$comprobante->num_comprobante}</h4>\n";
        $html .= "</div>\n";
        $html .= "<table class='details'>\n";
        $html .= "  <tr>\n";
        $html .= "    <td><b>Adquiriente:</b> {$nombreCliente}</td>\n";
        $html .= "    <td><b>NIT:</b> {$nitAdquiriente}</td>\n";
        $html .= "  </tr>\n";
        $html .= "  <tr>\n";
        $html .= "    <td><b>Fecha Emisión:</b> {$comprobante->fecha}</td>\n";
        $html .= "    <td><b>Forma de Pago:</b> {$comprobante->forma_pago}</td>\n";
        $html .= "  </tr>\n";
        $html .= "</table>\n";
        $html .= "<h3>Resumen Económico</h3>\n";
        $html .= "<table class='details'>\n";
        $html .= "  <tr><td>Subtotal</td><td align='right'>$" . number_format($comprobante->subtotal, 2) . "</td></tr>\n";
        $html .= "  <tr><td>IVA Desglosado</td><td align='right'>$" . number_format($comprobante->impuestos, 2) . "</td></tr>\n";
        $html .= "  <tr><td><b>Total Facturado</b></td><td align='right'><b>$" . number_format($comprobante->total, 2) . "</b></td></tr>\n";
        $html .= "</table>\n";
        $html .= "<div style='margin-top: 30px;'>\n";
        $html .= "  <b>CUFE (Código Único de Facturación Electrónica):</b>\n";
        $html .= "  <div class='cufe'>{$cufe}</div>\n";
        $html .= "</div>\n";
        $html .= "<div style='margin-top: 20px; text-align: center;'>\n";
        $html .= "  <p>Representación Gráfica Oficial de la Factura Electrónica conforme a las directrices de la DIAN.</p>\n";
        $html .= "</div>\n";
        $html .= "</body>\n</html>";

        return $html;
    }
}
