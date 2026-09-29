<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recibo de Caja #{{ $pago->num_recibo }}</title>
    <style>
        @page {
            size: 612pt 396pt;
            margin: 0.5cm 1.2cm 0.5cm 0.8cm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .border {
            border: 1px solid #000;
            padding: 12px;
            width: 100%;
        }

        .header {
            width: 100%;
            margin-bottom: 5px;
        }

        .logo-area {
            width: 60%;
            float: left;
        }

        .recibo-area {
            width: 35%;
            float: right;
            text-align: right;
        }

        .recibo-badge {
            display: inline-block;
            background: #f2f2f2;
            border: 1px solid #000;
            padding: 5px 10px;
            text-align: center;
        }

        .recibo-titulo {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }

        .recibo-numero {
            font-size: 15px;
            color: red;
            margin: 0;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .tabla td {
            padding: 4px 8px;
            border: 1px solid #ddd;
        }

        .negrita {
            font-weight: bold;
        }

        .bg-gray {
            background-color: #f9f9f9;
        }

        .monto-letras {
            font-style: italic;
            border-bottom: 1px solid #ccc;
        }

        .firmas {
            margin-top: 10px;
            width: 100%;
        }

        .firma-box {
            width: 45%;
            float: left;
            text-align: center;
        }

        .clear {
            clear: both;
        }

        .footer {
            margin-top: 15px;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #777;
        }

        .metodo {
            font-size: 11px;
            color: #2c3e50;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="border">
        <div class="header">
            <div class="logo-area">
                <img src="img/LOGO-LUPA.jpg" alt="Lupa Logo" width="120" style="margin-bottom: 5px;">
                <div class="negrita" style="font-size: 16px;">AGENCIA LUPA SAS</div>
                <div>NIT: 901086443-7</div>
                <div>Dirección: Cra 1 #23-60</div>
                <div>Teléfono Celular: 3165288931</div>
            </div>
            <div class="recibo-area">
                <div class="recibo-badge">
                    <p class="recibo-titulo">RECIBO DE CAJA</p>
                    <p class="recibo-numero">No. {{ str_pad($pago->num_recibo, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <table class="tabla">
            <tr>
                <td class="bg-gray negrita" width="20%">FECHA:</td>
                <td width="30%">{{ $pago->fecha }}</td>
                <td class="bg-gray negrita" width="20%">VALOR:</td>
                <td width="30%" style="font-size: 14px;" class="negrita">$ {{ number_format($pago->monto, 2) }}</td>
            </tr>
            <tr>
                <td class="bg-gray negrita">RECIBIDO DE:</td>
                <td colspan="3">{{ $pago->cliente->razonsocial ?? $pago->cliente->nombre }}</td>
            </tr>
            <tr>
                <td class="bg-gray negrita">LA SUMA DE:</td>
                <td colspan="3" class="monto-letras">
                    {{-- Opcional: Función para pasar monto a letras --}}
                    Son: {{ number_format($pago->monto, 2) }} M/CTE.
                </td>
            </tr>
            <tr>
                <td class="bg-gray negrita">CONCEPTO:</td>
                <td colspan="3">
                    @if($pago->comprobante)
                        Cuenta de Cobro No. {{ $pago->comprobante->num_comprobante }}
                    @else
                        Abono
                    @endif
                    @if($pago->observaciones)
                        <br><small>{{ $pago->observaciones }}</small>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="bg-gray negrita">FORMA DE PAGO:</td>
                <td colspan="3" class="metodo">{{ strtoupper($pago->forma_pago) }}</td>
            </tr>
        </table>

        <div class="info-col" style="margin-top:20px; text-align: right;">
            <table style="width: 300px; float: right; border-collapse: collapse;">
                <tr>
                    <td class="bg-gray negrita">VALOR PAGO:</td>
                    <td align="right" class="negrita" style="font-size: 14px;">$ {{ number_format($pago->monto, 2) }}
                    </td>
                </tr>
            </table>
            <div class="clear"></div>
        </div>

        <div class="firmas">
            <div class="firma-box">
                <br>
                _________________________________<br>
                Entregué (Cliente)
            </div>
            <div class="firma-box" style="float: right;">
                <br>
                _________________________________<br>
                Recibí (AGENCIA LUPA SAS)
                <br><small>Por: {{ $pago->usuario->usuario }}</small>
            </div>
            <div class="clear"></div>
        </div>

        <div class="footer">
            SOPORTE CONTABLE DE INGRESO. Generado el {{ date('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>
