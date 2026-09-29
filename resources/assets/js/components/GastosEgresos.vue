<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Egresos y Caja Menor</li>
        </ol>

        <div class="container-fluid">
            <!-- Pestañas de Navegación -->
            <ul class="nav nav-tabs custom-tabs mb-4 no-print-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: tabActiva === 'egresos' }" href="#" @click.prevent="cambiarTab('egresos')">
                        <i class="fa fa-money"></i> Control de Gastos y Egresos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: tabActiva === 'caja_menor' }" href="#" @click.prevent="cambiarTab('caja_menor')">
                        <i class="fa fa-archive"></i> Libro de Caja Menor
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: tabActiva === 'resultados' }" href="#" @click.prevent="cambiarTab('resultados')">
                        <i class="fa fa-line-chart"></i> Estado de Resultados
                    </a>
                </li>
            </ul>

            <!-- KPI Summary Cards -->
            <div class="row no-print-row" v-if="tabActiva !== 'resultados'">
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white bg-primary kpi-card">
                        <div class="card-body pb-0">
                            <div class="text-value font-2xl">$ {{ formatMonto(totalEgresosMonto) }}</div>
                            <div>Total Egresos Registrados</div>
                        </div>
                        <div class="chart-wrapper mt-3 px-3" style="height:30px;">
                            <i class="fa fa-arrow-circle-down fa-lg float-right opacity-50"></i>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card text-white kpi-card" :class="cajaMenorSaldo > 100000 ? 'bg-success' : 'bg-warning'">
                        <div class="card-body pb-0">
                            <div class="text-value font-2xl">$ {{ formatMonto(cajaMenorSaldo) }}</div>
                            <div>Saldo Caja Menor</div>
                        </div>
                        <div class="chart-wrapper mt-3 px-3" style="height:30px;">
                            <i class="fa fa-university fa-lg float-right opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO TAB 1: EGRESOS -->
            <div v-if="tabActiva === 'egresos'" class="animated fadeIn">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> Registro General de Egresos
                        <div class="float-right">
                            <button type="button" @click="abrirModalClasificacion()" class="btn btn-primary btn-sm mr-2">
                                <i class="fa fa-cogs"></i>&nbsp;Clasificaciones
                            </button>
                            <button type="button" @click="abrirModalEgreso()" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i>&nbsp;Nuevo Egreso
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Concepto/Beneficiario</label>
                                <input type="text" v-model="buscarEgresos" @keyup.enter="listarEgresos(1)" class="form-control form-control-sm" placeholder="Buscar...">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Clasificación</label>
                                <select v-model="tipoEgresoFiltro" @change="listarEgresos(1)" class="form-control form-control-sm">
                                    <option value="">Todas</option>
                                    <option v-for="clas in arrayClasificaciones" :key="clas.id" :value="clas.nombre" v-text="clas.nombre"></option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Método de Pago</label>
                                <select v-model="metodoPagoFiltro" @change="listarEgresos(1)" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="Caja Menor">Caja Menor</option>
                                    <option value="Banco">Banco</option>
                                    <option value="Caja Mayor">Caja Mayor</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" v-model="fechaDesdeEgresos" @change="listarEgresos(1)" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" v-model="fechaHastaEgresos" @change="listarEgresos(1)" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-1 mb-2 d-flex align-items-end">
                                <button type="button" @click="listarEgresos(1)" class="btn btn-primary btn-sm btn-block">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm text-center">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Clasificación</th>
                                        <th>Concepto</th>
                                        <th>Valor</th>
                                        <th>Forma de Pago</th>
                                        <th>Beneficiario</th>
                                        <th>Soporte</th>
                                        <th>Imprimir</th>
                                        <th>Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody v-if="arrayEgresos.length > 0">
                                    <tr v-for="egreso in arrayEgresos" :key="egreso.id">
                                        <td v-text="egreso.fecha"></td>
                                        <td>
                                            <span class="badge badge-info" v-text="egreso.tipo_egreso"></span>
                                            <div v-if="egreso.cuenta" class="mt-1">
                                                <span class="badge badge-light border text-muted" style="font-weight: 500; font-size: 0.72rem; padding: 2px 4px;">
                                                    <i class="fa fa-book mr-1"></i> {{ egreso.cuenta.codigo }} - {{ egreso.cuenta.nombre }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-left" v-text="egreso.concepto"></td>
                                        <td class="font-weight-bold text-dark">$ {{ formatMonto(egreso.valor) }}</td>
                                        <td v-text="egreso.metodo_pago"></td>
                                        <td v-text="egreso.beneficiario || 'N/A'"></td>
                                        <td>
                                            <a v-if="egreso.soporte" :href="'/uploads/' + (egreso.abono_id ? 'abonos_cuentas_por_pagar/' : 'egresos/') + egreso.soporte" target="_blank" class="btn btn-info btn-xs" title="Ver Soporte Digital">
                                                <i class="fa fa-file-image-o"></i> Soporte
                                            </a>
                                            <span v-else class="text-muted small">Sin soporte</span>
                                        </td>
                                        <td>
                                            <button type="button" @click="descargarPDF(egreso.id)" class="btn btn-primary btn-xs" title="Imprimir Comprobante de Egreso">
                                                <i class="fa fa-print"></i> PDF
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" @click="eliminarEgreso(egreso.id)" class="btn btn-danger btn-xs" title="Eliminar registro">
                                                <i class="fa fa-trash"></i> Borrar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="9">No se encontraron registros de egresos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <nav v-if="paginationEgresos.last_page > 1">
                            <ul class="pagination pagination-sm">
                                <li class="page-item" v-if="paginationEgresos.current_page > 1">
                                    <a class="page-link" href="#" @click.prevent="cambiarPaginaEgresos(paginationEgresos.current_page - 1)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumberEgresos" :key="page" :class="[page == isActivedEgresos ? 'active' : '']">
                                    <a class="page-link" href="#" @click.prevent="cambiarPaginaEgresos(page)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="paginationEgresos.current_page < paginationEgresos.last_page">
                                    <a class="page-link" href="#" @click.prevent="cambiarPaginaEgresos(paginationEgresos.current_page + 1)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO TAB 2: CAJA MENOR -->
            <div v-if="tabActiva === 'caja_menor'" class="animated fadeIn">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> Bitácora de Caja Menor (Libro Diario)
                        <button type="button" @click="abrirModalRecarga()" class="btn btn-success float-right btn-sm">
                            <i class="fa fa-plus-circle"></i>&nbsp;Recargar Caja Menor
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Filtros Caja Menor -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" v-model="fechaDesdeCajaMenor" @change="listarCajaMenor(1)" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" v-model="fechaHastaCajaMenor" @change="listarCajaMenor(1)" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" @click="listarCajaMenor(1)" class="btn btn-primary btn-sm btn-block">
                                    <i class="fa fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>

                        <!-- Tabla Caja Menor -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm text-center">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Ingresos</th>
                                        <th>Egresos</th>
                                        <th>Saldo Resultante</th>
                                        <th>Concepto / Descripción</th>
                                        <th>Soporte</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody v-if="arrayCajaMenor.length > 0">
                                    <tr v-for="mov in arrayCajaMenor" :key="mov.id">
                                        <td v-text="mov.fecha"></td>
                                        <td class="font-weight-bold text-success text-right">
                                            <span v-if="mov.tipo === 'Ingreso'">$ {{ formatMonto(mov.monto) }}</span>
                                            <span v-else class="text-muted">-</span>
                                        </td>
                                        <td class="font-weight-bold text-danger text-right">
                                            <span v-if="mov.tipo === 'Egreso'">$ {{ formatMonto(mov.monto) }}</span>
                                            <span v-else class="text-muted">-</span>
                                        </td>
                                        <td class="font-weight-bold text-dark">$ {{ formatMonto(mov.saldo_resultante) }}</td>
                                        <td class="text-left" v-text="mov.descripcion"></td>
                                        <td>
                                            <a v-if="mov.soporte" :href="'/uploads/' + (mov.tipo === 'Ingreso' ? 'caja_menor/' : (mov.egreso && mov.egreso.abono_id ? 'abonos_cuentas_por_pagar/' : 'egresos/')) + mov.soporte" target="_blank" class="btn btn-info btn-xs">
                                                <i class="fa fa-file-image-o"></i> Soporte
                                            </a>
                                            <span v-else class="text-muted small">Sin soporte</span>
                                        </td>
                                        <td>
                                            <template v-if="mov.tipo === 'Ingreso'">
                                                <button type="button" @click="abrirModalRecargaEditar(mov)" class="btn btn-warning btn-xs" title="Editar recarga">
                                                    <i class="fa fa-edit"></i> Editar
                                                </button>
                                                <button type="button" @click="eliminarRecarga(mov.id)" class="btn btn-danger btn-xs" title="Eliminar recarga">
                                                    <i class="fa fa-trash"></i> Borrar
                                                </button>
                                            </template>
                                            <span v-else class="text-muted small">N/A</span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="7">No se encontraron movimientos de caja menor.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación Caja Menor -->
                        <nav v-if="paginationCajaMenor.last_page > 1">
                            <ul class="pagination pagination-sm">
                                <li class="page-item" v-if="paginationCajaMenor.current_page > 1">
                                    <a class="page-link" href="#" @click.prevent="cambiarPaginaCajaMenor(paginationCajaMenor.current_page - 1)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumberCajaMenor" :key="page" :class="[page == isActivedCajaMenor ? 'active' : '']">
                                    <a class="page-link" href="#" @click.prevent="cambiarPaginaCajaMenor(page)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="paginationCajaMenor.current_page < paginationCajaMenor.last_page">
                                    <a class="page-link" href="#" @click.prevent="cambiarPaginaCajaMenor(paginationCajaMenor.current_page + 1)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO TAB 3: ESTADO DE RESULTADOS (P&L) -->
            <div v-if="tabActiva === 'resultados'" class="animated fadeIn">
                <div class="card no-print-card">
                    <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="h5 font-weight-bold text-dark"><i class="fa fa-calculator text-primary"></i> Consolidado y Estado de Resultados</span>
                        </div>
                        <div>
                            <button type="button" @click="imprimirReporte()" class="btn btn-secondary btn-sm">
                                <i class="fa fa-print"></i> Imprimir Reporte
                            </button>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <!-- Filtros del Reporte -->
                        <div class="row mb-3 bg-white p-3 rounded shadow-sm">
                            <div class="col-md-4 mb-2">
                                <label class="form-label text-muted">Fecha Desde</label>
                                <input type="date" v-model="fechaDesdeResultados" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label text-muted">Fecha Hasta</label>
                                <input type="date" v-model="fechaHastaResultados" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4 mb-2 d-flex align-items-end">
                                <button type="button" @click="obtenerConsolidado()" class="btn btn-primary btn-sm btn-block" :disabled="loadingResultados">
                                    <span v-if="loadingResultados"><i class="fa fa-spinner fa-spin"></i> Cargando...</span>
                                    <span v-else><i class="fa fa-search"></i> Generar Reporte</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EL REPORTE IMPRIMIBLE -->
                <div id="seccion-reporte" class="print-container">
                    <!-- Cabecera de Impresión -->
                    <div class="print-header text-center mb-4 d-none d-print-block">
                        <h2 class="font-weight-bold mb-1">ESTADO DE RESULTADOS CONSOLIDADO</h2>
                        <h5 class="text-muted">Período: {{ fechaDesdeResultados }} al {{ fechaHastaResultados }}</h5>
                        <hr class="my-3 border-dark">
                    </div>

                    <!-- KPI Cards (Ocultas o estilizadas en print) -->
                    <div class="row no-print-row">
                        <div class="col-sm-4">
                            <div class="card bg-gradient-primary text-white kpi-dashboard-card">
                                <div class="card-body">
                                    <div class="text-uppercase small font-weight-bold opacity-75">Ventas Operacionales (Ingresos)</div>
                                    <div class="h2 font-weight-bold mt-2">$ {{ formatMonto(datosConsolidado.ventas_total) }}</div>
                                    <div class="small mt-1 text-white-50">Pedidos activos en el período</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card bg-gradient-danger text-white kpi-dashboard-card">
                                <div class="card-body">
                                    <div class="text-uppercase small font-weight-bold opacity-75">Egresos & Gastos Totales</div>
                                    <div class="h2 font-weight-bold mt-2">$ {{ formatMonto(datosConsolidado.egresos_total) }}</div>
                                    <div class="small mt-1 text-white-50">Pagos clasificados realizados</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card text-white kpi-dashboard-card" :class="(datosConsolidado.ventas_total - datosConsolidado.egresos_total) >= 0 ? 'bg-gradient-success' : 'bg-gradient-warning'">
                                <div class="card-body">
                                    <div class="text-uppercase small font-weight-bold opacity-75">Utilidad Neta del Período</div>
                                    <div class="h2 font-weight-bold mt-2">$ {{ formatMonto(datosConsolidado.ventas_total - datosConsolidado.egresos_total) }}</div>
                                    <div class="small mt-1 text-white-50">
                                        {{ (datosConsolidado.ventas_total - datosConsolidado.egresos_total) >= 0 ? 'Rentabilidad Positiva' : 'Pérdida en el período' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- P&L Principal -->
                        <div class="col-lg-8 col-md-12 mb-4">
                            <div class="card border-0 shadow-sm rounded">
                                <div class="card-header bg-white font-weight-bold py-3 text-dark">
                                    <i class="fa fa-list-alt text-primary"></i> Estructura del Estado de Resultados
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover table-striped pl-table mb-0">
                                        <thead>
                                            <tr class="bg-light">
                                                <th>Cuentas / Clasificaciones</th>
                                                <th class="text-right">Montos</th>
                                                <th class="text-right">% Incidencia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Ingresos -->
                                            <tr class="font-weight-bold text-dark table-group-row">
                                                <td><i class="fa fa-arrow-circle-up text-success"></i> INGRESOS OPERACIONALES</td>
                                                <td class="text-right text-success">$ {{ formatMonto(datosConsolidado.ventas_total) }}</td>
                                                <td class="text-right">100.00%</td>
                                            </tr>
                                            <tr class="table-indent-row text-secondary">
                                                <td>&nbsp;&nbsp;&nbsp;&nbsp;Ventas por Pedidos</td>
                                                <td class="text-right">$ {{ formatMonto(datosConsolidado.ventas_total) }}</td>
                                                <td class="text-right">100.00%</td>
                                            </tr>

                                            <!-- Espaciado -->
                                            <tr><td colspan="3" class="p-1 table-spacer-row"></td></tr>

                                            <!-- Egresos -->
                                            <tr class="font-weight-bold text-dark table-group-row">
                                                <td><i class="fa fa-arrow-circle-down text-danger"></i> EGRESOS Y GASTOS OPERACIONALES</td>
                                                <td class="text-right text-danger">- $ {{ formatMonto(datosConsolidado.egresos_total) }}</td>
                                                <td class="text-right">
                                                    {{ datosConsolidado.ventas_total > 0 ? ((datosConsolidado.egresos_total / datosConsolidado.ventas_total) * 100).toFixed(2) : '0.00' }}%
                                                </td>
                                            </tr>
                                            <!-- Sub-egresos clasificados -->
                                            <tr v-for="det in datosConsolidado.egresos_detallados" :key="det.tipo_egreso" class="table-indent-row text-secondary">
                                                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ det.tipo_egreso || 'Sin Clasificar' }}</td>
                                                <td class="text-right">- $ {{ formatMonto(det.total) }}</td>
                                                <td class="text-right text-muted small">
                                                    {{ datosConsolidado.ventas_total > 0 ? ((det.total / datosConsolidado.ventas_total) * 100).toFixed(2) : '0.00' }}%
                                                </td>
                                            </tr>
                                            <tr v-if="datosConsolidado.egresos_detallados.length === 0" class="table-indent-row text-muted italic">
                                                <td colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;No se registraron egresos en este rango.</td>
                                            </tr>

                                            <!-- Espaciado -->
                                            <tr><td colspan="3" class="p-2 table-spacer-row"></td></tr>

                                            <!-- Totales Finales -->
                                            <tr class="font-weight-bold text-white bg-dark-summary-row">
                                                <td class="pl-3 py-3 font-14">UTILIDAD OPERACIONAL NETA</td>
                                                <td class="text-right py-3 pr-3 font-14 font-weight-bold" :class="(datosConsolidado.ventas_total - datosConsolidado.egresos_total) >= 0 ? 'text-success' : 'text-danger'">
                                                    $ {{ formatMonto(datosConsolidado.ventas_total - datosConsolidado.egresos_total) }}
                                                </td>
                                                <td class="text-right py-3 pr-3 font-14">
                                                    {{ datosConsolidado.ventas_total > 0 ? (((datosConsolidado.ventas_total - datosConsolidado.egresos_total) / datosConsolidado.ventas_total) * 100).toFixed(2) : '0.00' }}%
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Panel de Flujos Auxiliares -->
                        <div class="col-lg-4 col-md-12">
                            <!-- Flujo Caja Menor -->
                            <div class="card border-0 shadow-sm rounded mb-4">
                                <div class="card-header bg-white font-weight-bold text-dark py-3">
                                    <i class="fa fa-university text-info"></i> Flujo de Caja Menor
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-striped mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="pl-3 py-2 text-secondary">Saldo Inicial (Antes del período)</td>
                                                <td class="text-right pr-3 font-weight-bold text-dark">$ {{ formatMonto(datosConsolidado.caja_menor.saldo_inicial) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 py-2 text-secondary">Recargas e Ingresos (+)</td>
                                                <td class="text-right pr-3 text-success font-weight-bold">+ $ {{ formatMonto(datosConsolidado.caja_menor.recargas) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 py-2 text-secondary">Egresos Caja Menor (-)</td>
                                                <td class="text-right pr-3 text-danger font-weight-bold">- $ {{ formatMonto(datosConsolidado.caja_menor.egresos) }}</td>
                                            </tr>
                                            <tr class="bg-light font-weight-bold text-dark">
                                                <td class="pl-3 py-2">Saldo Final Resultante</td>
                                                <td class="text-right pr-3">$ {{ formatMonto(datosConsolidado.caja_menor.saldo_final) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Cuentas por Pagar -->
                            <div class="card border-0 shadow-sm rounded">
                                <div class="card-header bg-white font-weight-bold text-dark py-3">
                                    <i class="fa fa-hourglass-half text-warning"></i> Cuentas por Pagar (CXP)
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-striped mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="pl-3 py-2 text-secondary">Deuda Nueva Adquirida</td>
                                                <td class="text-right pr-3 font-weight-bold text-dark">$ {{ formatMonto(datosConsolidado.cuentas_por_pagar.deuda_creada) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="pl-3 py-2 text-secondary">Abonos / Pagos de Deudas</td>
                                                <td class="text-right pr-3 text-success font-weight-bold">$ {{ formatMonto(datosConsolidado.cuentas_por_pagar.abonos_pagados) }}</td>
                                            </tr>
                                            <tr class="bg-light font-weight-bold text-secondary">
                                                <td class="pl-3 py-2 small">Nota: Los abonos ya están incluidos en Egresos Totales si se pagaron en este período.</td>
                                                <td></td>
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

        <!-- MODAL REGISTRO DE EGRESO -->
        <div class="modal fade" tabindex="-1" :class="{ 'mostrar': modalEgreso }" style="display: none;" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Registrar Nuevo Egreso</h4>
                        <button type="button" class="close" @click="cerrarModalEgreso()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Fecha (*)</label>
                                <div class="col-md-9">
                                    <input type="date" v-model="formEgreso.fecha" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Clasificación (*)</label>
                                <div class="col-md-9">
                                    <select v-model="formEgreso.tipo_egreso" class="form-control form-control-sm">
                                        <option v-for="clas in arrayClasificaciones" :key="clas.id" :value="clas.nombre" v-text="clas.nombre"></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Concepto (*)</label>
                                <div class="col-md-9">
                                    <textarea v-model="formEgreso.concepto" rows="3" class="form-control form-control-sm" placeholder="Descripción detallada de la compra o pago..."></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Valor (*)</label>
                                <div class="col-md-9">
                                    <input type="number" step="0.01" v-model.number="formEgreso.valor" class="form-control form-control-sm" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Forma de Pago (*)</label>
                                <div class="col-md-9">
                                    <select v-model="formEgreso.metodo_pago" class="form-control form-control-sm">
                                        <option value="Caja Menor">Caja Menor</option>
                                        <option value="Banco">Banco</option>
                                        <option value="Caja Mayor">Caja Mayor</option>
                                        <option value="Otros">Otros</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Beneficiario</label>
                                <div class="col-md-9">
                                    <input type="text" v-model="formEgreso.beneficiario" class="form-control form-control-sm" placeholder="Persona o empresa a quien se le paga">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Cuenta Contable (Debe)</label>
                                <div class="col-md-9">
                                    <select v-model="formEgreso.cuenta_id" class="form-control form-control-sm">
                                        <option value="">-- Selección Automática (Por Defecto) --</option>
                                        <option v-for="cta in arrayCuentasContables" :key="cta.id" :value="cta.id">
                                            {{ cta.codigo }} - {{ cta.nombre }}
                                        </option>
                                    </select>
                                    <span class="form-text text-muted small">
                                        Deje vacío para aplicar la cuenta automática por defecto según la clasificación.
                                    </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Soporte Digital</label>
                                <div class="col-md-9">
                                    <input type="file" ref="soporteInput" @change="onFileChangeEgreso" class="form-control form-control-sm">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModalEgreso()">Cerrar</button>
                        <button type="button" class="btn btn-primary btn-sm" @click="guardarEgreso()">Guardar Egreso</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL RECARGA DE CAJA MENOR -->
        <div class="modal fade" tabindex="-1" :class="{ 'mostrar': modalRecarga }" style="display: none;" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-success modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" v-text="formRecarga.id ? 'Editar Recarga de Caja Menor' : 'Recargar Caja Menor'"></h4>
                        <button type="button" class="close" @click="cerrarModalRecarga()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Fecha (*)</label>
                                <div class="col-md-9">
                                    <input type="date" v-model="formRecarga.fecha" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Monto de Recarga (*)</label>
                                <div class="col-md-9">
                                    <input type="number" step="0.01" v-model.number="formRecarga.monto" class="form-control form-control-sm" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Descripción / Concepto (*)</label>
                                <div class="col-md-9">
                                    <textarea v-model="formRecarga.descripcion" rows="3" class="form-control form-control-sm" placeholder="Detalle del ingreso o recarga de efectivo..."></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 form-control-label font-weight-bold">Soporte Digital</label>
                                <div class="col-md-9">
                                    <input type="file" ref="recargaInput" @change="onFileChangeRecarga" class="form-control form-control-sm">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModalRecarga()">Cerrar</button>
                        <button type="button" class="btn btn-success btn-sm" @click="guardarRecarga()" v-text="formRecarga.id ? 'Actualizar Recarga' : 'Registrar Recarga'"></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL GESTIÓN DE CLASIFICACIONES -->
        <div class="modal fade" tabindex="-1" :class="{ 'mostrar': modalClasificacion }" style="display: none;" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Administrar Clasificaciones</h4>
                        <button type="button" class="close" @click="cerrarModalClasificacion()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulario para agregar -->
                        <div class="card card-accent-primary mb-3">
                            <div class="card-header font-weight-bold">Nueva Clasificación</div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="form-control-label font-weight-bold">Nombre (*)</label>
                                    <input type="text" v-model="formClasificacion.nombre" class="form-control form-control-sm" placeholder="Ej. Papelería, Mantenimiento...">
                                </div>
                                <div class="form-group">
                                    <label class="form-control-label font-weight-bold">Descripción</label>
                                    <input type="text" v-model="formClasificacion.descripcion" class="form-control form-control-sm" placeholder="Opcional...">
                                </div>
                                <button type="button" class="btn btn-success btn-sm btn-block" @click="guardarClasificacion()" :disabled="loadingClasificacion">
                                    <i class="fa fa-save"></i>&nbsp;Guardar Clasificación
                                </button>
                            </div>
                        </div>

                        <!-- Listado de clasificaciones -->
                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-sm text-center">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="clas in arrayClasificaciones" :key="clas.id">
                                        <td class="font-weight-bold" v-text="clas.nombre"></td>
                                        <td class="text-left" v-text="clas.descripcion || 'Sin descripción'"></td>
                                        <td>
                                            <button type="button" @click="eliminarClasificacion(clas.id)" class="btn btn-danger btn-xs" title="Eliminar clasificación">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="arrayClasificaciones.length === 0">
                                        <td colspan="3">No hay clasificaciones registradas.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModalClasificacion()">Cerrar</button>
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
            tabActiva: 'egresos',
            offset: 3,

            // Resultados / Consolidado States
            fechaDesdeResultados: '',
            fechaHastaResultados: '',
            datosConsolidado: {
                ventas_total: 0.00,
                egresos_total: 0.00,
                egresos_detallados: [],
                caja_menor: {
                    saldo_inicial: 0.00,
                    recargas: 0.00,
                    egresos: 0.00,
                    saldo_final: 0.00
                },
                cuentas_por_pagar: {
                    deuda_creada: 0.00,
                    abonos_pagados: 0.00
                }
            },
            loadingResultados: false,

            // Egresos States
            arrayEgresos: [],
            totalEgresosMonto: 0.00,
            buscarEgresos: '',
            tipoEgresoFiltro: '',
            metodoPagoFiltro: '',
            fechaDesdeEgresos: '',
            fechaHastaEgresos: '',
            paginationEgresos: {
                total: 0,
                current_page: 1,
                per_page: 15,
                last_page: 1,
                from: 0,
                to: 0
            },

            // Caja Menor States
            cajaMenorSaldo: 0.00,
            arrayCajaMenor: [],
            fechaDesdeCajaMenor: '',
            fechaHastaCajaMenor: '',
            paginationCajaMenor: {
                total: 0,
                current_page: 1,
                per_page: 15,
                last_page: 1,
                from: 0,
                to: 0
            },

            // Modals
            modalEgreso: 0,
            modalRecarga: 0,

            // Form Data
            formEgreso: {
                fecha: '',
                tipo_egreso: 'Gasto',
                concepto: '',
                valor: 0,
                metodo_pago: 'Caja Menor',
                beneficiario: '',
                cuenta_id: ''
            },
            soporteFile: null,

            formRecarga: {
                id: 0,
                fecha: '',
                monto: 0,
                descripcion: ''
            },
            recargaFile: null,

            // Classifications States
            arrayClasificaciones: [],
            arrayCuentasContables: [],
            modalClasificacion: 0,
            formClasificacion: {
                nombre: '',
                descripcion: ''
            },
            loadingClasificacion: false
        }
    },
    computed: {
        // Egresos Pagination Helper
        isActivedEgresos() {
            return this.paginationEgresos.current_page;
        },
        pagesNumberEgresos() {
            if (!this.paginationEgresos.to) return [];
            let from = this.paginationEgresos.current_page - this.offset;
            if (from < 1) from = 1;
            let to = from + (this.offset * 2);
            if (to >= this.paginationEgresos.last_page) to = this.paginationEgresos.last_page;
            
            let pagesArray = [];
            for (let page = from; page <= to; page++) {
                pagesArray.push(page);
            }
            return pagesArray;
        },

        // Caja Menor Pagination Helper
        isActivedCajaMenor() {
            return this.paginationCajaMenor.current_page;
        },
        pagesNumberCajaMenor() {
            if (!this.paginationCajaMenor.to) return [];
            let from = this.paginationCajaMenor.current_page - this.offset;
            if (from < 1) from = 1;
            let to = from + (this.offset * 2);
            if (to >= this.paginationCajaMenor.last_page) to = this.paginationCajaMenor.last_page;

            let pagesArray = [];
            for (let page = from; page <= to; page++) {
                pagesArray.push(page);
            }
            return pagesArray;
        }
    },
    methods: {
        formatMonto(val) {
            if (val === undefined || val === null) return '0.00';
            return parseFloat(val).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        cambiarTab(tab) {
            this.tabActiva = tab;
            if (tab === 'egresos') {
                this.listarEgresos(1);
            } else if (tab === 'caja_menor') {
                this.listarCajaMenor(1);
            } else if (tab === 'resultados') {
                this.obtenerConsolidado();
            }
        },

        // --- GENERAL EGRESOS METHODS ---
        listarEgresos(page) {
            let me = this;
            let url = `/egresos?page=${page}`
                + `&tipo_egreso=${me.tipoEgresoFiltro}`
                + `&metodo_pago=${me.metodoPagoFiltro}`
                + `&fecha_desde=${me.fechaDesdeEgresos}`
                + `&fecha_hasta=${me.fechaHastaEgresos}`
                + `&buscar=${me.buscarEgresos}`;

            axios.get(url).then(response => {
                let r = response.data;
                me.arrayEgresos = r.egresos.data;
                me.paginationEgresos = r.pagination;
                
                // Let's compute a sum of filtered/loaded egresos or running total for visual representation
                me.recalcularEgresosTotal();
            }).catch(error => {
                console.error(error);
            });
        },
        recalcularEgresosTotal() {
            // Recalculates from current loaded page or we can fetch a total. Let's do simple sum of loaded array.
            this.totalEgresosMonto = this.arrayEgresos.reduce((acc, curr) => acc + parseFloat(curr.valor), 0);
        },
        cambiarPaginaEgresos(page) {
            this.paginationEgresos.current_page = page;
            this.listarEgresos(page);
        },
        abrirModalEgreso() {
            this.modalEgreso = 1;
            this.formEgreso = {
                fecha: new Date().toISOString().split('T')[0],
                tipo_egreso: this.arrayClasificaciones.length > 0 ? this.arrayClasificaciones[0].nombre : 'Gasto',
                concepto: '',
                valor: 0,
                metodo_pago: 'Caja Menor',
                beneficiario: '',
                cuenta_id: ''
            };
            this.soporteFile = null;
            if (this.$refs.soporteInput) {
                this.$refs.soporteInput.value = '';
            }
        },
        cerrarModalEgreso() {
            this.modalEgreso = 0;
        },
        onFileChangeEgreso(e) {
            let files = e.target.files || e.dataTransfer.files;
            if (!files.length) return;
            this.soporteFile = files[0];
        },
        guardarEgreso() {
            let me = this;
            if (!me.formEgreso.concepto || !me.formEgreso.valor || !me.formEgreso.fecha) {
                Swal.fire('Error', 'Debe completar todos los campos requeridos (*).', 'error');
                return;
            }

            const data = new FormData();
            data.append('fecha', me.formEgreso.fecha);
            data.append('tipo_egreso', me.formEgreso.tipo_egreso);
            data.append('concepto', me.formEgreso.concepto);
            data.append('valor', me.formEgreso.valor);
            data.append('metodo_pago', me.formEgreso.metodo_pago);
            if (me.formEgreso.beneficiario) {
                data.append('beneficiario', me.formEgreso.beneficiario);
            }
            if (me.formEgreso.cuenta_id) {
                data.append('cuenta_id', me.formEgreso.cuenta_id);
            }
            if (me.soporteFile) {
                data.append('soporte', me.soporteFile);
            }

            axios.post('/egresos/registrar', data, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(response => {
                me.cerrarModalEgreso();
                Swal.fire('Guardado', 'El egreso ha sido registrado con éxito.', 'success');
                me.listarEgresos(1);
                me.cargarSaldoCajaMenor();
            }).catch(error => {
                if (error.response && error.response.data && error.response.data.error) {
                    Swal.fire('Error', error.response.data.error, 'error');
                } else {
                    Swal.fire('Error', 'Ocurrió un error al registrar el egreso.', 'error');
                }
            });
        },
        eliminarEgreso(id) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar este egreso?',
                text: "Si se pagó por Caja Menor, el saldo de la caja se recalculará automáticamente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.value) {
                    axios.delete(`/egresos/eliminar/${id}`).then(response => {
                        Swal.fire('Eliminado', 'El egreso ha sido eliminado.', 'success');
                        me.listarEgresos(1);
                        me.cargarSaldoCajaMenor();
                    }).catch(error => {
                        console.error(error);
                        Swal.fire('Error', 'No se pudo eliminar el egreso.', 'error');
                    });
                }
            });
        },
        descargarPDF(id) {
            window.open(`/egresos/pdf/${id}`, '_blank');
        },

        // --- CAJA MENOR METHODS ---
        cargarSaldoCajaMenor() {
            let me = this;
            axios.get('/caja-menor/status').then(response => {
                me.cajaMenorSaldo = parseFloat(response.data.saldo);
            }).catch(error => {
                console.error(error);
            });
        },
        listarCajaMenor(page) {
            let me = this;
            let url = `/caja-menor/movimientos?page=${page}`
                + `&fecha_desde=${me.fechaDesdeCajaMenor}`
                + `&fecha_hasta=${me.fechaHastaCajaMenor}`;

            axios.get(url).then(response => {
                let r = response.data;
                me.arrayCajaMenor = r.movimientos.data;
                me.paginationCajaMenor = r.pagination;
            }).catch(error => {
                console.error(error);
            });
        },
        cambiarPaginaCajaMenor(page) {
            this.paginationCajaMenor.current_page = page;
            this.listarCajaMenor(page);
        },
        abrirModalRecarga() {
            this.modalRecarga = 1;
            this.formRecarga = {
                id: 0,
                fecha: new Date().toISOString().split('T')[0],
                monto: 0,
                descripcion: ''
            };
            this.recargaFile = null;
            if (this.$refs.recargaInput) {
                this.$refs.recargaInput.value = '';
            }
        },
        cerrarModalRecarga() {
            this.modalRecarga = 0;
        },
        onFileChangeRecarga(e) {
            let files = e.target.files || e.dataTransfer.files;
            if (!files.length) return;
            this.recargaFile = files[0];
        },
        abrirModalRecargaEditar(mov) {
            this.modalRecarga = 1;
            this.formRecarga = {
                id: mov.id,
                fecha: mov.fecha,
                monto: parseFloat(mov.monto),
                descripcion: mov.descripcion
            };
            this.recargaFile = null;
            if (this.$refs.recargaInput) {
                this.$refs.recargaInput.value = '';
            }
        },
        guardarRecarga() {
            let me = this;
            if (!me.formRecarga.monto || !me.formRecarga.fecha || !me.formRecarga.descripcion) {
                Swal.fire('Error', 'Debe completar todos los campos requeridos (*).', 'error');
                return;
            }

            const data = new FormData();
            data.append('fecha', me.formRecarga.fecha);
            data.append('monto', me.formRecarga.monto);
            data.append('descripcion', me.formRecarga.descripcion);
            if (me.recargaFile) {
                data.append('soporte', me.recargaFile);
            }

            let url = me.formRecarga.id ? `/caja-menor/recargar/actualizar/${me.formRecarga.id}` : '/caja-menor/recargar';

            axios.post(url, data, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(response => {
                me.cerrarModalRecarga();
                Swal.fire(
                    me.formRecarga.id ? 'Actualizado' : 'Recargado',
                    me.formRecarga.id ? 'La recarga ha sido actualizada con éxito.' : 'La Caja Menor ha sido recargada con éxito.',
                    'success'
                );
                me.cargarSaldoCajaMenor();
                if (me.tabActiva === 'caja_menor') {
                    me.listarCajaMenor(1);
                }
            }).catch(error => {
                console.error(error);
                let msg = me.formRecarga.id ? 'No se pudo actualizar la recarga.' : 'No se pudo realizar la recarga.';
                if (error.response && error.response.data && error.response.data.error) {
                    msg = error.response.data.error;
                }
                Swal.fire('Error', msg, 'error');
            });
        },
        eliminarRecarga(id) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar esta recarga?',
                text: "El saldo de la Caja Menor se recalculará automáticamente de forma cronológica.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.value) {
                    axios.delete(`/caja-menor/recargar/eliminar/${id}`).then(response => {
                        Swal.fire('Eliminado', 'La recarga ha sido eliminada.', 'success');
                        me.cargarSaldoCajaMenor();
                        if (me.tabActiva === 'caja_menor') {
                            me.listarCajaMenor(1);
                        }
                    }).catch(error => {
                        console.error(error);
                        let msg = 'No se pudo eliminar la recarga.';
                        if (error.response && error.response.data && error.response.data.error) {
                            msg = error.response.data.error;
                        }
                        Swal.fire('Error', msg, 'error');
                    });
                }
            });
        },

        // --- CLASIFICACIONES DE EGRESOS METHODS ---
        listarClasificaciones() {
            let me = this;
            axios.get('/clasificaciones-egresos').then(response => {
                me.arrayClasificaciones = response.data;
            }).catch(error => {
                console.error(error);
            });
        },
        abrirModalClasificacion() {
            this.modalClasificacion = 1;
            this.formClasificacion = { nombre: '', descripcion: '' };
        },
        cerrarModalClasificacion() {
            this.modalClasificacion = 0;
        },
        guardarClasificacion() {
            let me = this;
            if (!me.formClasificacion.nombre) {
                Swal.fire('Error', 'Debe ingresar el nombre de la clasificación.', 'error');
                return;
            }
            me.loadingClasificacion = true;
            axios.post('/clasificaciones-egresos/registrar', me.formClasificacion).then(response => {
                me.loadingClasificacion = false;
                Swal.fire('Guardado', 'Clasificación guardada con éxito.', 'success');
                me.formClasificacion = { nombre: '', descripcion: '' };
                me.listarClasificaciones();
            }).catch(error => {
                me.loadingClasificacion = false;
                let msg = 'Ocurrió un error al registrar.';
                if (error.response && error.response.data && error.response.data.errors && error.response.data.errors.nombre) {
                    msg = error.response.data.errors.nombre[0];
                } else if (error.response && error.response.data && error.response.data.error) {
                    msg = error.response.data.error;
                } else if (error.response && error.response.data && error.response.data.message) {
                    msg = error.response.data.message;
                }
                Swal.fire('Error', msg, 'error');
            });
        },
        eliminarClasificacion(id) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar esta clasificación?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.value) {
                    axios.delete(`/clasificaciones-egresos/eliminar/${id}`).then(response => {
                        Swal.fire('Eliminado', 'La clasificación ha sido eliminada.', 'success');
                        me.listarClasificaciones();
                    }).catch(error => {
                        let msg = 'No se pudo eliminar la clasificación.';
                        if (error.response && error.response.data && error.response.data.error) {
                            msg = error.response.data.error;
                        }
                        Swal.fire('Error', msg, 'error');
                    });
                }
            });
        },
        obtenerConsolidado() {
            let me = this;
            me.loadingResultados = true;
            let url = `/egresos/consolidado-resultados?fecha_desde=${me.fechaDesdeResultados}&fecha_hasta=${me.fechaHastaResultados}`;
            axios.get(url).then(response => {
                me.datosConsolidado = response.data;
                me.loadingResultados = false;
            }).catch(error => {
                console.error(error);
                me.loadingResultados = false;
                Swal.fire('Error', 'No se pudo cargar el consolidado de resultados.', 'error');
            });
        },
        listarCuentasContables() {
            let me = this;
            axios.get('/cuentas-contables?es_detalle=1').then(response => {
                me.arrayCuentasContables = response.data.cuentas || [];
            }).catch(error => console.error(error));
        },
        imprimirReporte() {
            window.print();
        }
    },
    mounted() {
        let date = new Date();
        let y = date.getFullYear();
        let m = String(date.getMonth() + 1).padStart(2, '0');
        this.fechaDesdeResultados = `${y}-${m}-01`;
        let lastDayObj = new Date(y, date.getMonth() + 1, 0);
        this.fechaHastaResultados = `${y}-${m}-${String(lastDayObj.getDate()).padStart(2, '0')}`;

        this.listarClasificaciones();
        this.listarEgresos(1);
        this.cargarSaldoCajaMenor();
        this.listarCuentasContables();
    }
}
</script>

