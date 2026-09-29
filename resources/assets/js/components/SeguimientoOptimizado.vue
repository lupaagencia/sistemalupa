<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Seguimiento y Optimización de Producción</li>
        </ol>

        <div class="container-fluid">
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4" id="trackingTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: activeTab === 'stats' }" @click="setTab('stats')" href="#" role="tab">
                        <i class="fa fa-clock-o text-primary mr-1"></i> Tiempos de Entrega y Retardo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: activeTab === 'progress' }" @click="setTab('progress')" href="#" role="tab">
                        <i class="fa fa-tasks text-success mr-1"></i> Progreso en Tiempo Real (Pipeline)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: activeTab === 'optimization' }" @click="setTab('optimization')" href="#" role="tab">
                        <i class="fa fa-magic text-warning mr-1"></i> Proyección de Optimización
                    </a>
                </li>
            </ul>

            <!-- Tab Contents -->
            <div class="tab-content" style="border: none; background: transparent; padding: 0;">
                
                <!-- TAB 1: DELIVERY STATS -->
                <div v-if="activeTab === 'stats'" class="tab-pane active">
                    <!-- Filters -->
                    <div class="card shadow-sm border-0 mb-4 card-gradient-primary">
                        <div class="card-body py-3">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="font-weight-bold text-dark small">Fecha Inicio</label>
                                    <input type="date" v-model="filters.fecha_inicio" class="form-control form-control-sm border-0 shadow-xs" @change="loadDeliveryStats">
                                </div>
                                <div class="col-md-3">
                                    <label class="font-weight-bold text-dark small">Fecha Fin</label>
                                    <input type="date" v-model="filters.fecha_fin" class="form-control form-control-sm border-0 shadow-xs" @change="loadDeliveryStats">
                                </div>
                                <div class="col-md-3">
                                    <label class="font-weight-bold text-dark small">Mínimo Pedidos por Producto</label>
                                    <select v-model="filters.min_pedidos" class="form-control form-control-sm border-0 shadow-xs" @change="loadDeliveryStats">
                                        <option :value="1">1 o más pedidos</option>
                                        <option :value="3">3 o más pedidos</option>
                                        <option :value="5">5 o más pedidos</option>
                                        <option :value="10">10 o más pedidos</option>
                                    </select>
                                </div>
                                <div class="col-md-3 text-right">
                                    <button class="btn btn-primary btn-sm px-4 shadow-sm" @click="loadDeliveryStats">
                                        <i class="fa fa-refresh"></i> Recargar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overall Summary Cards -->
                    <div class="row mb-4" v-if="distribution">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center py-4 bg-white hover-up card-border-left-primary">
                                <div class="text-muted small uppercase font-weight-bold mb-1">Pedidos Analizados</div>
                                <div class="h3 font-weight-bold text-primary mb-0">{{ distribution.total_pedidos }}</div>
                                <div class="small text-muted mt-1">Órdenes entregadas</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center py-4 bg-white hover-up card-border-left-success">
                                <div class="text-muted small uppercase font-weight-bold mb-1">Promedio General</div>
                                <div class="h3 font-weight-bold text-success mb-0">{{ distribution.promedio_general }} días</div>
                                <div class="small text-muted mt-1">Tiempo de entrega medio</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm bg-white p-4">
                                <div class="text-muted small uppercase font-weight-bold mb-3 text-left">Distribución de Tiempos de Entrega</div>
                                <div class="row">
                                    <!-- 1 week -->
                                    <div class="col-sm-3 text-center">
                                        <div class="font-weight-bold text-success mb-1">{{ getPercentage(distribution.en_1_semana, distribution.total_pedidos) }}%</div>
                                        <div class="progress progress-xs mb-2">
                                            <div class="progress-bar bg-success" :style="{ width: getPercentage(distribution.en_1_semana, distribution.total_pedidos) + '%' }"></div>
                                        </div>
                                        <span class="small text-muted">&le; 7 días</span>
                                    </div>
                                    <!-- 2 weeks -->
                                    <div class="col-sm-3 text-center">
                                        <div class="font-weight-bold text-info mb-1">{{ getPercentage(distribution.en_2_semanas, distribution.total_pedidos) }}%</div>
                                        <div class="progress progress-xs mb-2">
                                            <div class="progress-bar bg-info" :style="{ width: getPercentage(distribution.en_2_semanas, distribution.total_pedidos) + '%' }"></div>
                                        </div>
                                        <span class="small text-muted">8-15 días</span>
                                    </div>
                                    <!-- 4 weeks -->
                                    <div class="col-sm-3 text-center">
                                        <div class="font-weight-bold text-warning mb-1">{{ getPercentage(distribution.en_un_mes, distribution.total_pedidos) }}%</div>
                                        <div class="progress progress-xs mb-2">
                                            <div class="progress-bar bg-warning" :style="{ width: getPercentage(distribution.en_un_mes, distribution.total_pedidos) + '%' }"></div>
                                        </div>
                                        <span class="small text-muted">16-30 días</span>
                                    </div>
                                    <!-- > 30 days -->
                                    <div class="col-sm-3 text-center">
                                        <div class="font-weight-bold text-danger mb-1">{{ getPercentage(distribution.mas_de_un_mes, distribution.total_pedidos) }}%</div>
                                        <div class="progress progress-xs mb-2">
                                            <div class="progress-bar bg-danger" :style="{ width: getPercentage(distribution.mas_de_un_mes, distribution.total_pedidos) + '%' }"></div>
                                        </div>
                                        <span class="small text-muted">&gt; 30 días</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-dark text-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-list-ol mr-1"></i> Ranking de Demora por Producto</span>
                            <span class="badge badge-info px-2 py-1">{{ products.length }} Productos listados</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle mb-0 text-left">
                                    <thead class="bg-light text-secondary small uppercase">
                                        <tr>
                                            <th class="py-3 px-4" style="width: 5%">#</th>
                                            <th class="py-3" style="width: 40%">Producto</th>
                                            <th class="py-3 text-center" style="width: 15%">Pedidos Entregados</th>
                                            <th class="py-3 text-center" style="width: 15%">Promedio Demora</th>
                                            <th class="py-3 text-center" style="width: 10%">Mínimo</th>
                                            <th class="py-3 text-center" style="width: 15%">Máximo Histórico</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <tr v-for="(p, index) in products" :key="p.articulo_id">
                                            <td class="py-3 px-4 font-weight-bold text-secondary">{{ index + 1 }}</td>
                                            <td class="py-3 font-weight-bold text-dark">{{ p.producto }}</td>
                                            <td class="py-3 text-center"><span class="badge badge-light px-3 py-2 font-weight-bold text-secondary">{{ p.total_pedidos }}</span></td>
                                            <td class="py-3 text-center">
                                                <div class="d-inline-block">
                                                    <span class="font-weight-bold text-dark">{{ p.promedio_dias }} días</span>
                                                    <div class="progress progress-xs mt-1" style="width: 80px;">
                                                        <div class="progress-bar" :class="getBarClass(p.promedio_dias)" :style="{ width: getPercentage(p.promedio_dias, maxAvgProductDays) + '%' }"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 text-center text-success font-weight-bold">{{ p.minimo_dias }} días</td>
                                            <td class="py-3 text-center text-danger font-weight-bold">{{ p.maximo_dias }} días</td>
                                        </tr>
                                        <tr v-if="products.length === 0">
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fa fa-folder-open-o h2 d-block mb-3 text-secondary"></i>
                                                No se encontraron registros de entregas en este rango de fechas.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: ACTIVE PIPELINE PROGRESS -->
                <div v-if="activeTab === 'progress'" class="tab-pane active">
                    <!-- Dashboard Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center py-4 bg-white card-border-left-primary">
                                <div class="text-muted small uppercase font-weight-bold mb-1">Pedidos en Producción</div>
                                <div class="h3 font-weight-bold text-primary mb-0">{{ activeOrdersTotal }}</div>
                                <div class="small text-muted mt-1">Activos en planta</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center py-4 bg-white card-border-left-danger">
                                <div class="text-muted small uppercase font-weight-bold mb-1">Pedidos Críticos / Atrasados</div>
                                <div class="h3 font-weight-bold text-danger mb-0">{{ activeCriticalOrders }}</div>
                                <div class="small text-muted mt-1">Llevan más de 20 días activos</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm bg-white p-3 d-flex flex-row justify-content-around align-items-center" style="height: 100%;">
                                <div class="text-center" v-for="(list, name) in pipeline" :key="name" v-if="list.length > 0">
                                    <div class="h4 font-weight-bold text-dark mb-0">{{ list.length }}</div>
                                    <span class="small text-secondary">{{ name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pipeline Board Layout -->
                    <div class="row pipeline-board flex-row flex-nowrap" style="overflow-x: auto; padding-bottom: 20px;">
                        <div v-for="(ordersList, stageName) in pipeline" :key="stageName" class="col-md-3 pipeline-column">
                            <div class="card border-0 shadow-sm bg-light" style="min-height: 500px; border-radius: 8px;">
                                <div class="card-header border-0 d-flex justify-content-between align-items-center py-3 bg-white shadow-xs" style="border-radius: 8px 8px 0 0;">
                                    <span class="font-weight-bold text-dark">{{ stageName }}</span>
                                    <span class="badge badge-pill badge-primary font-weight-bold" :class="getBadgeClassForStage(stageName)">{{ ordersList.length }}</span>
                                </div>
                                <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                                    
                                    <!-- Order Card -->
                                    <div v-for="order in ordersList" :key="order.id" class="card border-0 shadow-xs mb-2 p-3 bg-white hover-up order-card" :class="{ 'critical-border': order.dias_activo >= 20 }">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge badge-light text-secondary font-weight-bold small">Orden #{{ order.id }}</span>
                                            <span class="badge" :class="getPriorityClass(order.prioridad)">
                                                {{ order.prioridad === 3 ? 'Alta' : order.prioridad === 2 ? 'Media' : 'Baja' }}
                                            </span>
                                        </div>
                                        <div class="font-weight-bold text-dark mb-1 small">{{ order.producto }}</div>
                                        <div class="text-secondary small mb-2"><i class="fa fa-user-o mr-1"></i> {{ order.cliente }}</div>
                                        
                                        <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center small">
                                            <span class="text-muted"><i class="fa fa-calendar-check-o mr-1"></i> Prometido: {{ order.fecha_entrega_prometida || 'N/A' }}</span>
                                            <span class="font-weight-bold" :class="getAgeDaysClass(order.dias_activo)">
                                                <i class="fa fa-clock-o mr-1"></i> {{ order.dias_activo }} días
                                            </span>
                                        </div>

                                        <div v-if="order.status_observaciones" class="mt-2 p-2 bg-light rounded text-secondary" style="font-size: 11px;">
                                            <i class="fa fa-sticky-note-o text-muted mr-1"></i> {{ order.status_observaciones }}
                                        </div>
                                    </div>

                                    <div v-if="ordersList.length === 0" class="text-center py-5 text-muted small">
                                        <i class="fa fa-check-circle-o text-muted h4 d-block mb-2"></i>
                                        Sin pedidos en este proceso
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: PRINT OPTIMIZATION PROJECTIONS -->
                <div v-if="activeTab === 'optimization'" class="tab-pane active">
                    <!-- Overview Savings metrics -->
                    <div class="row mb-4" v-if="optimizationStats && optimizationStats.summary">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center py-4 bg-white card-border-left-warning">
                                <div class="text-muted small uppercase font-weight-bold mb-1">Ahorro de Tiempo de Setup</div>
                                <div class="h3 font-weight-bold text-warning mb-0">{{ optimizationStats.summary.total_setup_hours_saved }} hrs</div>
                                <div class="small text-muted mt-1">{{ optimizationStats.summary.total_setup_minutes_saved }} min totales ahorrados</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center py-4 bg-white card-border-left-success">
                                <div class="text-muted small uppercase font-weight-bold mb-1">Ahorro de Merma de Papel</div>
                                <div class="h3 font-weight-bold text-success mb-0">{{ optimizationStats.summary.total_sheets_saved }} hojas</div>
                                <div class="small text-muted mt-1">Hojas de prueba / cuadre evitadas</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center py-4 bg-white card-border-left-primary">
                                <div class="text-muted small uppercase font-weight-bold mb-1">Tiradas Agrupables</div>
                                <div class="h3 font-weight-bold text-primary mb-0">{{ optimizationStats.summary.total_suggestions }}</div>
                                <div class="small text-muted mt-1">Montajes óptimos detectados</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm text-center py-4 bg-white card-border-left-dark">
                                <div class="text-muted small uppercase font-weight-bold mb-1">Pedidos Optimizables</div>
                                <div class="h3 font-weight-bold text-dark mb-0">{{ optimizationStats.summary.total_jobs_optimized }}</div>
                                <div class="small text-muted mt-1">De un total en cola de impresión</div>
                            </div>
                        </div>
                    </div>

                    <!-- Suggestions & Scheduling -->
                    <div class="row">
                        <!-- Left side: Recommended group runs -->
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-dark text-white border-0 py-3 font-weight-bold">
                                    <i class="fa fa-magic mr-1"></i> Proyecciones de Agrupación (Gang-Run Printing)
                                </div>
                                <div class="card-body p-4 bg-light">
                                    <p class="text-secondary small mb-4">El algoritmo analiza los trabajos activos en cola de impresión y propone agrupar los que comparten el mismo <strong>tipo de papel</strong> y <strong>plancha (máquina)</strong> para ejecutarse secuencialmente, ahorrando tiempo de cambio y pruebas.</p>

                                    <!-- Group Cards -->
                                    <div v-for="sug in optimizationStats.suggestions" :key="sug.id" class="card border-0 shadow-xs mb-3 bg-white p-3 rounded" style="border-left: 5px solid #ffc107 !important;">
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                            <div>
                                                <h6 class="font-weight-bold text-dark mb-0">Grupo #{{ sug.id }} - Máquina/Plancha: {{ sug.plancha }}</h6>
                                                <span class="badge badge-light text-secondary px-2 mt-1 small">Material: {{ sug.papel }}</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-warning text-dark font-weight-bold mr-1 px-2 py-1">⏱ Ahorro: {{ sug.tiempo_setup_ahorrado }} min</span>
                                                <span class="badge badge-success font-weight-bold px-2 py-1">📄 Hojas Ahorradas: {{ sug.hojas_merma_ahorradas }}</span>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0 text-left" style="font-size: 11px;">
                                                <thead class="bg-light text-dark font-weight-bold">
                                                    <tr>
                                                        <th>Orden</th>
                                                        <th>Producto</th>
                                                        <th>Cliente</th>
                                                        <th class="text-center">Cantidad</th>
                                                        <th class="text-center">Cabida</th>
                                                        <th class="text-center">Hojas Req.</th>
                                                        <th>Tintas / Pantone</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="job in sug.trabajos" :key="job.id">
                                                        <td class="font-weight-bold text-primary">#{{ job.id }}</td>
                                                        <td class="font-weight-bold text-dark">{{ job.producto }}</td>
                                                        <td>{{ job.cliente }}</td>
                                                        <td class="text-center font-weight-bold">{{ job.cantidad }}</td>
                                                        <td class="text-center">{{ job.cabida }}</td>
                                                        <td class="text-center bg-light font-weight-bold">{{ job.hojas_requeridas }}</td>
                                                        <td>
                                                            <span v-for="ink in job.tintas" :key="ink" class="badge badge-light text-dark mr-1 border">
                                                                {{ ink }}
                                                            </span>
                                                            <span v-if="job.tintas.length === 0" class="text-muted">Ninguna</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-3 text-secondary small d-flex justify-content-between align-items-center">
                                            <span>Cabida del montaje / tamaño hojas: <strong>{{ sug.trabajos[0].medida_material || 'N/A' }}</strong></span>
                                            <span class="font-weight-bold text-dark">Tirada máxima requerida en este grupo: {{ sug.max_hojas_run }} hojas imp.</span>
                                        </div>
                                    </div>

                                    <div v-if="optimizationStats.suggestions && optimizationStats.suggestions.length === 0" class="text-center py-5 text-muted">
                                        <i class="fa fa-info-circle h2 d-block mb-3 text-secondary"></i>
                                        No hay suficientes órdenes activas compatibles para agrupar en este momento.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right side: Sequence print scheduling queue -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-dark text-white border-0 py-3 font-weight-bold">
                                    <i class="fa fa-list-ol mr-1"></i> Secuencia Prioritaria de Impresión
                                </div>
                                <div class="card-body p-0">
                                    <p class="p-3 text-secondary small mb-0 bg-light border-bottom">Cola de impresión general ordenada de forma inteligente (prioridad asignada y fecha de entrega comprometida más urgente primero).</p>
                                    
                                    <div style="max-height: 550px; overflow-y: auto;">
                                        <div v-for="(job, index) in optimizationStats.scheduled_jobs" :key="job.id" class="p-3 border-bottom d-flex align-items-center bg-white hover-up text-left">
                                            <div class="mr-3 font-weight-bold text-secondary text-center" style="width: 25px;">
                                                {{ index + 1 }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="font-weight-bold text-dark small">{{ job.producto }}</span>
                                                    <span class="badge" :class="getPriorityClass(job.prioridad)">
                                                        #{{ job.id }} - {{ job.prioridad === 3 ? 'Alta' : job.prioridad === 2 ? 'Media' : 'Baja' }}
                                                    </span>
                                                </div>
                                                <div class="text-secondary mb-1" style="font-size: 11px;">
                                                    Cliente: {{ job.cliente }} | Papel: {{ job.papel }}
                                                </div>
                                                <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
                                                    <span>Hojas: <strong>{{ job.hojas_requeridas }}</strong> (Cant: {{ job.cantidad }})</span>
                                                    <span class="text-danger font-weight-bold" v-if="job.fecha_entrega">Entrega: {{ job.fecha_entrega }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="optimizationStats.scheduled_jobs && optimizationStats.scheduled_jobs.length === 0" class="text-center py-5 text-muted">
                                            No hay trabajos de impresión pendientes en cola.
                                        </div>
                                    </div>
                                </div>
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
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            activeTab: 'stats',
            products: [],
            distribution: null,
            pipeline: {},
            optimizationStats: {
                suggestions: [],
                scheduled_jobs: [],
                summary: null
            },
            filters: {
                fecha_inicio: '2024-01-01',
                fecha_fin: new Date().toISOString().split('T')[0],
                min_pedidos: 3
            }
        }
    },
    computed: {
        maxAvgProductDays() {
            if (this.products.length === 0) return 1;
            return Math.max(...this.products.map(p => p.promedio_dias), 1);
        },
        activeOrdersTotal() {
            let total = 0;
            Object.values(this.pipeline).forEach(list => {
                total += list.length;
            });
            return total;
        },
        activeCriticalOrders() {
            let critical = 0;
            Object.values(this.pipeline).forEach(list => {
                list.forEach(order => {
                    if (order.dias_activo >= 20) {
                        critical++;
                    }
                });
            });
            return critical;
        }
    },
    methods: {
        setTab(tab) {
            this.activeTab = tab;
            if (tab === 'stats') {
                this.loadDeliveryStats();
            } else if (tab === 'progress') {
                this.loadActiveProgress();
            } else if (tab === 'optimization') {
                this.loadPrintOptimization();
            }
        },
        loadDeliveryStats() {
            let me = this;
            let url = `/seguimiento-produccion/delivery-stats?fecha_inicio=${me.filters.fecha_inicio}&fecha_fin=${me.filters.fecha_fin}&min_pedidos=${me.filters.min_pedidos}`;
            axios.get(url).then(response => {
                me.products = response.data.products;
                me.distribution = response.data.distribution;
            }).catch(error => {
                console.error("Error loading delivery stats: ", error);
            });
        },
        loadActiveProgress() {
            let me = this;
            axios.get('/seguimiento-produccion/active-progress').then(response => {
                me.pipeline = response.data;
            }).catch(error => {
                console.error("Error loading active progress: ", error);
            });
        },
        loadPrintOptimization() {
            let me = this;
            axios.get('/seguimiento-produccion/print-optimization').then(response => {
                me.optimizationStats = response.data;
            }).catch(error => {
                console.error("Error loading print optimization: ", error);
            });
        },
        getPercentage(val, total) {
            if (!total || total === 0) return 0;
            return Math.round((val / total) * 100);
        },
        getBarClass(days) {
            if (days <= 7) return 'bg-success';
            if (days <= 15) return 'bg-info';
            if (days <= 30) return 'bg-warning';
            return 'bg-danger';
        },
        getPriorityClass(p) {
            if (p === 3) return 'badge-danger';
            if (p === 2) return 'badge-warning text-dark';
            return 'badge-success';
        },
        getAgeDaysClass(d) {
            if (d >= 20) return 'text-danger font-weight-bold';
            if (d >= 10) return 'text-warning font-weight-bold';
            return 'text-success';
        },
        getBadgeClassForStage(stage) {
            if (stage === 'Sin iniciar') return 'badge-secondary';
            if (stage === 'Para entregar') return 'badge-success';
            if (stage === 'No recogido') return 'badge-danger';
            if (stage === 'Otros') return 'badge-light text-dark';
            return 'badge-primary';
        },
        getUniqueKey() {
            return Math.random().toString(36).substring(7);
        }
    },
    mounted() {
        this.loadDeliveryStats();
    }
}
</script>

<style scoped>
.progress-xs {
    height: 6px;
    border-radius: 4px;
    background-color: #e9ecef;
}
.hover-up {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-up:hover {
    transform: translateY(-3px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
}
.card-border-left-primary {
    border-left: 4px solid #20a8d8 !important;
}
.card-border-left-success {
    border-left: 4px solid #4dbd74 !important;
}
.card-border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.card-border-left-danger {
    border-left: 4px solid #f86c6b !important;
}
.card-border-left-dark {
    border-left: 4px solid #2f353a !important;
}
.pipeline-column {
    min-width: 290px;
    max-width: 320px;
}
.shadow-xs {
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.order-card {
    border-radius: 6px;
    border-left: 3px solid #20a8d8 !important;
}
.critical-border {
    border-left: 3px solid #f86c6b !important;
    animation: blinkBorder 2s infinite alternate;
}
@keyframes blinkBorder {
    0% { border-left-color: #f86c6b; }
    100% { border-left-color: #ffc107; }
}
.card-gradient-primary {
    background-color: #f0f3f5;
    border-bottom: 2px solid #20a8d8;
}
</style>
