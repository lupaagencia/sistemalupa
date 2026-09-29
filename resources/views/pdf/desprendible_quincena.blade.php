<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Desprendible de Pago Quincena #{{ $quincena->id }}</title>
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
            font-size: 10px;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        .border {
            border: 1px solid #000;
            padding: 12px;
            width: 100%;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
        }

        .header h4 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .header .nit {
            font-size: 10px;
            color: #666;
            font-weight: bold;
        }

        .header h5 {
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 0;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 12px;
        }

        .info-table td {
            padding: 3px 0;
            font-size: 11px;
            vertical-align: top;
        }

        .info-table td.label {
            font-weight: bold;
            width: 15%;
        }

        .info-table td.value {
            width: 35%;
        }

        .bases-box {
            width: 100%;
            border: 1px solid #000;
            background-color: #f9f9f9;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .bases-box table {
            width: 100%;
        }

        .bases-box td {
            padding: 2px 5px;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .tabla th, .tabla td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10px;
        }

        .tabla th {
            font-weight: bold;
            background-color: #f2f2f2;
            text-align: center;
        }

        .tabla td.text-left {
            text-align: left;
        }

        .tabla td.text-right {
            text-align: right;
        }

        .tabla td.text-center {
            text-align: center;
        }

        .total-box {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 15px;
        }

        .total-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-box td {
            padding: 8px;
            font-size: 11px;
        }

        .total-box td.letras {
            width: 65%;
            font-weight: bold;
            vertical-align: middle;
        }

        .total-box td.monto-neto {
            width: 35%;
            text-align: right;
            border-left: 1px solid #000;
            background-color: #f9f9f9;
        }

        .total-box td.monto-neto h6 {
            margin: 0;
            font-size: 11px;
            color: #333;
            text-transform: uppercase;
            font-weight: bold;
        }

        .total-box td.monto-neto h5 {
            margin: 2px 0 0 0;
            font-size: 14px;
            color: #27ae60;
            font-weight: bold;
        }

        .declaracion {
            text-align: justify;
            font-size: 9px;
            margin-bottom: 40px;
            color: #444;
            line-height: 1.4;
        }

        .firmas {
            width: 100%;
            margin-top: 30px;
        }

        .firma-col {
            width: 48%;
            display: inline-block;
            text-align: center;
            vertical-align: top;
        }

        .firma-line {
            width: 200px;
            margin: 0 auto;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 10px;
        }

        .firma-line p {
            margin: 2px 0;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    <div class="header">
        <h4>AGENCIA LUPA S.A.S.</h4>
        <div class="nit">NIT 901.086.443-7</div>
        <h5>Desprendible de Pago de Nomina - Quincena</h5>
    </div>

    <!-- Datos del Empleado y del Periodo -->
    <table class="info-table">
        <tr>
            <td class="label">Nombre:</td>
            <td class="value">{{ $quincena->empleado->nombre }} {{ $quincena->empleado->apellido }}</td>
            <td class="label">Fecha Pago:</td>
            <td class="value">{{ $quincena->fecha_pago }}</td>
        </tr>
        <tr>
            <td class="label">C.C. / Documento:</td>
            <td class="value">{{ $quincena->empleado->num_doc }}</td>
            <td class="label">Periodo Pago:</td>
            <td class="value">{{ $quincena->fecha_inicio }} al {{ $quincena->fecha_fin }}</td>
        </tr>
        <tr>
            <td class="label">Cargo:</td>
            <td class="value">{{ $quincena->empleado->cargo ?? 'OPERARIO' }}</td>
            <td class="label">Días Laborados:</td>
            <td class="value">{{ $quincena->dias_trabajados }} días</td>
        </tr>
    </table>

    <!-- Resumen de Bases -->
    <div class="bases-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;"><strong>Salario Básico Mensual:</strong> $ {{ number_format($quincena->empleado->salario, 2, ',', '.') }}</td>
                <td style="width: 50%; border-left: 1px solid #ccc; padding-left: 15px;"><strong>Valor Hora Ordinaria:</strong> $ {{ number_format($valorHoraOrdinaria, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Resumen de Liquidación Quincenal -->
    <h6 style="font-size: 11px; font-weight: bold; margin: 0 0 5px 0; text-transform: uppercase;">Resumen de Liquidación de la Quincena:</h6>
    <table class="tabla">
        <thead>
            <tr>
                <th class="text-left" style="width: 50%;">Concepto / Descripción</th>
                <th class="text-right" style="width: 25%;">Devengado</th>
                <th class="text-right" style="width: 25%;">Deducción</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">
                    @if($quincena->empleado->tipo_contrato === 'Prestación de servicios')
                        Honorarios por Servicios ({{ $quincena->dias_trabajados }} días)
                    @else
                        Sueldo Ordinario ({{ $quincena->dias_trabajados }} días)
                    @endif
                </td>
                <td class="text-right">$ {{ number_format($quincena->sueldo_neto, 2, ',', '.') }}</td>
                <td class="text-right">-</td>
            </tr>
            @if($quincena->empleado->tipo_contrato !== 'Prestación de servicios' && $quincena->auxilio_transporte > 0)
                <tr>
                    <td class="text-left">Auxilio Transporte Proporcional</td>
                    <td class="text-right">$ {{ number_format($quincena->auxilio_transporte, 2, ',', '.') }}</td>
                    <td class="text-right">-</td>
                </tr>
            @endif
            @if($totalHorasExtras > 0)
                <tr>
                    <td class="text-left">Monto Horas Extras / Recargos</td>
                    <td class="text-right">$ {{ number_format($totalHorasExtras, 2, ',', '.') }}</td>
                    <td class="text-right">-</td>
                </tr>
            @endif
            @if($quincena->empleado->tipo_contrato !== 'Prestación de servicios' && $quincena->salud_deduccion > 0)
                <tr>
                    <td class="text-left">Deducción Salud (4%)</td>
                    <td class="text-right">-</td>
                    <td class="text-right" style="color: #c0392b;">$ -{{ number_format($quincena->salud_deduccion, 2, ',', '.') }}</td>
                </tr>
            @endif
            @if($quincena->empleado->tipo_contrato !== 'Prestación de servicios' && $quincena->pension_deduccion > 0)
                <tr>
                    <td class="text-left">Deducción Pensión (4%)</td>
                    <td class="text-right">-</td>
                    <td class="text-right" style="color: #c0392b;">$ -{{ number_format($quincena->pension_deduccion, 2, ',', '.') }}</td>
                </tr>
            @endif
            @if($quincena->otras_deducciones > 0)
                <tr>
                    <td class="text-left">Otras Deducciones (Anticipos / Préstamos)</td>
                    <td class="text-right">-</td>
                    <td class="text-right" style="color: #c0392b;">$ -{{ number_format($quincena->otras_deducciones, 2, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="bg-gray" style="font-weight: bold;">
                <td class="text-left" style="padding: 8px;">NETO QUINCENA A PAGAR</td>
                <td colspan="2" class="text-right" style="font-size: 11px; color: #27ae60; padding: 8px;">$ {{ number_format($quincena->neto_pagado, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Detalle de Horas Extras y Recargos -->
    @if($totalHorasExtras > 0)
        <h6 style="font-size: 11px; font-weight: bold; margin: 10px 0 5px 0; text-transform: uppercase;">Detalle de horas extras liquidadas:</h6>
        <table class="tabla">
            <thead>
                <tr>
                    <th class="text-left" style="width: 40%;">Concepto</th>
                    <th style="width: 20%;">Recargo / Factor</th>
                    <th style="width: 15%;">Valor Hora</th>
                    <th style="width: 10%;">Cantidad</th>
                    <th class="text-right" style="width: 15%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @if($extras['horasHED'] > 0)
                    <tr>
                        <td class="text-left">Hora Extra Diurna (HED)</td>
                        <td class="text-center">{{ (($configHoras['factorHED'] - 1) * 100) }}% (x{{ number_format($configHoras['factorHED'], 2) }})</td>
                        <td class="text-center">$ {{ number_format($valorHoraOrdinaria * $configHoras['factorHED'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $extras['horasHED'] }}</td>
                        <td class="text-right">$ {{ number_format($montos['montoHED'], 2, ',', '.') }}</td>
                    </tr>
                @endif
                @if($extras['horasHEN'] > 0)
                    <tr>
                        <td class="text-left">Hora Extra Nocturna (HEN)</td>
                        <td class="text-center">{{ (($configHoras['factorHEN'] - 1) * 100) }}% (x{{ number_format($configHoras['factorHEN'], 2) }})</td>
                        <td class="text-center">$ {{ number_format($valorHoraOrdinaria * $configHoras['factorHEN'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $extras['horasHEN'] }}</td>
                        <td class="text-right">$ {{ number_format($montos['montoHEN'], 2, ',', '.') }}</td>
                    </tr>
                @endif
                @if($extras['horasHEDD'] > 0)
                    <tr>
                        <td class="text-left">Hora Extra Diurna Dominical/Festivo (HEDDF)</td>
                        <td class="text-center">{{ (($configHoras['factorHEDD'] - 1) * 100) }}% (x{{ number_format($configHoras['factorHEDD'], 2) }})</td>
                        <td class="text-center">$ {{ number_format($valorHoraOrdinaria * $configHoras['factorHEDD'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $extras['horasHEDD'] }}</td>
                        <td class="text-right">$ {{ number_format($montos['montoHEDD'], 2, ',', '.') }}</td>
                    </tr>
                @endif
                @if($extras['horasHEND'] > 0)
                    <tr>
                        <td class="text-left">Hora Extra Nocturna Dominical/Festivo (HENDF)</td>
                        <td class="text-center">{{ (($configHoras['factorHEND'] - 1) * 100) }}% (x{{ number_format($configHoras['factorHEND'], 2) }})</td>
                        <td class="text-center">$ {{ number_format($valorHoraOrdinaria * $configHoras['factorHEND'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $extras['horasHEND'] }}</td>
                        <td class="text-right">$ {{ number_format($montos['montoHEND'], 2, ',', '.') }}</td>
                    </tr>
                @endif
                @if($extras['horasRNO'] > 0)
                    <tr>
                        <td class="text-left">Recargo Nocturno Ordinario (RNO)</td>
                        <td class="text-center">{{ ($configHoras['factorRNO'] * 100) }}% (x{{ number_format($configHoras['factorRNO'], 2) }})</td>
                        <td class="text-center">$ {{ number_format($valorHoraOrdinaria * $configHoras['factorRNO'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $extras['horasRNO'] }}</td>
                        <td class="text-right">$ {{ number_format($montos['montoRNO'], 2, ',', '.') }}</td>
                    </tr>
                @endif
                @if($extras['horasRDD'] > 0)
                    <tr>
                        <td class="text-left">Recargo Diurno Dominical/Festivo (RDDF)</td>
                        <td class="text-center">{{ ($configHoras['factorRDD'] * 100) }}% (x{{ number_format($configHoras['factorRDD'], 2) }})</td>
                        <td class="text-center">$ {{ number_format($valorHoraOrdinaria * $configHoras['factorRDD'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $extras['horasRDD'] }}</td>
                        <td class="text-right">$ {{ number_format($montos['montoRDD'], 2, ',', '.') }}</td>
                    </tr>
                @endif
                @if($extras['horasRND'] > 0)
                    <tr>
                        <td class="text-left">Recargo Nocturno Dominical/Festivo (RNDF)</td>
                        <td class="text-center">{{ ($configHoras['factorRND'] * 100) }}% (x{{ number_format($configHoras['factorRND'], 2) }})</td>
                        <td class="text-center">$ {{ number_format($valorHoraOrdinaria * $configHoras['factorRND'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $extras['horasRND'] }}</td>
                        <td class="text-right">$ {{ number_format($montos['montoRND'], 2, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="bg-gray" style="font-weight: bold;">
                    <td colspan="4" class="text-left">TOTAL DEVENGADO POR EXTRAS</td>
                    <td class="text-right" style="color: #27ae60;">$ {{ number_format($totalHorasExtras, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Neto a recibir y Texto en Letras -->
    <div class="total-box">
        <table>
            <tr>
                <td class="letras">SON: {{ $numeroLetras }}</td>
                <td class="monto-neto">
                    <h6>Neto Recibido</h6>
                    <h5>$ {{ number_format($quincena->neto_pagado, 2, ',', '.') }}</h5>
                </td>
            </tr>
        </table>
    </div>

    <!-- Declaración de Paz y Salvo -->
    <div class="declaracion">
        Se hace constar que el valor recibido en este comprobante corresponde al pago neto de la quincena y conceptos relacionados en el período detallado. El trabajador firma en constancia del recibo a conformidad de los valores liquidados.
    </div>

    <!-- Firmas -->
    <div class="firmas">
        <div class="firma-col" style="float: left;">
            <div class="firma-line" style="margin-top: 25px;">
                <p style="font-weight: bold;">{{ $quincena->empleado->nombre }} {{ $quincena->empleado->apellido }}</p>
                <p>TRABAJADOR</p>
                <p>C.C. {{ $quincena->empleado->num_doc }}</p>
            </div>
        </div>
        <div class="firma-col" style="float: right;">
            <div class="firma-line" style="margin-top: 25px;">
                <p style="font-weight: bold;">ANDREA MORENO MARIN</p>
                <p>REPRESENTANTE LEGAL</p>
                <p>AGENCIA LUPA S.A.S.</p>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</body>

</html>
