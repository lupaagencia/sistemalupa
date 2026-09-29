<template>
    <main class="main" style="background-color: #f4f6f9; min-height: 100vh; padding-bottom: 40px;">
        <!-- Container Header -->
        <div class="container-fluid pt-3">
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h2 class="font-weight-bold mb-1" style="letter-spacing: -0.5px;">
                                <i class="fa fa-line-chart text-info mr-2"></i>Módulo de Estadísticas & Analítica Comercial
                            </h2>
                            <p class="text-muted mb-0" style="font-size: 0.9rem; color: #94a3b8 !important;">
                                Analiza a fondo el comportamiento de ventas, productos estrella, rendimiento de clientes y retención.
                            </p>
                        </div>
                        
                        <!-- Filter Controls -->
                        <div class="d-flex flex-wrap align-items-center mt-3 mt-md-0 gap-2">
                            <div class="mr-2">
                                <label class="small text-uppercase font-weight-bold text-muted mb-1 d-block">Año</label>
                                <select v-model="filtroYear" @change="cargarEstadisticas" class="form-control form-control-sm custom-select-dark">
                                    <option v-for="y in listaYears" :key="y" :value="y">{{ y }}</option>
                                </select>
                            </div>
                            
                            <div class="mr-2">
                                <label class="small text-uppercase font-weight-bold text-muted mb-1 d-block">Mes</label>
                                <select v-model="filtroMonth" @change="cargarEstadisticas" class="form-control form-control-sm custom-select-dark">
                                    <option :value="null">Todos los meses (Año entero)</option>
                                    <option v-for="(mNombre, mNum) in mesesNombres" :key="mNum" :value="mNum">{{ mNombre }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="small text-uppercase font-weight-bold text-muted mb-1 d-block">&nbsp;</label>
                                <button @click="cargarEstadisticas" class="btn btn-info btn-sm font-weight-bold px-3" style="border-radius: 6px; height: 31px;">
                                    <i class="fa fa-refresh mr-1"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Cargando datos...</span>
                </div>
                <h5 class="text-muted mt-3">Calculando estadísticas y analítica de datos...</h5>
            </div>

            <div v-else>
                <!-- KPI CARDS ROW -->
                <div class="row">
                    <!-- Card 1: Ventas Totales -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100 kpi-card bg-gradient-primary">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-uppercase font-weight-bold small text-white-50">Ventas Totales</span>
                                        <h3 class="font-weight-bold mb-0 text-white mt-1">${{ formatNumber(totales.total_ventas) }}</h3>
                                        <div class="mt-2 text-white-50 small">
                                            <span :class="totales.variacion_porcentaje >= 0 ? 'text-success-light' : 'text-warning-light'" class="font-weight-bold mr-1">
                                                <i :class="totales.variacion_porcentaje >= 0 ? 'fa fa-arrow-up' : 'fa fa-arrow-down'"></i>
                                                {{ totales.variacion_porcentaje }}%
                                            </span>
                                            vs. período anterior
                                        </div>
                                    </div>
                                    <div class="kpi-icon-wrapper bg-white-20">
                                        <i class="fa fa-dollar text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Total Pedidos -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100 kpi-card bg-gradient-success">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-uppercase font-weight-bold small text-white-50">Total Pedidos / Facturas</span>
                                        <h3 class="font-weight-bold mb-0 text-white mt-1">{{ totales.total_pedidos }}</h3>
                                        <div class="mt-2 text-white-50 small">
                                            Órdenes de compra procesadas
                                        </div>
                                    </div>
                                    <div class="kpi-icon-wrapper bg-white-20">
                                        <i class="fa fa-shopping-cart text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Ticket Promedio -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100 kpi-card bg-gradient-warning">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-uppercase font-weight-bold small text-white-50">Ticket Promedio / Pedido</span>
                                        <h3 class="font-weight-bold mb-0 text-white mt-1">${{ formatNumber(totales.ticket_promedio) }}</h3>
                                        <div class="mt-2 text-white-50 small">
                                            Facturación promedio por orden
                                        </div>
                                    </div>
                                    <div class="kpi-icon-wrapper bg-white-20">
                                        <i class="fa fa-bar-chart text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Cliente Estrella -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100 kpi-card bg-gradient-danger">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div style="max-width: 80%;">
                                        <span class="text-uppercase font-weight-bold small text-white-50">Cliente Top Aportación</span>
                                        <h5 class="font-weight-bold text-truncate text-white mb-0 mt-1" :title="clienteEstrella.nombre_cliente">
                                            {{ clienteEstrella.nombre_cliente || 'N/A' }}
                                        </h5>
                                        <div class="mt-2 text-white-50 small font-weight-bold">
                                            Aporta el {{ clienteEstrella.porcentaje_aportacion || 0 }}% de la facturación
                                        </div>
                                    </div>
                                    <div class="kpi-icon-wrapper bg-white-20">
                                        <i class="fa fa-trophy text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NAVIGATION TABS -->
                <ul class="nav nav-pills nav-fill bg-white p-2 rounded shadow-sm mb-3 border">
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" :class="{ active: tabActiva === 'resumen' }" href="#" @click.prevent="tabActiva = 'resumen'">
                            <i class="fa fa-dashboard mr-1"></i> Tendencia & Resumen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" :class="{ active: tabActiva === 'clientes' }" href="#" @click.prevent="tabActiva = 'clientes'">
                            <i class="fa fa-users mr-1"></i> Analítica de Clientes ({{ clientesTop.length }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" :class="{ active: tabActiva === 'productos' }" href="#" @click.prevent="tabActiva = 'productos'">
                            <i class="fa fa-cubes mr-1"></i> Productos & Referencias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold text-danger" :class="{ active: tabActiva === 'inactivos' }" href="#" @click.prevent="tabActiva = 'inactivos'">
                            <i class="fa fa-exclamation-triangle mr-1"></i> Clientes Inactivos ({{ clientesInactivos.length }})
                        </a>
                    </li>
                </ul>

                <!-- TAB 1: RESUMEN Y TENDENCIA MENSUAL -->
                <div v-if="tabActiva === 'resumen'">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-header bg-white font-weight-bold d-flex align-items-center justify-content-between border-bottom py-3">
                            <span class="text-primary font-weight-bold" style="font-size: 1.05rem;">
                                <i class="fa fa-area-chart mr-2"></i>Evolución y Comportamiento de Ventas Mensuales ({{ filtroYear }})
                            </span>
                            <span class="badge badge-primary px-3 py-2">Total Año: ${{ formatNumber(totales.total_ventas) }}</span>
                        </div>
                        <div class="card-body p-4">
                            <!-- Visual Bar Chart representation -->
                            <div class="row align-items-end mb-3" style="min-height: 220px;">
                                <div v-for="m in tendenciaMensual" :key="m.mes_num" class="col text-center d-flex flex-column align-items-center">
                                    <div class="small font-weight-bold text-dark mb-1" style="font-size: 0.75rem;">
                                        ${{ formatShortNumber(m.total) }}
                                    </div>
                                    <div class="w-100 rounded-top bg-gradient-info transition-all position-relative" 
                                         :style="{ height: getBarHeight(m.total) + 'px', minHeight: '6px' }"
                                         :title="m.mes_nombre + ': $' + formatNumber(m.total) + ' (' + m.pedidos + ' pedidos)'">
                                    </div>
                                    <span class="small font-weight-bold text-muted mt-2 d-block text-truncate w-100" style="font-size: 0.75rem;">
                                        {{ m.mes_nombre.substring(0, 3) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Side by side preview tables -->
                    <div class="row">
                        <!-- Top 5 Clientes Preview -->
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                                <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-user-circle text-info mr-2"></i>Top 5 Clientes en Ventas</span>
                                    <a href="#" @click.prevent="tabActiva = 'clientes'" class="small text-info">Ver todos →</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th class="text-right">Monto ($)</th>
                                                    <th class="text-right">% Aportación</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(c, idx) in clientesTop.slice(0, 5)" :key="idx">
                                                    <td class="font-weight-bold">{{ c.nombre_cliente }}</td>
                                                    <td class="text-right font-weight-bold text-success">${{ formatNumber(c.total_comprado) }}</td>
                                                    <td class="text-right">
                                                        <div class="d-flex align-items-center justify-content-end">
                                                            <span class="mr-2 font-weight-bold">{{ c.porcentaje_aportacion }}%</span>
                                                            <div class="progress" style="width: 50px; height: 6px;">
                                                                <div class="progress-bar bg-info" :style="{ width: c.porcentaje_aportacion + '%' }"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top 5 Productos Preview -->
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                                <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-cube text-warning mr-2"></i>Top 5 Productos Estrella ($)</span>
                                    <a href="#" @click.prevent="tabActiva = 'productos'" class="small text-info">Ver todos →</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Producto / Referencia</th>
                                                    <th class="text-right">Cant.</th>
                                                    <th class="text-right">Ingresos ($)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(p, idx) in productosPorIngreso.slice(0, 5)" :key="idx">
                                                    <td class="font-weight-bold">{{ p.nombre_producto }}</td>
                                                    <td class="text-right font-weight-bold text-primary">{{ formatNumber(p.cantidad_total) }}</td>
                                                    <td class="text-right font-weight-bold text-success">${{ formatNumber(p.ingresos_totales) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: ANALÍTICA COMPLETA DE CLIENTES -->
                <div v-if="tabActiva === 'clientes'">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between border-bottom">
                            <div>
                                <h5 class="font-weight-bold text-dark mb-0">Ranking y Comportamiento de Clientes</h5>
                                <span class="small text-muted">Ventas acumuladas y porcentaje de representación en el período seleccionado.</span>
                            </div>
                            <div class="search-container mt-2 mt-sm-0" style="width: 250px;">
                                <input type="text" v-model="buscarCliente" placeholder="Buscar cliente..." class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th class="text-center">Total Pedidos</th>
                                            <th class="text-right">Facturación ($)</th>
                                            <th class="text-right">% Aportación al Ingreso</th>
                                            <th class="text-center">Última Compra</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(c, idx) in clientesFiltrados" :key="idx">
                                            <td class="font-weight-bold text-muted">{{ idx + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ c.nombre_cliente }}</td>
                                            <td class="text-center font-weight-bold">{{ c.total_pedidos }}</td>
                                            <td class="text-right font-weight-bold text-success">${{ formatNumber(c.total_comprado) }}</td>
                                            <td class="text-right">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <span class="mr-2 font-weight-bold">{{ c.porcentaje_aportacion }}%</span>
                                                    <div class="progress" style="width: 70px; height: 8px;">
                                                        <div class="progress-bar bg-gradient-info" :style="{ width: c.porcentaje_aportacion + '%' }"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ c.ultima_compra || 'N/A' }}</td>
                                            <td class="text-center">
                                                <span v-if="c.dias_desde_ultima <= 30" class="badge badge-success px-2 py-1">Activo ({{ c.dias_desde_ultima }} días)</span>
                                                <span v-else-if="c.dias_desde_ultima <= 60" class="badge badge-warning px-2 py-1">Riesgo ({{ c.dias_desde_ultima }} días)</span>
                                                <span v-else class="badge badge-danger px-2 py-1">Inactivo ({{ c.dias_desde_ultima }} días)</span>
                                            </td>
                                        </tr>
                                        <tr v-if="clientesFiltrados.length === 0">
                                            <td colspan="7" class="text-center py-4 text-muted">No se encontraron clientes registrados en este período.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: PRODUCTOS Y REFERENCIAS -->
                <div v-if="tabActiva === 'productos'">
                    <div class="row">
                        <!-- Productos por Ingresos ($) -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                                <div class="card-header bg-white py-3 border-bottom">
                                    <h5 class="font-weight-bold text-primary mb-0">
                                        <i class="fa fa-dollar mr-2"></i>Productos con Mayor Aportación Financiera ($)
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Referencia / Producto</th>
                                                    <th class="text-right">Facturación ($)</th>
                                                    <th class="text-right">% Aportación</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(p, i) in productosPorIngreso" :key="i">
                                                    <td class="font-weight-bold text-muted">{{ i + 1 }}</td>
                                                    <td>
                                                        <div class="font-weight-bold text-dark">{{ p.nombre_producto }}</div>
                                                        <small class="text-muted">Ref: {{ p.codigo_referencia }}</small>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success">${{ formatNumber(p.ingresos_totales) }}</td>
                                                    <td class="text-right font-weight-bold">{{ p.porcentaje_aportacion }}%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos por Cantidad / Volumen -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                                <div class="card-header bg-white py-3 border-bottom">
                                    <h5 class="font-weight-bold text-success mb-0">
                                        <i class="fa fa-cubes mr-2"></i>Productos Más Vendidos por Volumen (Unidades)
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" style="font-size: 0.85rem;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Referencia / Producto</th>
                                                    <th class="text-right">Unidades Vendidas</th>
                                                    <th class="text-right">Precio Promedio</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(p, i) in productosPorVolumen" :key="i">
                                                    <td class="font-weight-bold text-muted">{{ i + 1 }}</td>
                                                    <td>
                                                        <div class="font-weight-bold text-dark">{{ p.nombre_producto }}</div>
                                                        <small class="text-muted">Ref: {{ p.codigo_referencia }}</small>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-primary">{{ formatNumber(p.cantidad_total) }}</td>
                                                    <td class="text-right">${{ formatNumber(p.precio_promedio) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: CLIENTES INACTIVOS / ALERTA DE DESERCIÓN -->
                <div v-if="tabActiva === 'inactivos'">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <h5 class="font-weight-bold text-danger mb-0">
                                    <i class="fa fa-exclamation-triangle mr-2"></i>Clientes Inactivos (Alerta de Riesgo de Deserción)
                                </h5>
                                <span class="small text-muted">Clientes que realizaron compras en el pasado pero llevan más de 30 días sin solicitar pedidos.</span>
                            </div>
                            <div class="search-container mt-2 mt-sm-0" style="width: 250px;">
                                <input type="text" v-model="buscarInactivo" placeholder="Filtrar cliente inactivo..." class="form-control form-control-sm border-danger">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                    <thead class="bg-danger text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th class="text-center">Total Histórico Compras</th>
                                            <th class="text-right">Monto Histórico ($)</th>
                                            <th class="text-center">Frecuencia Promedio</th>
                                            <th class="text-center">Última Compra Registrada</th>
                                            <th class="text-center">Días Inactivo</th>
                                            <th class="text-center">Estado Fidelidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(cl, idx) in clientesInactivosFiltrados" :key="idx">
                                            <td class="font-weight-bold text-muted">{{ idx + 1 }}</td>
                                            <td class="font-weight-bold text-dark">{{ cl.nombre_cliente }}</td>
                                            <td class="text-center font-weight-bold">{{ cl.total_pedidos }} pedidos</td>
                                            <td class="text-right font-weight-bold text-success">${{ formatNumber(cl.total_historico) }}</td>
                                            <td class="text-center">{{ cl.frecuencia_promedio_dias > 0 ? 'Cada ' + cl.frecuencia_promedio_dias + ' días' : '1 sola compra' }}</td>
                                            <td class="text-center">{{ cl.ultima_compra || 'N/A' }}</td>
                                            <td class="text-center font-weight-bold text-danger" style="font-size: 1rem;">
                                                {{ cl.dias_inactivo }} días
                                            </td>
                                            <td class="text-center">
                                                <span v-if="cl.dias_inactivo > 90" class="badge badge-danger px-3 py-1">Inactivo (>90 días)</span>
                                                <span v-else-if="cl.dias_inactivo > 60" class="badge badge-warning px-3 py-1">Riesgo Alto (60-90 días)</span>
                                                <span v-else class="badge badge-info px-3 py-1">Riesgo Moderado (30-60 días)</span>
                                            </td>
                                        </tr>
                                        <tr v-if="clientesInactivosFiltrados.length === 0">
                                            <td colspan="8" class="text-center py-4 text-muted">No hay clientes inactivos con los criterios seleccionados.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</template>

<script>
export default {
    data() {
        return {
            loading: true,
            tabActiva: 'resumen',
            filtroYear: new Date().getFullYear(),
            filtroMonth: null,
            listaYears: [2026, 2025, 2024, 2023, 2022],
            mesesNombres: {
                1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril',
                5: 'Mayo', 6: 'Junio', 7: 'Julio', 8: 'Agosto',
                9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
            },
            totales: {
                total_ventas: 0,
                total_subtotal: 0,
                total_pedidos: 0,
                ticket_promedio: 0,
                ventas_anteriores: 0,
                variacion_porcentaje: 0
            },
            clientesTop: [],
            productosPorVolumen: [],
            productosPorIngreso: [],
            clientesInactivos: [],
            tendenciaMensual: [],
            buscarCliente: '',
            buscarInactivo: ''
        };
    },
    computed: {
        clienteEstrella() {
            return this.clientesTop.length > 0 ? this.clientesTop[0] : {};
        },
        clientesFiltrados() {
            if (!this.buscarCliente || this.buscarCliente.trim() === '') {
                return this.clientesTop;
            }
            let term = this.buscarCliente.toLowerCase();
            return this.clientesTop.filter(c => c.nombre_cliente.toLowerCase().includes(term));
        },
        clientesInactivosFiltrados() {
            if (!this.buscarInactivo || this.buscarInactivo.trim() === '') {
                return this.clientesInactivos;
            }
            let term = this.buscarInactivo.toLowerCase();
            return this.clientesInactivos.filter(c => c.nombre_cliente.toLowerCase().includes(term));
        }
    },
    mounted() {
        this.cargarEstadisticas();
    },
    methods: {
        cargarEstadisticas() {
            let me = this;
            me.loading = true;
            let url = me.getUrl('/estadisticas-ventas/resumen?year=' + me.filtroYear + (me.filtroMonth ? '&month=' + me.filtroMonth : ''));
            
            axios.get(url).then(function (response) {
                let data = response.data;
                me.totales = data.totales;
                me.clientesTop = data.clientes_top || [];
                me.productosPorVolumen = data.productos_por_volumen || [];
                me.productosPorIngreso = data.productos_por_ingreso || [];
                me.clientesInactivos = data.clientes_inactivos || [];
                me.tendenciaMensual = data.tendencia_mensual || [];
                me.loading = false;
            })
            .catch(function (error) {
                console.log(error);
                me.loading = false;
            });
        },
        formatNumber(value) {
            if (!value) return '0';
            return parseFloat(value).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },
        formatShortNumber(val) {
            if (!val || val === 0) return '0';
            if (val >= 1000000) {
                return (val / 1000000).toFixed(1) + 'M';
            }
            if (val >= 1000) {
                return (val / 1000).toFixed(0) + 'k';
            }
            return val.toFixed(0);
        },
        getBarHeight(val) {
            let max = Math.max(...this.tendenciaMensual.map(m => m.total), 1);
            if (max === 0) return 6;
            let percent = (val / max) * 160;
            return Math.max(percent, 6);
        }
    }
};
</script>

<style scoped>
.custom-select-dark {
    background-color: #334155;
    color: #ffffff;
    border: 1px solid #475569;
    border-radius: 6px;
}
.custom-select-dark:focus {
    background-color: #1e293b;
    color: #ffffff;
}
.kpi-card {
    border-radius: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
}
.bg-gradient-danger {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}
.bg-gradient-info {
    background: linear-gradient(180deg, #0284c7 0%, #0369a1 100%);
}
.kpi-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.bg-white-20 {
    background: rgba(255, 255, 255, 0.2);
}
.text-success-light {
    color: #86efac;
}
.text-warning-light {
    color: #fde047;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>
