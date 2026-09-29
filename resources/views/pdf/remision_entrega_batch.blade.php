<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Remisión #{{ $comprobante->num_comprobante }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #33e034;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            width: 150px;
        }

        .info-empresa {
            text-align: right;
        }

        .titulo {
            font-size: 18px;
            font-weight: bold;
            color: #33e034;
            margin-bottom: 5px;
        }

        .detalles-doc {
            margin-bottom: 20px;
            width: 100%;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .tabla th {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        .tabla td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: top;
        }

        .footer {
            margin-top: 30px;
        }

        .firmas {
            width: 100%;
            margin-top: 50px;
        }

        .firma-caja {
            width: 45%;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 5px;
            display: inline-block;
        }

        .espacio {
            width: 8%;
            display: inline-block;
        }

        .negrita {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <table class="header" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                @if(file_exists($logo))
                    <img src="{{ $logo }}" class="logo">
                @else
                    <h2 style="color: #33e034; margin:0;">LUPA AGENCIA</h2>
                @endif
            </td>
            <td class="info-empresa">
                <div class="titulo">REMISIÓN DE ENTREGA</div>
                <div class="negrita">No. {{ str_pad($comprobante->num_comprobante, 5, '0', STR_PAD_LEFT) }}</div>
                @if(isset($pedido))
                    <div style="font-size: 10px;">Ref. Pedido No.
                        {{ str_pad($pedido->num_comprobante, 5, '0', STR_PAD_LEFT) }}</div>
                @endif
                <div>Fecha: {{ $comprobante->fecha }}</div>
                <div>Registrado por: {{ $comprobante->usuario->usuario ?? 'Sistema' }}</div>
            </td>
        </tr>
    </table>

    @php
        $envio = $cliente->envios->first();
        $factura = $cliente->empresas->first();

        $direccion = "";
        $telefono = "";
        $documento = "";
        $contacto = "";

        if ($envio) {
            $contacto = $envio->contacto;
            $direccion = $envio->direccion . ($envio->ciudad ? ' - ' . $envio->ciudad : '');
            $telefono = $envio->telefono;
            $documento = $envio->documento;
        } elseif ($factura) {
            $direccion = $factura->direccion . ($factura->ciudad ? ' - ' . $factura->ciudad : '');
            $telefono = $factura->telefono;
            $documento = $factura->numero;
        } else {
            $direccion = $cliente->direccionf;
            $telefono = $cliente->telefono;
            $documento = $cliente->nit;
        }
    @endphp

    <table class="detalles-doc" cellpadding="0" cellspacing="0">
        <tr>
            <td width="100%">
                <div class="negrita">{{ $cliente->razonsocial ?? $cliente->nombre }}</div>
                @if($contacto)
                <div><span class="negrita">Contacto:</span> {{ $contacto }}</div> @endif
                <div><span class="negrita">Dirección:</span> {{ $direccion }}</div>
                <div><span class="negrita">Teléfono:</span> {{ $telefono }}</div>
                <div><span class="negrita">NIT/CC:</span> {{ $documento }}</div>
            </td>
        </tr>
    </table>

    <table class="tabla" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="10%" class="text-center">Orden</th>
                <th>Descripción / Detalles</th>
                <th width="15%" class="text-center">Cantidad Entregada</th>
                <th width="15%" class="text-center">Cantidad Pedido</th>
                <th width="15%" class="text-center">Cantidad Pendiente</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineas as $linea)
                <tr>
                    <td class="text-center">#{{ $linea->ordentrabajo_id }}</td>
                    <td>
                        <div class="negrita">{{ $linea->articulo->nombre }}</div>
                        @if($linea->orden && $linea->orden->detalles_diseno)
                            <div style="font-size: 9px; color: #666;">{{ $linea->orden->detalles_diseno }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ (int) $linea->cantidad }}</td>
                    <td class="text-center">{{ (int) ($linea->orden->cantidad ?? 0) }}</td>
                    <td class="text-center">
                        @if($linea->orden)
                            {{ (int) ($linea->orden->cantidad - $linea->orden->cantidad_entregada) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($comprobante->observaciones)
        <div style="margin-top: 20px;">
            <span class="negrita">Observaciones del Pedido:</span> {{ $comprobante->observaciones }}
        </div>
    @endif

    <div class="footer">
        <p>Esta remisión soporta la entrega de los elementos arriba descritos. Favor verificar el estado y cantidades al
            recibir.</p>
    </div>

    <div class="firmas">
        <div class="firma-caja">
            Entregado por: LUPA AGENCIA
        </div>
        <div class="espacio"></div>
        <div class="firma-caja">
            Recibido por (Firma y Sello):
        </div>
    </div>
</body>

</html>