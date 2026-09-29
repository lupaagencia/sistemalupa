<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cuenta de Cobro #{{ $comprobante->num_comprobante }}</title>
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
                <div class="titulo">CUENTA DE COBRO</div>
                <div class="negrita">No. {{ str_pad($comprobante->num_comprobante, 5, '0', STR_PAD_LEFT) }}</div>
                <div>Fecha: {{ $comprobante->fecha }}</div>
                <div>Registrado por: {{ $comprobante->usuario->usuario ?? 'Sistema' }}</div>
            </td>
        </tr>
    </table>

    @php
        $envio = ($cliente && method_exists($cliente, 'envios') && $cliente->envios) ? $cliente->envios->first() : null;
        $factura = ($cliente && method_exists($cliente, 'empresas') && $cliente->empresas) ? $cliente->empresas->first() : null;

        $direccion = "";
        $telefono = "";
        $documento = "";
        $contacto = "";

        if ($envio) {
            $contacto = $envio->contacto;
            $direccion = $envio->direccion . ($envio->ciudad ? " - " . $envio->ciudad : "");
            $telefono = $envio->telefono;
            $documento = $envio->documento;
        } elseif ($factura) {
            $direccion = $factura->direccion . ($factura->ciudad ? " - " . $factura->ciudad : "");
            $telefono = $factura->telefono;
            $documento = $factura->numero;
        } elseif ($cliente) {
            $direccion = $cliente->direccion ?? $cliente->direccionf ?? "";
            $telefono = $cliente->telefono ?? "";
            $documento = $cliente->numero ?? $cliente->nit ?? $cliente->documento ?? "";
        }
    @endphp

    <table class="detalles-doc" cellpadding="0" cellspacing="0">
        <tr>
            <td width="100%">
                <div class="negrita">{{ $cliente->razonsocial ?? $cliente->nombre ?? 'N/A' }}</div>
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
                <th>Descripción / Artículo</th>
                <th width="10%" class="text-center">Cantidad</th>
                <th width="15%" class="text-right">Val. Unitario</th>
                <th width="15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineas as $linea)
                @php
                    $valTotalLinea = (float)($linea->valor_total ?? ($linea->cantidad * $linea->valor_unitario));
                @endphp
                <tr>
                    <td class="text-center">#{{ $linea->ordentrabajo_id ?? ($linea->orden_id ?? $comprobante->id) }}</td>
                    <td>
                        <div class="negrita">{{ optional($linea->articulo)->nombre ?? 'Artículo' }}</div>
                        @if($linea->orden && $linea->orden->detalles_diseno)
                            <div style="font-size: 9px; color: #666;">{{ $linea->orden->detalles_diseno }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ (int) $linea->cantidad }}</td>
                    <td class="text-right">$ {{ number_format($linea->valor_unitario, 2) }}</td>
                    <td class="text-right">$ {{ number_format($valTotalLinea, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if((float)($comprobante->impuestos ?? 0) > 0)
            <tr>
                <td colspan="4" class="text-right negrita" style="border: none; padding-top: 10px;">SUBTOTAL:</td>
                <td class="text-right negrita" style="border: none; padding-top: 10px;">$
                    {{ number_format($comprobante->subtotal, 2) }}
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-right negrita" style="border: none; padding-top: 5px;">IVA ({{ (int)(((float)($comprobante->iva ?? 0.19)) * 100) }}%):</td>
                <td class="text-right negrita" style="border: none; padding-top: 5px;">$
                    {{ number_format($comprobante->impuestos, 2) }}
                </td>
            </tr>
            @endif
            <tr>
                <td colspan="4" class="text-right negrita" style="border: none; padding-top: 5px;">TOTAL:</td>
                <td class="text-right negrita" style="border: none; padding-top: 5px;">$
                    {{ number_format($comprobante->total, 2) }}
                </td>
            </tr>
            @if((float) ($comprobante->abono ?? 0) > 0)
            <tr>
                <td colspan="4" class="text-right negrita" style="border: none; color: #28a745; padding-top: 5px;">ABONOS RECIBIDOS:</td>
                <td class="text-right negrita" style="border: none; color: #28a745; padding-top: 5px;">$
                    {{ number_format($comprobante->abono, 2) }}
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-right negrita" style="border: none; color: #dc3545; padding-top: 5px;">SALDO A PAGAR:</td>
                <td class="text-right negrita" style="border-bottom: 2px double #333; color: #dc3545; padding-top: 5px;">$
                    {{ number_format($comprobante->saldo ?? max(0, $comprobante->total - $comprobante->abono), 2) }}
                </td>
            </tr>
            @else
            <tr>
                <td colspan="4" class="text-right negrita" style="border: none; padding-top: 5px;">TOTAL A PAGAR:</td>
                <td class="text-right negrita" style="border-bottom: 2px double #333; padding-top: 5px;">$
                    {{ number_format($comprobante->total, 2) }}
                </td>
            </tr>
            @endif
        </tfoot>
    </table>

    <div class="footer">
        <p>Favor consignar a la cuenta de ahorros No. XXXX de Banco XXXX a nombre de XXXX.</p>
    </div>

    <div class="firmas">
        <div class="firma-caja">
            Cordialmente,<br>LUPA AGENCIA
        </div>
    </div>
</body>

</html>
