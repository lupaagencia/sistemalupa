<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Realizamos empaques ecológicos para todo tipo de producto, Comidas, Alimentos empacados, Productos terminados">
    <meta name="author" content="empaqueslupa.com">
    <meta name="keyword" content="Empaques ecológicos, Bolsas, etiquetas, etiquetas adhesivas para producto">
    <link rel="shortcut icon" href="img/favicon.png">
    <title>Empaques Lupa</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Icons -->
    <link href="css/plantilla.css" rel="stylesheet">
</head>

<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden">
    <div id="web">
        @yield('contenido')
    </div>
    
    

    <script src="js/web.js"></script>
    <script src="js/plantilla.js"></script>
    

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</body>
