<template>
    <div class="dashboard-costos-container p-4">
        <!-- Resumen de Métricas Principales -->
        <div class="row mb-4">
            <!-- Tarjeta 1: Valoración Total de Inventario -->
            <div class="col-md-3">
                <div class="kpi-card bg-gradient-purple-blue text-white shadow-sm rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-white-50 text-uppercase font-weight-bold mb-1 card-label">Valoración Inventario</p>
                            <h3 class="font-weight-bold mb-0">{{ formatCurrency(metrics.valoracion_inventario) }}</h3>
                        </div>
                        <div class="icon-circle bg-white-20">
                            <i class="fa fa-cubes text-white"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-white-50 small">
                        <span>Valor total en stock (Materias Primas)</span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Costo de Ventas / Consumo (COGS) -->
            <div class="col-md-3">
                <div class="kpi-card bg-gradient-orange-red text-white shadow-sm rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-white-50 text-uppercase font-weight-bold mb-1 card-label">Consumo Mes (COGS)</p>
                            <h3 class="font-weight-bold mb-0">{{ formatCurrency(metrics.cogs_mes_actual) }}</h3>
                        </div>
                        <div class="icon-circle bg-white-20">
                            <i class="fa fa-shopping-cart text-white"></i>
                        </div>
                    </div>
                    <div class="mt-2 small d-flex align-items-center">
                        <span :class="metrics.variacion_consumo >= 0 ? 'text-warning' : 'text-success-light'" class="font-weight-bold mr-1">
                            <i :class="metrics.variacion_consumo >= 0 ? 'fa fa-arrow-up' : 'fa fa-arrow-down'"></i>
                            {{ Math.abs(metrics.variacion_consumo) }}%
                        </span>
                        <span class="text-white-50">vs. Mes Anterior ({{ formatCurrency(metrics.cogs_mes_anterior) }})</span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3: Entradas del Mes -->
            <div class="col-md-3">
                <div class="kpi-card bg-gradient-teal-green text-white shadow-sm rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-white-50 text-uppercase font-weight-bold mb-1 card-label">Entradas del Mes</p>
                            <h3 class="font-weight-bold mb-0">{{ formatCurrency(metrics.entradas_mes) }}</h3>
                        </div>
                        <div class="icon-circle bg-white-20">
                            <i class="fa fa-sign-in text-white"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-white-50 small">
                        <span>Ingresos a bodega registrados</span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 4: Salidas del Mes -->
            <div class="col-md-3">
                <div class="kpi-card bg-gradient-cyan-blue text-white shadow-sm rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-white-50 text-uppercase font-weight-bold mb-1 card-label">Salidas del Mes</p>
                            <h3 class="font-weight-bold mb-0">{{ formatCurrency(metrics.salidas_mes) }}</h3>
                        </div>
                        <div class="icon-circle bg-white-20">
                            <i class="fa fa-sign-out text-white"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-white-50 small">
                        <span>Despachos y consumo directo</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fila de Gráficos e Insumos Principales -->
        <div class="row">
            <!-- Gráfico Histórico de Consumo (COGS) -->
            <div class="col-md-7">
                <div class="card shadow-sm border-0 rounded p-4 mb-4 h-100">
                    <div class="card-title-header d-flex justify-content-between align-items-center mb-4">
                        <h5 class="font-weight-bold text-dark m-0">Historial de Consumos (Últimos 6 Meses)</h5>
                        <span class="badge badge-info p-2">Materia Prima Directa</span>
                    </div>

                    <!-- Gráfico SVG Premium Interactivo -->
                    <div class="svg-container text-center position-relative" style="min-height: 250px;">
                        <svg viewBox="0 0 500 240" class="cogs-chart-svg" width="100%" height="240">
                            <!-- Definición de Gradientes -->
                            <defs>
                                <linearGradient id="barGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#8884d8" />
                                    <stop offset="100%" stop-color="#4e54c8" />
                                </linearGradient>
                                <linearGradient id="barHoverGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#ff9966" />
                                    <stop offset="100%" stop-color="#ff5e62" />
                                </linearGradient>
                            </defs>

                            <!-- Líneas de cuadrícula Y -->
                            <line x1="40" y1="30" x2="480" y2="30" stroke="#f0f0f0" stroke-width="1" stroke-dasharray="4" />
                            <line x1="40" y1="80" x2="480" y2="80" stroke="#f0f0f0" stroke-width="1" stroke-dasharray="4" />
                            <line x1="40" y1="130" x2="480" y2="130" stroke="#f0f0f0" stroke-width="1" stroke-dasharray="4" />
                            <line x1="40" y1="180" x2="480" y2="180" stroke="#f0f0f0" stroke-width="1" stroke-dasharray="4" />

                            <!-- Etiquetas del Eje Y -->
                            <text x="35" y="35" text-anchor="end" fill="#999" font-size="10">{{ formatAbbreviated(maxChartValue) }}</text>
                            <text x="35" y="85" text-anchor="end" fill="#999" font-size="10">{{ formatAbbreviated(maxChartValue * 0.75) }}</text>
                            <text x="35" y="135" text-anchor="end" fill="#999" font-size="10">{{ formatAbbreviated(maxChartValue * 0.5) }}</text>
                            <text x="35" y="185" text-anchor="end" fill="#999" font-size="10">{{ formatAbbreviated(maxChartValue * 0.25) }}</text>
                            <text x="35" y="210" text-anchor="end" fill="#999" font-size="10">$0</text>

                            <!-- Eje X e Y -->
                            <line x1="40" y1="210" x2="480" y2="210" stroke="#ccc" stroke-width="1.5" />
                            <line x1="40" y1="30" x2="40" y2="210" stroke="#ccc" stroke-width="1" />

                            <!-- Barras de Datos -->
                            <g v-for="(item, idx) in metrics.historico_consumo" :key="idx">
                                <!-- Fondo invisible para hacer hover interactivo más amplio -->
                                <rect :x="50 + idx * 72" y="30" width="48" height="180" fill="transparent"
                                      @mouseenter="activeBarIndex = idx" @mouseleave="activeBarIndex = null" />

                                <!-- Barra real -->
                                <rect :x="58 + idx * 72" :y="210 - calculateBarHeight(item.total)" width="32" :height="calculateBarHeight(item.total)" 
                                      rx="5" :fill="activeBarIndex === idx ? 'url(#barHoverGradient)' : 'url(#barGradient)'" class="chart-bar" />
                                
                                <!-- Etiquetas de Mes -->
                                <text :x="74 + idx * 72" y="226" text-anchor="middle" fill="#555" font-size="10" font-weight="500">{{ item.mes }}</text>

                                <!-- Valor de la barra al hacer hover o siempre si está activa -->
                                <text v-if="activeBarIndex === idx" :x="74 + idx * 72" :y="200 - calculateBarHeight(item.total)" 
                                      text-anchor="middle" fill="#333" font-size="10" font-weight="bold" class="tooltip-val bg-white p-1 rounded border shadow-sm">
                                    {{ formatCurrency(item.total) }}
                                </text>
                            </g>
                        </svg>

                        <!-- Tooltip flotante alternativo -->
                        <div v-if="activeBarIndex !== null" class="custom-tooltip shadow rounded p-2">
                            <span class="d-block font-weight-bold text-dark">{{ metrics.historico_consumo[activeBarIndex].mes }}</span>
                            <span class="d-block text-primary font-weight-bold">{{ formatCurrency(metrics.historico_consumo[activeBarIndex].total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribución de Valoración por Materiales -->
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded p-4 mb-4 h-100">
                    <h5 class="font-weight-bold text-dark mb-4">Insumos de Mayor Valoración</h5>
                    
                    <div v-if="metrics.top_insumos && metrics.top_insumos.length > 0" class="top-insumos-list">
                        <div v-for="(insumo, index) in metrics.top_insumos" :key="index" class="insumo-item mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="font-weight-bold text-muted small text-uppercase">{{ insumo.nombre }}</span>
                                <span class="font-weight-bold text-dark small">{{ formatCurrency(insumo.valoracion) }} ({{ insumo.porcentaje }}%)</span>
                            </div>
                            <!-- Barra de Progreso Estilizada -->
                            <div class="progress rounded-pill progress-bar-container" style="height: 8px;">
                                <div class="progress-bar rounded-pill" :class="getProgressColorClass(index)" role="progressbar" 
                                     :style="{ width: insumo.porcentaje + '%' }" aria-valuenow="insumo.porcentaje" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center p-5 text-muted">
                        <i class="fa fa-cubes fa-2x mb-3 text-white-50"></i>
                        <p class="m-0">No se encontraron insumos con existencias para valorar.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            metrics: {
                valoracion_inventario: 0,
                cogs_mes_actual: 0,
                cogs_mes_anterior: 0,
                variacion_consumo: 0,
                entradas_mes: 0,
                salidas_mes: 0,
                historico_consumo: [],
                top_insumos: []
            },
            activeBarIndex: null
        }
    },
    computed: {
        maxChartValue() {
            if (!this.metrics.historico_consumo || this.metrics.historico_consumo.length === 0) {
                return 1000;
            }
            const values = this.metrics.historico_consumo.map(e => e.total);
            const maxValue = Math.max(...values);
            return maxValue > 0 ? maxValue * 1.15 : 1000; // 15% margin
        }
    },
    methods: {
        loadDashboardMetrics() {
            let me = this;
            axios.get('/inventarios/dashboard-costos')
                .then(function(response) {
                    me.metrics = response.data;
                })
                .catch(function(error) {
                    console.error('Error al cargar métricas de costos:', error);
                });
        },
        formatCurrency(value) {
            if (value === null || value === undefined) return '$0,00';
            return '$' + parseFloat(value).toLocaleString('es-CO', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        },
        formatAbbreviated(value) {
            if (value >= 1000000) {
                return '$' + (value / 1000000).toFixed(1) + 'M';
            }
            if (value >= 1000) {
                return '$' + (value / 1000).toFixed(0) + 'K';
            }
            return '$' + value.toFixed(0);
        },
        calculateBarHeight(value) {
            const chartHeight = 170; // Max height in SVG pixels
            return (value / this.maxChartValue) * chartHeight;
        },
        getProgressColorClass(index) {
            const classes = ['bg-primary', 'bg-info', 'bg-warning', 'bg-danger', 'bg-success'];
            return classes[index % classes.length];
        }
    },
    mounted() {
        this.loadDashboardMetrics();
    }
}
</script>

<style scoped>
.dashboard-costos-container {
    background-color: #fafbfc;
}

.kpi-card {
    border: none;
    border-radius: 12px !important;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}

.card-label {
    letter-spacing: 0.8px;
    font-size: 11px;
}

.bg-gradient-purple-blue {
    background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%);
}

.bg-gradient-orange-red {
    background: linear-gradient(135deg, #FF416C 0%, #FF4B2B 100%);
}

.bg-gradient-teal-green {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-cyan-blue {
    background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
}

.icon-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-white-20 {
    background-color: rgba(255, 255, 255, 0.2);
}

.text-success-light {
    color: #a3ffb4;
}

.chart-bar {
    transition: y 0.4s ease-out, height 0.4s ease-out, fill 0.2s ease;
}

.cogs-chart-svg {
    overflow: visible;
}

.custom-tooltip {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #ddd;
    pointer-events: none;
    font-size: 11px;
    z-index: 10;
    min-width: 120px;
    text-align: center;
}

.progress-bar-container {
    background-color: #e9ecef;
}

.top-insumos-list {
    margin-top: 10px;
}

.insumo-item {
    transition: transform 0.2s ease;
}

.insumo-item:hover {
    transform: translateX(4px);
}
</style>
