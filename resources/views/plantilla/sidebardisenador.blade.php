<div class="sidebar">
        <nav class="sidebar-nav">
            <ul class="nav">
                <li @click="menu=0" class="nav-item">
                    <a class="nav-link active" href="#"><i class="icon-speedometer"></i> Escritorio</a>
                </li>
                <li @click="menu=24" class="nav-item">
                    <a class="nav-link active" href="#"><i class="icon-speedometer"></i> Registro Producción</a>
                </li>
                <li class="nav-title">
                    Mantenimiento
                </li>
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-bag"></i> Almacén</a>
                    <ul class="nav-dropdown-items">
                        <li @click="menu=1" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-bag"></i> Categorías</a>
                        </li>
                        <li @click="menu=32" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-bag"></i> Tipo Producto</a>
                        </li>
                        <li @click="menu=2" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-bag"></i> Artículos</a>
                        </li>
                        <li @click="menu=23" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-bag"></i> Inventarios</a>
                        </li>
                        <li @click="menu=100" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-bag"></i> Atributos Tienda</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-wallet"></i> Producción</a>
                    <ul class="nav-dropdown-items">
                        <li @click="menu=14" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-wallet"></i> Ordenes de trabajo</a>
                        </li>
                        <li @click="menu=15" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-wallet"></i> Producción</a>
                        </li>
                        <li @click="menu=50" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-calculator"></i> Despiece Cocinas</a>
                        </li>
                        <li @click="menu=13" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-wallet"></i> Insumos y Servicios</a>
                        </li>
                        <li @click="menu=22" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-wallet"></i> Estado de producción</a>
                        </li>
                        <li @click="menu=40" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-chart"></i> Análisis de flujo</a>
                        </li>
                        <li @click="menu=45" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-chart"></i> Seguimiento y Optimización</a>
                        </li>
                    </ul>
                </li>
              
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-wallet"></i> Compras</a>
                    <ul class="nav-dropdown-items">
                        <li @click="menu=3" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-wallet"></i> Ingresos</a>
                        </li>
                        <li @click="menu=4" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-notebook"></i> Proveedores</a>
                        </li>
                       
                        
                    </ul>
                </li>
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-basket"></i> Ventas</a>
                    <ul class="nav-dropdown-items">
                        <li @click="menu=6" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-notebook"></i> Clientes</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-pie-chart"></i> Reportes</a>
                    <ul class="nav-dropdown-items">
                        <li @click="menu=19" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-chart"></i> Reportes de producción</a>
                        </li>
                        <li @click="menu=9" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-chart"></i> Reporte Ingresos</a>
                        </li>
                        <li @click="menu=18" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-chart"></i> Reporte Egresos</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item" @click="menu=20" >
                    <a class="nav-link" href="#"><i class="icon-pie-chart"></i> Ajustes</a>
                </li>
                <li @click="menu=11" class="nav-item">
                    <a class="nav-link" href="#"><i class="icon-book-open"></i> Ayuda <span class="badge badge-danger">PDF</span></a>
                </li>
               
            </ul>
        </nav>
        <button class="sidebar-minimizer brand-minimizer" type="button"></button>
    </div>