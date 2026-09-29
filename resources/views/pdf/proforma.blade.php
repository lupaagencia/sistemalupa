<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Proforma No. {{ $factura->num_comprobante }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            vertical-align: top;
        }
        
        /* Header styling */
        .header-bar {
            background-color: #1e293b;
            height: 4px;
            width: 100%;
            margin-bottom: 15px;
        }
        .header-table {
            margin-bottom: 20px;
        }
        .logo-img {
            max-width: 140px;
            height: auto;
        }
        .company-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .company-subtitle {
            font-size: 10px;
            color: #64748b;
            line-height: 1.3;
        }
        .doc-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: right;
        }
        .doc-type {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .doc-number {
            font-size: 18px;
            font-weight: 900;
            color: #dc2626;
            margin-bottom: 4px;
        }
        .doc-date {
            font-size: 10px;
            color: #475569;
            font-weight: 600;
        }

        /* Client Info Box */
        .client-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 10px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .label-text {
            font-weight: 700;
            color: #334155;
            width: 85px;
            display: inline-block;
        }
        .value-text {
            color: #0f172a;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .items-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border: none;
        }
        .items-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 11px;
            margin-bottom: 3px;
        }
        .item-details-list {
            margin: 4px 0 0 0;
            padding: 0;
            list-style: none;
        }
        .item-detail-tag {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
        }
        .item-detail-bullet {
            color: #2563eb;
            font-weight: bold;
            margin-right: 3px;
        }

        /* Totals & Notes Section */
        .notes-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.4;
        }
        .letras-box {
            margin-top: 8px;
            font-size: 10px;
            color: #1e293b;
            font-weight: 600;
        }

        .totals-table {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            background-color: #ffffff;
        }
        .totals-table td, .totals-table th {
            padding: 6px 12px;
            font-size: 10.5px;
        }
        .totals-table th {
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }
        .totals-table td {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .row-total-final {
            background-color: #f8fafc;
        }
        .row-total-final th {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
        }
        .row-total-final td {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
        }

        .row-saldo-anticipo {
            background-color: #0f172a;
        }
        .row-saldo-anticipo th {
            font-size: 11px;
            font-weight: 800;
            color: #ffffff;
            border-bottom: none;
            padding: 8px 12px;
        }
        .row-saldo-anticipo td {
            font-size: 13px;
            font-weight: 900;
            color: #38bdf8;
            border-bottom: none;
            padding: 8px 12px;
        }

        .footer-note {
            margin-top: 25px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <div class="header-bar"></div>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <table>
                    <tr>
                        <td style="width: 120px;">
                            <img class="logo-img" src="{{ public_path(isset($empresa['logo']) ? ltrim($empresa['logo'], '/') : 'img/LOGO-LUPA.jpg') }}" alt="Logo Empresa">
                        </td>
                        <td style="padding-left: 10px;">
                            <div class="company-title">{{ isset($empresa['nombre']) ? $empresa['nombre'] : 'AGENCIA LUPA S.A.S.' }}</div>
                            <div class="company-subtitle">
                                <strong>NIT:</strong> {{ isset($empresa['nit']) ? $empresa['nit'] : '901086443-7' }}<br>
                                {{ isset($empresa['direccion']) ? $empresa['direccion'] : 'Carrera 1 # 23-60 • Cali, Valle del Cauca - Colombia' }}<br>
                                <strong>Tel / Cel:</strong> {{ isset($empresa['telefono']) ? $empresa['telefono'] : '+57 316 528 8931' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%;">
                <div class="doc-box">
                    <div class="doc-type">FACTURA PROFORMA</div>
                    <div class="doc-number">No. {{ sprintf('%04d', $factura->num_comprobante) }}</div>
                    <div class="doc-date">
                        <strong>Fecha de Emisión:</strong> {{ date('d/m/Y', strtotime($factura->fecha)) }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Client Info Section -->
    <div class="client-card">
        <div class="section-title">Información del Cliente</div>
        <table>
            <tr>
                <td style="width: 50%;">
                    <div style="margin-bottom: 4px;">
                        <span class="label-text">Razón Social:</span>
                        <span class="value-text"><strong>{{ $factura->cliente ? ($factura->cliente->razonsocial ?? $factura->cliente->nombre ?? 'N/A') : 'N/A' }}</strong></span>
                    </div>
                    <div style="margin-bottom: 4px;">
                        <span class="label-text">{{ ($factura->cliente && $factura->cliente->tipo_documento) ? $factura->cliente->tipo_documento : 'NIT / C.C.' }}:</span>
                        <span class="value-text">{{ $factura->cliente ? (($factura->cliente->numero ?? $factura->cliente->num_documento ?? '') . ($factura->cliente->digito ? '-'.$factura->cliente->digito : '')) : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="label-text">Teléfono:</span>
                        <span class="value-text">{{ $factura->cliente ? ($factura->cliente->telefono ?? $factura->cliente->telefono_contacto ?? 'N/A') : 'N/A' }}</span>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="margin-bottom: 4px;">
                        <span class="label-text">Dirección:</span>
                        <span class="value-text">{{ $factura->cliente ? ($factura->cliente->direccion ?? $factura->cliente->direccionf ?? 'N/A') : 'N/A' }}</span>
                    </div>
                    <div style="margin-bottom: 4px;">
                        <span class="label-text">Correo:</span>
                        <span class="value-text">{{ $factura->cliente ? ($factura->cliente->correo ?? $factura->cliente->email ?? 'N/A') : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="label-text">Forma Pago:</span>
                        <span class="value-text">{{ $factura->forma_pago ? $factura->forma_pago : 'Anticipo' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">CANT.</th>
                <th style="width: 55%; text-align: left;">DESCRIPCIÓN DE PRODUCTO / SERVICIO</th>
                <th style="width: 17.5%; text-align: right;">V. UNITARIO</th>
                <th style="width: 17.5%; text-align: right;">VALOR TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->lineas as $linea)
            <tr>
                <td style="text-align: center; font-weight: bold; color: #0f172a;">
                    {{ number_format($linea->cantidad, 0, ',', '.') }}
                </td>
                <td>
                    <div class="item-name">
                        {{ $linea->articulo ? $linea->articulo->nombre : ($linea->nombre ? $linea->nombre : 'Producto / Servicio') }}
                    </div>
                    
                    @if(!empty($linea->detalles) && count($linea->detalles) > 0)
                    <ul class="item-details-list">
                        @foreach($linea->detalles as $detalle)
                        @php
                            $valMostrar = '';
                            if (!empty($detalle->valor_formateado)) {
                                $valMostrar = $detalle->valor_formateado;
                            } else {
                                $raw = $detalle->valor;
                                if (is_string($raw) && (strpos(trim($raw), '[') === 0 || strpos(trim($raw), '{') === 0)) {
                                    $dec = json_decode($raw, true);
                                    if (is_array($dec)) {
                                        $arrP = [];
                                        foreach($dec as $it) {
                                            if (is_array($it) && isset($it['pantone'])) $arrP[] = $it['pantone'];
                                            elseif (is_array($it) && isset($it['nombre'])) $arrP[] = $it['nombre'];
                                            elseif (is_string($it)) $arrP[] = $it;
                                        }
                                        $valMostrar = implode(', ', $arrP);
                                    } else {
                                        $valMostrar = $raw;
                                    }
                                } elseif (is_array($raw)) {
                                    $arrP = [];
                                    foreach($raw as $it) {
                                        if (is_array($it) && isset($it['pantone'])) $arrP[] = $it['pantone'];
                                        elseif (is_array($it) && isset($it['nombre'])) $arrP[] = $it['nombre'];
                                        elseif (is_string($it)) $arrP[] = $it;
                                    }
                                    $valMostrar = implode(', ', $arrP);
                                } else {
                                    $valMostrar = (string)$raw;
                                }
                            }
                        @endphp
                        @if(($valMostrar !== '' && $valMostrar !== null) || (!empty($detalle->descripcion)) || (!empty($detalle->titulo)))
                        <li class="item-detail-tag">
                            <span class="item-detail-bullet">•</span> 
                            <strong>{{ $detalle->titulo }}:</strong> {{ $valMostrar }} {{ $detalle->descripcion }}
                        </li>
                        @endif
                        @endforeach
                    </ul>
                    @endif
                </td>
                <td style="text-align: right; font-weight: 600;">
                    $ {{ number_format($linea->valor_unitario, 0, ',', '.') }}
                </td>
                <td style="text-align: right; font-weight: 700; color: #0f172a;">
                    $ {{ number_format($linea->valor_total, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals & Notes Section -->
    <table>
        <tr>
            <td style="width: 58%; padding-right: 15px;">
                <div class="notes-box">
                    <strong>TÉRMINOS Y CONDICIONES:</strong><br>
                    {!! !empty($terminos) ? nl2br(e($terminos)) : '• Documento generado como Factura Proforma para la solicitud y cobro de anticipo de producción.<br>• Por favor tener en cuenta que el saldo final por pagar puede variar según ajustes de producción (+/- 10%).' !!}
                </div>
                <div class="letras-box">
                    <strong>SON:</strong> {{ $factura->letras ? $factura->letras : 'PESOS M/CTE' }}
                </div>
            </td>
            <td style="width: 42%;">
                <table class="totals-table">
                    <tr>
                        <th>SUBTOTAL</th>
                        <td>$ {{ number_format($factura->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($factura->impuestos > 0)
                    <tr>
                        <th>IVA (19%)</th>
                        <td>$ {{ number_format($factura->impuestos, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="row-total-final">
                        <th>TOTAL PROFORMA</th>
                        <td>$ {{ number_format($factura->total, 0, ',', '.') }}</td>
                    </tr>
                    @if($factura->abono > 0)
                    <tr>
                        <th>ABONO REGISTRADO</th>
                        <td style="color: #059669;">- $ {{ number_format($factura->abono, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="row-saldo-anticipo">
                        <th>SALDO PENDIENTE</th>
                        <td>$ {{ number_format(($factura->total - $factura->abono), 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        {{ !empty($notaPie) ? $notaPie : 'Este documento es un comprobante de cotización / factura proforma sin efectos fiscales inmediatos.' }} Documento emitido por {{ isset($empresa['nombre']) ? $empresa['nombre'] : 'AGENCIA LUPA S.A.S.' }}.
    </div>

</body>
</html>
