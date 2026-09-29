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
                    <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-action-redo"></i> Empresa</a>
                    <ul class="nav-dropdown-items">
                        <li @click="menu=33" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-user"></i> Empleados</a>
                        </li>
                        <li @click="menu=53" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-clock"></i> Control Asistencia & QR</a>
                        </li>
                    </ul>
                </li>
                <li @click="menu=21" class="nav-item">
                    <a class="nav-link" href="#"><i class="icon-bag"></i> Comprobantes</a>
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
                        <li @click="menu=5" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-basket-loaded"></i> Ventas</a>
                        </li>
                        <li @click="menu=60" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-people"></i> CRM & Cotizaciones</a>
                        </li>
                        <li @click="menu=17" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-basket-loaded"></i> Seguimiento ventas</a>
                        </li>
                        <li @click="menu=6" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-notebook"></i> Clientes</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-pie-chart"></i> Financiero</a>
                    <ul class="nav-dropdown-items">
                        <li @click="menu=41" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-wallet"></i> Bancos y Cajas</a>
                        </li>
                        <li @click="menu=16" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-basket-loaded"></i> Cartera (Por Cobrar)</a>
                        </li>
                        <li @click="menu=42" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-calculator"></i> Cuentas por Pagar</a>
                        </li>
                        <li @click="menu=51" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-calendar"></i> Pagos Programados</a>
                        </li>
                        <li @click="menu=43" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-notebook"></i> Gastos y Egresos</a>
                        </li>
                        <li @click="menu=44" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-people"></i> Nómina</a>
                        </li>
                    </ul>
                </li>
              
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-people"></i> Acceso</a>
                    <ul class="nav-dropdown-items">
                      
                        <li @click="menu=7" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-user"></i> Usuarios</a>
                        </li>
                        <li @click="menu=8" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-user-following"></i> Roles</a>
                        </li>
                        <li @click="menu=71" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-user"></i> Actividad de Usuarios</a>
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