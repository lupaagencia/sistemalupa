<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb no-print">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Reportes Contables</li>
        </ol>

        <div class="container-fluid">
            <!-- Header (Visible in print) -->
            <div class="d-none d-print-block text-center my-4">
                <h2>Lupa Agencia - Sistema Contable</h2>
                <h3>{{ currentReportTitle }}</h3>
                <p v-if="activeTab === 'balance'">Al corte de: {{ filterBalance.fecha_fin }}</p>
                <p v-else-if="activeTab === 'resultados'">Período: {{ filterResultados.fecha_inicio }} al {{ filterResultados.fecha_fin }}</p>
                <p v-else-if="activeTab === 'balanza'">Período: {{ filterBalanza.fecha_inicio }} al {{ filterBalanza.fecha_fin }}</p>
                <p v-else-if="activeTab === 'auxiliar'">
                    Cuenta: {{ selectedAccountName }} | Período: {{ filterAuxiliar.fecha_inicio }} al {{ filterAuxiliar.fecha_fin }}
                </p>
                <hr />
            </div>

            <!-- Tab Container -->
            <div class="card shadow-sm border-0">
                <div class="card-header no-print bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="icon-chart mr-2 text-primary font-weight-bold"></i>
                        <h4 class="mb-0 text-dark font-weight-bold">Informes y Reportes Contables</h4>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" @click="imprimirReporte" :disabled="loading">
                        <i class="fa fa-print mr-1"></i> Imprimir Reporte
                    </button>
                </div>

                <div class="card-body">
                    <!-- Nav Tabs (No print) -->
                    <ul class="nav nav-tabs no-print mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" :class="{ active: activeTab === 'balance' }" @click="setTab('balance')" href="#">
                                <i class="icon-pie-chart mr-1"></i> Balance General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" :class="{ active: activeTab === 'resultados' }" @click="setTab('resultados')" href="#">
                                <i class="icon-graph mr-1"></i> Estado de Resultados (P&L)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" :class="{ active: activeTab === 'balanza' }" @click="setTab('balanza')" href="#">
                                <i class="icon-layers mr-1"></i> Balanza de Comprobación
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" :class="{ active: activeTab === 'auxiliar' }" @click="setTab('auxiliar')" href="#">
                                <i class="icon-notebook mr-1"></i> Auxiliar por Cuenta
                            </a>
                        </li>
                    </ul>

                    <!-- Loading state -->
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Generando reporte contable...</p>
                    </div>

                    <!-- Tabs Content -->
                    <div v-else class="tab-content">
                        
                        <!-- TAB 1: BALANCE GENERAL -->
                        <div v-if="activeTab === 'balance'" class="tab-pane active">
                            <!-- Filters -->
                            <div class="row no-print mb-4 bg-light p-3 rounded">
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Fecha de Corte</label>
                                    <input type="date" class="form-control" v-model="filterBalance.fecha_fin" @change="obtenerBalanceGeneral" />
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <button class="btn btn-primary" @click="obtenerBalanceGeneral">
                                        <i class="fa fa-refresh mr-1"></i> Actualizar
                                    </button>
                                </div>
                            </div>

                            <!-- Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-3 col-sm-6">
                                    <div class="card border-left-primary bg-light p-3">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Activos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency(balanceData.totales.activo) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="card border-left-danger bg-light p-3">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Pasivos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency(balanceData.totales.pasivo) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="card border-left-info bg-light p-3">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Patrimonio</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency(balanceData.totales.patrimonio) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="card p-3" :class="balanceData.diferencia == 0 ? 'border-left-success bg-light' : 'border-left-warning bg-warning-light'">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" :class="balanceData.diferencia == 0 ? 'text-success' : 'text-warning'">Diferencia (Ecuación)</div>
                                        <div class="h5 mb-0 font-weight-bold">{{ formatCurrency(balanceData.diferencia) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Balance Sheet Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered bg-white">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Cuenta Contable</th>
                                            <th class="text-right">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="cuenta in balanceData.cuentas" :key="cuenta.id" :style="rowStyle(cuenta)">
                                            <td :style="cellPadding(cuenta)">{{ cuenta.codigo }}</td>
                                            <td>{{ cuenta.nombre }}</td>
                                            <td class="text-right font-weight-bold">{{ formatCurrency(cuenta.balance) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: ESTADO DE RESULTADOS -->
                        <div v-if="activeTab === 'resultados'" class="tab-pane active">
                            <!-- Filters -->
                            <div class="row no-print mb-4 bg-light p-3 rounded">
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Fecha Inicio</label>
                                    <input type="date" class="form-control" v-model="filterResultados.fecha_inicio" />
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Fecha Fin</label>
                                    <input type="date" class="form-control" v-model="filterResultados.fecha_fin" />
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-primary" @click="obtenerEstadoResultados">
                                        <i class="fa fa-refresh mr-1"></i> Generar
                                    </button>
                                </div>
                            </div>

                            <!-- P&L Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-4 col-sm-6">
                                    <div class="card border-left-success bg-light p-3">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ingresos Operacionales</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency(resultadosData.totales.ingresos) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="card border-left-warning bg-light p-3">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Costos de Venta</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency(resultadosData.totales.costos) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="card border-left-danger bg-light p-3">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Gastos Operacionales</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ formatCurrency(resultadosData.totales.gastos) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profit Margins summary card -->
                            <div class="alert alert-info border-0 shadow-sm d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <span class="mr-4"><strong>Utilidad Bruta:</strong> {{ formatCurrency(resultadosData.utilidad_bruta) }}</span>
                                    <span><strong>Utilidad Neta del Ejercicio:</strong> {{ formatCurrency(resultadosData.utilidad_neta) }}</span>
                                </div>
                                <span class="badge" :class="resultadosData.utilidad_neta >= 0 ? 'badge-success' : 'badge-danger'" style="font-size: 1.1em; padding: 8px 12px;">
                                    {{ resultadosData.utilidad_neta >= 0 ? 'EXCEDENTE / UTILIDAD' : 'DÉFICIT / PÉRDIDA' }}
                                </span>
                            </div>

                            <!-- Income Statement Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered bg-white">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Cuenta de Resultados</th>
                                            <th class="text-right">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="cuenta in resultadosData.cuentas" :key="cuenta.id" :style="rowStyle(cuenta)">
                                            <td :style="cellPadding(cuenta)">{{ cuenta.codigo }}</td>
                                            <td>{{ cuenta.nombre }}</td>
                                            <td class="text-right font-weight-bold">{{ formatCurrency(cuenta.balance) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 3: BALANZA DE COMPROBACIÓN -->
                        <div v-if="activeTab === 'balanza'" class="tab-pane active">
                            <!-- Filters -->
                            <div class="row no-print mb-4 bg-light p-3 rounded">
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Fecha Inicio</label>
                                    <input type="date" class="form-control" v-model="filterBalanza.fecha_inicio" />
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Fecha Fin</label>
                                    <input type="date" class="form-control" v-model="filterBalanza.fecha_fin" />
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-primary" @click="obtenerBalanzaComprobacion">
                                        <i class="fa fa-refresh mr-1"></i> Generar
                                    </button>
                                </div>
                            </div>

                            <!-- Trial Balance Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered bg-white text-nowrap">
                                    <thead class="thead-light text-center">
                                        <tr>
                                            <th rowspan="2" class="align-middle">Código</th>
                                            <th rowspan="2" class="align-middle text-left">Cuenta PUC (Auxiliar)</th>
                                            <th colspan="2">Saldo Anterior</th>
                                            <th colspan="2">Movimientos del Período</th>
                                            <th colspan="2">Nuevo Saldo</th>
                                        </tr>
                                        <tr>
                                            <th>Débito</th>
                                            <th>Crédito</th>
                                            <th>Débito</th>
                                            <th>Crédito</th>
                                            <th>Débito</th>
                                            <th>Crédito</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="cuenta in balanzaData.cuentas" :key="cuenta.id">
                                            <td class="font-weight-bold">{{ cuenta.codigo }}</td>
                                            <td>{{ cuenta.nombre }}</td>
                                            <td class="text-right">{{ formatCurrency(cuenta.saldo_anterior_debe) }}</td>
                                            <td class="text-right">{{ formatCurrency(cuenta.saldo_anterior_haber) }}</td>
                                            <td class="text-right text-primary">{{ formatCurrency(cuenta.debe_periodo) }}</td>
                                            <td class="text-right text-primary">{{ formatCurrency(cuenta.haber_periodo) }}</td>
                                            <td class="text-right font-weight-bold text-dark">{{ formatCurrency(cuenta.saldo_final_debe) }}</td>
                                            <td class="text-right font-weight-bold text-dark">{{ formatCurrency(cuenta.saldo_final_haber) }}</td>
                                        </tr>
                                        <!-- Totals Row -->
                                        <tr class="table-info font-weight-bold text-right text-dark">
                                            <td colspan="2" class="text-center">TOTALES CONSOLIDADOS</td>
                                            <td>{{ formatCurrency(balanzaData.totales.saldo_anterior_debe) }}</td>
                                            <td>{{ formatCurrency(balanzaData.totales.saldo_anterior_haber) }}</td>
                                            <td>{{ formatCurrency(balanzaData.totales.debe_periodo) }}</td>
                                            <td>{{ formatCurrency(balanzaData.totales.haber_periodo) }}</td>
                                            <td>{{ formatCurrency(balanzaData.totales.saldo_final_debe) }}</td>
                                            <td>{{ formatCurrency(balanzaData.totales.saldo_final_haber) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 4: AUXILIAR POR CUENTA -->
                        <div v-if="activeTab === 'auxiliar'" class="tab-pane active">
                            <!-- Filters -->
                            <div class="row no-print mb-4 bg-light p-3 rounded">
                                <div class="col-md-3">
                                    <label class="font-weight-bold">Cuenta Contable (Detalle)</label>
                                    <select class="form-control" v-model="filterAuxiliar.cuenta_id" @change="obtenerAuxiliarCuenta">
                                        <option value="" disabled>Seleccione una cuenta...</option>
                                        <option v-for="c in arrayCuentasPUC" :key="c.id" :value="c.id">
                                            {{ c.codigo }} - {{ c.nombre }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="font-weight-bold">Fecha Inicio</label>
                                    <input type="date" class="form-control" v-model="filterAuxiliar.fecha_inicio" />
                                </div>
                                <div class="col-md-3">
                                    <label class="font-weight-bold">Fecha Fin</label>
                                    <input type="date" class="form-control" v-model="filterAuxiliar.fecha_fin" />
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-primary" @click="obtenerAuxiliarCuenta" :disabled="!filterAuxiliar.cuenta_id">
                                        <i class="fa fa-refresh mr-1"></i> Generar Auxiliar
                                    </button>
                                </div>
                            </div>

                            <div v-if="!filterAuxiliar.cuenta_id" class="text-center py-5 no-print text-muted">
                                <i class="icon-info fa-2x mb-3"></i>
                                <p>Por favor seleccione una cuenta contable de la lista para ver sus movimientos de auditoría.</p>
                            </div>

                            <div v-else>
                                <!-- Account header -->
                                <div class="bg-dark text-white p-3 rounded mb-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1 font-weight-bold">Cuenta: {{ auxiliarData.cuenta.codigo }} - {{ auxiliarData.cuenta.nombre }}</h5>
                                        <small>Naturaleza: <strong>{{ auxiliarData.cuenta.naturaleza }}</strong></small>
                                    </div>
                                    <div class="text-right">
                                        <small class="text-uppercase text-muted d-block">Saldo Anterior</small>
                                        <h4 class="mb-0 font-weight-bold">{{ formatCurrency(auxiliarData.saldo_anterior) }}</h4>
                                    </div>
                                </div>

                                <!-- Movements table -->
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered bg-white">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Comprobante</th>
                                                <th>Descripción</th>
                                                <th>Tercero</th>
                                                <th>Referencia</th>
                                                <th class="text-right">Débito</th>
                                                <th class="text-right">Crédito</th>
                                                <th class="text-right">Saldo Acumulado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="auxiliarData.movimientos.length === 0">
                                                <td colspan="8" class="text-center text-muted py-4">No se registraron movimientos en el período seleccionado.</td>
                                            </tr>
                                            <tr v-for="(mov, index) in auxiliarData.movimientos" :key="index">
                                                <td>{{ mov.fecha }}</td>
                                                <td>
                                                    <span class="badge badge-secondary mr-1">{{ mov.comprobante_tipo }}</span>
                                                    <strong>No. {{ mov.comprobante_numero }}</strong>
                                                </td>
                                                <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ mov.comprobante_descripcion }}
                                                </td>
                                                <td>{{ mov.tercero_nombre }}</td>
                                                <td>{{ mov.referencia || '-' }}</td>
                                                <td class="text-right text-success">{{ mov.debe > 0 ? formatCurrency(mov.debe) : '-' }}</td>
                                                <td class="text-right text-danger">{{ mov.haber > 0 ? formatCurrency(mov.haber) : '-' }}</td>
                                                <td class="text-right font-weight-bold">{{ formatCurrency(mov.saldo) }}</td>
                                            </tr>
                                            <!-- Total summary row -->
                                            <tr class="table-dark font-weight-bold text-right">
                                                <td colspan="5" class="text-center">SUMAS Y SALDO FINAL</td>
                                                <td>{{ formatCurrency(auxiliarData.total_debe) }}</td>
                                                <td>{{ formatCurrency(auxiliarData.total_haber) }}</td>
                                                <td class="bg-primary text-white">{{ formatCurrency(auxiliarData.saldo_final) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
    props: ['user'],
    data() {
        return {
            activeTab: 'balance',
            loading: false,
            arrayCuentasPUC: [], // Dropdown selector for Auxiliares
            
            // Tab 1 Balance
            filterBalance: {
                fecha_fin: new Date().toISOString().substring(0, 10)
            },
            balanceData: {
                cuentas: [],
                totales: { activo: 0, pasivo: 0, patrimonio: 0 },
                diferencia: 0,
                utilidad_ejercicio: 0
            },

            // Tab 2 Resultados
            filterResultados: {
                fecha_inicio: new Date(new Date().getFullYear(), 0, 1).toISOString().substring(0, 10),
                fecha_fin: new Date().toISOString().substring(0, 10)
            },
            resultadosData: {
                cuentas: [],
                totales: { ingresos: 0, gastos: 0, costos: 0 },
                utilidad_bruta: 0,
                utilidad_neta: 0
            },

            // Tab 3 Balanza
            filterBalanza: {
                fecha_inicio: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().substring(0, 10),
                fecha_fin: new Date().toISOString().substring(0, 10)
            },
            balanzaData: {
                cuentas: [],
                totales: {
                    saldo_anterior_debe: 0,
                    saldo_anterior_haber: 0,
                    debe_periodo: 0,
                    haber_periodo: 0,
                    saldo_final_debe: 0,
                    saldo_final_haber: 0
                }
            },

            // Tab 4 Auxiliar
            filterAuxiliar: {
                cuenta_id: '',
                fecha_inicio: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().substring(0, 10),
                fecha_fin: new Date().toISOString().substring(0, 10)
            },
            auxiliarData: {
                cuenta: { codigo: '', nombre: '', naturaleza: '' },
                saldo_anterior: 0,
                movimientos: [],
                total_debe: 0,
                total_haber: 0,
                saldo_final: 0
            }
        }
    },
    computed: {
        currentReportTitle() {
            switch(this.activeTab) {
                case 'balance': return 'Estado de Situación Financiera - Balance General';
                case 'resultados': return 'Estado de Resultados Integral (P&L)';
                case 'balanza': return 'Balanza de Comprobación Contable';
                case 'auxiliar': return 'Libro Auxiliar por Cuenta Contable';
                default: return 'Reportes Contables';
            }
        },
        selectedAccountName() {
            if (!this.filterAuxiliar.cuenta_id) return '';
            const match = this.arrayCuentasPUC.find(c => c.id === this.filterAuxiliar.cuenta_id);
            return match ? `${match.codigo} - ${match.nombre}` : '';
        }
    },
    methods: {
        setTab(tab) {
            this.activeTab = tab;
            this.ejecutarCargaTab();
        },
        ejecutarCargaTab() {
            if (this.activeTab === 'balance') {
                this.obtenerBalanceGeneral();
            } else if (this.activeTab === 'resultados') {
                this.obtenerEstadoResultados();
            } else if (this.activeTab === 'balanza') {
                this.obtenerBalanzaComprobacion();
            } else if (this.activeTab === 'auxiliar') {
                this.listarCuentasDetalle();
                if (this.filterAuxiliar.cuenta_id) {
                    this.obtenerAuxiliarCuenta();
                }
            }
        },
        formatCurrency(value) {
            if (value === undefined || value === null) return '$ 0,00';
            const cleanValue = parseFloat(value);
            const formatted = Math.abs(cleanValue).toLocaleString('es-CO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            return `${cleanValue < 0 ? '-' : ''}$ ${formatted}`;
        },
        // Styling based on code hierarchy
        rowStyle(cuenta) {
            const len = cuenta.codigo.length;
            if (len === 1) {
                return {
                    backgroundColor: '#e9ecef',
                    fontWeight: 'bold',
                    textTransform: 'uppercase',
                    color: '#212529'
                };
            } else if (len === 2) {
                return {
                    backgroundColor: '#f8f9fa',
                    fontWeight: 'bold',
                    color: '#343a40'
                };
            } else if (len === 4) {
                return {
                    fontWeight: '600',
                    color: '#495057'
                };
            }
            return {
                color: '#6c757d',
                fontSize: '0.95em'
            };
        },
        cellPadding(cuenta) {
            const len = cuenta.codigo.length;
            let pad = 8;
            if (len === 2) pad = 24;
            else if (len === 4) pad = 40;
            else if (len >= 6) pad = 56;
            return {
                paddingLeft: `${pad}px`
            };
        },
        obtenerBalanceGeneral() {
            this.loading = true;
            axios.get('/reportes-contables/balance-general', {
                params: { fecha_fin: this.filterBalance.fecha_fin }
            }).then(response => {
                this.balanceData = response.data;
                this.loading = false;
            }).catch(error => {
                console.error("Error al obtener Balance General", error);
                this.loading = false;
            });
        },
        obtenerEstadoResultados() {
            this.loading = true;
            axios.get('/reportes-contables/estado-resultados', {
                params: {
                    fecha_inicio: this.filterResultados.fecha_inicio,
                    fecha_fin: this.filterResultados.fecha_fin
                }
            }).then(response => {
                this.resultadosData = response.data;
                this.loading = false;
            }).catch(error => {
                console.error("Error al obtener Estado de Resultados", error);
                this.loading = false;
            });
        },
        obtenerBalanzaComprobacion() {
            this.loading = true;
            axios.get('/reportes-contables/balanza-comprobacion', {
                params: {
                    fecha_inicio: this.filterBalanza.fecha_inicio,
                    fecha_fin: this.filterBalanza.fecha_fin
                }
            }).then(response => {
                this.balanzaData = response.data;
                this.loading = false;
            }).catch(error => {
                console.error("Error al obtener Balanza de Comprobación", error);
                this.loading = false;
            });
        },
        listarCuentasDetalle() {
            // Cargar cuentas auxiliares para el selector
            axios.get('/cuentas-contables', {
                params: { es_detalle: 1 }
            }).then(response => {
                this.arrayCuentasPUC = response.data.cuentas || response.data;
            }).catch(error => {
                console.error("Error al listar cuentas de detalle", error);
            });
        },
        obtenerAuxiliarCuenta() {
            if (!this.filterAuxiliar.cuenta_id) return;
            this.loading = true;
            axios.get('/reportes-contables/auxiliar-cuenta', {
                params: {
                    cuenta_id: this.filterAuxiliar.cuenta_id,
                    fecha_inicio: this.filterAuxiliar.fecha_inicio,
                    fecha_fin: this.filterAuxiliar.fecha_fin
                }
            }).then(response => {
                this.auxiliarData = response.data;
                this.loading = false;
            }).catch(error => {
                console.error("Error al obtener Auxiliar de Cuenta", error);
                this.loading = false;
            });
        },
        imprimirReporte() {
            window.print();
        }
    },
    mounted() {
        this.ejecutarCargaTab();
    }
}
</script>

<style scoped>
/* Resumen de estilos y bordes decorativos */
.border-left-primary {
    border-left: 4px solid #20a8d8 !important;
}
.border-left-danger {
    border-left: 4px solid #f86c6b !important;
}
.border-left-info {
    border-left: 4px solid #63c2de !important;
}
.border-left-success {
    border-left: 4px solid #4dbd74 !important;
}
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.bg-warning-light {
    background-color: #fff3cd !important;
}

/* Custom print layouts inside Vue scoped styles for cleaner printing */
@media print {
    .no-print {
        display: none !important;
    }
    .main {
        padding: 0 !important;
        margin: 0 !important;
    }
    .card {
        border: none !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
    table {
        border: 1px solid #000 !important;
        width: 100% !important;
    }
    th, td {
        border: 1px solid #000 !important;
        padding: 6px 10px !important;
    }
}
</style>
