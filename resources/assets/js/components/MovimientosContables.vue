<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Libro Diario / Movimientos Contables</li>
        </ol>

        <div class="container-fluid">
            <!-- KPI Summary Cards -->
            <div class="row mb-4">
                <!-- Total Debe -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-lg bg-gradient-primary text-white">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-white-50 font-weight-bold uppercase">Total Débitos (Debe)</small>
                                    <h3 class="mb-0 font-weight-bold mt-1">${{ summary.total_debe | formatPrice }}</h3>
                                </div>
                                <div class="bg-white-20 p-2 rounded">
                                    <i class="fa fa-arrow-circle-down fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Haber -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-lg bg-gradient-info text-white">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-white-50 font-weight-bold uppercase">Total Créditos (Haber)</small>
                                    <h3 class="mb-0 font-weight-bold mt-1">${{ summary.total_haber | formatPrice }}</h3>
                                </div>
                                <div class="bg-white-20 p-2 rounded">
                                    <i class="fa fa-arrow-circle-up fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diferencia (Balance Status) -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-lg" :class="summary.diferencia === 0 ? 'bg-gradient-success text-white' : 'bg-gradient-danger text-white'">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-white-50 font-weight-bold uppercase">Estado de Balance</small>
                                    <h3 class="mb-0 font-weight-bold mt-1">${{ summary.diferencia | formatPrice }}</h3>
                                    <span class="small font-weight-bold opacity-8">
                                        <i class="fa" :class="summary.diferencia === 0 ? 'fa-check-circle' : 'fa-exclamation-triangle'"></i>
                                        {{ summary.diferencia === 0 ? 'Balance Cuadrado' : 'Desbalance Detectado' }}
                                    </span>
                                </div>
                                <div class="bg-white-20 p-2 rounded">
                                    <i class="fa" :class="summary.diferencia === 0 ? 'fa-balance-scale fa-2x' : 'fa-warning fa-2x'"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Comprobantes -->
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-lg bg-white text-dark">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-muted font-weight-bold uppercase">Comprobantes Registrados</small>
                                    <h3 class="mb-0 font-weight-bold mt-1 text-primary">{{ summary.comprobantes_count }}</h3>
                                </div>
                                <div class="bg-light p-2 rounded text-primary">
                                    <i class="fa fa-folder fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-body p-3">
                    <div class="row align-items-end">
                        <!-- Fecha Inicio -->
                        <div class="col-md-3">
                            <label class="font-weight-bold text-dark mb-1 small">Fecha de Inicio</label>
                            <input type="date" v-model="fechaInicio" class="form-control" @change="listarMovimientos(1)">
                        </div>
                        <!-- Fecha Fin -->
                        <div class="col-md-3">
                            <label class="font-weight-bold text-dark mb-1 small">Fecha de Vencimiento/Fin</label>
                            <input type="date" v-model="fechaFin" class="form-control" @change="listarMovimientos(1)">
                        </div>
                        <!-- Tipo Comprobante -->
                        <div class="col-md-2">
                            <label class="font-weight-bold text-dark mb-1 small">Tipo</label>
                            <select class="form-control" v-model="tipo" @change="listarMovimientos(1)">
                                <option value="">Todos</option>
                                <option value="Ingreso">Ingreso</option>
                                <option value="Egreso">Egreso</option>
                                <option value="Diario">Diario</option>
                                <option value="Traspaso">Traspaso</option>
                            </select>
                        </div>
                        <!-- Búsqueda -->
                        <div class="col-md-4">
                            <label class="font-weight-bold text-dark mb-1 small">Buscar</label>
                            <div class="input-group">
                                <input type="text" v-model="buscar" @keyup.enter="listarMovimientos(1)" class="form-control" placeholder="Buscar por concepto, cuenta o tercero...">
                                <div class="input-group-append">
                                    <button type="button" @click="listarMovimientos(1)" class="btn btn-primary px-3"><i class="fa fa-search"></i></button>
                                    <button type="button" @click="limpiarFiltros()" class="btn btn-secondary px-3"><i class="fa fa-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Actions -->
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-warning text-white px-4 shadow-sm font-weight-bold mr-2" @click="irAlSimulador()">
                    <i class="fa fa-support mr-2"></i>Simulador y Guía Contable
                </button>
                <button type="button" class="btn btn-info px-4 shadow-sm font-weight-bold" @click="imprimirLibroDiario()">
                    <i class="fa fa-print mr-2"></i>Imprimir Libro Diario
                </button>
            </div>

            <!-- Journal Book Entries -->
            <div id="printArea">
                <div class="print-header d-none">
                    <h3 class="text-center font-weight-bold">SISTEMA DE FACTURACION E INVENTARIOS</h3>
                    <h4 class="text-center">REPORTE DE MOVIMIENTOS CONTABLES (LIBRO DIARIO)</h4>
                    <p class="text-center small">Filtros: {{ fechaInicio || 'N/A' }} al {{ fechaFin || 'N/A' }} | Tipo: {{ tipo || 'Todos' }}</p>
                    <hr>
                </div>

                <div v-if="arrayComprobantes.length === 0" class="card border-0 shadow-sm rounded-lg p-5 text-center text-muted">
                    <i class="fa fa-balance-scale fa-3x mb-3 text-secondary"></i>
                    <h5 class="font-weight-bold">No hay movimientos registrados</h5>
                    <p class="mb-0">Prueba cambiando los filtros de fecha o buscando otro término.</p>
                </div>

                <div v-for="comp in arrayComprobantes" :key="comp.id" class="card border-0 shadow-sm rounded-lg mb-4 print-card">
                    <!-- Comprobante Header -->
                    <div class="card-header bg-white border-bottom-0 py-3 d-flex flex-wrap align-items-center justify-content-between">
                        <div class="d-flex align-items-center flex-wrap">
                            <span class="badge px-3 py-2 font-weight-bold text-white mr-3 shadow-sm" :class="getTipoBadgeClass(comp.tipo)">
                                {{ comp.tipo.toUpperCase() }} #{{ comp.numero }}
                            </span>
                            <span class="text-muted font-weight-bold mr-3"><i class="fa fa-calendar mr-1"></i>{{ comp.fecha }}</span>
                            <span class="text-dark font-weight-bold">{{ comp.descripcion }}</span>
                        </div>
                        <div class="text-muted small">
                            <i class="fa fa-user mr-1"></i>Registrado por: <strong>{{ comp.user ? comp.user.usuario : 'Sistema' }}</strong>
                        </div>
                    </div>

                    <!-- Details Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="py-2 px-4" style="width: 150px;">Código</th>
                                        <th class="py-2">Cuenta Contable</th>
                                        <th class="py-2">Tercero / Persona</th>
                                        <th class="py-2 text-right" style="width: 150px;">Débito (Debe)</th>
                                        <th class="py-2 text-right" style="width: 150px;">Crédito (Haber)</th>
                                        <th class="py-2 px-4" style="width: 200px;">Referencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="det in comp.detalles" :key="det.id">
                                        <td class="px-4 py-2 font-weight-bold text-primary">{{ det.cuenta.codigo }}</td>
                                        <td class="py-2">{{ det.cuenta.nombre }}</td>
                                        <td class="py-2 text-dark">{{ det.tercero ? det.tercero.nombre : 'Varios / General' }}</td>
                                        <td class="py-2 text-right text-success font-weight-bold">
                                            {{ det.debe > 0 ? '$' + (parseFloat(det.debe) | formatPrice) : '-' }}
                                        </td>
                                        <td class="py-2 text-right text-info font-weight-bold">
                                            {{ det.haber > 0 ? '$' + (parseFloat(det.haber) | formatPrice) : '-' }}
                                        </td>
                                        <td class="px-4 py-2 text-muted small">{{ det.referencia || '-' }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr class="font-weight-bold">
                                        <td colspan="3" class="text-right py-2">TOTAL COMPROBANTE:</td>
                                        <td class="text-right py-2 text-success">${{ sumDebe(comp.detalles) | formatPrice }}</td>
                                        <td class="text-right py-2 text-info">${{ sumHaber(comp.detalles) | formatPrice }}</td>
                                        <td class="px-4 py-2 text-center text-success small">
                                            <i class="fa fa-lock mr-1"></i>Balanceado
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination Controls -->
            <nav v-if="pagination.last_page > 1">
                <ul class="pagination justify-content-center">
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === 1 }">
                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">
                            <i class="fa fa-angle-left"></i> Anterior
                        </a>
                    </li>
                    <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == pagination.current_page ? 'active' : '']">
                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page)" v-text="page"></a>
                    </li>
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === pagination.last_page }">
                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">
                            Siguiente <i class="fa fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </main>
