<template>
    <div>
        <!-- KPI Cards Summary -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0 border-left-primary bg-white h-100" style="border-left: 4px solid #2563eb; border-radius: 8px;">
                    <div class="card-body py-3 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Prospectos Activos</div>
                                <div class="h4 mb-0 font-weight-bold text-dark">{{ kpis.totalProspectos }}</div>
                                <div class="small text-muted mt-1">+{{ kpis.totalNuevosProspectos }} en los últimos 30 días</div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-users fa-2x text-gray-300" style="color: #93c5fd;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0 bg-white h-100" style="border-left: 4px solid #eab308; border-radius: 8px;">
                    <div class="card-body py-3 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Valor Embudo de Ventas</div>
                                <div class="h4 mb-0 font-weight-bold text-dark">${{ formatMonto(kpis.montoTotalEmbudo) }}</div>
                                <div class="small text-muted mt-1">{{ kpis.oportunidadesAbiertas }} oportunidades abiertas</div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-line-chart fa-2x" style="color: #fde047;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0 bg-white h-100" style="border-left: 4px solid #22c55e; border-radius: 8px;">
                    <div class="card-body py-3 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Cotizaciones Aprobadas</div>
                                <div class="h4 mb-0 font-weight-bold text-dark">${{ formatMonto(kpis.montoCotizacionesAprobadas) }}</div>
                                <div class="small text-muted mt-1">{{ kpis.cotizacionesAprobadas }} cotizaciones ganadas</div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-check-circle fa-2x" style="color: #86efac;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0 bg-white h-100" style="border-left: 4px solid #8b5cf6; border-radius: 8px;">
                    <div class="card-body py-3 px-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-purple text-uppercase mb-1" style="color: #7c3aed;">Tasa de Conversión</div>
                                <div class="h4 mb-0 font-weight-bold text-dark">{{ kpis.tasaConversion }}%</div>
                                <div class="small text-muted mt-1">{{ kpis.actividadesPendientes }} tareas de seguimiento pendientes</div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-pie-chart fa-2x" style="color: #ddd6fe;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Pipeline Board -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="font-weight-bold text-dark mb-0">
                <i class="fa fa-columns mr-2 text-primary"></i>Tablero Embudo de Ventas (Kanban)
            </h5>
            <button class="btn btn-sm btn-outline-primary" @click="cargarDatos">
                <i class="fa fa-refresh mr-1"></i> Actualizar
            </button>
        </div>

        <div v-if="cargando" class="text-center py-5">
            <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="text-muted mt-2">Cargando embudo de ventas...</p>
        </div>

        <div v-else class="kanban-container d-flex overflow-auto pb-3">
            <div 
                v-for="col in kanban" 
                :key="col.id" 
                class="kanban-column flex-shrink-0 mr-3 shadow-sm bg-light rounded"
                style="width: 300px; min-height: 500px; border-top: 4px solid #cbd5e1;"
                :style="{ borderTopColor: col.color }"
            >
                <div class="kanban-header p-3 bg-white border-bottom rounded-top d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 14px;">{{ col.nombre }}</h6>
                        <span class="small text-muted">${{ formatMonto(col.monto_total) }} ({{ col.total_items }})</span>
                    </div>
                    <span class="badge badge-pill text-white" :style="{ backgroundColor: col.color }">
                        {{ col.probabilidad }}%
                    </span>
                </div>

                <div class="kanban-body p-2" style="max-height: 600px; overflow-y: auto;">
                    <div v-if="!col.items.length" class="text-center text-muted py-4 small">
                        Sin oportunidades
                    </div>

                    <div 
                        v-for="opp in col.items" 
                        :key="opp.id" 
                        class="card shadow-sm mb-2 border-0" 
                        style="border-radius: 6px;"
                    >
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge badge-secondary small" style="font-size: 10px;">{{ opp.codigo }}</span>
                                <span class="small font-weight-bold text-success">${{ formatMonto(opp.monto_estimado) }}</span>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">{{ opp.nombre }}</h6>
                            <p class="small text-muted mb-2">
                                <i class="fa fa-building-o mr-1"></i>
                                {{ opp.cliente ? opp.cliente.nombre : (opp.prospecto ? opp.prospecto.nombre : 'Sin prospecto') }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="small text-muted" style="font-size: 11px;">
                                    <i class="fa fa-user mr-1"></i>{{ opp.vendedor ? opp.vendedor.usuario : 'N/A' }}
                                </span>
                                
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                        Mover
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a 
                                            v-for="targetCol in kanban" 
                                            :key="targetCol.id"
                                            v-if="targetCol.id !== col.id"
                                            class="dropdown-item small" 
                                            href="#" 
                                            @click.prevent="moverEtapa(opp.id, targetCol.id)"
                                        >
                                            <i class="fa fa-arrow-right mr-1" :style="{ color: targetCol.color }"></i>
                                            {{ targetCol.nombre }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['vendedorId', 'userRole'],
        data() {
            return {
                cargando: false,
                kanban: [],
                kpis: {
                    totalProspectos: 0,
                    totalNuevosProspectos: 0,
                    totalOportunidades: 0,
                    oportunidadesAbiertas: 0,
                    montoTotalEmbudo: 0,
                    montoGanado: 0,
                    totalCotizaciones: 0,
                    cotizacionesAprobadas: 0,
                    montoCotizacionesAprobadas: 0,
                    actividadesPendientes: 0,
                    tasaConversion: 0,
                    montoMeta: 0,
                    porcentajeMeta: 0
                }
            };
        },
        watch: {
            vendedorId() {
                this.cargarDatos();
            }
        },
        methods: {
            formatMonto(val) {
                if (!val) return '0.00';
                return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            cargarDatos() {
                this.cargando = true;

                const p1 = axios.get('/crm/kpis', { params: { vendedor_id: this.vendedorId } });
                const p2 = axios.get('/crm/oportunidad/kanban', { params: { vendedor_id: this.vendedorId } });

                Promise.all([p1, p2])
                .then(([resKpis, resKanban]) => {
                    this.cargando = false;
                    this.kpis = resKpis.data.kpis || this.kpis;
                    this.kanban = resKanban.data.kanban || [];
                })
                .catch(error => {
                    this.cargando = false;
                    console.error('Error al cargar datos del embudo:', error);
                });
            },
            moverEtapa(oportunidadId, nuevaEtapaId) {
                axios.put('/crm/oportunidad/cambiar-etapa', {
                    id: oportunidadId,
                    etapa_id: nuevaEtapaId
                })
                .then(response => {
                    this.cargarDatos();
                })
                .catch(error => {
                    console.error('Error al mover etapa:', error);
                });
            }
        },
        mounted() {
            this.cargarDatos();
        }
    };
</script>

<style scoped>
    .kanban-container {
        scrollbar-width: thin;
    }
</style>
