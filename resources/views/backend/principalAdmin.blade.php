<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistema de control Empaques Lupa">
    <meta name="author" content="empaqueslupa.com">
    <meta name="keyword" content="Sistema de control Empaques Lupa">
    <link rel="shortcut icon" href="img/favicon.png">
    <title>Sistema Lupa</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Icons -->
    <link href="{{ asset('css/plantilla.css') }}" rel="stylesheet">
    <style>
        /* ==========================================================================
           SOLUCIÓN DEFINITIVA A VENTANAS FLOTANTES EN TODO EL SISTEMA
           ========================================================================== */
        
        /* 1. Forzar a que los modales visibles sean siempre FIXED (fijos en la pantalla del usuario) */
        .modal.mostrar,
        .modal.show,
        .modal.in,
        div.modal.mostrar,
        div.modal.show {
            display: flex !important;
            align-items: flex-start !important;
            justify-content: center !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 10500 !important;
            overflow: hidden !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            opacity: 1 !important;
        }

        /* SweetAlert2 emergente SIEMPRE por delante de los modales (z-index > 10500) */
        .swal2-container,
        div.swal2-container,
        body .swal2-container {
            z-index: 999999 !important;
        }

        /* 2. El cuadro del modal siempre inicia en la parte superior visible (10px) y no se sale hacia abajo */
        .modal-dialog,
        .modal-bajo,
        div.modal-dialog {
            position: relative !important;
            top: 0 !important;
            margin: 10px auto !important;
            max-height: calc(100vh - 20px) !important;
            display: flex !important;
            flex-direction: column !important;
            z-index: 10501 !important;
        }

        /* 3. La tarjeta interior del modal ocupa la altura visible disponible */
        .modal-content {
            max-height: calc(100vh - 20px) !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            border-radius: 8px !important;
            width: 100% !important;
        }

        /* 4. Encabezado fijo arriba */
        .modal-header {
            flex: 0 0 auto !important;
            flex-shrink: 0 !important;
        }

        /* 5. Pie de página con botones (Guardar, Cancelar) FIJO en la parte inferior visible de la pantalla */
        .modal-footer {
            flex: 0 0 auto !important;
            flex-shrink: 0 !important;
            background-color: #f8f9fa !important;
        }

        /* 6. El cuerpo del modal absorbe todo el espacio intermedio y tiene SCROLL VERTICAL AUTOMÁTICO */
        .modal-body {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            max-height: calc(100vh - 120px) !important;
            padding: 20px !important;
        }
    </style>
</head>