<style scoped>
.custom-tabs .nav-link {
    font-weight: 600;
    color: #4a5568;
    border-radius: 6px 6px 0 0;
}
.custom-tabs .nav-link.active {
    color: #3182ce;
    border-bottom: 2px solid #3182ce;
    background-color: #f7fafc;
}
.kpi-card {
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    margin-bottom: 1.5rem;
    transition: transform 0.2s ease-in-out;
}
.kpi-card:hover {
    transform: translateY(-2px);
}
.modal.mostrar {
    display: block !important;
    opacity: 1 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    transition: background-color 0.15s linear;
    overflow-y: auto;
}
.table td, .table th {
    vertical-align: middle;
}
.btn-xs {
    padding: 2px 5px;
    font-size: 11px;
}
.opacity-50 {
    opacity: 0.5;
}
.form-label {
    font-weight: 600;
    font-size: 11px;
    margin-bottom: 2px;
    color: #4a5568;
}

/* Consolidado / P&L Premium Styles */
.bg-gradient-primary {
    background: linear-gradient(135deg, #3182ce 0%, #2b6cb0 100%);
}
.bg-gradient-danger {
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #dd6b20 0%, #c05621 100%);
}
.kpi-dashboard-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}
.pl-table {
    border-radius: 8px;
    overflow: hidden;
}
.table-group-row {
    background-color: #f7fafc !important;
}
.table-indent-row td:first-child {
    padding-left: 2rem;
}
.bg-dark-summary-row {
    background-color: #2d3748 !important;
}
.font-14 {
    font-size: 14px;
}
.italic {
    font-style: italic;
}

/* Printing styles */
@media print {
    .app-header, .sidebar, .breadcrumb, .footer, .no-print-tabs, .no-print-card, .no-print-row {
        display: none !important;
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
    }
    .main {
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding: 0 !important;
        margin-top: 0 !important;
        width: 100% !important;
    }
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
    }
    .print-container {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    .table td, .table th {
        background-color: #fff !important;
        color: #000 !important;
        border: 1px solid #dee2e6 !important;
    }
    .bg-dark-summary-row {
        background-color: #e2e8f0 !important;
        color: #000 !important;
    }
    .bg-dark-summary-row td {
        color: #000 !important;
    }
}
</style>
