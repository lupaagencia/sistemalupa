<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Análisis de Producción</li>
        </ol>
        <div class="container-fluid">
            <!-- Sección de Filtros Compacta -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Criterio de Búsqueda</label>
                            <select class="form-control" v-model="criterio">
                                <option value="proceso">Proceso</option>
                                <option value="usuario">Operario</option>
                                <option value="orden"># Orden</option>
                            </select>
                        </div>
                        <div class="col-md-3" v-if="criterio == 'proceso'">
                            <label>Seleccionar Proceso</label>
                            <select class="form-control" v-model="buscar">
                                <option value="">Todos los procesos</option>
                                <option v-for="p in arrayProcesos" :key="p.proceso" :value="p.proceso">{{ p.proceso }}</option>
                            </select>
                        </div>
                        <div class="col-md-3" v-else>
                            <label>Texto a Buscar</label>
                            <input type="text" v-model="buscar" @keyup.enter="listarFlujo(1,buscar,criterio)" class="form-control" placeholder="Escriba aquí...">
                        </div>
                        <div class="col-md-2">
                            <label>Fecha Inicio</label>
                            <input type="date" v-model="fecha_inicio" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label>Fecha Fin</label>
                            <input type="date" v-model="fecha_fin" class="form-control">
                        </div>
                        <div class="col-md-2 mt-2">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkTerminado" v-model="soloTerminados">
                                <label class="form-check-label" for="checkTerminado">Solo Terminados</label>
                            </div>
                            <button type="submit" @click="listarFlujo(1,buscar,criterio)" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Filtrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados de la Búsqueda - Dashboard Rápido -->
            <div class="row" v-if="arrayFlujo.length > 0">
                <div class="col-md-12">
                    <div class="card card-accent-info">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-bar-chart"></i> RESUMEN DE ESTA BÚSQUEDA (Resultados en Tabla)</span>
                            <span class="badge badge-info">{{ arrayFlujo.length }} Registros encontrados</span>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-4">
                                <div class="col-sm-3">
                                    <div class="text-muted small uppercase font-weight-bold">Total Unidades</div>
                                    <div class="h4 mb-0 text-primary">{{ searchSummary.totalQty }}</div>
                                </div>
                                <div class="col-sm-3 border-left">
                                    <div class="text-muted small uppercase font-weight-bold">Terminados</div>
                                    <div class="h4 mb-0 text-success">{{ searchSummary.completed }}</div>
                                </div>
                                <div class="col-sm-3 border-left">
                                    <div class="text-muted small uppercase font-weight-bold">En Proceso</div>
                                    <div class="h4 mb-0 text-danger">{{ arrayFlujo.length - searchSummary.completed }}</div>
                                </div>
                                <div class="col-sm-3 border-left">
                                    <div class="text-muted small uppercase font-weight-bold">Promedio Tiempo</div>
                                    <div class="h4 mb-0 text-dark">{{ formatDuration(searchSummary.avgDuration) }}</div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h6>Distribución de Carga por {{ criterio == 'usuario' ? 'Proceso' : 'Operario' }}</h6>
                                    <div class="row">
                                        <div v-for="userStat in searchStats" :key="userStat.name" class="col-md-4 mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small">{{ userStat.name }}</span>
                                                <span class="small font-weight-bold text-info">{{ userStat.total }} Unid. ({{ userStat.count }} Proc.)</span>
                                            </div>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-info" role="progressbar" :style="{width: (userStat.total / maxSearchTotal * 100) + '%'}" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficas Simples / Estadísticas Generales -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-accent-primary">
                        <div class="card-header">
                            <i class="fa fa-pie-chart"></i> Eficiencia por Operario (Histórico)
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div v-for="user in stats.byUser" :key="user.usuario" class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ user.usuario }}</span>
                                    <strong class="text-primary">{{ user.promedio_minutos }} min (prom)</strong>
                                </div>
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-info" role="progressbar" :style="{width: calculatePercentage(user.promedio_minutos, maxUserAvg) + '%'}" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ user.total_cantidad }} unidades en {{ user.total_procesos }} procesos</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-accent-danger">
                        <div class="card-header font-weight-bold text-danger">
                            <i class="fa fa-warning"></i> CUELLOS DE BOTELLA (Sin Mover)
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <p class="small text-muted mb-2">Procesos con mayor cantidad de órdenes actualmente estancadas.</p>
                            <div v-for="bot in stats.bottlenecks" :key="bot.proceso" class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="font-weight-bold">{{ bot.proceso }}</span>
                                    <span class="badge badge-danger text-white">{{ bot.total_pendientes }} pendientes</span>
                                </div>
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-danger" role="progressbar" :style="{width: calculatePercentage(bot.total_pendientes, maxBottleneck) + '%'}" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div v-if="!stats.bottlenecks || stats.bottlenecks.length == 0" class="text-center py-4">
                                <i class="fa fa-check-circle text-success h1"></i>
                                <p>No hay procesos estancados</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-accent-success">
                        <div class="card-header">
                            <i class="fa fa-clock-o"></i> Tiempo Promedio por Proceso
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div v-for="proc in stats.byProcess" :key="proc.proceso" class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ proc.proceso }}</span>
                                    <strong class="text-success">{{ formatDuration(Math.round(proc.promedio_minutos)) }}</strong>
                                </div>
                                <div class="progress progress-xs">
                                    <div class="progress-bar bg-success" role="progressbar" :style="{width: calculatePercentage(proc.promedio_minutos, maxProcessAvg) + '%'}" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ proc.total }} procesos analizados</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listado de Resultados -->
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-align-justify"></i> Historial de Producción (Listado detallado)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th># Orden</th>
                                    <th>Cliente</th>
                                    <th>Artículo</th>
                                    <th>Proceso</th>
                                    <th>Inicia</th>
                                    <th>Termina</th>
                                    <th>Duración</th>
                                    <th>Cant. Proceso</th>
                                    <th>Cant. Orden</th>
                                    <th>Operario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in arrayFlujo" :key="item.id">
                                    <td>{{ item.orden_trabajo_id }}</td>
                                    <td>{{ item.orden_trabajo ? (item.orden_trabajo.cliente ? item.orden_trabajo.cliente.razonsocial : 'N/A') : 'N/A' }}</td>
                                    <td>{{ item.orden_trabajo ? (item.orden_trabajo.articulo ? item.orden_trabajo.articulo.nombre : 'N/A') : 'N/A' }}</td>
                                    <td><span class="badge badge-info">{{ item.proceso }}</span></td>
                                    <td>{{ formatDate(item.fecha_inicia) }} {{ item.hora_inicia }}</td>
                                    <td>{{ item.fecha_termina ? formatDate(item.fecha_termina) + ' ' + item.hora_termina : 'En curso...' }}</td>
                                    <td>
                                        <span v-if="item.duracion_minutos !== null" class="badge badge-success">
                                            {{ formatDuration(item.duracion_minutos) }}
                                        </span>
                                        <span v-else class="badge badge-warning">Procesando</span>
                                    </td>
                                    <td><strong>{{ item.cantidad }}</strong></td>
                                    <td>{{ item.orden_trabajo ? item.orden_trabajo.cantidad : 'N/A' }}</td>
                                    <td>{{ item.usuario }}</td>
                                    <td>
                                        <template v-if="item.orden_trabajo">
                                            <!-- Si hay entregas con CC, mostrar la última -->
                                            <button v-if="item.orden_trabajo.entregas && item.orden_trabajo.entregas.some(e => e.cuentacobro_id)" 
                                                    type="button" @click="imprimirPedido(item.orden_trabajo.entregas.filter(e => e.cuentacobro_id).pop().cuentacobro_id)" 
                                                    class="btn btn-info btn-sm" title="Ver Última Cuenta de Cobro (Entrega)">
                                                <i class="fa fa-file-text-o"></i> CC Entrega
                                            </button>
                                            <!-- Si no, mostrar el Pedido original -->
                                            <button v-else-if="item.orden_trabajo.linea && item.orden_trabajo.linea.pedido" 
                                                    type="button" @click="imprimirPedido(item.orden_trabajo.linea.pedido.id)" 
                                                    class="btn btn-outline-info btn-sm" title="Ver Pedido Original">
                                                <i class="fa fa-file-text-o"></i> Cta. Pedido
                                            </button>
                                            <span v-else class="text-muted small">Sin Cta.</span>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>



                    <nav>
                        <ul class="pagination">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)">Ant</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar,criterio)" v-text="page"></a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar,criterio)">Sig</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    data() {
        return {
            arrayFlujo: [],
            arrayProcesos: [],
            pagination: {
                'total': 0,
                'current_page': 0,
                'per_page': 0,
                'last_page': 0,
                'from': 0,
                'to': 0,
            },
            offset: 3,
            criterio: 'proceso',
            buscar: '',
            fecha_inicio: '',
            fecha_fin: '',
            soloTerminados: false,
            stats: {
                byUser: [],
                byProcess: [],
                byDay: [],
                bottlenecks: []
            }
        }
    },
    computed: {
        isActived: function() {
            return this.pagination.current_page;
        },
        pagesNumber: function() {
            if (!this.pagination.to) {
                return [];
            }
            var from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            var to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            var pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        },
        maxUserAvg() {
            if (this.stats.byUser.length === 0) return 1;
            return Math.max(...this.stats.byUser.map(u => u.promedio_minutos), 1);
        },
        maxProcessAvg() {
            if (this.stats.byProcess.length === 0) return 1;
            return Math.max(...this.stats.byProcess.map(p => p.promedio_minutos), 1);
        },
        maxDayAvg() {
            if (this.stats.byDay.length === 0) return 1;
            return Math.max(...this.stats.byDay.map(d => d.promedio_minutos), 1);
        },
        maxBottleneck() {
            if (!this.stats.bottlenecks || this.stats.bottlenecks.length === 0) return 1;
            return Math.max(...this.stats.bottlenecks.map(b => b.total_pendientes), 1);
        },
        searchStats() {
            let res = {};
            let groupByKey = (this.criterio === 'usuario') ? 'proceso' : 'usuario';

            this.arrayFlujo.forEach(item => {
                let key = item[groupByKey] || 'N/A';
                if (!res[key]) res[key] = { total: 0, count: 0 };
                res[key].total += item.cantidad;
                res[key].count += 1;
            });
            return Object.keys(res).map(key => ({ 
                name: key, 
                total: res[key].total,
                count: res[key].count
            }));
        },
        maxSearchTotal() {
            let stats = this.searchStats;
            if (stats.length === 0) return 1;
            return Math.max(...stats.map(s => s.total), 1);
        },
        searchSummary() {
            let totalQty = 0;
            let completed = 0;
            let totalDur = 0;
            let durCount = 0;

            this.arrayFlujo.forEach(item => {
                totalQty += item.cantidad;
                if (item.fecha_termina) completed++;
                if (item.duracion_minutos) {
                    totalDur += item.duracion_minutos;
                    durCount++;
                }
            });

            return {
                totalQty,
                completed,
                avgDuration: durCount > 0 ? Math.round(totalDur / durCount) : 0
            };
        }
    },
    methods: {
        listarFlujo(page, buscar, criterio) {
            let me = this;
            var url = '/flujo?page=' + page + '&buscar=' + buscar + '&criterio=' + criterio + '&fecha_inicio=' + this.fecha_inicio + '&fecha_fin=' + this.fecha_fin + '&terminado=' + this.soloTerminados;
            axios.get(url).then(function(response) {
                var respuesta = response.data;
                me.arrayFlujo = respuesta.flujo.data;
                me.pagination = respuesta.pagination;
            })
            .catch(function(error) {
                console.log(error);
            });
            this.getStats();
        },
        formatDate(dateStr) {
            if (!dateStr) return '';
            if (typeof dateStr === 'string' && dateStr.includes('T')) {
                return dateStr.split('T')[0];
            }
            return dateStr;
        },
        imprimirPedido(id) {
            window.open('/imprimirPedido?id=' + id, '_blank');
        },
        getStats() {
            let me = this;
            var url = '/flujo/stats?fecha_inicio=' + this.fecha_inicio + '&fecha_fin=' + this.fecha_fin;
            axios.get(url).then(function(response) {
                me.stats = response.data;
            });
        },
        getProcesos() {
            let me = this;
            axios.get('/flujo/procesos').then(function(response) {
                me.arrayProcesos = response.data.procesos;
            });
        },
        formatDuration(minutes) {
            if (minutes < 60) return minutes + ' min';
            let hours = Math.floor(minutes / 60);
            let mins = minutes % 60;
            return hours + 'h ' + mins + 'm';
        },
        calculatePercentage(val, max) {
            return (val / max) * 100;
        },
        cambiarPagina(page, buscar, criterio) {
            this.pagination.current_page = page;
            this.listarFlujo(page, buscar, criterio);
        }
    },

    mounted() {
        this.getProcesos();
        this.listarFlujo(1, this.buscar, this.criterio);
    }
}
</script>

<style scoped>
.progress-xs {
    height: 4px;
}
.mb-3 {
    margin-bottom: 1rem !important;
}
</style>
