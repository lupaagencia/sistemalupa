<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $cotizacion->numero_cotizacion }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #933B8F;
            margin-bottom: 4px;
        }
        .quote-number {
            font-size: 16px;
            font-weight: bold;
            color: #933B8F;
            text-align: right;
        }
        .box {
            border: 1px solid #e9d8fd;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #fcf8ff;
        }
        .box-title {
            font-weight: bold;
            color: #933B8F;
            border-bottom: 1px solid #e9d8fd;
            padding-bottom: 4px;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #933B8F;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            font-size: 11px;
        }
        .items-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #f3e8ff;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) {
            background-color: #fcf8ff;
        }
        .totals-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 6px;
            font-size: 11px;
        }
        .totals-table tr.total-row {
            font-weight: bold;
            font-size: 13px;
            background-color: #fcf8ff;
            color: #933B8F;
            border-top: 2px solid #933B8F;
        }
        .vendedor-box {
            margin-top: 6px;
            background-color: #fcf8ff;
            padding: 6px 10px;
            border-radius: 6px;
            border-left: 4px solid #60B12E;
            text-align: right;
            display: inline-block;
        }
        .footer-terms {
            margin-top: 30px;
            font-size: 10px;
            color: #64748b;
            border-top: 2px solid #60B12E;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td width="55%">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-height: 55px; max-width: 180px; margin-bottom: 4px;" alt="Logo Empresa">
                    <br>
                @endif
                <div class="company-title" style="font-size: 15px; font-weight: bold; color: #933B8F; margin-bottom: 3px;">
                    {{ $empresa['nombre'] ?? 'EMPAQUES LUPA Y/O AGENCIA LUPA S.A.S.' }}
                </div>
                <div style="font-size: 11px; color: #475569; font-weight: bold; margin-bottom: 2px;">{{ $empresa['slogan'] ?? '' }}</div>
                <div style="font-size: 11px;"><strong>NIT:</strong> {{ $empresa['nit'] ?? '' }}</div>
                <div style="font-size: 11px;"><strong>PBX / Teléfono:</strong> {{ $empresa['telefono'] ?? '' }}</div>
                <div style="font-size: 11px;"><strong>Email:</strong> {{ $empresa['email'] ?? '' }}</div>
                @if(!empty($empresa['direccion']))
                    <div style="font-size: 11px;"><strong>Dirección:</strong> {{ $empresa['direccion'] }}</div>
                @endif
            </td>
            <td width="45%" style="text-align: right;">
                <div class="quote-number">COTIZACIÓN</div>
                <div style="font-size: 15px; font-weight: bold; color: #1e293b;">{{ $cotizacion->numero_cotizacion }}</div>
                <div style="margin-top: 6px;"><strong>Fecha de Emisión:</strong> {{ date('d/m/Y', strtotime($cotizacion->fecha_emision)) }}</div>
                <div><strong>Válida Hasta:</strong> {{ $cotizacion->fecha_vencimiento ? date('d/m/Y', strtotime($cotizacion->fecha_vencimiento)) : '15 días' }}</div>
                
                <div class="vendedor-box">
                    <div><strong>Vendedor:</strong> {{ $vendedorNombreCompleto ?? ($cotizacion->vendedor ? $cotizacion->vendedor->usuario : 'N/A') }}</div>
                    @if(isset($vendedorTelefono) && $vendedorTelefono)
                        <div><strong>Tel / Cel:</strong> {{ $vendedorTelefono }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Client Info -->
    <div class="box">
        <div class="box-title">INFORMACIÓN DEL CLIENTE</div>
        <table width="100%" style="font-size: 11px; line-height: 1.5;">
            <tr>
                <td width="50%" style="vertical-align: top;">
                    <strong>Cliente / Razón Social:</strong> {{ $clienteData['nombre'] ?? ($cotizacion->cliente ? ($cotizacion->cliente->razonsocial ?? $cotizacion->cliente->nombre) : 'N/A') }}
                    @if(!empty($clienteData['empresa']) && $clienteData['empresa'] !== ($clienteData['nombre'] ?? ''))
                        <br><strong>Empresa:</strong> {{ $clienteData['empresa'] }}
                    @endif
                    <br><strong>NIT / Documento:</strong> {{ $clienteData['documento'] ?? 'N/A' }}
                    @if(!empty($clienteData['contacto']))
                        <br><strong>Contacto:</strong> {{ $clienteData['contacto'] }}
                    @endif
                </td>
                <td width="50%" style="vertical-align: top;">
                    <strong>Dirección:</strong> {{ $clienteData['direccion'] ?? 'N/A' }}
                    @if(!empty($clienteData['ciudad']))
                        ({{ $clienteData['ciudad'] }})
                    @endif
                    <br><strong>Teléfono / Celular:</strong> {{ $clienteData['telefono'] ?? 'N/A' }}
                    <br><strong>Correo Electrónico:</strong> {{ $clienteData['email'] ?? 'N/A' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Details Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="55%">Concepto / Descripción</th>
                <th width="12%" style="text-align: center;">Cant.</th>
                <th width="14%" style="text-align: right;">Precio Unit.</th>
                <th width="14%" style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->detalles as $index => $det)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $det->concepto }}</strong>
                    @if($det->descripcion)
                        <br><span style="color: #64748b; font-size: 10px;">{{ $det->descripcion }}</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ number_format($det->cantidad, 0) }}</td>
                <td style="text-align: right;">${{ number_format($det->precio_unitario, 2) }}</td>
                <td style="text-align: right;">${{ number_format($det->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $rawObs = $cotizacion->observaciones ?? '';
        $ocultarTotales = (strpos($rawObs, '[NO_TOTALIZAR]') !== false || strpos($rawObs, '[SIN_TOTALES]') !== false);
        $obsLimpia = trim(str_replace(['[NO_TOTALIZAR]', '[SIN_TOTALES]'], '', $rawObs));
    @endphp

    <!-- Totals Table (Se oculta si se seleccionó 'No Totalizar') -->
    @if(!$ocultarTotales)
    <table class="totals-table">
        <tr>
            <td style="text-align: right;">Subtotal:</td>
            <td style="text-align: right;">${{ number_format($cotizacion->subtotal, 2) }}</td>
        </tr>
        @if($cotizacion->iva > 0)
        <tr>
            <td style="text-align: right;">IVA:</td>
            <td style="text-align: right;">${{ number_format($cotizacion->iva, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td style="text-align: right;">TOTAL:</td>
            <td style="text-align: right;">${{ number_format($cotizacion->total, 2) }}</td>
        </tr>
    </table>
    @endif

    <!-- Terms & Conditions -->
    <div class="footer-terms">
        <strong>Condiciones Comerciales:</strong>
        <p style="margin: 4px 0;">
            <strong>Forma de Pago:</strong> {{ $cotizacion->condiciones_pago ?: 'Contado' }}
        </p>
        @if(!empty($obsLimpia))
        <p style="margin: 4px 0;">
            <strong>Observaciones:</strong> {{ $obsLimpia }}
        </p>
        @endif
        <p style="margin-top: 15px; font-size: 10px; text-align: center; color: #94a3b8;">
            Gracias por su confianza. Esta cotización ha sido generada electrónicamente por el sistema ERP / CRM de Lupack.
        </p>
    </div>

</body>
</html>
