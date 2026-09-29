<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Egreso #{{ $egreso->id }}</title>
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
            font-size: 12px;
            font-weight: bold;
            margin: 0;
        }

        .recibo-numero {
            font-size: 15px;
            color: #d35400;
            font-weight: bold;
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
            margin-top: 15px;
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
    @php
    if (!function_exists('numeroALetras')) {
        function numeroALetras($numero) {
            $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE', 'VEINTE'];
            $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
            $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SIETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
            
            $numero = (int)$numero;
            if ($numero === 0) return 'CERO';
            if ($numero === 100) return 'CIEN';
            
            $txt = '';
            if ($numero >= 1000000) {
                $millones = (int)($numero / 1000000);
                $numero = $numero % 1000000;
                if ($millones == 1) {
                    $txt .= 'UN MILLON ';
                } else {
                    $txt .= numeroALetras($millones) . ' MILLONES ';
                }
            }
            
            if ($numero >= 1000) {
                $miles = (int)($numero / 1000);
                $numero = $numero % 1000;
                if ($miles == 1) {
                    $txt .= 'MIL ';
                } else {
                    $txt .= numeroALetras($miles) . ' MIL ';
                }
            }
            
            if ($numero >= 100) {
                $cent = (int)($numero / 100);
                $numero = $numero % 100;
                if ($cent == 1 && $numero == 0) {
                    $txt .= 'CIEN ';
                } else {
                    $txt .= $centenas[$cent] . ' ';
                }
            }
            
            if ($numero > 20) {
                $dec = (int)($numero / 10);
                $uni = $numero % 10;
                $txt .= $decenas[$dec];
                if ($uni > 0) {
                    $txt .= ' Y ' . $unidades[$uni];
                }
            } else if ($numero > 0) {
                $txt .= $unidades[$numero];
            }
            
            return trim($txt);
        }
    }

    $enteros = (int)$egreso->valor;
    $centavos = round(($egreso->valor - $enteros) * 100);
    $centavos_str = str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100 M/CTE.';
    $letras = numeroALetras($enteros) . ' PESOS CON ' . $centavos_str;
    @endphp

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
                    <p class="recibo-titulo">COMPROBANTE DE EGRESO</p>
                    <p class="recibo-numero">No. {{ str_pad($egreso->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <table class="tabla">
            <tr>
                <td class="bg-gray negrita" width="20%">FECHA:</td>
                <td width="30%">{{ $egreso->fecha }}</td>
                <td class="bg-gray negrita" width="20%">VALOR:</td>
                <td width="30%" style="font-size: 14px;" class="negrita">$ {{ number_format($egreso->valor, 2) }}</td>
            </tr>
            <tr>
                <td class="bg-gray negrita">PAGADO A:</td>
                <td colspan="3">{{ $egreso->beneficiario ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bg-gray negrita">LA SUMA DE:</td>
                <td colspan="3" class="monto-letras">
                    Son: {{ $letras }}
                </td>
            </tr>
            <tr>
                <td class="bg-gray negrita">CONCEPTO / DETALLE:</td>
                <td colspan="3">
                    <strong>[{{ $egreso->tipo_egreso }}]</strong> {{ $egreso->concepto }}
                </td>
            </tr>
            <tr>
                <td class="bg-gray negrita">MÉTODO DE PAGO:</td>
                <td colspan="3" class="metodo">{{ strtoupper($egreso->metodo_pago) }}</td>
            </tr>
        </table>

        <div class="firmas">
            <div class="firma-box">
                <br><br>
                _________________________________<br>
                Firma del Beneficiario / C.C.
            </div>
            <div class="firma-box" style="float: right;">
                <br><br>
                _________________________________<br>
                Preparado por (AGENCIA LUPA SAS)
                @if($egreso->user)
                    <br><small>Usuario: {{ $egreso->user->usuario }}</small>
                @endif
            </div>
            <div class="clear"></div>
        </div>

        <div class="footer">
            SOPORTE CONTABLE DE EGRESO. Generado el {{ date('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>
