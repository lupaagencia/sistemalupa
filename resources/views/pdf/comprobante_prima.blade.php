<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de Prima #{{ $prima->id }}</title>
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
        }

        .header .nit {
            font-size: 11px;
            color: #666;
            font-weight: bold;
        }

        .header h5 {
            font-size: 13px;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 0;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 4px 0;
            font-size: 11px;
            vertical-align: top;
        }

        .info-table td.label {
            font-weight: bold;
            width: 18%;
        }

        .info-table td.value {
            width: 32%;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 10px;
        }

        .tabla th, .tabla td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 11px;
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
            margin-bottom: 20px;
        }

        .total-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-box td {
            padding: 10px;
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
            font-size: 10px;
            color: #555;
            text-transform: uppercase;
            font-weight: bold;
        }

        .total-box td.monto-neto h5 {
            margin: 3px 0 0 0;
            font-size: 15px;
            color: #27ae60;
            font-weight: bold;
        }

        .declaracion {
            text-align: justify;
            font-size: 9.5px;
            margin-bottom: 50px;
            color: #444;
            line-height: 1.5;
        }

        .firmas {
            width: 100%;
            margin-top: 40px;
        }

        .firma-col {
            width: 48%;
            display: inline-block;
            text-align: center;
            vertical-align: top;
        }

        .firma-line {
            width: 210px;
            margin: 0 auto;
            border-top: 1px solid #000;
            padding-top: 6px;
            font-size: 10px;
        }

        .firma-line p {
            margin: 3px 0;
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
        <h5>Comprobante de Pago de Prima de Servicios</h5>
    </div>

    <!-- Datos de la Liquidación -->
    <table class="info-table">
        <tr>
            <td class="label">Nombre Empleado:</td>
            <td class="value">{{ $prima->empleado->nombre }} {{ $prima->empleado->apellido }}</td>
            <td class="label">Fecha de Pago:</td>
            <td class="value">{{ $prima->fecha_pago }}</td>
        </tr>
        <tr>
            <td class="label">C.C. / Documento:</td>
            <td class="value">{{ $prima->empleado->num_doc }}</td>
            <td class="label">Año / Periodo:</td>
            <td class="value">{{ $prima->anio }} - {{ $prima->periodo == 1 ? 'I Semestre' : 'II Semestre' }}</td>
        </tr>
        <tr>
            <td class="label">Cargo:</td>
            <td class="value">{{ $prima->empleado->cargo ?? 'OPERARIO' }}</td>
            <td class="label">Días Liquidados:</td>
            <td class="value">{{ $prima->dias_trabajados }} días</td>
        </tr>
    </table>

    <!-- Desglose de Liquidación de la Prima -->
    <h6 style="font-size: 11px; font-weight: bold; margin: 15px 0 5px 0; text-transform: uppercase;">Detalle de Liquidación:</h6>
    <table class="tabla">
        <thead>
            <tr>
                <th class="text-left" style="width: 50%;">Concepto</th>
                <th class="text-center" style="width: 20%;">Variable / Base</th>
                <th class="text-right" style="width: 30%;">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">Salario Básico Mensual</td>
                <td class="text-center">Salario base</td>
                <td class="text-right">$ {{ number_format($prima->salario_base, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-left">Promedio de Horas Extras y Suplementario (Semestre)</td>
                <td class="text-center">Extras / Recargos</td>
                <td class="text-right">$ {{ number_format($prima->promedio_extras, 2, ',', '.') }}</td>
            </tr>
            <tr class="bg-gray" style="font-weight: bold;">
                <td class="text-left">Base de Liquidación de Prima</td>
                <td class="text-center">Salario + Promedio</td>
                <td class="text-right">$ {{ number_format($prima->salario_base + $prima->promedio_extras, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-left">Días Trabajados en el Semestre</td>
                <td class="text-center">Días laborados</td>
                <td class="text-right">{{ $prima->dias_trabajados }} días</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="text-left">VALOR PRIMA DE SERVICIOS LIQUIDADA</td>
                <td class="text-center">(Base * Días) / 360</td>
                <td class="text-right" style="color: #27ae60; font-size: 12px;">$ {{ number_format($prima->valor_prima, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Neto a recibir y Texto en Letras -->
    <div class="total-box">
        <table>
            <tr>
                <td class="letras">SON: {{ $numeroLetras }}</td>
                <td class="monto-neto">
                    <h6>Total Prima Recibido</h6>
                    <h5>$ {{ number_format($prima->valor_prima, 2, ',', '.') }}</h5>
                </td>
            </tr>
        </table>
    </div>

    <!-- Declaración de Paz y Salvo -->
    <div class="declaracion">
        Se hace constar que el valor recibido en este comprobante corresponde al pago definitivo y liquidación de la Prima de Servicios correspondiente al semestre indicado, de conformidad con lo establecido en el Artículo 306 del Código Sustantivo del Trabajo. El trabajador firma en constancia del recibo a entera satisfacción y conformidad de los valores liquidados.
    </div>

    <!-- Firmas -->
    <div class="firmas">
        <div class="firma-col" style="float: left;">
            <div class="firma-line" style="margin-top: 30px;">
                <p style="font-weight: bold;">{{ $prima->empleado->nombre }} {{ $prima->empleado->apellido }}</p>
                <p>TRABAJADOR</p>
                <p>C.C. {{ $prima->empleado->num_doc }}</p>
            </div>
        </div>
        <div class="firma-col" style="float: right;">
            <div class="firma-line" style="margin-top: 30px;">
                <p style="font-weight: bold;">ANDREA MORENO MARIN</p>
                <p>REPRESENTANTE LEGAL</p>
                <p>AGENCIA LUPA S.A.S.</p>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</body>

</html>
