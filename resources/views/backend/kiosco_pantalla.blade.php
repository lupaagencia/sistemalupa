<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Estación Kiosco Marcación QR - LUPACK</title>
    <link href="{{ asset('css/plantilla.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body, html {
            min-height: 100%;
            margin: 0;
            background-color: #0f172a;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-y: auto;
        }
        #app {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <div id="app">
        <kiosco-pantalla :empleado-logueado-id="'{{ Auth::check() ? (Auth::user()->empleado_id ?: '') : '' }}'"></kiosco-pantalla>
    </div>

    <script src="{{ asset('js/app.js?v=' . time()) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        (function() {
            if (window.Swal) {
                const _Swal = window.Swal;
                window.swal = function(...args) {
                    if (args.length === 1 && typeof args[0] === 'object') return _Swal.fire(args[0]);
                    if (args.length >= 1) return _Swal.fire(args[0], args[1], args[2]);
                    return _Swal.fire(...args);
                };
                Object.assign(window.swal, _Swal);
                if (_Swal.fire) window.swal.fire = _Swal.fire.bind(_Swal);
                if (_Swal.mixin) window.swal.mixin = _Swal.mixin.bind(_Swal);
                if (_Swal.close) window.swal.close = _Swal.close.bind(_Swal);
                if (_Swal.DismissReason) window.swal.DismissReason = _Swal.DismissReason;
            }
        })();
    </script>
</body>
</html>
