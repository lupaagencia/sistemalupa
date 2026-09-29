<div class="sidebar">
        <nav class="sidebar-nav">
            <ul class="nav">
                <li @click="menu=21" class="nav-item">
                    <a class="nav-link active" href="#"><i class="icon-speedometer"></i> Facturas</a>
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
                        <li @click="menu=52" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-clock"></i> Control Asistencia & QR</a>
                        </li>
                        <li @click="menu=49" class="nav-item">
                            <a class="nav-link" href="#"><i class="icon-support"></i> Simulador Contable</a>
                        </li>
                    </ul>
                </li>
               
            </ul>
        </nav>
        <button class="sidebar-minimizer brand-minimizer" type="button"></button>
    </div>