<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden">
    <div id="app">
        <header class="app-header navbar">
            <button class="navbar-toggler mobile-sidebar-toggler d-lg-none mr-auto" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="#"></a>
            <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>

            <ul class="nav navbar-nav ml-auto">

                <campana-notificaciones></campana-notificaciones>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle nav-link" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="true" aria-expanded="false">
                        <!-- <img src="img/avatars/6.jpg" class="img-avatar" alt="admin@bootstrapmaster.com"> -->
                        <div class="d-inline-flex flex-column text-right mr-2"
                            style="vertical-align: middle; line-height: 1.2;">
                            <span class="font-weight-bold" style="font-size: 0.85rem;">{{Auth::user()->usuario}}</span>
                            <small class="text-muted" style="font-size: 0.7rem;">{{Auth::user()->idrol}}</small>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-header text-center">
                            <strong>Cuenta</strong>
                        </div>
                        <!-- Calculator inside dropdown for mobile/accessibility -->
                        <div class="dropdown-item d-md-none" @click="showCalculator = !showCalculator"
                            style="cursor: pointer;">
                            <i class="fa fa-calculator text-success"></i> Calculadora
                        </div>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa fa-lock"></i> Cerrar sesión</a>
                        <div class="dropdown-item" @click="menu=30">
                            <i class="fa fa-update"></i> Resetear Contraseña
                        </div>
                        <div class="dropdown-item" @click="menu=31">
                            <i class="fa fa-update"></i> Cambiar Contraseña
                        </div>


                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>
                <!-- Quick Access to Facial & QR Attendance Station -->
                <li class="nav-item mr-2">
                    <a href="/kiosco-asistencia" target="_blank" class="btn btn-primary btn-sm font-weight-bold shadow-sm px-3 text-white rounded-pill" style="display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fa fa-camera"></i> <span>Marcación Facial & QR</span>
                    </a>
                </li>
                <!-- Desktop Calculator -->
                <li class="nav-item d-md-down-none mr-2">
                    <button class="btn btn-outline-success border-0 font-weight-bold"
                        @click="showCalculator = !showCalculator">
                        <i class="fa fa-calculator mr-1"></i> Calculadora
                    </button>
                </li>
            </ul>
        </header>

        <div class="app-body">

            @if(Auth::user()->idrol == 'Administrador' || Auth::user()->idrol == 'Superadministrador')
                @include('plantilla.sidebaradministrador')
            @elseif (Auth::user()->idrol == 'Vendedor')
                @include('plantilla.sidebarvendedor')
            @elseif (Auth::user()->idrol == 'Coordinador')
                @include('plantilla.sidebarcoordinador')
            @elseif (Auth::user()->idrol == 'Diseñador')
                @include('plantilla.sidebardisenador')
            @elseif (Auth::user()->idrol == 'Operario')
                @include('plantilla.sidebaroperario')
            @elseif (Auth::user()->idrol=='Auxiliar producción' || Auth::user()->idrol=='Auxiliar de producción')
                @include('plantilla.sidebarauxiliarproduccion')
            @elseif (Auth::user()->idrol == 'Contador')
                @include('plantilla.sidebarcontador')
            @else
            @endif

            <!-- Contenido Principal -->

            @yield('contenido')
            <!-- /Fin del contenido principal -->
        </div>

        <!-- Floating Calculator -->
        <transition name="fade">
            <div v-show="showCalculator" class="floating-calculator shadow-lg">
                <div class="calculator-top-bar">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-calculator mr-2"></i>
                        <span class="font-weight-bold">Calculadora Rápida</span>
                    </div>
                    <button class="close-calc-btn" @click="showCalculator = false">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="calculator-content-wrapper">
                    <calculotamano></calculotamano>
                </div>
            </div>
        </transition>

        <!-- Chat Widget -->
        <chat-component usuario="{{ Auth::check() ? Auth::user()->usuario : 'Invitado' }}"
            idrol="{{ Auth::check() ? Auth::user()->idrol : '' }}"></chat-component>

    </div>
    <script>
        window.APP_URL = "{{ url('/') }}";
    </script>

    <!-- Styles -->
    <style>
        .floating-calculator {
            position: fixed;
            top: 70px;
            /* Below standard header height */
            right: 20px;
            width: 850px;
            /* Decent width for 2-col layout */
            max-width: 90vw;
            height: auto;
            max-height: 85vh;
            /* Prevent overflow on small screens */
            background: #fff;
            z-index: 9999;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(0, 0, 0, 0.08);
            /* Subtle border */
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .calculator-top-bar {
            background: #1a1a1a;
            /* Dark header to match button */
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
        }

        .close-calc-btn {
            background: transparent;
            border: none;
            color: #ccc;
            font-size: 1.1rem;
            cursor: pointer;
            transition: color 0.2s;
            padding: 0;
            line-height: 1;
        }

        .close-calc-btn:hover {
            color: #fff;
        }

        .calculator-content-wrapper {
            overflow-y: auto;
            flex: 1;
            padding: 0;
            background: white;
        }

        /* Overrides for the component inside the modal */
        /* Make it fit naturally */
        .calculator-content-wrapper .calculator-container {
            padding: 20px !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        .calculator-content-wrapper .header-section {
            display: none !important;
            /* Hide internal title as we have the modal header */
        }

        /* Fade animation */
        .fade-enter-active,
        .fade-leave-active {
            transition: opacity 0.3s, transform 0.3s;
        }

        .fade-enter,
        .fade-leave-to {
            opacity: 0;
            transform: translateY(-10px);
        }
    </style>
    <footer class="app-footer">
        <span><a href="http://www.empaqueslupa.com/">Lupa</a> &copy; 2021</span>
        <span class="ml-auto">Desarrollado por <a href="http://www.empaqueslupa.com/">Lupa</a></span>
    </footer>


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
                if (_Swal.DismissReason) window.swal.DismissReason = _Swal.DismissReason;
            }
        })();
    </script>
</body>