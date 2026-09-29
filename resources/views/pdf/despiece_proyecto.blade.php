<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Despiece - Proyecto #{{ $proyecto->id }}</title>
    <style>
        @page {
            size: letter;
            margin: 1.5cm 1.5cm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            font-size: 10.5px;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }

        .header h4 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
            color: #1a1a1a;
        }

        .header .company {
            font-size: 12px;
            color: #555;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h5 {
            font-size: 13px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 0;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #333;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 8px;
            font-size: 11px;
            border: 1px solid #ddd;
        }

        .info-table td.label {
            font-weight: bold;
            background-color: #f9f9f9;
            width: 20%;
        }

        .info-table td.value {
            width: 30%;
        }

        .mueble-card {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .mueble-header {
            background-color: #333;
            color: #fff;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            border-radius: 3px 3px 0 0;
            margin-bottom: 5px;
        }

        .mueble-info {
            font-size: 10px;
            color: #555;
            margin-bottom: 8px;
            padding: 0 4px;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .tabla th, .tabla td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            font-size: 10px;
            text-align: center;
        }

        .tabla th {
            font-weight: bold;
            background-color: #eaeaea;
        }

        .tabla td.text-left {
            text-align: left;
        }

        .canto-active {
            font-weight: bold;
            color: #008000;
            background-color: #e6ffe6;
        }

        .canto-inactive {
            color: #ccc;
        }

        .summary-card {
            background-color: #f7f9fa;
            border: 1px solid #b2c2cc;
            padding: 12px;
            border-radius: 4px;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .summary-card h6 {
            margin: 0 0 8px 0;
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            border-bottom: 1px solid #b2c2cc;
            padding-bottom: 4px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 4px 0;
            font-size: 11px;
        }

        .summary-table td.label {
            font-weight: bold;
            width: 40%;
        }

        .summary-table td.value {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            color: #16a085;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <span class="company">AGENCIA LUPA S.A.S.</span>
        <h4>SISTEMA DE GESTIÓN Y PRODUCCIÓN</h4>
        <h5>Reporte de Despiece y Cortes de Mueble</h5>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Proyecto ID:</td>
            <td class="value">#{{ str_pad($proyecto->id, 5, '0', STR_PAD_LEFT) }}</td>
            <td class="label">Fecha:</td>
            <td class="value">{{ \Carbon\Carbon::parse($proyecto->fecha)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Cliente:</td>
            <td class="value">{{ $proyecto->cliente ?: 'N/A' }}</td>
            <td class="label">Descripción:</td>
            <td class="value">{{ $proyecto->descripcion ?: 'Sin descripción' }}</td>
        </tr>
    </table>

    @if($muebles->count() == 0)
        <div style="text-align: center; padding: 30px; border: 1px dashed #ccc; font-size: 12px;">
            No se han registrado muebles en este proyecto.
        </div>
    @else
        @foreach($muebles as $mueble)
            <div class="mueble-card">
                <div class="mueble-header">
                    {{ $mueble->nombre }} (Módulo {{ $mueble->tipo_mueble }})
                </div>
                <div class="mueble-info">
                    <strong>Dimensiones Base:</strong> {{ $mueble->ancho }} Ancho x {{ $mueble->alto }} Alto x {{ $mueble->profundidad }} Fondo (mm) &nbsp;|&nbsp; 
                    <strong>Espesor:</strong> {{ $mueble->espesor_material }} mm <br>
                    <strong>Mat. Casco (Interno):</strong> {{ $mueble->material_interno ?: $mueble->material ?: 'No especificado' }} &nbsp;|&nbsp;
                    <strong>Mat. Vistas (Externo):</strong> {{ $mueble->material_externo ?: $mueble->material ?: 'No especificado' }} &nbsp;|&nbsp;
                    <strong>Costados Vistos:</strong> {{ $mueble->costados_vistos ?: 'Ninguno' }}<br>
                    @if($mueble->tipo_meson)
                        <strong>Tapa/Mesón:</strong> {{ $mueble->tipo_meson }} &nbsp;|&nbsp;
                    @endif
                    @if($mueble->sistema_apertura)
                        <strong>Apertura:</strong> {{ $mueble->sistema_apertura }}
                        @if($mueble->tipo_tirador) ({{ $mueble->tipo_tirador }}) @endif
                    @endif
                    @if($mueble->notas)
                        <br><strong>Notas:</strong> {{ $mueble->notas }}
                    @endif
                </div>

                <table class="tabla">
                    <thead>
                        <tr>
                            <th width="30%">Nombre Pieza</th>
                            <th width="20%">Material / Tablero</th>
                            <th width="6%">Cant.</th>
                            <th width="11%">Largo (mm)</th>
                            <th width="11%">Ancho (mm)</th>
                            <th width="5%">L1</th>
                            <th width="5%">L2</th>
                            <th width="5%">A1</th>
                            <th width="5%">A2</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mueble->piezas as $pieza)
                            <tr>
                                <td class="text-left font-weight-bold">{{ $pieza->nombre_pieza }}</td>
                                <td class="text-left" style="font-size: 9px; color: #555;">{{ $pieza->material ?: $mueble->material_interno ?: $mueble->material }}</td>
                                <td style="font-weight: bold;">{{ $pieza->cantidad }}</td>
                                <td>{{ number_format($pieza->largo, 0, ',', '.') }}</td>
                                <td>{{ number_format($pieza->ancho, 0, ',', '.') }}</td>
                                <td class="{{ $pieza->canto_l1 ? 'canto-active' : 'canto-inactive' }}">{{ $pieza->canto_l1 ? 'SI' : 'no' }}</td>
                                <td class="{{ $pieza->canto_l2 ? 'canto-active' : 'canto-inactive' }}">{{ $pieza->canto_l2 ? 'SI' : 'no' }}</td>
                                <td class="{{ $pieza->canto_a1 ? 'canto-active' : 'canto-inactive' }}">{{ $pieza->canto_a1 ? 'SI' : 'no' }}</td>
                                <td class="{{ $pieza->canto_a2 ? 'canto-active' : 'canto-inactive' }}">{{ $pieza->canto_a2 ? 'SI' : 'no' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <div class="summary-card">
            <h6>Consumo Estimado de Materiales</h6>
            <table class="summary-table">
                <tr>
                    <td class="label">Total Material Necesario (Melamina / MDF):</td>
                    <td class="value">{{ number_format($totalAreaMelamina, 2, ',', '.') }} m²</td>
                </tr>
                <tr>
                    <td class="label">Total Metros Lineales de Canto (Tapacantos):</td>
                    <td class="value">{{ number_format($totalMetrosCanto, 1, ',', '.') }} m</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        Generado automáticamente por el Sistema de Producción Lupa - {{ date('d/m/Y H:i') }}
    </div>
</body>

</html>