</template>

<script>
export default {
    props: ['user'],
    data() {
        return {
            arrayComprobantes: [],
            summary: {
                total_debe: 0,
                total_haber: 0,
                comprobantes_count: 0,
                diferencia: 0
            },
            fechaInicio: '',
            fechaFin: '',
            tipo: '',
            buscar: '',
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 15,
                last_page: 0,
                from: 0,
                to: 0
            },
            offset: 3
        }
    },
    filters: {
        formatPrice(value) {
            let val = (value / 1).toFixed(2).replace('.', ',');
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    },
    computed: {
        pagesNumber() {
            if (!this.pagination.to) {
                return [];
            }
            let from = this.pagination.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) {
                to = this.pagination.last_page;
            }
            let pagesArray = [];
            while (from <= to) {
                pagesArray.push(from);
                from++;
            }
            return pagesArray;
        }
    },
    methods: {
        irAlSimulador() {
            this.$root.menu = 49;
        },
        listarMovimientos(page = 1) {
            let me = this;
            let url = '/movimientos-contables?page=' + page +
                '&fecha_inicio=' + me.fechaInicio +
                '&fecha_fin=' + me.fechaFin +
                '&tipo=' + me.tipo +
                '&buscar=' + me.buscar;

            axios.get(url).then(function (response) {
                me.arrayComprobantes = response.data.comprobantes.data;
                me.summary = response.data.summary;
                me.pagination = {
                    total: response.data.comprobantes.total,
                    current_page: response.data.comprobantes.current_page,
                    per_page: response.data.comprobantes.per_page,
                    last_page: response.data.comprobantes.last_page,
                    from: response.data.comprobantes.from,
                    to: response.data.comprobantes.to
                };
            })
            .catch(function (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar los movimientos contables.', 'error');
            });
        },
        cambiarPagina(page) {
            this.listarMovimientos(page);
        },
        limpiarFiltros() {
            this.fechaInicio = '';
            this.fechaFin = '';
            this.tipo = '';
            this.buscar = '';
            this.listarMovimientos(1);
        },
        sumDebe(detalles) {
            return detalles.reduce((sum, det) => sum + parseFloat(det.debe || 0), 0);
        },
        sumHaber(detalles) {
            return detalles.reduce((sum, det) => sum + parseFloat(det.haber || 0), 0);
        },
        getTipoBadgeClass(tipo) {
            const classes = {
                'Ingreso': 'bg-success',
                'Egreso': 'bg-danger',
                'Diario': 'bg-info',
                'Traspaso': 'bg-warning text-dark'
            };
            return classes[tipo] || 'bg-secondary';
        },
        imprimirLibroDiario() {
            // Simple client-side print view
            window.print();
        }
    },
    mounted() {
        // Default range to current month
        let now = new Date();
        let firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        let lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
        this.fechaInicio = firstDay;
        this.fechaFin = lastDay;

        this.listarMovimientos(1);
    }
}
</script>

<style scoped>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.bg-gradient-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}
.bg-white-20 {
    background-color: rgba(255, 255, 255, 0.2);
}
.opacity-8 {
    opacity: 0.8;
}
.uppercase {
    text-transform: uppercase;
}
.table td, .table th {
    vertical-align: middle;
}
tfoot.bg-light {
    background-color: #f8f9fa !important;
}

/* Printing styles */
@media print {
    body * {
        visibility: hidden;
    }
    #printArea, #printArea * {
        visibility: visible;
    }
    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .print-header {
        display: block !important;
        margin-bottom: 20px;
    }
    .print-card {
        border: 1px solid #ddd !important;
        margin-bottom: 30px !important;
        page-break-inside: avoid;
    }
    .pagination, .main-header, .sidebar, .breadcrumb, .btn, .card-body:first-of-type {
        display: none !important;
    }
}
</style>
