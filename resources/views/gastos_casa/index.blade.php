<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistema de Control de Gastos e Ingresos - Casa Familiar">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <title>Gestión de Gastos e Ingresos Casa</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Styles -->
    <link href="{{ asset('css/plantilla.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        /* Modal fixed fixes */
        .modal.mostrar, .modal.show, div.modal.mostrar, div.modal.show {
            display: flex !important;
            align-items: flex-start !important;
            justify-content: center !important;
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            width: 100vw !important; height: 100vh !important;
            z-index: 10500 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
        .swal2-container { z-index: 999999 !important; }
    </style>
</head>
<body class="bg-light">
    <div id="app">
        <!-- Header Bar -->
        <header class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-2 px-2 px-md-4 mb-3">
            <a class="navbar-brand font-weight-bold text-white d-flex align-items-center mr-auto" href="/gastos" style="font-size: 1rem;">
                <i class="fa fa-home text-warning mr-2 fa-lg"></i>
                <span class="d-none d-sm-inline">Gestión de Gastos e Ingresos Casa</span>
                <span class="d-inline d-sm-none">Gastos Casa</span>
            </a>
            <div class="d-flex align-items-center flex-wrap gap-2">
                @if(Auth::check())
                    <span class="text-white-50 small mr-2 d-none d-md-inline">
                        <i class="fa fa-user-circle"></i> {{ Auth::user()->usuario }} ({{ Auth::user()->idrol }})
                    </span>
                    <a href="/main" class="btn btn-sm btn-outline-light rounded-pill px-2 px-sm-3" style="font-size: 12px;">
                        <i class="fa fa-arrow-left"></i> <span class="d-none d-sm-inline">Volver al Sistema</span><span class="d-inline d-sm-none">Volver</span>
                    </a>
                @else
                    <span class="text-white-50 small mr-2 d-none d-md-inline"><i class="fa fa-globe"></i> Acceso Público Directo</span>
                    <a href="/login" class="btn btn-sm btn-outline-warning rounded-pill px-2 px-sm-3" style="font-size: 12px;">
                        <i class="fa fa-sign-in"></i> Acceder
                    </a>
                @endif
            </div>
        </header>

        <main class="container-fluid px-2 px-md-4 py-1 py-md-2">
            <gastos-casa :user="{{ Auth::check() ? json_encode(Auth::user()) : 'null' }}"></gastos-casa>
        </main>
    </div>

    <script>
        window.APP_URL = "{{ url('/') }}";
    </script>
    <script src="{{ asset('js/app.js?v=' . time()) }}"></script>
    <script src="{{ asset('js/plantilla.js?v=' . time()) }}"></script>
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
            }
        })();
    </script>
</body>
</html>
