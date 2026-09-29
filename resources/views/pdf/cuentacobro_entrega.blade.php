<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cuenta de Cobro #{{ $entrega->consecutivo }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            width: 120px;
        }

        .info-personal {
            text-align: left;
            margin-bottom: 20px;
        }

        .titulo-doc {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            text-decoration: underline;
        }

        .info-cliente {
            margin-bottom: 20px;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tabla th {
            background-color: #f2f2f2;
            border: 1px solid #333;
            padding: 8px;
        }

        .tabla td {
            border: 1px solid #333;
            padding: 8px;
        }

        .total-area {
            margin-top: 20px;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }

        .leyenda {
            margin-top: 40px;
            text-align: justify;
            line-height: 1.4;
        }

        .firma {
            margin-top: 60px;
            text-align: center;
        }

        .negrita {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <table style="width: 100%; border:none;">
            <tr style="border:none;">
                <td style="border:none; width: 60%;">
                    @if(isset($logo) && file_exists($logo))
                        <img src="{{ $logo }}" class="logo">
                    @else
                        <div class="negrita" style="font-size: 18px; color: #33e034;">LUPA AGENCIA S.A.S</div>
                    @endif
                </td>
                <td style="border:none; text-align: right; vertical-align: top;">
                    <div class="negrita" style="font-size: 14px;">NIT: 901.086.443-7</div>
                    <div>Carrera 1 # 23-60 Cali</div>
                    <div>Tel: +57 316 5288931</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="titulo-doc" style="color: #33e034;">CUENTA DE COBRO No.
        {{ str_pad($entrega->numero_remision, 5, '0', STR_PAD_LEFT) }}</div>
    <div class="text-center small" style="margin-top: -15px; margin-bottom: 20px;">Fecha: {{ $entrega->fecha }} |
        Registrado por: {{ $entrega->usuario->usuario ?? 'Sistema' }}</div>

    @php
        $envio = $cliente->envios->first();
        $factura = $cliente->empresas->first();

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
        } else {
            $direccion = $cliente->direccionf;
            $telefono = $cliente->telefono;
            $documento = $cliente->nit;
        }
    @endphp

    <div class="info-cliente">
        <div class="negrita">DEBE A: LUPA AGENCIA S.A.S</div>
        <br>
        <div class="negrita">{{ $cliente->razonsocial ?? $cliente->nombre }}</div>
        @if($contacto)
        <div><span class="negrita">Contacto:</span> {{ $contacto }}</div> @endif
        <div><span class="negrita">Dirección:</span> {{ $direccion }}</div>
        <div><span class="negrita">Teléfono:</span> {{ $telefono }}</div>
        <div><span class="negrita">NIT/CC:</span> {{ $documento }}</div>
    </div>

    <p>Por concepto de la entrega parcial de los siguientes servicios/productos de la Orden #{{ $orden->id }}:</p>

    <table class="tabla">
        <thead>
            <tr>
                <th>Descripción</th>
                <th width="15%" class="text-center">Cantidad</th>
                <th width="15%" class="text-center">V. Unitario</th>
                <th width="20%" class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $articulo->nombre }} - Entrega Parcial</td>
                <td class="text-center">{{ $entrega->cantidad }}</td>
                <td class="text-right">$ {{ number_format($orden->valor_unitario, 2) }}</td>
                <td class="text-right">$
                    {{ number_format($entrega->cantidad * $orden->valor_unitario, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    @php
        $ccObj = $entrega->cuentacobro ?? $entrega->comprobante;
        $subtotalCC = $entrega->cantidad * $orden->valor_unitario;
        $tasaIvaCC = (float) ($ccObj->iva ?? 0.19);
        $montoImpuestosCC = (float) ($ccObj->impuestos ?? 0);
        $tieneIva = ($montoImpuestosCC > 0) || ($ccObj && $tasaIvaCC > 0 && $montoImpuestosCC > 0);
        $ivaCC = $montoImpuestosCC > 0 ? $montoImpuestosCC : round($subtotalCC * $tasaIvaCC, 2);
        $vTotalCC = $subtotalCC + $ivaCC;
        $vAbonoCC = (float) ($ccObj->abono ?? 0);
        $vSaldoCC = max(0, $vTotalCC - $vAbonoCC);
    @endphp

    <div class="total-area">
        @if($montoImpuestosCC > 0 || $tieneIva)
        <div>SUBTOTAL: $ {{ number_format($subtotalCC, 2) }}</div>
        <div>IVA ({{ (int)($tasaIvaCC * 100) }}%): $ {{ number_format($ivaCC, 2) }}</div>
        @endif
        <div>VALOR TOTAL: $ {{ number_format($vTotalCC, 2) }}</div>
        @if($vAbonoCC > 0)
        <div style="color: #28a745;">ABONOS RECIBIDOS: $ {{ number_format($vAbonoCC, 2) }}</div>
        <div style="color: #dc3545; font-size: 15px; margin-top: 4px;">SALDO A PAGAR: $ {{ number_format($vSaldoCC, 2) }}</div>
        @else
        <div style="font-size: 15px; margin-top: 4px;">TOTAL A PAGAR: $ {{ number_format($vTotalCC, 2) }}</div>
        @endif
    </div>

    <div class="leyenda">
        <p class="negrita">CERTIFICO QUE:</p>
        <p>Esta cuenta de cobro se asimila en sus efectos a la letra de cambio según el artículo 774 del Código de
            Comercio. El pago de esta obligación deberá realizarse en un plazo de 30 días a partir de la fecha.</p>
        <p>Favor consignar a la cuenta de ahorros No. XXXXXXXX del Banco XXXXX a nombre de LUPA AGENCIA S.A.S.</p>
    </div>

    <div class="firma">
        <br><br>
        _________________________________<br>
        <span class="negrita">LUPA AGENCIA S.A.S</span><br>
        Representante Legal
    </div>
</body>

</html>