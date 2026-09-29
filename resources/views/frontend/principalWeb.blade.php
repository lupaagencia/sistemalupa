<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description"
        content="Realizamos empaques ecológicos para todo tipo de producto, Comidas, Alimentos empacados, Productos terminados">
    <meta name="author" content="empaqueslupa.com">
    <meta name="keyword" content="Empaques ecológicos, Bolsas, etiquetas, etiquetas adhesivas para producto">
    <link rel="shortcut icon" href="img/favicon.png">
    <title>Empaques Lupa</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Icons -->
    <link href="{{ asset('css/plantilla.css') }}" rel="stylesheet">
</head>

<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden">
    <div id="web">
        @yield('contenido')
    </div>



    <script src="{{ asset('js/web.js') }}"></script>
    <script src="{{ asset('js/plantilla.js') }}"></script>


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