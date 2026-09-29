<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Remisión #{{ $entrega->consecutivo }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 12px; margin: 0; padding: 20px; }
        .header { width: 100%; border-bottom: 2px solid #33e034; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 150px; }
        .info-empresa { text-align: right; }
        .titulo { font-size: 18px; font-weight: bold; color: #33e034; margin-bottom: 5px; }
        .detalles-doc { margin-bottom: 20px; width: 100%; }
        .tabla { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabla th { background-color: #f2f2f2; border: 1px solid #ccc; padding: 8px; text-align: left; }
        .tabla td { border: 1px solid #ccc; padding: 8px; }
        .footer { margin-top: 50px; }
        .firmas { width: 100%; margin-top: 60px; }
        .firma-caja { width: 45%; border-top: 1px solid #333; text-align: center; padding-top: 5px; display: inline-block; }
        .espacio { width: 8%; display: inline-block; }
        .negrita { font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <table class="header">
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
                <div class="negrita">No. {{ str_pad($entrega->numero_remision, 5, '0', STR_PAD_LEFT) }}</div>
                <div>Fecha: {{ $entrega->fecha }}</div>
                <div>Registrado por: {{ $entrega->usuario->usuario ?? 'Sistema' }}</div>
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

    <table class="detalles-doc">
        <tr>
            <td width="50%">
                <div class="negrita">{{ $cliente->razonsocial ?? $cliente->nombre }}</div>
                @if($contacto) <div><span class="negrita">Contacto:</span> {{ $contacto }}</div> @endif
                <div><span class="negrita">Dirección:</span> {{ $direccion }}</div>
                <div><span class="negrita">Teléfono:</span> {{ $telefono }}</div>
                <div><span class="negrita">NIT/CC:</span> {{ $documento }}</div>
            </td>
            <td width="50%">
                <div class="negrita">PROYECTO / ORDEN:</div>
                <div>Orden No. {{ $orden->id }}</div>
                <div>Producto: {{ $articulo->nombre }}</div>
            </td>
        </tr>
    </table>

    <table class="tabla">
        <thead>
            <tr>
                <th>Descripción del Producto</th>
                <th width="15%" class="text-center">Cantidad Pedido</th>
                <th width="15%" class="text-center">Cantidad Entregada</th>
                <th width="15%" class="text-center">Cantidad Pendiente</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $articulo->nombre }} - {{ $orden->detalles_diseno }}</td>
                <td class="text-center">{{ $orden->cantidad }}</td>
                <td class="text-center">{{ $entrega->cantidad }}</td>
                <td class="text-center">{{ $entrega->saldo_restante }}</td>
            </tr>
        </tbody>
    </table>

    @if($entrega->observaciones)
    <div style="margin-top: 20px;">
        <span class="negrita">Observaciones:</span> {{ $entrega->observaciones }}
    </div>
    @endif

    <div class="footer">
        <p>Esta remisión soporta la entrega parcial/total de los elementos arriba descritos. Favor verificar el estado de los mismos al recibir.</p>
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
