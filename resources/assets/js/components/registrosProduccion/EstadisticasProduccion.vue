<template>
    <div class="stats-container animate__animated animate__fadeIn">
        <!-- Filtros Superiores -->
        <div class="card mb-4 border-0 shadow-sm rounded-xl">
            <div class="card-body p-4">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold text-muted small">FECHA INICIO</label>
                        <input type="date" class="form-control form-control-shadow" v-model="fecha_inicio" @change="cargarEstadisticas">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold text-muted small">FECHA FIN</label>
                        <input type="date" class="form-control form-control-shadow" v-model="fecha_fin" @change="cargarEstadisticas">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold text-muted small">EMPLEADO</label>
                        <select class="form-control form-control-shadow" v-model="empleado_id" @change="cargarEstadisticas">
                            <option value="0">Todos los empleados</option>
                            <option v-for="emp in empleados" :key="emp.id" :value="emp.id">{{ emp.nombre }} {{ emp.apellido }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-right">
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-light btn-sm" @click="setRange('hoy')">Hoy</button>
                            <button class="btn btn-light btn-sm" @click="setRange('semana')">Semana</button>
                            <button class="btn btn-light btn-sm" @click="setRange('mes')">Mes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="text-center p-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Calculando indicadores...</p>
        </div>

        <template v-else>
            <!-- KPI CARDS -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-primary-gradient text-white shadow">
                        <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
                        <div class="stat-info">
                            <h3 class="font-weight-bold mb-0">{{ formatTime(resumen.total_minutos) }}</h3>
                            <span class="small opacity-80">Tiempo Total Producido</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success-gradient text-white shadow">
                        <div class="stat-icon"><i class="fa fa-cubes"></i></div>
                        <div class="stat-info">
                            <h3 class="font-weight-bold mb-0">{{ formatNumber(resumen.total_cantidad) }}</h3>
                            <span class="small opacity-80">Unidades Totales</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-info-gradient text-white shadow">
                        <div class="stat-icon"><i class="fa fa-bolt"></i></div>
                        <div class="stat-info">
                            <h3 class="font-weight-bold mb-0">{{ resumen.count_registros }}</h3>
                            <span class="small opacity-80">Actividades Registradas</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-warning-gradient text-white shadow">
                        <div class="stat-icon"><i class="fa fa-hourglass-half"></i></div>
                        <div class="stat-info">
                            <h3 class="font-weight-bold mb-0">{{ formatAvg(resumen.total_minutos / resumen.count_registros) }}</h3>
                            <span class="small opacity-80">Promedio por Actividad</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Rendimiento por Empleado -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 rounded-xl overflow-hidden">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 font-weight-bold"><i class="fa fa-users mr-2 text-primary"></i>Rendimiento por Personal</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light text-muted small">
                                        <tr>
                                            <th class="border-0 px-4">Empleado</th>
                                            <th class="border-0">Tiempo</th>
                                            <th class="border-0">Producción</th>
                                            <th class="border-0 px-4" style="width: 150px;">Distribución</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="emp in por_empleado" :key="emp.empleado_id" class="align-middle">
                                            <td class="px-4 py-3 font-weight-bold">{{ emp.nombre_empleado }}</td>
                                            <td>{{ formatTime(emp.total_minutos) }}</td>
                                            <td>{{ formatNumber(emp.total_cantidad) }} uds</td>
                                            <td class="px-4">
                                                <div class="progress rounded-pill shadow-sm" style="height: 8px;">
                                                    <div class="progress-bar bg-primary" 
                                                         :style="{ width: (emp.total_minutos / resumen.total_minutos * 100) + '%' }">
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

                <!-- Tiempos Medios por Actividad -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 rounded-xl overflow-hidden">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 font-weight-bold"><i class="fa fa-list-alt mr-2 text-success"></i>Tiempos Promedio por Actividad</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light text-muted small">
                                        <tr>
                                            <th class="border-0 px-4">Actividad</th>
                                            <th class="border-0">Repet.</th>
                                            <th class="border-0">Total</th>
                                            <th class="border-0 px-4">Promedio/Gasto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="act in por_actividad" :key="act.actividad" class="align-middle">
                                            <td class="px-4 py-3">
                                                <span class="badge badge-soft-primary px-2 py-1">{{ act.actividad }}</span>
                                            </td>
                                            <td>{{ act.total_registros }}</td>
                                            <td class="font-weight-bold">{{ formatTime(act.total_minutos) }}</td>
                                            <td class="px-4">
                                                <span class="text-success font-weight-bold">{{ act.avg_minutos }} min</span>
                                                <small class="text-muted ml-1">prom.</small>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: ['user'],
    data() {
        return {
            fecha_inicio: new Date().toLocaleDateString('en-CA'),
            fecha_fin: new Date().toLocaleDateString('en-CA'),
            empleado_id: 0,
            loading: true,
            empleados: [],
            resumen: {},
            por_empleado: [],
            por_actividad: []
        };
    },
    methods: {
        async getEmpleados() {
            try {
                const response = await axios.get('/empleado/getEmpleados');
                this.empleados = response.data;
            } catch (error) {
                console.error('Error al cargar empleados:', error);
            }
        },
        async cargarEstadisticas() {
            this.loading = true;
            try {
                const response = await axios.get('/registros/estadisticas', {
                    params: {
                        inicio: this.fecha_inicio,
                        fin: this.fecha_fin,
                        empleado_id: this.empleado_id > 0 ? this.empleado_id : null
                    }
                });
                this.resumen = response.data.resumen;
                this.por_empleado = response.data.por_empleado;
                this.por_actividad = response.data.por_actividad;
            } catch (error) {
                console.error('Error al cargar estadisticas:', error);
            } finally {
                this.loading = false;
            }
        },
        setRange(range) {
            const today = new Date();
            if (range === 'hoy') {
                this.fecha_inicio = today.toLocaleDateString('en-CA');
                this.fecha_fin = today.toLocaleDateString('en-CA');
            } else if (range === 'semana') {
                const lastWeek = new Date(today);
                lastWeek.setDate(today.getDate() - 7);
                this.fecha_inicio = lastWeek.toLocaleDateString('en-CA');
                this.fecha_fin = today.toLocaleDateString('en-CA');
            } else if (range === 'mes') {
                const lastMonth = new Date(today);
                lastMonth.setMonth(today.getMonth() - 1);
                this.fecha_inicio = lastMonth.toLocaleDateString('en-CA');
                this.fecha_fin = today.toLocaleDateString('en-CA');
            }
            this.cargarEstadisticas();
        },
        formatTime(totalMinutos) {
            if (!totalMinutos) return '0m';
            const h = Math.floor(totalMinutos / 60);
            const m = Math.floor(totalMinutos % 60);
            return h > 0 ? `${h}h ${m}m` : `${m}m`;
        },
        formatNumber(val) {
            if (!val) return '0';
            return new Intl.NumberFormat().format(val);
        },
        formatAvg(val) {
            if (!val || isNaN(val)) return '0 min';
            return val.toFixed(1) + ' min';
        }
    },
    async created() {
        await this.getEmpleados();
        await this.cargarEstadisticas();
    }
};
</script>

<style scoped>
.stats-container {
    padding: 10px;
    background: #f4f7f6;
    min-height: 80vh;
}

.rounded-xl {
    border-radius: 1rem !important;
}

.form-control-shadow {
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
    font-size: 0.9rem;
    padding: 0.6rem 1rem;
    height: auto;
}

.form-control-shadow:focus {
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
    border-color: #4299e1;
}

.stat-card {
    padding: 20px;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 15px;
}

.bg-primary-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.bg-success-gradient { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
.bg-info-gradient { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }
.bg-warning-gradient { background: linear-gradient(135deg, #ecc94b 0%, #d69e2e 100%); }

.opacity-80 { opacity: 0.8; }

.badge-soft-primary {
    background-color: #ebf4ff;
    color: #3182ce;
    border: 1px solid #bee3f8;
}

.table-hover tbody tr:hover {
    background-color: #f8fafc;
}

.align-middle td {
    vertical-align: middle !important;
}
</style>
