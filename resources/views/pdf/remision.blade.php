<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Remisión #{{ $delivery->numero_remision }}</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      width: 150px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th,
    td {
      border: 1px solid #333;
      padding: 6px;
    }

    .no-border {
      border: none;
    }

    .right {
      text-align: right;
    }
  </style>
</head>

<body>
  <div class="header">
    <div>
      <h2>EMPAQUES LUPA</h2>
      <div>Remisión #: <strong>{{ $delivery->numero_remision }}</strong></div>
      <div>Fecha: {{ $delivery->fecha->format('Y-m-d H:i') }}</div>
    </div>
    <div class="logo">
      @if(file_exists(public_path('img/LOGO-LUPA.jpg')))
        <img src="{{ public_path('img/LOGO-LUPA.jpg') }}" alt="logo" style="max-width:140px;">
      @endif
    </div>
  </div>

  <hr>

  <div>
    <strong>Cliente:</strong> {{ optional($delivery->order->cliente)->nombre ?? 'N/D' }}<br>
    <strong>Orden:</strong> {{ $delivery->order->id }} |
    <strong>Usuario:</strong> {{ optional($delivery->usuario)->name ?? 'Sistema' }}
  </div>

  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th>Descripción</th>
        <th>Cant. Solicitada</th>
        <th>Cant. Entregada en esta remisión</th>
        <th>Total Entregado (acumulado)</th>
        <th>Pendiente</th>
      </tr>
    </thead>
    <tbody>
      @foreach($delivery->items as $di)
        @php
          $oi = $di->orderItem;
        @endphp
        <tr>
          <td>{{ $oi->id }}</td>
          <td>{{ $oi->descripcion ?? optional($oi->product)->nombre ?? 'Producto' }}</td>
          <td class="right">{{ $oi->cantidad_solicitada }}</td>
          <td class="right">{{ $di->cantidad_entregada }}</td>
          <td class="right">{{ $oi->cantidad_entregada }}</td>
          <td class="right">{{ max(0, $oi->cantidad_solicitada - $oi->cantidad_entregada) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div style="margin-top:20px;">
    <strong>Observaciones:</strong>
    <div style="min-height:40px; border:1px solid #ccc; padding:6px;">{{ $delivery->observaciones }}</div>
  </div>

  <div style="margin-top:30px; display:flex; justify-content:space-between;">
    <div>_________________________<br>Recibido por</div>
    <div>_________________________<br>Entregado por</div>
  </div>

</body>

</html>