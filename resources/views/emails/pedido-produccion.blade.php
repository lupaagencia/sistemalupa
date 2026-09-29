<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido en Producción</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f6f9fc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f6f9fc;
            padding: 40px 0;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #582a72;
            padding: 25px;
            text-align: center;
        }
        .logo {
            max-height: 65px;
            margin-bottom: 5px;
        }
        .banner {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .content {
            padding: 35px 30px;
            color: #2d3748;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #582a72;
            margin-top: 0;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            font-size: 15px;
            color: #718096;
            margin-bottom: 25px;
            text-align: center;
            line-height: 1.5;
        }
        .summary-card {
            background-color: #fcf8ff;
            border-left: 4px solid #8c4f8c;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-grid td {
            padding: 6px 0;
            font-size: 14px;
        }
        .summary-label {
            font-weight: 600;
            color: #4a5568;
            width: 35%;
        }
        .summary-val {
            color: #2d3748;
        }
        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: #582a72;
            margin-bottom: 15px;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 8px;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .item-table th {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            border-bottom: 2px solid #edf2f7;
            text-align: left;
        }
        .item-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
            color: #4a5568;
            vertical-align: top;
        }
        .item-total {
            font-weight: 600;
            color: #2d3748;
        }
        .footer {
            background-color: #f7fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            border-top: 1px solid #edf2f7;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header with embedded Logo -->
            <div class="header">
                @if(file_exists(public_path('img/LOGO-LUPA.jpg')))
                    <img class="logo" src="{{ $message->embed(public_path('img/LOGO-LUPA.jpg')) }}" alt="Empaques Lupa">
                @else
                    <h1 style="color: #ffffff; margin: 0; font-size: 28px;">LUPACK</h1>
                @endif
            </div>

            <!-- Embedded Banner Image (Half Height cropped with object-fit) and Delivery Date Ribbon -->
            @php
                $fechaEntregaGeneral = null;
                foreach ($pedido->lineas as $linea) {
                    if ($linea->fecha_entrega) {
                        $fechaEntregaGeneral = $linea->fecha_entrega;
                        break;
                    }
                }
            @endphp
            @if(file_exists(public_path('img/lupa_packaging_banner.png')))
                <div style="position: relative; width: 100%; height: 200px; overflow: hidden; background-color: #582a72;">
                    <img class="banner" src="{{ $message->embed(public_path('img/lupa_packaging_banner.png')) }}" alt="Empaques Premium" style="width: 100%; height: 200px; object-fit: cover; display: block; opacity: 0.9;">
                    @if($fechaEntregaGeneral)
                        <div style="position: absolute; bottom: 12px; right: 15px; background-color: #c53030; color: #ffffff; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(0,0,0,0.25); font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                            ENTREGA: {{ \Carbon\Carbon::parse($fechaEntregaGeneral)->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            @else
                @if($fechaEntregaGeneral)
                    <div style="background-color: #c53030; color: #ffffff; padding: 8px 15px; font-size: 13px; font-weight: bold; text-align: center; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; letter-spacing: 0.5px;">
                        📅 FECHA DE ENTREGA COMPROMISO: {{ \Carbon\Carbon::parse($fechaEntregaGeneral)->format('d/m/Y') }}
                    </div>
                @endif
            @endif

            <!-- Main Content -->
            <div class="content">
                <h2 class="title">¡Pedido en Producción!</h2>
                <p class="subtitle">Se ha registrado o cambiado a producción un nuevo pedido en el sistema. A continuación se presentan los detalles:</p>

                <!-- Summary Card -->
                <div class="summary-card">
                    <table class="summary-grid">
                        <tr>
                            <td class="summary-label">Pedido ID:</td>
                            <td class="summary-val" style="font-weight: bold; color: #582a72;">#{{ $pedido->id }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Cliente:</td>
                            <td class="summary-val" style="font-weight: 600;">{{ $pedido->cliente ? $pedido->cliente->razonsocial : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Fecha Registro:</td>
                            <td class="summary-val">{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</td>
                        </tr>
                        @if($fechaEntregaGeneral)
                        <tr>
                            <td class="summary-label">Fecha de Entrega:</td>
                            <td class="summary-val" style="font-weight: bold; color: #c53030;">{{ \Carbon\Carbon::parse($fechaEntregaGeneral)->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="summary-label">Forma de Pago:</td>
                            <td class="summary-val">{{ $pedido->forma_pago }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Total Pedido:</td>
                            <td class="summary-val" style="font-weight: 600;">${{ number_format($pedido->total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Abonado:</td>
                            <td class="summary-val" style="color: #2f855a; font-weight: 600;">${{ number_format($pedido->abono, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Saldo Pendiente:</td>
                            <td class="summary-val" style="color: #c53030; font-weight: bold;">${{ number_format($pedido->saldo, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Items Details Table -->
                <div class="table-title">Detalles Técnicos de los Artículos</div>
                <table class="item-table">
                    <thead>
                        <tr>
                            <th>Artículo e Info. Técnica</th>
                            <th style="text-align: center; width: 100px;">Fecha Entr.</th>
                            <th style="text-align: center; width: 60px;">Cant.</th>
                            <th style="text-align: right; width: 90px;">V. Unitario</th>
                            <th style="text-align: right; width: 90px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->lineas as $linea)
                            <tr>
                                <td>
                                    <div style="font-weight: bold; color: #2d3748; font-size: 15px;">{{ $linea->articulo ? $linea->articulo->nombre : 'N/A' }}</div>
                                    
                                    <!-- Badges de detalles técnicos de la orden (solo los de la tabla detalletrabajos) -->
                                    @if($linea->orden && $linea->orden->detalles->count() > 0)
                                        <div style="margin-top: 6px; line-height: 1.6;">
                                            @foreach($linea->orden->detalles as $detalle)
                                                @php
                                                    $valStr = trim($detalle->valor);
                                                    $decoded = json_decode($valStr);
                                                    $isJson = (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded)));
                                                @endphp
                                                <span style="display: inline-block; background-color: #fcf8ff; padding: 3px 8px; border-radius: 4px; margin-right: 5px; margin-bottom: 4px; border: 1px solid #e9d8fd; font-size: 12px; color: #4a5568; vertical-align: middle;">
                                                    <strong style="color: #582a72;">{{ trim(ucfirst(strtolower($detalle->titulo))) }}:</strong> 
                                                    @if($isJson)
                                                        @foreach($decoded as $colorVal)
                                                            <span style="display: inline-block; background-color: {{ $colorVal->hex ?? '#582a72' }}; color: #ffffff; padding: 1px 6px; border-radius: 3px; font-size: 10px; margin-right: 3px; font-weight: bold; border: 1px solid rgba(0,0,0,0.15); text-shadow: 0 1px 1px rgba(0,0,0,0.3); vertical-align: middle;">
                                                                {{ $colorVal->pantone ?? '' }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        {{ $detalle->valor }}
                                                    @endif
                                                    @if($detalle->descripcion) 
                                                        <span style="color: #718096; font-style: italic; font-size: 11px;">({{ $detalle->descripcion }})</span> 
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    

                                </td>
                                <td style="text-align: center; white-space: nowrap; font-size: 13px; font-weight: 500; color: #c53030;">
                                    {{ $linea->fecha_entrega ? \Carbon\Carbon::parse($linea->fecha_entrega)->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td style="text-align: center; font-weight: 600;">{{ $linea->cantidad }}</td>
                                <td style="text-align: right; white-space: nowrap;">${{ number_format($linea->valor_unitario, 2) }}</td>
                                <td style="text-align: right; white-space: nowrap;" class="item-total">${{ number_format($linea->valor_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p><strong>Empaques Lupa</strong> - Soluciones Premium de Empaques y Diseño</p>
                <p>Este es un correo automático generado por el sistema. Por favor no lo responda.</p>
            </div>
        </div>
    </div>
</body>
</html>
