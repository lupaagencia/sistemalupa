<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Producción - {{ $estado }}</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            color: #333;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #33e034;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .logo {
            width: 110px;
            height: auto;
        }
        .info-reporte {
            text-align: right;
            vertical-align: bottom;
        }
        .titulo {
            font-size: 16px;
            font-weight: bold;
            color: #222;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .subtitulo {
            font-size: 12px;
            color: #33e034;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .fecha {
            font-size: 9px;
            color: #666;
        }
        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .tabla th {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            padding: 5px 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            text-align: center;
        }
        .tabla td {
            border: 1px solid #ddd;
            padding: 5px 4px;
            font-size: 9px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .negrita {
            font-weight: bold;
        }
        .prioridad-alta {
            color: #d9534f;
            font-weight: bold;
        }
        .prioridad-media {
            color: #f0ad4e;
            font-weight: bold;
        }
        .prioridad-baja {
            color: #5cb85c;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
        .row-alt {
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    <table class="header" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 50%;">
                @if(file_exists(public_path('img/LOGO-LUPA.jpg')))
                    <img src="{{ public_path('img/LOGO-LUPA.jpg') }}" class="logo">
                @else
                    <h2 style="color: #33e034; margin: 0; font-size: 20px;">LUPA AGENCIA</h2>
                @endif
            </td>
            <td class="info-reporte" style="width: 50%;">
                <div class="titulo">Reporte de Producción</div>
                <div class="subtitulo">Estado: {{ $estado }}</div>
                <div class="fecha">Fecha Generación: {{ $fecha }}</div>
            </td>
        </tr>
    </table>

    <table class="tabla" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 6%;">N° Orden</th>
                <th style="width: 20%; text-align: left;">Referencia / Producto</th>
                <th style="width: 18%; text-align: left;">Cliente</th>
                <th style="width: 7%;">Cantidad</th>
                <th style="width: 8%;">Tamaño</th>
                <th style="width: 6%;">Cabida</th>
                <th style="width: 7%;">Pliegos</th>
                <th style="width: 10%;">Corte Material</th>
                <th style="width: 10%;">Corte Final</th>
                <th style="width: 8%;">Cant. Tamaños</th>
                <th style="width: 6%;">Sobrante</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordenes as $index => $orden)
                <tr class="{{ $index % 2 == 0 ? '' : 'row-alt' }}">
                    <td class="text-center negrita">#{{ $orden->id }}</td>
                    <td class="text-left">{{ $orden->articulo->nombre ?? 'N/A' }}</td>
                    <td class="text-left">{{ $orden->cliente->razonsocial ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($orden->cantidad) }}</td>
                    <td class="text-center">{{ $orden->tamano ?? 'N/A' }}</td>
                    <td class="text-center">{{ $orden->cabida ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($orden->pliegos) }}</td>
                    <td class="text-center">{{ $orden->medida_material ?? 'N/A' }}</td>
                    <td class="text-center">{{ $orden->medida_final ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($orden->tamanos) }}</td>
                    <td class="text-center">{{ number_format($orden->sobrante) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; font-size: 11px; color: #777;">
                        No hay órdenes activas en este estado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        LUPA AGENCIA &copy; {{ date('Y') }} - Sistema de Gestión de Producción
    </div>
</body>
</html>
