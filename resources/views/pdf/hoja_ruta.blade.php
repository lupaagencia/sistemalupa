<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Ruta - Orden #{{ $orden->id }}</title>
    <style>
        @page {
            size: {{ $paperCssSize ?? (is_string($paper) ? $paper : 'letter') }} {{ $orientation ?? 'portrait' }};
            margin: 3mm 6mm 3mm 6mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #2b2b2b;
            line-height: 1.15;
            background-color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        th, td {
            padding: 2px 4px;
            vertical-align: middle;
        }
        
        /* Header styling */
        .header-table {
            border-bottom: 2px solid #5c2c74;
            margin-bottom: 3px;
            padding-bottom: 2px;
        }
        .company-logo {
            max-width: 100px;
            max-height: 38px;
        }
        .company-title {
            font-size: 15px;
            font-weight: bold;
            color: #5c2c74;
            text-transform: uppercase;
            margin: 0;
        }
        .company-sub {
            font-size: 8.5px;
            color: #666;
            margin-top: 1px;
        }
        .doc-title-box {
            background-color: #5c2c74;
            color: #ffffff;
            text-align: center;
            padding: 3px 5px;
            border-radius: 3px;
        }
        .doc-title {
            font-size: 11.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-number {
            font-size: 14px;
            font-weight: bold;
            color: #ffe600;
            margin-top: 1px;
        }

        /* Section Headings */
        .section-header {
            background-color: #f1f0f5;
            border-left: 3px solid #5c2c74;
            padding: 2px 5px;
            font-weight: bold;
            font-size: 9.5px;
            color: #4a154b;
            text-transform: uppercase;
            margin-top: 3px;
            margin-bottom: 3px;
        }

        /* Info grids */
        .info-table {
            border: 1px solid #dcdcdc;
            border-radius: 3px;
            background-color: #fafafa;
        }
        .info-table th {
            background-color: #eae7f0;
            color: #333;
            font-size: 9px;
            text-align: left;
            border: 1px solid #dcdcdc;
            font-weight: bold;
            width: 14%;
            padding: 2px 4px;
        }
        .info-table td {
            border: 1px solid #dcdcdc;
            font-size: 9.5px;
            padding: 2px 4px;
            background-color: #fff;
        }
        
        .badge-priority {
            display: inline-block;
            padding: 1px 5px;
            font-size: 8px;
            font-weight: bold;
            color: #fff;
            border-radius: 2px;
            text-transform: uppercase;
        }
        .badge-alta { background-color: #e74c3c; }
        .badge-urgente { background-color: #c0392b; }
        .badge-normal { background-color: #27ae60; }

        /* Technical specs table */
        .specs-table {
            border: 1px solid #cbd5e1;
        }
        .specs-table th {
            background-color: #4a154b;
            color: #ffffff;
            font-size: 9px;
            text-align: left;
            padding: 2px 4px;
            border: 1px solid #3b113c;
        }
        .specs-table td {
            border: 1px solid #cbd5e1;
            padding: 2px 4px;
            font-size: 9px;
        }

        /* Routing Processes Table (Hoja de Ruta) */
        .process-table {
            border: 1px solid #4a154b;
            margin-top: 3px;
        }
        .process-table th {
            background-color: #5c2c74;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            padding: 3px 2px;
            border: 1px solid #4a154b;
            text-transform: uppercase;
        }
        .process-table td {
            border: 1px solid #cbd5e1;
            padding: 1px 3px;
            font-size: 9px;
            vertical-align: middle;
        }
        .process-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        
        .col-num { width: 3%; text-align: center; font-weight: bold; }
        .col-proceso { width: 13%; font-weight: bold; color: #2c3e50; }
        .col-qr { width: 10%; text-align: center; }
        .col-espec { width: 13%; font-size: 8px; color: #475569; }
        .col-cant-ent { width: 9%; text-align: center; }
        .col-cant-sal { width: 11%; text-align: center; }
        td.col-cant-sal { background-color: #fffde7 !important; color: #1e293b !important; }
        .col-calidad { width: 9%; text-align: center; }
        .col-firma { width: 11%; text-align: center; }
        .col-obs { width: 21%; }

        .checkbox-box {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1.2px solid #333;
            margin-right: 2px;
            vertical-align: middle;
            background-color: #fff;
        }

        /* Quality sign-off section */
        .sign-table {
            border: 1px solid #cbd5e1;
            margin-top: 3px;
            background-color: #fdfdfd;
        }
        .sign-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            vertical-align: top;
        }
        .sign-line {
            border-bottom: 1px dashed #64748b;
            margin-top: 10px;
            margin-bottom: 2px;
            width: 90%;
        }
        .sign-title {
            font-weight: bold;
            font-size: 8.5px;
            color: #334155;
            text-transform: uppercase;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <!-- ENCABEZADO INSTITUCIONAL -->
    <table class="header-table">
        <tr>
            <td style="width: 25%;">
                <img src="{{ public_path('img/LOGO-LUPA.jpg') }}" class="company-logo" alt="Agencia Lupa">
            </td>
            <td style="width: 45%; text-align: center;">
                <h1 class="company-title">AGENCIA LUPA S.A.S.</h1>
                <div class="company-sub">
                    NIT: 901086443-7 &bull; Tel: 316 528 8931 &bull; Carrera 1 # 23-60 Barrio El Piloto<br>
                    Soluciones Integrales en Empaques e Impresión
                </div>
            </td>
            <td style="width: 30%;">
                <div class="doc-title-box">
                    <div class="doc-title">HOJA DE RUTA</div>
                    <div style="font-size: 9px; opacity: 0.9;">CONTROL DE PROCESOS Y CALIDAD</div>
                    <div class="doc-number">ORDEN #{{ $orden->idorden ?? $orden->id }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- INFORMACIÓN GENERAL Y MATERIALES (DISEÑO SOLICITADO) -->
    <div style="border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 6px; font-size: 10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 25%;"><strong>Fecha creación:</strong> {{ $orden->fecha ? date('Y-m-d', strtotime($orden->fecha)) : ($orden->created_at ? date('Y-m-d', strtotime($orden->created_at)) : 'N/A') }}</td>
                <td style="width: 25%;"><strong>Fecha entrega:</strong> {{ $orden->fecha_entrega ? date('Y-m-d', strtotime($orden->fecha_entrega)) : 'N/A' }}</td>
                <td style="width: 25%; text-align: center;"><strong>Cliente:</strong> {{ strtoupper($orden->cliente->razonsocial ?? $orden->rasonsocial ?? 'N/A') }}</td>
                <td style="width: 25%; text-align: right;"><strong>Articulo:</strong> {{ strtoupper($orden->articulo->nombre ?? $orden->articulo ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td style="width: 25%; padding-top: 4px;"><strong>Cantidad final:</strong> {{ number_format($orden->cantidad) }}</td>
                <td style="width: 25%; padding-top: 4px;"><strong>Medida Final:</strong> {{ $orden->medida_final ?: 'N/A' }}</td>
                <td style="width: 25%; text-align: center; padding-top: 4px;"></td>
                <td style="width: 25%; text-align: right; padding-top: 4px;"></td>
            </tr>
        </table>
    </div>

    <!-- TABLA DE PAPEL / MATERIAL DE CORTE -->
    <table class="specs-table" style="width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 9px; border: 1px solid #cbd5e1;">
        <thead>
            <tr style="background-color: #f8fafc; border-bottom: 1px solid #cbd5e1;">
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; width: 30%;">Papel / Material</th>
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; width: 12%;">Pliegos</th>
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; width: 16%;">Corte Material</th>
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; width: 14%;">Tamaño</th>
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; width: 12%;">Cabida</th>
                <th style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; width: 16%;">Tamaños + Sobrante</th>
            </tr>
        </thead>
        <tbody>
            @php
                $papeles = [];
                if (isset($orden->costos)) {
                    foreach ($orden->costos as $c) {
                        if (stripos($c->titulo ?? '', 'papel') !== false || stripos($c->nombre_insumo ?? '', 'papel') !== false) {
                            $papeles[] = $c;
                        }
                    }
                }
            @endphp
            @if(count($papeles) > 0)
                @foreach($papeles as $p)
                    @php
                        // 1. Paper Name Resolution
                        $nombrePapel = $p->nombre_insumo ?: ($p->costois->nombre ?? null);
                        if (!$nombrePapel && isset($orden->detalles)) {
                            foreach ($orden->detalles as $d) {
                                if (stripos($d->titulo ?? '', 'papel') !== false) {
                                    $nombrePapel = $d->valor_detalle ?: $d->valor;
                                    break;
                                }
                            }
                        }
                        if (!$nombrePapel) {
                            $nombrePapel = ($p->titulo && strtolower(trim($p->titulo)) != 'papel') ? $p->titulo : 'Papel / Material';
                        }

                        // 2. Quantity & Pliegos Calculation
                        $tamanosSobrante = intval($p->descripcion ?: $p->cantidad ?: $orden->cantidad ?: 0);
                        $tamanoPliego = floatval($p->tamano ?: $orden->tamano ?: 1);
                        $pliegosCalc = ($tamanoPliego > 0) ? ceil($tamanosSobrante / $tamanoPliego) : $tamanosSobrante;
                    @endphp
                    <tr>
                        <td style="border: 1px solid #cbd5e1; padding: 4px 6px; color: #6b21a8; font-weight: bold;">
                            {{ $nombrePapel }}
                        </td>
                        <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; color: #16a34a; font-weight: bold;">
                            {{ $pliegosCalc }}
                        </td>
                        <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center;">
                            {{ $p->medida_material ?: ($orden->medida_material ?: '-') }}
                        </td>
                        <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center;">
                            {{ $p->tamano ?: ($orden->tamano ?: '-') }}
                        </td>
                        <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center;">
                            {{ $p->cabida ?: ($orden->cabida ?: '-') }}
                        </td>
                        <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center;">
                            {{ $tamanosSobrante }}
                        </td>
                    </tr>
                @endforeach
            @else
                @php
                    $nombrePapel = null;
                    if (isset($orden->detalles)) {
                        foreach ($orden->detalles as $d) {
                            if (stripos($d->titulo ?? '', 'papel') !== false) {
                                $nombrePapel = $d->valor_detalle ?: $d->valor;
                                break;
                            }
                        }
                    }
                    if (!$nombrePapel) {
                        $nombrePapel = $orden->papel_nombre ?? 'Papel / Material según orden';
                    }
                    $tamanosSobrante = intval($orden->cantidad ?: 0) + intval($orden->carpeta_cliente ?: 0);
                    $tamanoPliego = floatval($orden->tamano ?: 1);
                    $pliegosCalc = ($tamanoPliego > 0) ? ceil($tamanosSobrante / $tamanoPliego) : $tamanosSobrante;
                @endphp
                <tr>
                    <td style="border: 1px solid #cbd5e1; padding: 4px 6px; color: #6b21a8; font-weight: bold;">
                        {{ $nombrePapel }}
                    </td>
                    <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; color: #16a34a; font-weight: bold;">
                        {{ $pliegosCalc ?: '-' }}
                    </td>
                    <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center;">
                        {{ $orden->medida_material ?: '-' }}
                    </td>
                    <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center;">
                        {{ $orden->tamano ?: '-' }}
                    </td>
                    <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center;">
                        {{ $orden->cabida ?: '-' }}
                    </td>
                    <td style="border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center;">
                        {{ $tamanosSobrante ?: '-' }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    @if(isset($orden->detalles) && count($orden->detalles) > 0)
    <table class="specs-table" style="margin-top: 4px;">
        <thead>
            <tr>
                <th style="width: 25%;">Detalle / Parámetro</th>
                <th style="width: 35%;">Valor Configurado</th>
                <th style="width: 40%;">Especificación de Producción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $det)
            @php
                $titulo = $det->titulo_detalle ?: $det->titulo ?: '';
                $val = $det->valor_detalle ?: $det->valor;
                $parsedTinta = null;
                if (is_string($val) && (strpos($val, '[') === 0 || strpos($val, '{') === 0)) {
                    $parsedTinta = json_decode($val, true);
                } elseif (is_array($val) || is_object($val)) {
                    $parsedTinta = json_decode(json_encode($val), true);
                }
            @endphp
            <tr>
                <td><strong>{{ $titulo }}</strong></td>
                <td>
                    @if(!empty($parsedTinta) && is_array($parsedTinta))
                        <div style="margin: 0; padding: 0;">
                        @foreach($parsedTinta as $tintaItem)
                            @php
                                $hex = !empty($tintaItem['hex']) ? $tintaItem['hex'] : (!empty($tintaItem['color']) ? $tintaItem['color'] : '#333333');
                                $pantone = !empty($tintaItem['pantone']) ? $tintaItem['pantone'] : (!empty($tintaItem['nombre']) ? $tintaItem['nombre'] : 'Pantone');
                            @endphp
                            <span style="display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 9.5px; font-weight: bold; color: #ffffff; background-color: {{ $hex }}; border: 1px solid rgba(0,0,0,0.25); margin: 2px 4px 2px 0;">
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 1px; background-color: #ffffff; vertical-align: middle; margin-right: 4px; box-shadow: 0 0 1px rgba(0,0,0,0.5);"></span>
                                {{ strtoupper($pantone) }}
                            </span>
                        @endforeach
                        </div>
                    @else
                        {{ $val }}
                    @endif
                </td>
                <td>{{ $det->descripcion_detalle ?: $det->descripcion ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(!empty($orden->observaciones))
    <div style="background-color: #fffde7; border: 1px solid #ffe082; padding: 5px 8px; font-size: 10px; margin-top: 4px; border-radius: 3px;">
        <strong>Observaciones Generales de la Orden:</strong> {{ $orden->observaciones }}
    </div>
    @endif

    <!-- HOJA DE RUTA Y SEGUIMIENTO DE PROCESOS EN PLANTA -->
    <div class="section-header">3. Hoja de Ruta - Registro de Procesos, Salidas y Calidad</div>
    <table class="process-table">
        <thead>
            <tr>
                <th class="col-num">N°</th>
                <th class="col-proceso">Estación / Proceso</th>
                <th class="col-qr">Escanear QR</th>
                <th class="col-espec">Fecha y Hora Inicio / Termina</th>
                <th class="col-cant-ent">Cant. Entrada</th>
                <th class="col-cant-sal">Cant. Buena</th>
                <th class="col-calidad">Calidad (✓)</th>
                <th class="col-firma">Operario / Firma</th>
                <th class="col-obs">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @php $itemNum = 1; @endphp
            @foreach($procesos as $p)
                @php
                    $nombreProc = trim($p->proceso ?? $p);
                @endphp
                <tr>
                    <td class="col-num">{{ $itemNum++ }}</td>
                    <td class="col-proceso">
                        {{ $nombreProc }}
                    </td>
                    <td class="col-qr" style="text-align: center; vertical-align: middle; padding: 0px;">
                        @if(!empty($p->qr_url))
                            <img src="{{ $p->qr_url }}" width="38" height="38" style="display: block; margin: 0 auto; border: 1px solid #cbd5e1; padding: 0px; background: #fff;" alt="QR Status">
                        @endif
                    </td>
                    <td class="col-espec" style="font-size: 8px; line-height: 1.3;">
                        <div style="margin-bottom: 2px;"><strong>Inicio:</strong> <span style="color: #94a3b8;">___/___ ___:___</span></div>
                        <div><strong>Termina:</strong> <span style="color: #94a3b8;">___/___ ___:___</span></div>
                    </td>
                    <td class="col-cant-ent">
                        <span style="color: #94a3b8;">_______</span>
                    </td>
                    <td class="col-cant-sal">
                        <!-- Destacado para la cantidad que sale del proceso -->
                        <span style="font-weight: bold; color: #1e293b;">_______</span>
                    </td>
                    <td class="col-calidad">
                        <span class="checkbox-box"></span> OK<br>
                        <span class="checkbox-box"></span> No
                    </td>
                    <td class="col-firma">
                        <div style="border-bottom: 1px solid #94a3b8; width: 90%; margin: 5px auto 1px auto;"></div>
                        <span style="font-size: 7.5px; color: #64748b;">Nombre / Firma</span>
                    </td>
                    <td class="col-obs">
                        <div style="border-bottom: 1px dotted #cbd5e1; height: 7px; margin-bottom: 2px;"></div>
                        <div style="border-bottom: 1px dotted #cbd5e1; height: 7px;"></div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- CONTROL DE CALIDAD FINAL Y LIBERACIÓN -->
    <div class="section-header">4. Cierre, Control de Calidad Final y Entrega</div>
    <table class="sign-table">
        <tr>
            <td style="width: 50%;">
                <div class="sign-title">INSPECCIÓN FINAL DE CONTROL DE CALIDAD</div>
                <div style="margin-top: 6px; font-size: 10px;">
                    Estado de Liberación:<br>
                    <span class="checkbox-box" style="margin-top: 4px;"></span> <strong>APROBADO CONFORME</strong> &nbsp;&nbsp;&nbsp;
                    <span class="checkbox-box"></span> <strong>CON OBSERVACIONES</strong> &nbsp;&nbsp;&nbsp;
                    <span class="checkbox-box"></span> <strong>RECHAZADO</strong>
                </div>
                <div style="margin-top: 8px; font-size: 9.5px; color: #475569;">
                    Unidades Conformes Finales: _______________ &nbsp;|&nbsp; Mermas Totales: _______________
                </div>
                <div class="sign-line"></div>
                <div class="sign-title" style="font-size: 8.5px; color: #64748b;">Firma Inspector de Calidad / Fecha: ____/____/202__</div>
            </td>
            <td style="width: 50%;">
                <div class="sign-title">RECEPCIÓN Y SALIDA EN DESPACHOS</div>
                <div style="margin-top: 6px; font-size: 9.5px; color: #475569;">
                    Recibido en Bodega / Despacho para embalaje y entrega al cliente.
                </div>
                <div style="margin-top: 14px; font-size: 9.5px; color: #475569;">
                    Embalado en: ______ Cajas / Bultos &nbsp;|&nbsp; Peso Total: ______ Kg
                </div>
                <div class="sign-line"></div>
                <div class="sign-title" style="font-size: 8.5px; color: #64748b;">Firma Responsable Despacho / Fecha: ____/____/202__</div>
            </td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div style="text-align: center; font-size: 8.5px; color: #94a3b8; margin-top: 10px; border-top: 1px solid #e2e8f0; padding-top: 4px;">
        Documento de Control Interno de Producción &bull; AGENCIA LUPA S.A.S. &bull; Generado el {{ date('d/m/Y H:i:s') }}
    </div>

</body>
</html>
