<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb no-print-tabs">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Nómina</li>
        </ol>

        <div class="container-fluid">
            <!-- VISTA 1: LISTADO DE EMPLEADOS -->
            <!-- CONTAINER DE NÓMINA (TABS) -->
            <div v-if="['listado', 'historial_quincenas', 'seguridad_social', 'primas_semestrales', 'historial_primas'].includes(vista)" class="animated fadeIn no-print-card">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0 pb-0">
                        <span class="h5 font-weight-bold text-dark mb-0">
                            <i class="fa fa-money text-primary"></i> Gestión de Nómina y Seguridad Social
                        </span>
                        <ul class="nav nav-pills card-header-pills ml-auto">
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" :class="{ 'active': vista === 'listado' }" href="#" @click.prevent="cambiarVistaTab('listado')">
                                    <i class="fa fa-users"></i> Empleados
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" :class="{ 'active': vista === 'historial_quincenas' }" href="#" @click.prevent="cambiarVistaTab('historial_quincenas')">
                                    <i class="fa fa-history"></i> Historial de Quincenas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" :class="{ 'active': vista === 'seguridad_social' }" href="#" @click.prevent="cambiarVistaTab('seguridad_social')">
                                    <i class="fa fa-shield"></i> Seguridad Social Mensual
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" :class="{ 'active': ['primas_semestrales', 'historial_primas'].includes(vista) }" href="#" @click.prevent="cambiarVistaTab('primas_semestrales')">
                                    <i class="fa fa-gift"></i> Primas Semestrales
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <!-- TAB 1: LISTADO DE EMPLEADOS -->
                        <div v-if="vista === 'listado'">
                            <!-- Filtros de búsqueda y Configuración Global -->
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" v-model="buscar" @keyup.enter="listarEmpleados(1)" class="form-control form-control-sm" placeholder="Buscar por nombre exacto...">
                                        <div class="input-group-append">
                                            <button type="button" @click="listarEmpleados(1)" class="btn btn-primary btn-sm">
                                                <i class="fa fa-search"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" @click="abrirModalConfigNomina" class="btn btn-warning btn-sm font-weight-bold text-dark shadow-sm">
                                        <i class="fa fa-cogs mr-1"></i> Configurar Auxilio de Transporte ($ {{ formatMonto(auxilioTransporteGlobal) }})
                                    </button>
                                </div>
                            </div>

                            <!-- Tabla de Empleados -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover text-center table-sm">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Foto</th>
                                            <th>Empleado</th>
                                            <th>Identificación</th>
                                            <th>Cargo / Área</th>
                                            <th>Fecha Ingreso</th>
                                            <th>Salario Base</th>
                                            <th>Contrato</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayEmpleados.length > 0">
                                        <tr v-for="emp in arrayEmpleados" :key="emp.id">
                                            <td>
                                                <figure class="m-0 text-center">
                                                    <img v-if="emp.foto" :src="`img/productos/${emp.foto}`" width="40px" height="40px" class="rounded-circle shadow-sm" alt="Foto">
                                                    <i v-else class="fa fa-user-circle fa-2x text-muted"></i>
                                                </figure>
                                            </td>
                                            <td class="font-weight-bold text-dark text-left">
                                                {{ emp.nombre }} {{ emp.apellido }}
                                            </td>
                                            <td>{{ emp.tipo_doc }} {{ emp.num_doc }}</td>
                                            <td class="text-left">
                                                <span class="font-weight-bold">{{ emp.cargo || 'N/A' }}</span>
                                                <br>
                                                <small class="text-muted">{{ emp.area || 'N/A' }}</small>
                                            </td>
                                            <td>{{ emp.fecha_ingreso || 'N/A' }}</td>
                                            <td class="font-weight-bold text-dark">$ {{ formatMonto(emp.salario) }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ emp.tipo_contrato || 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <button type="button" @click="abrirCalculadora(emp)" class="btn btn-primary btn-sm mr-2" title="Realizar Liquidación Definitiva">
                                                        <i class="fa fa-calculator"></i> Liquidar
                                                    </button>
                                                    <button type="button" @click="abrirCalculadoraHoras(emp)" class="btn btn-warning btn-sm text-dark font-weight-bold" title="Liquidar Horas Extras y Quincena">
                                                        <i class="fa fa-clock-o"></i> Liquidar Quincena
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="8" class="py-4">No se encontraron empleados registrados.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <nav v-if="pagination.last_page > 1">
                                <ul class="pagination pagination-sm">
                                    <li class="page-item" v-if="pagination.current_page > 1">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page)" v-text="page"></a>
                                    </li>
                                    <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>

                        <!-- TAB 2: HISTORIAL DE QUINCENAS -->
                        <div v-if="vista === 'historial_quincenas'">
                            <!-- Filtros -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label text-dark font-weight-bold">Empleado</label>
                                    <select v-model="filtroQuincenaEmpleadoId" class="form-control form-control-sm" @change="listarQuincenas(1)">
                                        <option value="">-- Todos los Empleados --</option>
                                        <option v-for="emp in arrayTodosEmpleados" :key="emp.id" :value="emp.id">
                                            {{ emp.nombre }} {{ emp.apellido }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-dark font-weight-bold">Fecha Desde (Pago)</label>
                                    <input type="date" v-model="filtroQuincenaFechaDesde" class="form-control form-control-sm" @change="listarQuincenas(1)">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-dark font-weight-bold">Fecha Hasta (Pago)</label>
                                    <input type="date" v-model="filtroQuincenaFechaHasta" class="form-control form-control-sm" @change="listarQuincenas(1)">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" @click="filtroQuincenaEmpleadoId=''; filtroQuincenaFechaDesde=''; filtroQuincenaFechaHasta=''; listarQuincenas(1);" class="btn btn-secondary btn-sm w-100">
                                        <i class="fa fa-refresh"></i> Limpiar Filtros
                                    </button>
                                </div>
                            </div>

                            <!-- Tabla de Quincenas -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover text-center table-sm">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Consecutivo</th>
                                            <th>Empleado</th>
                                            <th>Fecha Pago</th>
                                            <th>Periodo</th>
                                            <th>Días</th>
                                            <th>Sueldo Neto</th>
                                            <th>Aux. Trans.</th>
                                            <th>Horas Extras</th>
                                            <th>Deducciones</th>
                                            <th>Neto Pagado</th>
                                            <th>Estado</th>
                                            <th>Egreso Asoc.</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayQuincenas.length > 0">
                                        <tr v-for="q in arrayQuincenas" :key="q.id">
                                            <td class="font-weight-bold">#{{ q.id }}</td>
                                            <td class="text-left font-weight-bold text-dark">
                                                {{ q.empleado ? `${q.empleado.nombre} ${q.empleado.apellido}` : 'Desconocido' }}
                                            </td>
                                            <td>{{ q.fecha_pago }}</td>
                                            <td>{{ q.fecha_inicio }} al {{ q.fecha_fin }}</td>
                                            <td>{{ q.dias_trabajados }}</td>
                                            <td class="text-right">$ {{ formatMonto(q.sueldo_neto) }}</td>
                                            <td class="text-right">$ {{ formatMonto(q.auxilio_transporte) }}</td>
                                            <td class="text-right text-success font-weight-bold">$ {{ formatMonto(q.monto_extras) }}</td>
                                            <td class="text-right text-danger">$ {{ formatMonto(parseFloat(q.salud_deduccion) + parseFloat(q.pension_deduccion) + parseFloat(q.otras_deducciones)) }}</td>
                                            <td class="text-right font-weight-bold text-primary">$ {{ formatMonto(q.neto_pagado) }}</td>
                                            <td>
                                                <span v-if="q.estado_display === 'Pagada' || q.estado === 'Pagada' || q.egreso_id" class="badge badge-success px-2 py-1 font-weight-bold">
                                                    <i class="fa fa-check-circle"></i> Pagada
                                                </span>
                                                <span v-else class="badge badge-warning px-2 py-1 font-weight-bold text-dark">
                                                    <i class="fa fa-clock-o"></i> Por Pagar
                                                </span>
                                            </td>
                                            <td>
                                                <a v-if="q.egreso" :href="`/egresos/pdf/${q.egreso.id}`" target="_blank" class="btn btn-outline-success btn-xs" title="Ver Egreso PDF">
                                                    <i class="fa fa-file-pdf-o"></i> Egreso #{{ q.egreso.id }}
                                                </a>
                                                <span v-else class="text-muted">Ninguno</span>
                                            </td>
                                            <td>
                                                <button v-if="!(q.estado_display === 'Pagada' || q.estado === 'Pagada' || q.egreso_id)" type="button" @click="abrirModalPagarQuincena(q)" class="btn btn-success btn-sm font-weight-bold mr-1" title="Pagar Quincena (Generar Egreso y Contabilidad)">
                                                    <i class="fa fa-dollar"></i> Pagar
                                                </button>
                                                <button type="button" @click="abrirEditarQuincena(q)" class="btn btn-warning btn-sm text-dark font-weight-bold" title="Editar Liquidación">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <a :href="`/nomina/quincenas/pdf/${q.id}`" target="_blank" class="btn btn-danger btn-sm" title="Descargar PDF">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </a>
                                                <button type="button" @click="reimprimirQuincena(q)" class="btn btn-info btn-sm text-white" title="Reimprimir Recibo de Pago">
                                                    <i class="fa fa-print"></i>
                                                </button>
                                                <button type="button" @click="eliminarQuincena(q.id)" class="btn btn-danger btn-sm" title="Eliminar Liquidación">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="13" class="py-4">No se encontraron liquidaciones de quincenas.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación Quincenas -->
                            <nav v-if="paginacionQuincenas.last_page > 1">
                                <ul class="pagination pagination-sm">
                                    <li class="page-item" v-if="paginacionQuincenas.current_page > 1">
                                        <a class="page-link" href="#" @click.prevent="listarQuincenas(paginacionQuincenas.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in paginacionQuincenas.last_page" :key="page" :class="[page == paginacionQuincenas.current_page ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="listarQuincenas(page)" v-text="page"></a>
                                    </li>
                                    <li class="page-item" v-if="paginacionQuincenas.current_page < paginacionQuincenas.last_page">
                                        <a class="page-link" href="#" @click.prevent="listarQuincenas(paginacionQuincenas.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>

                        <!-- TAB 3: SEGURIDAD SOCIAL MENSUAL -->
                        <div v-if="vista === 'seguridad_social'">
                            <!-- Filtros -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label text-dark font-weight-bold">Año</label>
                                    <select v-model.number="reporteSegAnio" class="form-control form-control-sm">
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-dark font-weight-bold">Mes</label>
                                    <select v-model.number="reporteSegMes" class="form-control form-control-sm">
                                        <option value="1">Enero</option>
                                        <option value="2">Febrero</option>
                                        <option value="3">Marzo</option>
                                        <option value="4">Abril</option>
                                        <option value="5">Mayo</option>
                                        <option value="6">Junio</option>
                                        <option value="7">Julio</option>
                                        <option value="8">Agosto</option>
                                        <option value="9">Septiembre</option>
                                        <option value="10">Octubre</option>
                                        <option value="11">Noviembre</option>
                                        <option value="12">Diciembre</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" @click="generarReporteSeguridadSocial()" class="btn btn-primary btn-sm w-100" :disabled="cargandoReporteSeg">
                                        <i class="fa" :class="cargandoReporteSeg ? 'fa-spin fa-spinner' : 'fa-search'"></i> Generar Reporte PILA
                                    </button>
                                </div>
                            </div>

                            <div v-if="cargandoReporteSeg" class="text-center py-5">
                                <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                                <p class="mt-2 text-muted">Procesando y consolidando aportes de nómina...</p>
                            </div>

                            <!-- Tabla del Reporte PILA -->
                            <div v-else-if="arrayReporteSeguridadSocial.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover text-center table-sm">
                                        <thead>
                                            <tr class="bg-primary text-white">
                                                <th rowspan="2" class="align-middle">Identificación</th>
                                                <th rowspan="2" class="align-middle">Empleado</th>
                                                <th rowspan="2" class="align-middle">Días</th>
                                                <th rowspan="2" class="align-middle">IBC</th>
                                                <th colspan="3" class="align-middle">Salud (EPS)</th>
                                                <th colspan="3" class="align-middle">Pensión (AFP)</th>
                                                <th rowspan="2" class="align-middle">ARL (0.522%)</th>
                                                <th rowspan="2" class="align-middle">Total Aportes</th>
                                            </tr>
                                            <tr class="bg-light text-dark">
                                                <th>Trab. (4%)</th>
                                                <th>Empl. (8.5%)</th>
                                                <th>Total (12.5%)</th>
                                                <th>Trab. (4%)</th>
                                                <th>Empl. (12%)</th>
                                                <th>Total (16%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="rep in arrayReporteSeguridadSocial" :key="rep.empleado_id">
                                                <td>{{ rep.identificacion }}</td>
                                                <td class="text-left font-weight-bold text-dark">{{ rep.nombre }}</td>
                                                <td>{{ rep.dias_trabajados }}</td>
                                                <td class="text-right font-weight-bold">$ {{ formatMonto(rep.ibc) }}</td>
                                                <td class="text-right text-danger">$ {{ formatMonto(rep.salud.empleado) }}</td>
                                                <td class="text-right">$ {{ formatMonto(rep.salud.empleador) }}</td>
                                                <td class="text-right font-weight-bold">$ {{ formatMonto(rep.salud.total) }}</td>
                                                <td class="text-right text-danger">$ {{ formatMonto(rep.pension.empleado) }}</td>
                                                <td class="text-right">$ {{ formatMonto(rep.pension.empleador) }}</td>
                                                <td class="text-right font-weight-bold">$ {{ formatMonto(rep.pension.total) }}</td>
                                                <td class="text-right">$ {{ formatMonto(rep.arl) }}</td>
                                                <td class="text-right font-weight-bold text-success">$ {{ formatMonto(rep.total_seguridad_social) }}</td>
                                            </tr>
                                            <!-- Totales Consolidados -->
                                            <tr class="bg-dark text-white font-weight-bold font-13">
                                                <td colspan="2" class="text-left py-2">TOTAL CONSOLIDADO</td>
                                                <td>{{ arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.dias_trabajados, 0) }}</td>
                                                <td class="text-right">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.ibc, 0)) }}</td>
                                                <td class="text-right">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.salud.empleado, 0)) }}</td>
                                                <td class="text-right">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.salud.empleador, 0)) }}</td>
                                                <td class="text-right">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.salud.total, 0)) }}</td>
                                                <td class="text-right">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.pension.empleado, 0)) }}</td>
                                                <td class="text-right">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.pension.empleador, 0)) }}</td>
                                                <td class="text-right">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.pension.total, 0)) }}</td>
                                                <td class="text-right">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.arl, 0)) }}</td>
                                                <td class="text-right text-success font-weight-bold">$ {{ formatMonto(arrayReporteSeguridadSocial.reduce((acc, r) => acc + r.total_seguridad_social, 0)) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 text-muted small">
                                    <i class="fa fa-info-circle text-info"></i> <strong>Nota:</strong> Los cálculos de aportes del empleador están sujetos a las exoneraciones vigentes según el art. 114-1 del Estatuto Tributario para trabajadores que devenguen menos de 10 SMMLV.
                                </div>
                            </div>
                            <div v-else class="text-center py-5 text-muted">
                                <i class="fa fa-folder-open fa-2x mb-2"></i>
                                <p class="mb-0">No se encontraron pagos de quincenas registrados en el mes/año seleccionado.</p>
                            </div>
                        </div>

                        <!-- TAB 4: PRIMAS SEMESTRALES -->
                        <div v-if="vista === 'primas_semestrales'">
                            <!-- Mini-navigation for calculation vs history -->
                            <div class="row mb-3 border-bottom pb-2">
                                <div class="col-12">
                                    <button type="button" @click="vista = 'primas_semestrales'" class="btn btn-sm" :class="vista === 'primas_semestrales' ? 'btn-primary' : 'btn-outline-primary'">
                                        <i class="fa fa-calculator"></i> Calcular Semestre
                                    </button>
                                    <button type="button" @click="cambiarVistaTab('historial_primas')" class="btn btn-sm" :class="vista === 'historial_primas' ? 'btn-primary' : 'btn-outline-primary'">
                                        <i class="fa fa-list"></i> Historial de Primas Pagadas
                                    </button>
                                </div>
                            </div>

                            <!-- Filtros y Opciones del Semestre -->
                            <div class="row mb-3 bg-light p-3 rounded mx-0">
                                <div class="col-md-2">
                                    <label class="form-label text-dark font-weight-bold">Año</label>
                                    <select v-model.number="primaFiltroAnio" class="form-control form-control-sm">
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-dark font-weight-bold">Semestre / Período</label>
                                    <select v-model.number="primaFiltroPeriodo" class="form-control form-control-sm">
                                        <option value="1">I Semestre (Ene - Jun)</option>
                                        <option value="2">II Semestre (Jul - Dic)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-dark font-weight-bold">Fecha de Pago</label>
                                    <input type="date" v-model="primaFechaPago" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-dark font-weight-bold">Método Pago</label>
                                    <select v-model="primaMetodoPago" class="form-control form-control-sm">
                                        <option value="Banco">Banco</option>
                                        <option value="Caja Menor">Caja Menor</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" @click="calcularPrimasSemestre()" class="btn btn-primary btn-sm w-100" :disabled="cargandoPrimas">
                                        <i class="fa" :class="cargandoPrimas ? 'fa-spinner fa-spin' : 'fa-calculator'"></i> Calcular Primas
                                    </button>
                                </div>
                            </div>

                            <div v-if="cargandoPrimas" class="text-center py-5">
                                <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                                <p class="mt-2 text-muted">Consultando y simulando primas semestrales...</p>
                            </div>

                            <!-- Tabla de Simulación de Primas -->
                            <div v-else-if="arrayCalculoPrimas.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover text-center table-sm">
                                        <thead>
                                            <tr class="bg-primary text-white">
                                                <th style="width: 40px;"><input type="checkbox" v-model="seleccionarTodasPrimas" @change="toggleSeleccionarTodasPrimas()"></th>
                                                <th>Empleado</th>
                                                <th>Identificación</th>
                                                <th>Contrato</th>
                                                <th>Días Laborados</th>
                                                <th>Salario Base</th>
                                                <th>Aux. Transp.</th>
                                                <th>Promedio Extras</th>
                                                <th>Base Cálculo</th>
                                                <th>Valor Prima</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="p in arrayCalculoPrimas" :key="p.empleado_id">
                                                <td class="align-middle">
                                                    <input type="checkbox" v-model="p.seleccionado" :disabled="p.ya_pagado">
                                                </td>
                                                <td class="text-left font-weight-bold text-dark align-middle">{{ p.nombre }}</td>
                                                <td class="align-middle">{{ p.num_doc }}</td>
                                                <td class="align-middle"><span class="badge badge-info">{{ p.tipo_contrato }}</span></td>
                                                <td class="align-middle font-weight-bold">{{ p.dias_trabajados }}</td>
                                                <td class="text-right align-middle">$ {{ formatMonto(p.salario_base) }}</td>
                                                <td class="text-right align-middle">$ {{ formatMonto(p.auxilio_transporte) }}</td>
                                                <td class="text-right align-middle text-success">$ {{ formatMonto(p.promedio_extras) }}</td>
                                                <td class="text-right align-middle font-weight-bold">$ {{ formatMonto(p.base_calculo) }}</td>
                                                <td class="text-right align-middle font-weight-bold text-primary">$ {{ formatMonto(p.valor_prima) }}</td>
                                                <td class="align-middle">
                                                    <div v-if="p.ya_pagado" class="d-flex align-items-center justify-content-center">
                                                        <span class="badge badge-success mr-1">Pagado</span>
                                                        <a :href="`/nomina/primas/pdf/${p.liquidacion_prima_id}`" target="_blank" class="btn btn-outline-danger btn-xs" title="Descargar Comprobante PDF" style="padding: 1px 4px; font-size: 10px;">
                                                            <i class="fa fa-file-pdf-o"></i>
                                                        </a>
                                                    </div>
                                                    <span v-else class="badge badge-secondary">Pendiente</span>
                                                </td>
                                            </tr>
                                            <!-- Totales -->
                                            <tr class="bg-dark text-white font-weight-bold font-13">
                                                <td colspan="5" class="text-left py-2">TOTAL PRIMAS SELECCIONADAS</td>
                                                <td colspan="4" class="text-right">Empleados: {{ totalPrimasSeleccionadasCount }}</td>
                                                <td class="text-right text-success font-weight-bold">$ {{ formatMonto(totalPrimasSeleccionadasMonto) }}</td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="button" @click="registrarPagoPrimas()" class="btn btn-success font-weight-bold rounded shadow-sm" :disabled="totalPrimasSeleccionadasCount === 0 || registrandoPrimas">
                                        <i class="fa" :class="registrandoPrimas ? 'fa-spinner fa-spin' : 'fa-save'"></i> Registrar Pago de Primas
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center py-5 text-muted">
                                <i class="fa fa-info-circle fa-2x mb-2 text-info"></i>
                                <p class="mb-0">Haga clic en "Calcular Primas" para simular el pago de primas del período seleccionado.</p>
                            </div>
                        </div>

                        <!-- TAB 5: HISTORIAL DE PRIMAS -->
                        <div v-if="vista === 'historial_primas'">
                            <!-- Mini-navigation for calculation vs history -->
                            <div class="row mb-3 border-bottom pb-2">
                                <div class="col-12">
                                    <button type="button" @click="vista = 'primas_semestrales'" class="btn btn-sm" :class="vista === 'primas_semestrales' ? 'btn-primary' : 'btn-outline-primary'">
                                        <i class="fa fa-calculator"></i> Calcular Semestre
                                    </button>
                                    <button type="button" @click="cambiarVistaTab('historial_primas')" class="btn btn-sm" :class="vista === 'historial_primas' ? 'btn-primary' : 'btn-outline-primary'">
                                        <i class="fa fa-list"></i> Historial de Primas Pagadas
                                    </button>
                                </div>
                            </div>

                            <!-- Filtros de búsqueda historial -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label text-dark font-weight-bold">Empleado</label>
                                    <select v-model="filtroPrimaEmpleadoId" class="form-control form-control-sm" @change="listarPrimas(1)">
                                        <option value="">-- Todos los Empleados --</option>
                                        <option v-for="emp in arrayTodosEmpleados" :key="emp.id" :value="emp.id">
                                            {{ emp.nombre }} {{ emp.apellido }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-dark font-weight-bold">Año</label>
                                    <select v-model="filtroPrimaAnio" class="form-control form-control-sm" @change="listarPrimas(1)">
                                        <option value="">-- Todos --</option>
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-dark font-weight-bold">Periodo / Semestre</label>
                                    <select v-model="filtroPrimaPeriodo" class="form-control form-control-sm" @change="listarPrimas(1)">
                                        <option value="">-- Todos --</option>
                                        <option value="1">I Semestre</option>
                                        <option value="2">II Semestre</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" @click="filtroPrimaEmpleadoId=''; filtroPrimaAnio=''; filtroPrimaPeriodo=''; listarPrimas(1);" class="btn btn-secondary btn-sm w-100">
                                        <i class="fa fa-refresh"></i> Limpiar Filtros
                                    </button>
                                </div>
                            </div>

                            <!-- Tabla del Historial -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover text-center table-sm">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Consecutivo</th>
                                            <th>Empleado</th>
                                            <th>Año/Semestre</th>
                                            <th>Fecha Pago</th>
                                            <th>Días</th>
                                            <th>Salario Base</th>
                                            <th>Promedio Extras</th>
                                            <th>Total Pagado</th>
                                            <th>Egreso Asoc.</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="arrayHistorialPrimas.length > 0">
                                        <tr v-for="pr in arrayHistorialPrimas" :key="pr.id">
                                            <td class="font-weight-bold">#{{ pr.id }}</td>
                                            <td class="text-left font-weight-bold text-dark">
                                                {{ pr.empleado ? `${pr.empleado.nombre} ${pr.empleado.apellido}` : 'Desconocido' }}
                                            </td>
                                            <td>{{ pr.anio }} - {{ pr.periodo == 1 ? 'I Semestre' : 'II Semestre' }}</td>
                                            <td>{{ pr.fecha_pago }}</td>
                                            <td>{{ pr.dias_trabajados }}</td>
                                            <td class="text-right">$ {{ formatMonto(pr.salario_base) }}</td>
                                            <td class="text-right">$ {{ formatMonto(pr.promedio_extras) }}</td>
                                            <td class="text-right font-weight-bold text-primary">$ {{ formatMonto(pr.valor_prima) }}</td>
                                            <td>
                                                <a v-if="pr.egreso" :href="`/egresos/pdf/${pr.egreso.id}`" target="_blank" class="btn btn-outline-success btn-xs" title="Ver Egreso PDF">
                                                    <i class="fa fa-file-pdf-o"></i> Egreso #{{ pr.egreso.id }}
                                                </a>
                                                <span v-else class="text-muted">Ninguno</span>
                                            </td>
                                            <td>
                                                <a :href="`/nomina/primas/pdf/${pr.id}`" target="_blank" class="btn btn-danger btn-sm text-white" title="Descargar Comprobante PDF">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </a>
                                                <button type="button" @click="eliminarPrima(pr.id)" class="btn btn-danger btn-sm" title="Eliminar Pago">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="10" class="py-4">No se encontraron registros de primas pagadas.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación Historial Primas -->
                            <nav v-if="paginacionPrimas.last_page > 1">
                                <ul class="pagination pagination-sm">
                                    <li class="page-item" v-if="paginacionPrimas.current_page > 1">
                                        <a class="page-link" href="#" @click.prevent="listarPrimas(paginacionPrimas.current_page - 1)">Ant</a>
                                    </li>
                                    <li class="page-item" v-for="page in paginacionPrimas.last_page" :key="page" :class="[page == paginacionPrimas.current_page ? 'active' : '']">
                                        <a class="page-link" href="#" @click.prevent="listarPrimas(page)" v-text="page"></a>
                                    </li>
                                    <li class="page-item" v-if="paginacionPrimas.current_page < paginacionPrimas.last_page">
                                        <a class="page-link" href="#" @click.prevent="listarPrimas(paginacionPrimas.current_page + 1)">Sig</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VISTA 2: CALCULADORA DE LIQUIDACIÓN -->
            <div v-if="vista === 'calculadora'" class="animated fadeIn">
                <!-- Tarjeta Principal del Formulario (No Imprimible) -->
                <div class="card border-0 shadow-sm rounded no-print-card mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" @click="volverAlListado()" class="btn btn-outline-secondary btn-sm mr-2">
                                <i class="fa fa-arrow-left"></i> Volver
                            </button>
                            <span class="h5 font-weight-bold text-dark mb-0">
                                Calculadora de Liquidación de Contrato
                            </span>
                        </div>
                        <div>
                            <button type="button" @click="imprimirLiquidacion()" class="btn btn-secondary btn-sm mr-2">
                                <i class="fa fa-print"></i> Imprimir Reporte
                            </button>
                            <button type="button" @click="abrirModalEgreso()" class="btn btn-success btn-sm">
                                <i class="fa fa-save"></i> Registrar Egreso
                            </button>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row">
                            <!-- Columna Izquierda: Parámetros y Formulario -->
                            <div class="col-lg-6 col-md-12">
                                <!-- Parámetros de Referencia -->
                                <div class="card mb-3 border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-cogs"></i> Parámetros de Referencia Colombiana (2026)</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Salario Mínimo (SMMLV)</label>
                                                <input type="number" v-model.number="salarioMinimo" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Auxilio Transporte</label>
                                                <input type="number" v-model.number="auxilioTransporte" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Datos Generales del Contrato -->
                                <div class="card mb-3 border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-file-text-o"></i> Datos del Empleado y Contrato</h6>

                                        <div v-if="esContratistaServicios" class="alert alert-info border-info p-3 mb-3 rounded shadow-sm">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-info-circle fa-2x text-info mr-3"></i>
                                                <div>
                                                    <h6 class="font-weight-bold mb-1 text-dark">Contrato de Prestación de Servicios (Honorarios Profesionales)</h6>
                                                    <p class="small mb-0 text-muted">
                                                        De acuerdo con la legislación colombiana (Ley de Colombia), este contrato es de carácter civil/comercial y <strong>no genera prestaciones sociales</strong> (Prima, Cesantías, Intereses de Cesantías, Vacaciones), <strong>ni Auxilio de Transporte ni Parafiscales/Deducciones de Nómina</strong>. La liquidación se limita exclusivamente al pago de **honorarios / días / horas pendientes** de servicios prestados.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Empleado</label>
                                                <input type="text" readonly :value="`${empleadoSeleccionado.nombre} ${empleadoSeleccionado.apellido}`" class="form-control form-control-sm bg-white">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Tipo Contrato</label>
                                                <select v-model="formCalculo.tipoContrato" class="form-control form-control-sm">
                                                    <option value="Término Fijo">Término Fijo</option>
                                                    <option value="Término Indefinido">Término Indefinido</option>
                                                    <option value="Obra o Labor">Obra o Labor</option>
                                                    <option value="Prestación de servicios">Prestación de servicios (Honorarios)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Fecha Ingreso</label>
                                                <input type="date" v-model="formCalculo.fechaIngreso" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Fecha Retiro</label>
                                                <input type="date" v-model="formCalculo.fechaRetiro" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Salario Base</label>
                                                <input type="number" v-model.number="formCalculo.baseSalarial" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2 d-flex align-items-center mt-4">
                                                <div class="form-check">
                                                    <input type="checkbox" v-model="formCalculo.aplicaAuxTransp" class="form-check-input" id="checkAuxTransp">
                                                    <label class="form-check-label font-weight-bold text-dark" for="checkAuxTransp">Aplica Aux. Transporte</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2" v-if="formCalculo.tipoContrato === 'Término Fijo'">
                                                <label class="form-label text-muted">Fecha Finalización Contrato</label>
                                                <input type="date" v-model="formCalculo.fechaFinalizacion" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Causa de Liquidación (Impresión)</label>
                                                <input type="text" v-model="formCalculo.causaRetiro" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Motivo / Indemnización</label>
                                                <select v-model="formCalculo.motivoRetiro" class="form-control form-control-sm">
                                                    <option value="Renuncia">Renuncia / Mutuo Acuerdo</option>
                                                    <option value="Despido con Justa Causa">Despido con Justa Causa</option>
                                                    <option value="Despido sin Justa Causa">Despido sin Justa Causa (Aplica Indemnización)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cortes de Acumulación y Permisos -->
                                <div class="card mb-3 border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-calendar"></i> Fechas de Corte e Inasistencias</h6>
                                        <div class="row">
                                            <!-- Sección Prima -->
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Inicio Prima</label>
                                                <input type="date" v-model="formCalculo.fechaDesdePrima" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 mb-2 d-flex align-items-center mt-4">
                                                <div class="form-check">
                                                     <input type="checkbox" v-model="formCalculo.primaPagada" class="form-check-input" id="checkPrimaPagada">
                                                     <label class="form-check-label font-weight-bold text-dark" for="checkPrimaPagada">¿Prima de Servicios ya Pagada?</label>
                                                </div>
                                            </div>

                                            <!-- Sección Cesantías -->
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Inicio Cesantías</label>
                                                <input type="date" v-model="formCalculo.fechaDesdeCesantias" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Ausencias/Sanciones (Días Resta)</label>
                                                <input type="number" step="0.1" min="0" v-model.number="formCalculo.descuentoAusencias" class="form-control form-control-sm" placeholder="0">
                                            </div>

                                            <!-- Sección Vacaciones -->
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label text-muted">Inicio Vacaciones</label>
                                                <input type="date" v-model="formCalculo.fechaDesdeVacaciones" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label text-muted">Vacaciones Disfrutadas (Resta)</label>
                                                <input type="number" step="0.1" v-model.number="formCalculo.diasVacacionesTomados" class="form-control form-control-sm" placeholder="0">
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label text-muted">Vacaciones Anticipadas (Resta)</label>
                                                <input type="number" step="0.1" v-model.number="formCalculo.diasVacacionesAnticipadas" class="form-control form-control-sm" placeholder="0">
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label class="form-label text-muted">Vacaciones Extras (Suma)</label>
                                                <input type="number" step="0.1" v-model.number="formCalculo.diasVacacionesManual" class="form-control form-control-sm" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Devengos y Deducciones Pendientes -->
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-money"></i> Salarios Pendientes y Ajustes Finanzas</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Días Laborados Pendientes Pago</label>
                                                <input type="number" step="0.1" min="0" v-model.number="formCalculo.diasSueldoPendiente" class="form-control form-control-sm" placeholder="0">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Horas Extras/Recargos Pendientes (Pago)</label>
                                                <input type="number" v-model.number="formCalculo.horasExtrasPromedio" class="form-control form-control-sm font-weight-bold" placeholder="0">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Promedio Extras Prima (Cálculo)</label>
                                                <input type="number" v-model.number="formCalculo.promedioExtrasPrima" class="form-control form-control-sm font-weight-bold" placeholder="0">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Promedio Extras Cesantías (Cálculo)</label>
                                                <input type="number" v-model.number="formCalculo.promedioExtrasCesantias" class="form-control form-control-sm font-weight-bold" placeholder="0">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Promedio Extras Vacaciones (Cálculo)</label>
                                                <input type="number" v-model.number="formCalculo.promedioExtrasVacaciones" class="form-control form-control-sm font-weight-bold" placeholder="0">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Deducción Fondo Solidaridad</label>
                                                <input type="number" v-model.number="formCalculo.fondoSolidaridad" class="form-control form-control-sm" placeholder="0">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Retención en la Fuente</label>
                                                <input type="number" v-model.number="formCalculo.retencionFuente" class="form-control form-control-sm" placeholder="0">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label text-muted">Otros Descuentos (Anticipos)</label>
                                                <input type="number" v-model.number="formCalculo.otrosDescuentos" class="form-control form-control-sm" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha: Vista Previa y Resultados -->
                            <div class="col-lg-6 col-md-12">
                                <div class="card border-0 shadow-sm bg-white rounded h-100">
                                    <div class="card-header bg-white font-weight-bold py-3 text-dark d-flex justify-content-between align-items-center">
                                        <span><i class="fa fa-file-text text-success"></i> Resumen de Liquidación</span>
                                        <span class="badge badge-success font-weight-bold">Simulación en Tiempo Real</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm text-center">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Concepto</th>
                                                        <th>Días / Base</th>
                                                        <th class="text-right">Monto</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Prima de Servicios</td>
                                                        <td>{{ diasPrima }} días <br><small class="text-muted">Base: $ {{ formatMonto(basePrima) }} (Ext: $ {{ formatMonto(formCalculo.promedioExtrasPrima) }})</small></td>
                                                        <td class="text-right font-weight-bold text-dark">
                                                            <span v-if="formCalculo.primaPagada" class="text-muted"><del>$ {{ formatMonto((basePrima * diasPrima) / 360) }}</del> <br><span class="badge badge-success">Pagada</span></span>
                                                            <span v-else>$ {{ formatMonto(liquidacionPrima) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Cesantías</td>
                                                        <td>{{ diasCesantias }} días <br><small class="text-muted">Base: $ {{ formatMonto(baseCesantias) }} (Ext: $ {{ formatMonto(formCalculo.promedioExtrasCesantias) }})</small></td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(liquidacionCesantias) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Intereses Cesantías</td>
                                                        <td>12% s/ cesantías</td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(liquidacionIntereses) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Vacaciones Pendientes</td>
                                                        <td>
                                                            {{ totalDiasVacacionesLiquidar.toFixed(1) }} días <br>
                                                            <small class="text-muted">
                                                                Base: $ {{ formatMonto(baseVacaciones) }} (Ext: $ {{ formatMonto(formCalculo.promedioExtrasVacaciones) }})
                                                                <span v-if="formCalculo.diasVacacionesAnticipadas > 0" class="text-danger"><br>Anticipadas: -{{ formCalculo.diasVacacionesAnticipadas }} días</span>
                                                            </small>
                                                        </td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(liquidacionVacaciones) }}</td>
                                                    </tr>
                                                    <tr v-if="formCalculo.diasSueldoPendiente > 0">
                                                        <td class="text-left font-weight-bold text-info">Sueldo Pendiente</td>
                                                        <td>{{ formCalculo.diasSueldoPendiente }} días</td>
                                                        <td class="text-right font-weight-bold text-info">$ {{ formatMonto(sueldoPendiente) }}</td>
                                                    </tr>
                                                    <tr v-if="auxilioTransportePendiente > 0">
                                                        <td class="text-left font-weight-bold text-info">Auxilio Transporte Pendiente</td>
                                                        <td>{{ formCalculo.diasSueldoPendiente }} días</td>
                                                        <td class="text-right font-weight-bold text-info">$ {{ formatMonto(auxilioTransportePendiente) }}</td>
                                                    </tr>
                                                    <tr v-if="formCalculo.horasExtrasPromedio > 0">
                                                        <td class="text-left font-weight-bold text-info">Horas Extras / Recargos</td>
                                                        <td>Adicional</td>
                                                        <td class="text-right font-weight-bold text-info">$ {{ formatMonto(formCalculo.horasExtrasPromedio) }}</td>
                                                    </tr>
                                                    <tr v-if="formCalculo.motivoRetiro === 'Despido sin Justa Causa'">
                                                        <td class="text-left font-weight-bold text-danger font-14">Indemnización Despido</td>
                                                        <td>Según Ley</td>
                                                        <td class="text-right font-weight-bold text-danger">$ {{ formatMonto(liquidacionIndemnizacion) }}</td>
                                                    </tr>
                                                    <tr class="bg-light text-dark font-weight-bold">
                                                        <td colspan="2" class="text-left py-2 font-weight-bold">TOTAL DEVENGADO</td>
                                                        <td class="text-right py-2 font-weight-bold">$ {{ formatMonto(totalDevengado) }}</td>
                                                    </tr>
                                                    <tr v-if="saludDeduccion > 0 || pensionDeduccion > 0 || formCalculo.otrosDescuentos > 0 || formCalculo.fondoSolidaridad > 0 || formCalculo.retencionFuente > 0">
                                                        <td colspan="3" class="text-left font-weight-bold bg-light text-danger small">Deducciones Aplicadas:</td>
                                                    </tr>
                                                    <tr v-if="saludDeduccion > 0">
                                                        <td class="text-left text-muted small">&nbsp;&nbsp;- Salud (4% Sueldo/HE)</td>
                                                        <td>Deducción</td>
                                                        <td class="text-right text-danger">- $ {{ formatMonto(saludDeduccion) }}</td>
                                                    </tr>
                                                    <tr v-if="pensionDeduccion > 0">
                                                        <td class="text-left text-muted small">&nbsp;&nbsp;- Pensión (4% Sueldo/HE)</td>
                                                        <td>Deducción</td>
                                                        <td class="text-right text-danger">- $ {{ formatMonto(pensionDeduccion) }}</td>
                                                    </tr>
                                                    <tr v-if="formCalculo.fondoSolidaridad > 0">
                                                        <td class="text-left text-muted small">&nbsp;&nbsp;- Fondo Solidaridad</td>
                                                        <td>Deducción</td>
                                                        <td class="text-right text-danger">- $ {{ formatMonto(formCalculo.fondoSolidaridad) }}</td>
                                                    </tr>
                                                    <tr v-if="formCalculo.retencionFuente > 0">
                                                        <td class="text-left text-muted small">&nbsp;&nbsp;- Retención en la Fuente</td>
                                                        <td>Deducción</td>
                                                        <td class="text-right text-danger">- $ {{ formatMonto(formCalculo.retencionFuente) }}</td>
                                                    </tr>
                                                    <tr v-if="formCalculo.otrosDescuentos > 0">
                                                        <td class="text-left text-muted small">&nbsp;&nbsp;- Anticipos / Otros desc.</td>
                                                        <td>Deducción</td>
                                                        <td class="text-right text-danger">- $ {{ formatMonto(formCalculo.otrosDescuentos) }}</td>
                                                    </tr>
                                                    <tr v-if="totalDeducciones > 0" class="bg-light text-danger font-weight-bold">
                                                        <td colspan="2" class="text-left py-2 font-weight-bold">TOTAL DEDUCCIONES</td>
                                                        <td class="text-right py-2 font-weight-bold">- $ {{ formatMonto(totalDeducciones) }}</td>
                                                    </tr>
                                                    <tr class="bg-dark text-white font-weight-bold font-14">
                                                        <td colspan="2" class="text-left py-2 font-weight-bold">TOTAL NETO LIQUIDADO</td>
                                                        <td class="text-right py-2 font-weight-bold text-success">$ {{ formatMonto(liquidacionTotal) }}</td>
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

                <!-- EL DOCUMENTO DE LIQUIDACIÓN DE IMPRESIÓN (PIXEL PERFECT - MODELO EXCEL) -->
                <div id="seccion-liquidacion" class="print-container">
                    <div class="print-sheet bg-white p-4">
                        <!-- Cabecera -->
                        <div class="text-center mb-4">
                            <h4 class="font-weight-bold m-0" style="font-size: 18px; letter-spacing: 0.5px;">AGENCIA LUPA S.A.S.</h4>
                            <div class="font-weight-bold text-muted small" style="font-size: 11px;">NIT 901.086.443-7</div>
                            <h5 class="font-weight-bold mt-3 border-bottom border-dark pb-2" style="font-size: 14px;">LIQUIDACION DE CONTRATO DE TRABAJO</h5>
                        </div>

                        <!-- Datos del Empleado -->
                        <div class="row mb-3" style="font-size: 12px;">
                            <div class="col-8">
                                <p class="mb-1"><strong>NOMBRE:</strong> {{ empleadoSeleccionado.nombre }} {{ empleadoSeleccionado.apellido }}</p>
                                <p class="mb-1"><strong>C.C.:</strong> {{ empleadoSeleccionado.num_doc }}</p>
                                <p class="mb-1"><strong>CARGO:</strong> {{ empleadoSeleccionado.cargo || 'OPERARIO' }}</p>
                            </div>
                            <div class="col-4 text-right">
                                <p class="mb-1"><strong>CAUSA DE LA LIQUIDACION:</strong></p>
                                <p class="mb-1 text-muted">{{ formCalculo.causaRetiro }}</p>
                            </div>
                        </div>

                        <!-- Período de liquidación y bases -->
                        <div class="row mb-4 border border-dark p-2 bg-light" style="font-size: 11px;">
                            <div class="col-6">
                                <h6 class="font-weight-bold mb-2 border-bottom border-secondary pb-1 text-uppercase">Período de Liquidación</h6>
                                <p class="mb-1"><strong>Fecha Inicio Contrato:</strong> {{ formCalculo.fechaIngreso }}</p>
                                <p class="mb-1"><strong>Fecha Terminación de Contrato:</strong> {{ formCalculo.fechaRetiro }}</p>
                                <p class="mb-1"><strong>Tiempo Total Laborado:</strong> {{ diasTotalesAntiguedad }} días</p>
                                <p class="mb-1"><strong>(-) Descto. Ausencias/Permisos:</strong> {{ formCalculo.descuentoAusencias }} días</p>
                            </div>
                            <div class="col-6 border-left border-dark pl-3">
                                <h6 class="font-weight-bold mb-2 border-bottom border-secondary pb-1 text-uppercase">Bases de Liquidación</h6>
                                <p class="mb-1"><strong>Sueldo Básico:</strong> $ {{ formatMonto(formCalculo.baseSalarial) }}</p>
                                <p class="mb-1" v-if="formCalculo.aplicaAuxTransp"><strong>Auxilio Transporte:</strong> $ {{ formatMonto(auxilioTransporte) }}</p>
                                <p class="mb-1"><strong>Base Prima:</strong> $ {{ formatMonto(basePrima) }} <small class="text-muted">(Prom. Ext: $ {{ formatMonto(formCalculo.promedioExtrasPrima) }})</small></p>
                                <p class="mb-1"><strong>Base Cesantías:</strong> $ {{ formatMonto(baseCesantias) }} <small class="text-muted">(Prom. Ext: $ {{ formatMonto(formCalculo.promedioExtrasCesantias) }})</small></p>
                                <p class="mb-1"><strong>Base Vacaciones:</strong> $ {{ formatMonto(baseVacaciones) }} <small class="text-muted">(Prom. Ext: $ {{ formatMonto(formCalculo.promedioExtrasVacaciones) }})</small></p>
                            </div>
                        </div>

                        <!-- Detalle de días de acumulación -->
                        <div class="row mb-3" style="font-size: 11px;">
                            <div class="col-12">
                                <table class="table table-bordered table-sm text-center">
                                    <thead>
                                        <tr class="bg-light border-dark">
                                            <th class="font-weight-bold">Prestación</th>
                                            <th class="font-weight-bold">Fecha Inicio</th>
                                            <th class="font-weight-bold">Fecha Corte</th>
                                            <th class="font-weight-bold">Días Acumulados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Prima de Servicios</td>
                                            <td>{{ formCalculo.fechaDesdePrima }}</td>
                                            <td>{{ formCalculo.fechaRetiro }}</td>
                                            <td>
                                                {{ diasPrima }} días 
                                                <small v-if="formCalculo.descuentoAusencias > 0">(-{{ formCalculo.descuentoAusencias }} ausencias)</small> 
                                                <small v-if="formCalculo.primaPagada" class="text-success font-weight-bold ml-1">(Ya pagada)</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Cesantías</td>
                                            <td>{{ formCalculo.fechaDesdeCesantias }}</td>
                                            <td>{{ formCalculo.fechaRetiro }}</td>
                                            <td>{{ diasCesantias }} días <small v-if="formCalculo.descuentoAusencias > 0">(-{{ formCalculo.descuentoAusencias }} ausencias)</small></td>
                                        </tr>
                                        <tr>
                                            <td>Vacaciones</td>
                                            <td>{{ formCalculo.fechaDesdeVacaciones }}</td>
                                            <td>{{ formCalculo.fechaRetiro }}</td>
                                            <td>
                                                {{ totalDiasVacacionesLiquidar.toFixed(1) }} días 
                                                <span class="small text-muted">
                                                    (Acum: {{ diasVacacionesAccrued.toFixed(1) }} 
                                                    - Disf: {{ formCalculo.diasVacacionesTomados || 0 }} 
                                                    <span v-if="formCalculo.diasVacacionesAnticipadas > 0">- Anticipadas: {{ formCalculo.diasVacacionesAnticipadas }}</span> 
                                                    <span v-if="formCalculo.diasVacacionesManual > 0">+ Ext: {{ formCalculo.diasVacacionesManual }}</span>)
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Valores Liquidados -->
                        <div class="row mb-3" style="font-size: 11px;">
                            <div class="col-12">
                                <h6 class="font-weight-bold border-bottom pb-1 text-uppercase">Resumen Liquidación Pagos (Devengos):</h6>
                                <table class="table table-bordered table-sm text-center">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Concepto</th>
                                            <th>Sueldo Base</th>
                                            <th>Días Liquidar</th>
                                            <th>% / Proporción</th>
                                            <th class="text-right">Valores</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-left font-weight-bold">Días de Vacaciones Pendientes</td>
                                            <td>$ {{ formatMonto(baseVacaciones) }}</td>
                                            <td>{{ totalDiasVacacionesLiquidar.toFixed(1) }}</td>
                                            <td>0.0417</td>
                                            <td class="text-right">$ {{ formatMonto(liquidacionVacaciones) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left font-weight-bold">Cesantías</td>
                                            <td>$ {{ formatMonto(baseCesantias) }}</td>
                                            <td>{{ diasCesantias }}</td>
                                            <td>0.0833</td>
                                            <td class="text-right">$ {{ formatMonto(liquidacionCesantias) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left font-weight-bold">Intereses de Cesantías</td>
                                            <td>$ {{ formatMonto(liquidacionCesantias) }}</td>
                                            <td>{{ diasCesantias }}</td>
                                            <td>12% anual</td>
                                            <td class="text-right">$ {{ formatMonto(liquidacionIntereses) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left font-weight-bold">Prima de Servicios</td>
                                            <td>$ {{ formatMonto(basePrima) }}</td>
                                            <td>{{ diasPrima }}</td>
                                            <td>0.0833</td>
                                            <td class="text-right">
                                                <span v-if="formCalculo.primaPagada" class="text-muted"><del>$ {{ formatMonto((basePrima * diasPrima) / 360) }}</del> (Pagada)</span>
                                                <span v-else>$ {{ formatMonto(liquidacionPrima) }}</span>
                                            </td>
                                        </tr>
                                        <tr v-if="formCalculo.diasSueldoPendiente > 0">
                                            <td class="text-left font-weight-bold">Sueldo Pendiente</td>
                                            <td>$ {{ formatMonto(formCalculo.baseSalarial) }}</td>
                                            <td>{{ formCalculo.diasSueldoPendiente }}</td>
                                            <td>Días Laborados</td>
                                            <td class="text-right">$ {{ formatMonto(sueldoPendiente) }}</td>
                                        </tr>
                                        <tr v-if="auxilioTransportePendiente > 0">
                                            <td class="text-left font-weight-bold">Auxilio de Transporte Pendiente</td>
                                            <td>$ {{ formatMonto(auxilioTransporte) }}</td>
                                            <td>{{ formCalculo.diasSueldoPendiente }}</td>
                                            <td>Días Laborados</td>
                                            <td class="text-right">$ {{ formatMonto(auxilioTransportePendiente) }}</td>
                                        </tr>
                                        <tr v-if="formCalculo.horasExtrasPromedio > 0">
                                            <td class="text-left font-weight-bold">Horas Extras / Recargos Pendientes</td>
                                            <td>$ {{ formatMonto(formCalculo.horasExtrasPromedio) }}</td>
                                            <td>-</td>
                                            <td>Pendientes</td>
                                            <td class="text-right">$ {{ formatMonto(formCalculo.horasExtrasPromedio) }}</td>
                                        </tr>
                                        <tr v-if="formCalculo.motivoRetiro === 'Despido sin Justa Causa'">
                                            <td class="text-left font-weight-bold text-danger">Indemnización por Despido</td>
                                            <td>$ {{ formatMonto(formCalculo.baseSalarial) }}</td>
                                            <td>-</td>
                                            <td>Sin Justa Causa</td>
                                            <td class="text-right text-danger">$ {{ formatMonto(liquidacionIndemnizacion) }}</td>
                                        </tr>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="4" class="text-left py-2 font-weight-bold">TOTAL DEVENGOS</td>
                                            <td class="text-right py-2 font-weight-bold">$ {{ formatMonto(totalDevengado) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Deducciones -->
                        <div class="row mb-3" style="font-size: 11px;" v-if="totalDeducciones > 0">
                            <div class="col-12">
                                <h6 class="font-weight-bold border-bottom pb-1 text-uppercase text-danger">Resumen Deducciones / Descuentos:</h6>
                                <table class="table table-bordered table-sm text-center">
                                    <thead>
                                        <tr class="bg-light text-danger">
                                            <th>Concepto</th>
                                            <th>Tarifa / Base</th>
                                            <th class="text-right">Valores</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="saludDeduccion > 0">
                                            <td class="text-left">Salud (4% Sueldo Pendiente + Extras)</td>
                                            <td>4% s/ $ {{ formatMonto(sueldoPendiente + formCalculo.horasExtrasPromedio) }}</td>
                                            <td class="text-right text-danger">- $ {{ formatMonto(saludDeduccion) }}</td>
                                        </tr>
                                        <tr v-if="pensionDeduccion > 0">
                                            <td class="text-left">Pensión (4% Sueldo Pendiente + Extras)</td>
                                            <td>4% s/ $ {{ formatMonto(sueldoPendiente + formCalculo.horasExtrasPromedio) }}</td>
                                            <td class="text-right text-danger">- $ {{ formatMonto(pensionDeduccion) }}</td>
                                        </tr>
                                        <tr v-if="formCalculo.fondoSolidaridad > 0">
                                            <td class="text-left">Fondo de Solidaridad Pensional</td>
                                            <td>Deducción especial</td>
                                            <td class="text-right text-danger">- $ {{ formatMonto(formCalculo.fondoSolidaridad) }}</td>
                                        </tr>
                                        <tr v-if="formCalculo.retencionFuente > 0">
                                            <td class="text-left">Retención en la Fuente (Art 383)</td>
                                            <td>Deducción tributaria</td>
                                            <td class="text-right text-danger">- $ {{ formatMonto(formCalculo.retencionFuente) }}</td>
                                        </tr>
                                        <tr v-if="formCalculo.otrosDescuentos > 0">
                                            <td class="text-left">Anticipos / Descuentos Varios / Deudas</td>
                                            <td>Deducción por nómina</td>
                                            <td class="text-right text-danger">- $ {{ formatMonto(formCalculo.otrosDescuentos) }}</td>
                                        </tr>
                                        <tr class="bg-light text-danger font-weight-bold">
                                            <td colspan="2" class="text-left py-2 font-weight-bold">TOTAL DEDUCCIONES</td>
                                            <td class="text-right py-2 font-weight-bold">- $ {{ formatMonto(totalDeducciones) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Neto a recibir y Texto en Letras -->
                        <div class="row mb-4 border border-dark p-2" style="font-size: 11px;">
                            <div class="col-8">
                                <p class="mb-0 pt-2 font-weight-bold">SON: {{ valorEnLetras }}</p>
                            </div>
                            <div class="col-4 border-left border-dark text-right py-2">
                                <h6 class="font-weight-bold text-dark m-0" style="font-size: 13px;">VALOR NETO LIQUIDACION</h6>
                                <h5 class="font-weight-bold text-success mt-1 m-0" style="font-size: 16px;">$ {{ formatMonto(liquidacionTotal) }}</h5>
                            </div>
                        </div>

                        <!-- Declaración de Aceptación y Paz y Salvo -->
                        <div class="row mb-5" style="font-size: 10px; line-height: 1.4; text-align: justify;">
                            <div class="col-12">
                                <h6 class="font-weight-bold text-uppercase border-bottom pb-1 mb-2">SE HACE CONSTAR:</h6>
                                <p class="mb-2"><strong>1.</strong> Que el patrono ha incorporado en la presente liquidación los importes correspondientes a salarios, horas extras, descansos compensatorios, cesantías, vacaciones, prima de servicios, auxilio de transporte, y en sí, todo concepto relacionado con salarios, prestaciones o indemnizaciones causadas al quedar extinguido el contrato de trabajo.</p>
                                <p class="mb-0"><strong>2.</strong> Que con el pago del dinero anotado en la presente liquidación, queda transada cualquier diferencia relativa al contrato de trabajo extinguido, o a cualquier diferencia anterior. Por lo tanto, esta transacción tiene como efecto la terminación de las obligaciones provenientes de la relación laboral que existió entre <strong>AGENCIA LUPA S.A.S.</strong> y el trabajador, quienes declaran estar a paz y salvo por todo concepto.</p>
                            </div>
                        </div>

                        <!-- Firmas -->
                        <div class="row pt-4 text-center" style="font-size: 11px;">
                            <div class="col-6">
                                <div class="mx-auto" style="width: 220px; border-top: 1px solid #000; padding-top: 5px; margin-top: 40px;">
                                    <p class="mb-1 font-weight-bold">{{ empleadoSeleccionado.nombre }} {{ empleadoSeleccionado.apellido }}</p>
                                    <p class="mb-0 text-muted">TRABAJADOR</p>
                                    <p class="mb-0 text-muted">C.C. {{ empleadoSeleccionado.num_doc }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mx-auto" style="width: 220px; border-top: 1px solid #000; padding-top: 5px; margin-top: 40px;">
                                    <p class="mb-1 font-weight-bold">ANDREA MORENO MARIN</p>
                                    <p class="mb-0 text-muted">REPRESENTANTE LEGAL</p>
                                    <p class="mb-0 text-muted">AGENCIA LUPA S.A.S.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CONFIGURACIÓN GENERAL DE AUXILIO DE TRANSPORTE Y NÓMINA -->
        <div class="modal fade" tabindex="-1" :class="{ 'mostrar': modalConfigNomina }" style="display: none;" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg rounded-20">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fa fa-cogs mr-2 text-warning"></i> Configurar Auxilio de Transporte General
                        </h5>
                        <button type="button" class="close text-white" @click="modalConfigNomina = false" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold text-dark">Auxilio de Transporte Legal Vigente ($) (*)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text font-weight-bold">$</span></div>
                                <input type="number" step="100" min="0" v-model.number="configNomina.auxilio_transporte" class="form-control font-weight-bold text-success text-right" required />
                            </div>
                            <small class="text-muted">Este valor legal es el aplicado a empleados con sueldo menor o igual a 2 SMMLV.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold text-dark">Salario Mínimo Legal Vigente (SMMLV) ($) (*)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text font-weight-bold">$</span></div>
                                <input type="number" step="100" min="0" v-model.number="configNomina.salario_minimo" class="form-control font-weight-bold text-primary text-right" required />
                            </div>
                        </div>
                        <div class="form-check mb-2 bg-light p-3 rounded border">
                            <input class="form-check-input" type="checkbox" id="checkAplicarTodos" v-model="configNomina.aplicar_a_empleados">
                            <label class="form-check-label font-weight-bold text-dark ml-2" for="checkAplicarTodos">
                                Actualizar y aplicar este valor de Auxilio de Transporte a todos los empleados (fichas de empleados)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" @click="modalConfigNomina = false">Cancelar</button>
                        <button type="button" class="btn btn-primary btn-sm font-weight-bold" @click="guardarConfigNomina">
                            <i class="fa fa-check mr-1"></i> Guardar y Aplicar a Empleados
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL REGISTRO DE EGRESO DESDE LIQUIDACIÓN -->
        <div class="modal fade" tabindex="-1" :class="{ 'mostrar': modalEgreso }" style="display: none;" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-md" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">Registrar Egreso de Liquidación</h5>
                        <button type="button" class="close" @click="cerrarModalEgreso()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Fecha del Egreso (*)</label>
                            <input type="date" v-model="formEgreso.fecha" class="form-control form-control-sm">
                        </div>
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Clasificación (*)</label>
                            <select v-model="formEgreso.tipo_egreso" class="form-control form-control-sm">
                                <option v-for="clas in arrayClasificaciones" :key="clas.id" :value="clas.nombre" v-text="clas.nombre"></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Concepto (*)</label>
                            <textarea v-model="formEgreso.concepto" rows="3" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Valor (*)</label>
                            <input type="number" step="0.01" v-model.number="formEgreso.valor" class="form-control form-control-sm font-weight-bold text-dark">
                        </div>
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Forma de Pago (*)</label>
                            <select v-model="formEgreso.metodo_pago" class="form-control form-control-sm">
                                <option value="Banco">Banco</option>
                                <option value="Caja Menor">Caja Menor</option>
                                <option value="Caja Mayor">Caja Mayor</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Beneficiario (*)</label>
                            <input type="text" v-model="formEgreso.beneficiario" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top-0">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModalEgreso()">Cerrar</button>
                        <button type="button" class="btn btn-success btn-sm" @click="guardarEgresoLiquidacion()">Confirmar Egreso</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CALCULADORA Y LIQUIDACIÓN DE HORAS EXTRAS Y QUINCENA -->
        <div class="modal fade" tabindex="-1" :class="{ 'mostrar': modalHorasExtras }" style="display: none;" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-primary modal-lg" role="document" style="max-width: 95%; width: 1000px;">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fa fa-clock-o"></i> 
                            {{ editandoQuincenaId ? 'Editar' : 'Liquidación de' }} Quincena y Horas Extras - 
                            <span v-if="editandoQuincenaId">#{{ editandoQuincenaId }} - </span>
                            {{ empleadoSeleccionado.nombre }} {{ empleadoSeleccionado.apellido }}
                        </h5>
                        <button type="button" class="close" @click="cerrarCalculadoraHoras()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body bg-light" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Header with employee details -->
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body p-3 bg-white">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted small"><strong>Empleado:</strong></p>
                                        <h6 class="font-weight-bold text-dark">{{ empleadoSeleccionado.nombre }} {{ empleadoSeleccionado.apellido }}</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted small"><strong>Salario Base:</strong></p>
                                        <h6 class="font-weight-bold text-dark">$ {{ formatMonto(empleadoSeleccionado.salario) }}</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted small"><strong>Valor Hora Ordinaria:</strong></p>
                                        <h6 class="font-weight-bold text-primary">$ {{ formatMonto(valorHoraOrdinaria) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón para mostrar/ocultar configuración -->
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" @click="mostrarConfigHoras = !mostrarConfigHoras" class="btn btn-outline-primary btn-sm rounded shadow-sm">
                                <i class="fa animate-spin-hover" :class="mostrarConfigHoras ? 'fa-eye-slash' : 'fa-cogs'"></i>
                                {{ mostrarConfigHoras ? ' Ocultar Configuración de Horas' : ' Configurar Horarios y Factores' }}
                            </button>
                        </div>

                        <div v-if="mostrarConfigHoras" class="card mb-3 border-0 shadow-sm animated fadeIn">
                            <div class="card-header bg-white font-weight-bold text-primary py-2 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #f0f3f5;">
                                <span><i class="fa fa-sliders text-primary mr-2"></i> Configuración General de Jornada y Recargos</span>
                                <button type="button" @click="guardarConfigHoras()" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                                    <i class="fa fa-save mr-1"></i> Guardar Configuración General
                                </button>
                            </div>
                            <div class="card-body bg-white p-3">
                                <div class="row">
                                    <!-- Rango Horario Diurno -->
                                    <div class="col-md-5 border-right">
                                        <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                                            <i class="fa fa-sun-o text-warning mr-1"></i> Límites de Jornada Diurna
                                        </h6>
                                        <div class="row">
                                            <div class="col-6 mb-2">
                                                <label class="form-label text-muted">Hora Inicio Diurna</label>
                                                <input type="time" v-model="configHoras.inicioDiurna" @change="recalcularTurnos()" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <label class="form-label text-muted">Hora Fin Diurna</label>
                                                <input type="time" v-model="configHoras.finDiurna" @change="recalcularTurnos()" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <label class="form-label text-muted">Inicio Horario Laboral</label>
                                                <input type="time" v-model="configHoras.inicioJornadaOrdinaria" @change="recalcularTurnos()" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <label class="form-label text-muted">Fin Horario Laboral</label>
                                                <input type="time" v-model="configHoras.finJornadaOrdinaria" @change="recalcularTurnos()" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 mb-2 mt-2">
                                                <div class="form-check">
                                                    <input type="checkbox" v-model="configHoras.sabadoLaboral" @change="recalcularTurnos()" class="form-check-input" id="checkSabadoLaboral">
                                                    <label class="form-check-label font-weight-bold text-dark" for="checkSabadoLaboral">Sábados Laborales Ordinarios</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-muted small" style="line-height: 1.3;">
                                            <i class="fa fa-info-circle text-info"></i> Las horas laboradas fuera de este rango horario se liquidarán con recargos nocturnos.
                                        </div>
                                    </div>

                                    <!-- Factores de Multiplicación -->
                                    <div class="col-md-7">
                                        <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                                            <i class="fa fa-percent text-success mr-1"></i> Factores de Multiplicación
                                        </h6>
                                        <div class="row">
                                            <div class="col-6 col-md-3 mb-2">
                                                <label class="form-label text-muted" title="Hora Extra Diurna (HED)">HED (Extra Diu)</label>
                                                <input type="number" step="0.01" min="1" v-model.number="configHoras.factorHED" @input="recalcularTurnos()" class="form-control form-control-sm text-center font-weight-bold">
                                            </div>
                                            <div class="col-6 col-md-3 mb-2">
                                                <label class="form-label text-muted" title="Hora Extra Nocturna (HEN)">HEN (Extra Noc)</label>
                                                <input type="number" step="0.01" min="1" v-model.number="configHoras.factorHEN" @input="recalcularTurnos()" class="form-control form-control-sm text-center font-weight-bold">
                                            </div>
                                            <div class="col-6 col-md-3 mb-2">
                                                <label class="form-label text-muted" title="Hora Extra Diurna Festivo/Dominical (HEDDF)">HEDDF (Fest Extra)</label>
                                                <input type="number" step="0.01" min="1" v-model.number="configHoras.factorHEDD" @input="recalcularTurnos()" class="form-control form-control-sm text-center font-weight-bold">
                                            </div>
                                            <div class="col-6 col-md-3 mb-2">
                                                <label class="form-label text-muted" title="Hora Extra Nocturna Festivo/Dominical (HENDF)">HENDF (Fest Noc Ex)</label>
                                                <input type="number" step="0.01" min="1" v-model.number="configHoras.factorHEND" @input="recalcularTurnos()" class="form-control form-control-sm text-center font-weight-bold">
                                            </div>
                                            <div class="col-4 mb-2">
                                                <label class="form-label text-muted" title="Recargo Nocturno Ordinario (RNO)">RNO (Rec Noc)</label>
                                                <input type="number" step="0.01" min="0" v-model.number="configHoras.factorRNO" @input="recalcularTurnos()" class="form-control form-control-sm text-center font-weight-bold">
                                            </div>
                                            <div class="col-4 mb-2">
                                                <label class="form-label text-muted" title="Recargo Diurno Festivo/Dominical (RDDF)">RDDF (Fest Diu)</label>
                                                <input type="number" step="0.01" min="0" v-model.number="configHoras.factorRDD" @input="recalcularTurnos()" class="form-control form-control-sm text-center font-weight-bold">
                                            </div>
                                            <div class="col-4 mb-2">
                                                <label class="form-label text-muted" title="Recargo Nocturno Festivo/Dominical (RNDF)">RNDF (Fest Noc)</label>
                                                <input type="number" step="0.01" min="0" v-model.number="configHoras.factorRND" @input="recalcularTurnos()" class="form-control form-control-sm text-center font-weight-bold">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2-Column layout for daily shift entry and calculation -->
                        <div class="row">
                            <!-- Left Column: Daily Shift Entries for standard employees -->
                            <div v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios'" class="col-xl-6 col-lg-12 mb-3">
                                <div class="card border-0 shadow-sm h-100 bg-white">
                                    <div class="card-header bg-white font-weight-bold text-dark py-2 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f0f3f5;">
                                        <span><i class="fa fa-calendar-check-o text-warning"></i> Registro de Turnos Diarios</span>
                                        <button type="button" @click="agregarTurno()" class="btn btn-warning btn-sm text-dark font-weight-bold">
                                            <i class="fa fa-plus"></i> Agregar Turno
                                        </button>
                                    </div>
                                    <div class="card-body p-2" style="max-height: 550px; overflow-y: auto;">
                                        <div v-if="arrayTurnos.length === 0" class="text-center py-5 text-muted">
                                            <i class="fa fa-clock-o fa-2x mb-2 text-warning"></i>
                                            <p class="mb-0">No se han registrado turnos diarios.</p>
                                            <small class="text-muted">Use "+ Agregar Turno" para calcular automáticamente las horas extras por día y hora de entrada/salida o ingréselas manualmente a la derecha.</small>
                                        </div>
                                        <table v-else class="table table-bordered table-sm text-center bg-white m-0">
                                            <thead class="bg-light font-weight-bold small">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th style="width: 65px;">Festivo</th>
                                                    <th>Entrada</th>
                                                    <th>Salida</th>
                                                    <th>Horas</th>
                                                    <th style="width: 40px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(turno, idx) in arrayTurnos" :key="idx">
                                                    <td>
                                                        <input type="date" v-model="turno.fecha" @change="onTurnoDateChange(turno)" class="form-control form-control-sm border-0 p-1" style="font-size: 11px;">
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="checkbox" v-model="turno.esFestivoManual" @change="recalcularTurnos()" style="transform: scale(1.25); cursor: pointer;">
                                                    </td>
                                                    <td>
                                                        <input type="time" v-model="turno.horaEntrada" @change="recalcularTurnos()" class="form-control form-control-sm border-0 p-1" style="font-size: 11px;">
                                                    </td>
                                                    <td>
                                                        <input type="time" v-model="turno.horaSalida" @change="recalcularTurnos()" class="form-control form-control-sm border-0 p-1" style="font-size: 11px;">
                                                    </td>
                                                    <td class="align-middle font-weight-bold small">
                                                        {{ (turno.duracion || 0).toFixed(1) }}h
                                                    </td>
                                                    <td class="align-middle">
                                                        <button type="button" @click="eliminarTurno(idx)" class="btn btn-outline-danger btn-sm py-0 px-1 border-0" title="Eliminar Turno">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Left Column: Service Contract Inputs for contractors -->
                            <div v-else class="col-xl-6 col-lg-12 mb-3">
                                <div class="card border-0 shadow-sm h-100 bg-white">
                                    <div class="card-header bg-white font-weight-bold text-dark py-2" style="border-bottom: 1px solid #f0f3f5;">
                                        <i class="fa fa-file-text-o text-primary"></i> Modalidad de Cobro (Contrato de Servicios)
                                    </div>
                                    <div class="card-body p-3 font-13 bg-white">
                                        <div class="form-group mb-3">
                                            <label class="form-label text-dark font-weight-bold" style="font-size: 12px;">Forma de Pago del Contratista</label>
                                            <select v-model="formHorasExtras.tipoPagoContratista" class="form-control form-control-sm font-weight-bold">
                                                <option value="Fijo">Valor Fijo (proporcional por días)</option>
                                                <option value="Horas">Pago por Horas Trabajadas</option>
                                            </select>
                                        </div>

                                        <!-- If paid by hours -->
                                        <div v-if="formHorasExtras.tipoPagoContratista === 'Horas'" class="row animated fadeIn mt-3">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Horas Trabajadas</label>
                                                <input type="number" min="0" step="0.5" v-model.number="formHorasExtras.horasTrabajadasContratista" class="form-control form-control-sm font-weight-bold text-success">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Valor por Hora ($)</label>
                                                <input type="number" min="0" step="0.01" v-model.number="formHorasExtras.valorHoraContratista" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                        </div>

                                        <div class="mt-4 p-3 bg-light rounded text-muted small" style="line-height: 1.4; border-left: 3px solid #00bfff;">
                                            <i class="fa fa-info-circle text-info"></i> <strong>Nota Importante:</strong> Los contratistas por prestación de servicios realizan de forma autónoma sus aportes al sistema de seguridad social. En consecuencia, el sistema no deducirá aportes a Salud (4%) ni Pensión (4%), y no aplicará Auxilio de Transporte ni Horas Extras en el neto a pagar.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Overtime and Quincena Summary -->
                            <div class="col-xl-6 col-lg-12 mb-3">
                                <!-- Overtime table -->
                                <div v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios'" class="card border-0 shadow-sm mb-3 bg-white">
                                    <div class="card-header bg-white font-weight-bold text-dark py-2" style="border-bottom: 1px solid #f0f3f5;">
                                        <i class="fa fa-calculator text-primary"></i> Horas Extras y Recargos
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm text-center m-0 bg-white">
                                                <thead class="bg-light font-weight-bold small">
                                                    <tr>
                                                        <th class="text-left">Concepto</th>
                                                        <th>Factor</th>
                                                        <th>V. Hora</th>
                                                        <th style="width: 60px;">Cant.</th>
                                                        <th class="text-right" style="width: 100px;">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size: 10.5px;">
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Extra Diurna (HED)</td>
                                                        <td>{{ configHoras.factorHED.toFixed(2) }}</td>
                                                        <td class="text-right">$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorHED) }}</td>
                                                        <td>
                                                            <input type="number" min="0" step="0.1" v-model.number="formHorasExtras.horasHED" class="form-control form-control-sm text-center py-0 px-1 border-0 font-weight-bold" :readonly="arrayTurnos.length > 0">
                                                        </td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(montoHED) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Extra Nocturna (HEN)</td>
                                                        <td>{{ configHoras.factorHEN.toFixed(2) }}</td>
                                                        <td class="text-right">$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorHEN) }}</td>
                                                        <td>
                                                            <input type="number" min="0" step="0.1" v-model.number="formHorasExtras.horasHEN" class="form-control form-control-sm text-center py-0 px-1 border-0 font-weight-bold" :readonly="arrayTurnos.length > 0">
                                                        </td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(montoHEN) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Extra Diurna Fest. (HEDDF)</td>
                                                        <td>{{ configHoras.factorHEDD.toFixed(2) }}</td>
                                                        <td class="text-right">$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorHEDD) }}</td>
                                                        <td>
                                                            <input type="number" min="0" step="0.1" v-model.number="formHorasExtras.horasHEDD" class="form-control form-control-sm text-center py-0 px-1 border-0 font-weight-bold" :readonly="arrayTurnos.length > 0">
                                                        </td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(montoHEDD) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Extra Noct. Fest. (HENDF)</td>
                                                        <td>{{ configHoras.factorHEND.toFixed(2) }}</td>
                                                        <td class="text-right">$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorHEND) }}</td>
                                                        <td>
                                                            <input type="number" min="0" step="0.1" v-model.number="formHorasExtras.horasHEND" class="form-control form-control-sm text-center py-0 px-1 border-0 font-weight-bold" :readonly="arrayTurnos.length > 0">
                                                        </td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(montoHEND) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Recargo Noct. Ord. (RNO)</td>
                                                        <td>{{ configHoras.factorRNO.toFixed(2) }}</td>
                                                        <td class="text-right">$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorRNO) }}</td>
                                                        <td>
                                                            <input type="number" min="0" step="0.1" v-model.number="formHorasExtras.horasRNO" class="form-control form-control-sm text-center py-0 px-1 border-0 font-weight-bold" :readonly="arrayTurnos.length > 0">
                                                        </td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(montoRNO) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Recargo Diu. Fest. (RDDF)</td>
                                                        <td>{{ configHoras.factorRDD.toFixed(2) }}</td>
                                                        <td class="text-right">$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorRDD) }}</td>
                                                        <td>
                                                            <input type="number" min="0" step="0.1" v-model.number="formHorasExtras.horasRDD" class="form-control form-control-sm text-center py-0 px-1 border-0 font-weight-bold" :readonly="arrayTurnos.length > 0">
                                                        </td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(montoRDD) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-left font-weight-bold">Recargo Noc. Fest. (RNDF)</td>
                                                        <td>{{ configHoras.factorRND.toFixed(2) }}</td>
                                                        <td class="text-right">$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorRND) }}</td>
                                                        <td>
                                                            <input type="number" min="0" step="0.1" v-model.number="formHorasExtras.horasRND" class="form-control form-control-sm text-center py-0 px-1 border-0 font-weight-bold" :readonly="arrayTurnos.length > 0">
                                                        </td>
                                                        <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(montoRND) }}</td>
                                                    </tr>
                                                    <tr class="bg-light font-weight-bold">
                                                        <td colspan="3" class="text-left py-1 text-uppercase">TOTAL EXTRAS</td>
                                                        <td class="align-middle text-center">{{ (formHorasExtras.horasHED + formHorasExtras.horasHEN + formHorasExtras.horasHEDD + formHorasExtras.horasHEND + formHorasExtras.horasRNO + formHorasExtras.horasRDD + formHorasExtras.horasRND).toFixed(1) }}h</td>
                                                        <td class="text-right py-1 text-success">$ {{ formatMonto(totalHorasExtras) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quincena parameters and payroll breakdown -->
                                <div class="card border-0 shadow-sm bg-white">
                                    <div class="card-header bg-white font-weight-bold text-dark py-2" style="border-bottom: 1px solid #f0f3f5;">
                                        <i class="fa fa-money text-success"></i> Parámetros y Pago Quincenal
                                    </div>
                                    <div class="card-body p-3 font-13">
                                        <div class="row mb-2">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Fecha Pago</label>
                                                <input type="date" v-model="formHorasExtras.fechaPago" class="form-control form-control-sm">
                                            </div>
                                            <div v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios' || formHorasExtras.tipoPagoContratista !== 'Horas'" class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Días Trabajados</label>
                                                <input type="number" step="0.1" min="0.1" max="31" v-model.number="formHorasExtras.diasTrabajados" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Fecha Inicio Periodo</label>
                                                <input type="date" v-model="formHorasExtras.fechaInicio" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Fecha Fin Periodo</label>
                                                <input type="date" v-model="formHorasExtras.fechaFin" class="form-control form-control-sm">
                                            </div>
                                            <div v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios' || formHorasExtras.tipoPagoContratista !== 'Horas'" class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Salario Base (Mensual)</label>
                                                <input type="number" min="0" step="0.01" v-model.number="formHorasExtras.salarioBaseOverride" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Forma de Pago</label>
                                                <select v-model="formHorasExtras.metodoPago" class="form-control form-control-sm">
                                                    <option value="Banco">Banco</option>
                                                    <option value="Caja Menor">Caja Menor</option>
                                                    <option value="Caja Mayor">Caja Mayor</option>
                                                    <option value="Otros">Otros</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label text-muted">Otras Deducciones (Anticipos)</label>
                                                <input type="number" min="0" step="0.01" v-model.number="formHorasExtras.otrasDeducciones" class="form-control form-control-sm font-weight-bold">
                                            </div>
                                            <div v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios'" class="col-md-12 mb-2 d-flex align-items-center mt-1">
                                                <div class="form-check">
                                                    <input type="checkbox" v-model="formHorasExtras.aplicaAuxTransp" class="form-check-input" id="checkAuxTranspQuincena">
                                                    <label class="form-check-label font-weight-bold text-dark" for="checkAuxTranspQuincena">Aplica Auxilio Transporte</label>
                                                </div>
                                            </div>
                                        </div>
 
                                        <!-- Payroll Calculation breakdown -->
                                        <table class="table table-bordered table-sm text-center m-0 bg-white">
                                            <tbody style="font-size: 11px;">
                                                <tr>
                                                    <td class="text-left font-weight-bold">
                                                        <span v-if="empleadoSeleccionado.tipo_contrato === 'Prestación de servicios'">
                                                            Honorarios por Servicios ({{ formHorasExtras.tipoPagoContratista === 'Horas' ? 'Por Horas' : formHorasExtras.diasTrabajados + ' días' }})
                                                        </span>
                                                        <span v-else>
                                                            Sueldo Ordinario ({{ formHorasExtras.diasTrabajados }} días)
                                                        </span>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(sueldoQuincenaNeto) }}</td>
                                                </tr>
                                                <tr v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios'">
                                                    <td class="text-left font-weight-bold">Auxilio Transporte Proporcional</td>
                                                    <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(auxTranspQuincena) }}</td>
                                                </tr>
                                                <tr v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios'">
                                                    <td class="text-left font-weight-bold">Monto Horas Extras / Recargos</td>
                                                    <td class="text-right font-weight-bold text-dark">$ {{ formatMonto(totalHorasExtras) }}</td>
                                                </tr>
                                                <tr v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios'" class="text-danger font-weight-bold">
                                                    <td class="text-left">Deducción Salud (4%)</td>
                                                    <td class="text-right">$ -{{ formatMonto(saludDeduccionQuincena) }}</td>
                                                </tr>
                                                <tr v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios'" class="text-danger font-weight-bold">
                                                    <td class="text-left">Deducción Pensión (4%)</td>
                                                    <td class="text-right">$ -{{ formatMonto(pensionDeduccionQuincena) }}</td>
                                                </tr>
                                                <tr class="text-danger font-weight-bold" v-if="formHorasExtras.otrasDeducciones > 0">
                                                    <td class="text-left">Otras Deducciones</td>
                                                    <td class="text-right">$ -{{ formatMonto(formHorasExtras.otrasDeducciones) }}</td>
                                                </tr>
                                                <tr class="bg-success text-white font-weight-bold small">
                                                    <td class="text-left py-2 font-weight-bold text-uppercase">NETO QUINCENA A PAGAR</td>
                                                    <td class="text-right py-2 font-weight-bold">$ {{ formatMonto(netoQuincenaAPagar) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top-0 d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm" @click="cerrarCalculadoraHoras()">Cerrar</button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-info btn-sm mr-2 text-white" @click="imprimirHorasExtras()">
                                <i class="fa fa-print"></i> Imprimir Recibo
                            </button>
                            <button type="button" class="btn btn-success btn-sm font-weight-bold" @click="guardarPagoQuincena()">
                                <i class="fa fa-save"></i> {{ editandoQuincenaId ? 'Guardar Cambios' : 'Guardar / Liquidar Quincena' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Pagar Quincena -->
        <div class="modal fade" :class="{'mostrar': modalPagarQuincena}" tabindex="-1" role="dialog" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white py-3">
                        <h5 class="modal-title font-weight-bold mb-0">
                            <i class="fa fa-dollar mr-2"></i> Pagar Quincena #{{ quincenaAPagar.id }}
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalPagarQuincena()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 px-3 mb-3 small">
                            <i class="fa fa-info-circle mr-1"></i> Al procesar el pago se registrará el <strong>Egreso contable</strong> y se generará el <strong>Comprobante de Nómina</strong>.
                        </div>
                        <div class="mb-3 p-3 bg-light rounded border">
                            <p class="mb-1"><strong>Empleado:</strong> {{ quincenaAPagar.empleado ? `${quincenaAPagar.empleado.nombre} ${quincenaAPagar.empleado.apellido}` : 'Desconocido' }}</p>
                            <p class="mb-1"><strong>Período:</strong> {{ quincenaAPagar.fecha_inicio }} al {{ quincenaAPagar.fecha_fin }}</p>
                            <p class="mb-0 text-primary font-weight-bold font-14"><strong>Valor Neto a Pagar:</strong> $ {{ formatMonto(quincenaAPagar.neto_pagado) }}</p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold">Fecha de Pago (*)</label>
                            <input type="date" v-model="formPagarQuincena.fecha_pago" class="form-control form-control-sm">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold">Forma / Método de Pago (*)</label>
                            <select v-model="formPagarQuincena.metodo_pago" class="form-control form-control-sm font-weight-bold">
                                <option value="Banco">Banco</option>
                                <option value="Caja Menor">Caja Menor</option>
                                <option value="Caja Mayor">Caja Mayor</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" @click="cerrarModalPagarQuincena()">Cancelar</button>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold" @click="confirmarPagoQuincena()">
                            <i class="fa fa-check mr-1"></i> Confirmar y Registrar Pago
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOCUMENTO DE IMPRESIÓN DE COMPROBANTE DE HORAS EXTRAS (TAMAÑO CARTA) -->
        <div id="seccion-horas-extras" class="print-container">
            <div class="print-sheet bg-white p-4">
                <!-- Cabecera -->
                <div class="text-center mb-4">
                    <h4 class="font-weight-bold m-0" style="font-size: 18px; letter-spacing: 0.5px;">AGENCIA LUPA S.A.S.</h4>
                    <div class="font-weight-bold text-muted small" style="font-size: 11px;">NIT 901.086.443-7</div>
                    <h5 class="font-weight-bold mt-3 border-bottom border-dark pb-2" style="font-size: 14px;">DESPRENDIBLE DE PAGO DE NOMINA - QUINCENA</h5>
                </div>

                <!-- Datos del Empleado -->
                <div class="row mb-3" style="font-size: 12px;">
                    <div class="col-8">
                        <p class="mb-1"><strong>NOMBRE:</strong> {{ empleadoSeleccionado.nombre }} {{ empleadoSeleccionado.apellido }}</p>
                        <p class="mb-1"><strong>C.C.:</strong> {{ empleadoSeleccionado.num_doc }}</p>
                        <p class="mb-1"><strong>CARGO:</strong> {{ empleadoSeleccionado.cargo || 'OPERARIO' }}</p>
                        <p class="mb-1"><strong>PERIODO DE PAGO:</strong> {{ formHorasExtras.fechaInicio }} al {{ formHorasExtras.fechaFin }}</p>
                    </div>
                    <div class="col-4 text-right">
                        <p class="mb-1"><strong>FECHA PAGO:</strong></p>
                        <p class="mb-1 text-muted">{{ formHorasExtras.fechaPago || new Date().toISOString().split('T')[0] }}</p>
                    </div>
                </div>

                <!-- Resumen de Bases -->
                <div class="row mb-4 border border-dark p-2 bg-light" style="font-size: 11px;">
                    <div class="col-6">
                        <p class="mb-0"><strong>Salario Básico Mensual:</strong> $ {{ formatMonto(empleadoSeleccionado.salario) }}</p>
                    </div>
                    <div class="col-6 border-left border-dark pl-3">
                        <p class="mb-0"><strong>Valor Hora Ordinaria:</strong> $ {{ formatMonto(valorHoraOrdinaria) }}</p>
                    </div>
                </div>

                <!-- Resumen de Liquidación Quincenal -->
                <div class="row mb-4" style="font-size: 11px;">
                    <div class="col-12">
                        <h6 class="font-weight-bold border-bottom pb-1 text-uppercase">Resumen de Liquidación de la Quincena:</h6>
                        <table class="table table-bordered table-sm text-center">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-left">Concepto / Descripción</th>
                                    <th class="text-right">Devengado</th>
                                    <th class="text-right">Deducción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-left">
                                        <span v-if="empleadoSeleccionado.tipo_contrato === 'Prestación de servicios'">
                                            Honorarios por Servicios ({{ formHorasExtras.tipoPagoContratista === 'Horas' ? 'Por Horas' : formHorasExtras.diasTrabajados + ' días' }})
                                        </span>
                                        <span v-else>
                                            Sueldo Ordinario ({{ formHorasExtras.diasTrabajados }} días)
                                        </span>
                                    </td>
                                    <td class="text-right">$ {{ formatMonto(sueldoQuincenaNeto) }}</td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios' && auxTranspQuincena > 0">
                                    <td class="text-left">Auxilio Transporte Proporcional</td>
                                    <td class="text-right">$ {{ formatMonto(auxTranspQuincena) }}</td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr v-if="totalHorasExtras > 0">
                                    <td class="text-left">Monto Horas Extras / Recargos</td>
                                    <td class="text-right">$ {{ formatMonto(totalHorasExtras) }}</td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios' && saludDeduccionQuincena > 0">
                                    <td class="text-left">Deducción Salud (4%)</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right text-danger">$ -{{ formatMonto(saludDeduccionQuincena) }}</td>
                                </tr>
                                <tr v-if="empleadoSeleccionado.tipo_contrato !== 'Prestación de servicios' && pensionDeduccionQuincena > 0">
                                    <td class="text-left">Deducción Pensión (4%)</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right text-danger">$ -{{ formatMonto(pensionDeduccionQuincena) }}</td>
                                </tr>
                                <tr v-if="formHorasExtras.otrasDeducciones > 0">
                                    <td class="text-left">Otras Deducciones (Anticipos / Préstamos)</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right text-danger">$ -{{ formatMonto(formHorasExtras.otrasDeducciones) }}</td>
                                </tr>
                                <tr class="bg-light font-weight-bold">
                                    <td class="text-left py-2 font-weight-bold">NETO QUINCENA A PAGAR</td>
                                    <td colspan="2" class="text-right py-2 font-weight-bold text-success" style="font-size: 13px;">$ {{ formatMonto(netoQuincenaAPagar) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detalle de Horas Extras y Recargos -->
                <div v-if="totalHorasExtras > 0" class="row mb-3" style="font-size: 11px;">
                    <div class="col-12">
                        <h6 class="font-weight-bold border-bottom pb-1 text-uppercase">Detalle de horas extras liquidadas:</h6>
                        <table class="table table-bordered table-sm text-center">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-left">Concepto</th>
                                    <th>Recargo / Factor</th>
                                    <th>Valor Hora</th>
                                    <th>Cantidad</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="formHorasExtras.horasHED > 0">
                                    <td class="text-left">Hora Extra Diurna (HED)</td>
                                    <td>{{ ((configHoras.factorHED - 1) * 100).toFixed(0) }}% (x{{ configHoras.factorHED.toFixed(2) }})</td>
                                    <td>$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorHED) }}</td>
                                    <td>{{ formHorasExtras.horasHED }}</td>
                                    <td class="text-right">$ {{ formatMonto(montoHED) }}</td>
                                </tr>
                                <tr v-if="formHorasExtras.horasHEN > 0">
                                    <td class="text-left">Hora Extra Nocturna (HEN)</td>
                                    <td>{{ ((configHoras.factorHEN - 1) * 100).toFixed(0) }}% (x{{ configHoras.factorHEN.toFixed(2) }})</td>
                                    <td>$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorHEN) }}</td>
                                    <td>{{ formHorasExtras.horasHEN }}</td>
                                    <td class="text-right">$ {{ formatMonto(montoHEN) }}</td>
                                </tr>
                                <tr v-if="formHorasExtras.horasHEDD > 0">
                                    <td class="text-left">Hora Extra Diurna Dominical/Festivo (HEDDF)</td>
                                    <td>{{ ((configHoras.factorHEDD - 1) * 100).toFixed(0) }}% (x{{ configHoras.factorHEDD.toFixed(2) }})</td>
                                    <td>$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorHEDD) }}</td>
                                    <td>{{ formHorasExtras.horasHEDD }}</td>
                                    <td class="text-right">$ {{ formatMonto(montoHEDD) }}</td>
                                </tr>
                                <tr v-if="formHorasExtras.horasHEND > 0">
                                    <td class="text-left">Hora Extra Nocturna Dominical/Festivo (HENDF)</td>
                                    <td>{{ ((configHoras.factorHEND - 1) * 100).toFixed(0) }}% (x{{ configHoras.factorHEND.toFixed(2) }})</td>
                                    <td>$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorHEND) }}</td>
                                    <td>{{ formHorasExtras.horasHEND }}</td>
                                    <td class="text-right">$ {{ formatMonto(montoHEND) }}</td>
                                </tr>
                                <tr v-if="formHorasExtras.horasRNO > 0">
                                    <td class="text-left">Recargo Nocturno Ordinario (RNO)</td>
                                    <td>{{ (configHoras.factorRNO * 100).toFixed(0) }}% (x{{ configHoras.factorRNO.toFixed(2) }})</td>
                                    <td>$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorRNO) }}</td>
                                    <td>{{ formHorasExtras.horasRNO }}</td>
                                    <td class="text-right">$ {{ formatMonto(montoRNO) }}</td>
                                </tr>
                                <tr v-if="formHorasExtras.horasRDD > 0">
                                    <td class="text-left">Recargo Diurno Dominical/Festivo (RDDF)</td>
                                    <td>{{ (configHoras.factorRDD * 100).toFixed(0) }}% (x{{ configHoras.factorRDD.toFixed(2) }})</td>
                                    <td>$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorRDD) }}</td>
                                    <td>{{ formHorasExtras.horasRDD }}</td>
                                    <td class="text-right">$ {{ formatMonto(montoRDD) }}</td>
                                </tr>
                                <tr v-if="formHorasExtras.horasRND > 0">
                                    <td class="text-left">Recargo Nocturno Dominical/Festivo (RNDF)</td>
                                    <td>{{ (configHoras.factorRND * 100).toFixed(0) }}% (x{{ configHoras.factorRND.toFixed(2) }})</td>
                                    <td>$ {{ formatMonto(valorHoraOrdinaria * configHoras.factorRND) }}</td>
                                    <td>{{ formHorasExtras.horasRND }}</td>
                                    <td class="text-right">$ {{ formatMonto(montoRND) }}</td>
                                </tr>
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="4" class="text-left py-2 font-weight-bold">TOTAL DEVENGADO POR EXTRAS</td>
                                    <td class="text-right py-2 font-weight-bold text-success">$ {{ formatMonto(totalHorasExtras) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Neto a recibir y Texto en Letras -->
                <div class="row mb-4 border border-dark p-2" style="font-size: 11px;">
                    <div class="col-8">
                        <p class="mb-0 pt-2 font-weight-bold">SON: {{ netoQuincenaAPagarLetras }} M/CTE</p>
                    </div>
                    <div class="col-4 border-left border-dark text-right py-2">
                        <h6 class="font-weight-bold text-dark m-0" style="font-size: 13px;">NETO RECIBIDO</h6>
                        <h5 class="font-weight-bold text-success mt-1 m-0" style="font-size: 16px;">$ {{ formatMonto(netoQuincenaAPagar) }}</h5>
                    </div>
                </div>

                <!-- Declaración de Paz y Salvo Parcial -->
                <div class="row mb-5" style="font-size: 10px; line-height: 1.4; text-align: justify;">
                    <div class="col-12">
                        <p class="mb-0">Se hace constar que el valor recibido en este comprobante corresponde al pago neto de la quincena y conceptos relacionados en el período detallado. El trabajador firma en constancia del recibo a conformidad de los valores liquidados.</p>
                    </div>
                </div>

                <!-- Firmas -->
                <div class="row pt-4 text-center" style="font-size: 11px;">
                    <div class="col-6">
                        <div class="mx-auto" style="width: 220px; border-top: 1px solid #000; padding-top: 5px; margin-top: 40px;">
                            <p class="mb-1 font-weight-bold">{{ empleadoSeleccionado.nombre }} {{ empleadoSeleccionado.apellido }}</p>
                            <p class="mb-0 text-muted">TRABAJADOR</p>
                            <p class="mb-0 text-muted">C.C. {{ empleadoSeleccionado.num_doc }}</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mx-auto" style="width: 220px; border-top: 1px solid #000; padding-top: 5px; margin-top: 40px;">
                            <p class="mb-1 font-weight-bold">ANDREA MORENO MARIN</p>
                            <p class="mb-0 text-muted">REPRESENTANTE LEGAL</p>
                            <p class="mb-0 text-muted">AGENCIA LUPA S.A.S.</p>
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
            vista: 'listado',
            buscar: '',
            arrayEmpleados: [],
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 15,
                last_page: 1,
                from: 0,
                to: 0
            },
            offset: 3,

            // Empleado a liquidar
            empleadoSeleccionado: {},

            // Parámetros de la Liquidación
            salarioMinimo: 1400000,
            auxilioTransporte: 200000,
            auxilioTransporteGlobal: 200000,
            modalConfigNomina: false,
            configNomina: {
                auxilio_transporte: 200000,
                salario_minimo: 1400000,
                aplicar_a_empleados: true
            },

            formCalculo: {
                tipoContrato: 'Término Indefinido',
                fechaIngreso: '',
                fechaRetiro: '',
                fechaFinalizacion: '',
                baseSalarial: 0,
                aplicaAuxTransp: false,
                motivoRetiro: 'Renuncia',
                causaRetiro: 'Terminación de contrato por renuncia del empleado',
                fechaDesdePrima: '',
                fechaDesdeCesantias: '',
                fechaDesdeVacaciones: '',
                diasVacacionesTomados: 0,
                diasVacacionesAnticipadas: 0,
                diasVacacionesManual: 0,
                descuentoAusencias: 0,
                diasSueldoPendiente: 0,
                horasExtrasPromedio: 0,
                promedioExtrasPrima: 0,
                promedioExtrasCesantias: 0,
                promedioExtrasVacaciones: 0,
                fondoSolidaridad: 0,
                retencionFuente: 0,
                otrosDescuentos: 0,
                primaPagada: false
            },

            // Modal Registro Egreso
            modalEgreso: 0,
            arrayClasificaciones: [],
            formEgreso: {
                fecha: '',
                tipo_egreso: 'Nómina',
                concepto: '',
                valor: 0,
                metodo_pago: 'Banco',
                beneficiario: ''
            },

            // Modal Horas Extras
            modalHorasExtras: 0,
            formHorasExtras: {
                horasHED: 0,
                horasHEN: 0,
                horasHEDD: 0,
                horasHEND: 0,
                horasRNO: 0,
                horasRDD: 0,
                horasRND: 0,
                fechaInicio: '',
                fechaFin: '',
                fechaPago: '',
                diasTrabajados: 15,
                metodoPago: 'Banco',
                otrasDeducciones: 0,
                aplicaAuxTransp: true,
                salarioBaseOverride: undefined,
                auxTranspOverride: undefined,
                saludDeduccionOverride: undefined,
                pensionDeduccionOverride: undefined,
                netoOverride: undefined,
                // contractor additions
                tipoPagoContratista: 'Fijo',
                horasTrabajadasContratista: 0,
                valorHoraContratista: 0
            },
            arrayTurnos: [],
            mostrarConfigHoras: false,
            configHoras: {
                inicioJornadaOrdinaria: '07:00',
                finJornadaOrdinaria: '17:00',
                sabadoLaboral: false,
                factorHED: 1.25,
                factorHEN: 1.75,
                factorHEDD: 2.00,
                factorHEND: 2.50,
                factorRNO: 0.35,
                factorRDD: 0.75,
                factorRND: 1.10
            },

            // Quincenas & Seguridad Social Additions
            modalPagarQuincena: 0,
            quincenaAPagar: {},
            formPagarQuincena: {
                fecha_pago: '',
                metodo_pago: 'Banco'
            },
            editandoQuincenaId: null,
            arrayTodosEmpleados: [],
            arrayQuincenas: [],
            paginacionQuincenas: {
                total: 0,
                current_page: 1,
                per_page: 15,
                last_page: 1
            },
            filtroQuincenaEmpleadoId: '',
            filtroQuincenaFechaDesde: '',
            filtroQuincenaFechaHasta: '',
            
            reporteSegAnio: new Date().getFullYear(),
            reporteSegMes: new Date().getMonth() + 1,
            arrayReporteSeguridadSocial: [],
            cargandoReporteSeg: false,

            // Primas Semestrales Additions
            primaFiltroAnio: new Date().getFullYear(),
            primaFiltroPeriodo: 1,
            primaFechaPago: new Date().toISOString().split('T')[0],
            primaMetodoPago: 'Banco',
            cargandoPrimas: false,
            registrandoPrimas: false,
            arrayCalculoPrimas: [],
            seleccionarTodasPrimas: false,

            // Historial Primas Additions
            filtroPrimaEmpleadoId: '',
            filtroPrimaAnio: '',
            filtroPrimaPeriodo: '',
            arrayHistorialPrimas: [],
            paginacionPrimas: {
                total: 0,
                current_page: 1,
                per_page: 15,
                last_page: 1
            }
        }
    },
    computed: {
        isActived() {
            return this.pagination.current_page;
        },
        pagesNumber() {
            if (!this.pagination.to) return [];
            let from = this.pagination.current_page - this.offset;
            if (from < 1) from = 1;
            let to = from + (this.offset * 2);
            if (to >= this.pagination.last_page) to = this.pagination.last_page;
            
            let pagesArray = [];
            for (let page = from; page <= to; page++) {
                pagesArray.push(page);
            }
            return pagesArray;
        },

        // --- PRESTACIONES Y DETALLE DE CÁLCULO ---
        diasTotalesAntiguedad() {
            return this.calcularDiasComerciales(this.formCalculo.fechaIngreso, this.formCalculo.fechaRetiro);
        },
        diasPrima() {
            let baseDias = this.calcularDiasComerciales(this.formCalculo.fechaDesdePrima, this.formCalculo.fechaRetiro);
            let total = baseDias - parseFloat(this.formCalculo.descuentoAusencias || 0);
            return total > 0 ? total : 0;
        },
        diasCesantias() {
            let baseDias = this.calcularDiasComerciales(this.formCalculo.fechaDesdeCesantias, this.formCalculo.fechaRetiro);
            let total = baseDias - parseFloat(this.formCalculo.descuentoAusencias || 0);
            return total > 0 ? total : 0;
        },
        diasVacacionesAccrued() {
            let totalDiasAnt = this.calcularDiasComerciales(this.formCalculo.fechaDesdeVacaciones, this.formCalculo.fechaRetiro);
            return (totalDiasAnt * 15) / 360;
        },
        totalDiasVacacionesLiquidar() {
            let total = parseFloat(this.diasVacacionesAccrued) 
                      - parseFloat(this.formCalculo.diasVacacionesTomados || 0) 
                      - parseFloat(this.formCalculo.diasVacacionesAnticipadas || 0) 
                      + parseFloat(this.formCalculo.diasVacacionesManual || 0);
            return total > 0 ? total : 0;
        },
        basePrima() {
            let base = parseFloat(this.formCalculo.baseSalarial || 0);
            if (this.formCalculo.aplicaAuxTransp) {
                base += parseFloat(this.auxilioTransporte || 0);
            }
            base += parseFloat(this.formCalculo.promedioExtrasPrima || 0);
            return base;
        },
        baseCesantias() {
            let base = parseFloat(this.formCalculo.baseSalarial || 0);
            if (this.formCalculo.aplicaAuxTransp) {
                base += parseFloat(this.auxilioTransporte || 0);
            }
            base += parseFloat(this.formCalculo.promedioExtrasCesantias || 0);
            return base;
        },
        baseVacaciones() {
            let base = parseFloat(this.formCalculo.baseSalarial || 0);
            base += parseFloat(this.formCalculo.promedioExtrasVacaciones || 0);
            return base;
        },
        esContratistaServicios() {
            return (this.empleadoSeleccionado && this.empleadoSeleccionado.tipo_contrato === 'Prestación de servicios')
                || (this.formCalculo && this.formCalculo.tipoContrato === 'Prestación de servicios');
        },
        liquidacionPrima() {
            if (this.esContratistaServicios) return 0;
            if (this.formCalculo.primaPagada) return 0;
            return (this.basePrima * this.diasPrima) / 360;
        },
        liquidacionCesantias() {
            if (this.esContratistaServicios) return 0;
            return (this.baseCesantias * this.diasCesantias) / 360;
        },
        liquidacionIntereses() {
            if (this.esContratistaServicios) return 0;
            return (this.liquidacionCesantias * this.diasCesantias * 0.12) / 360;
        },
        liquidacionVacaciones() {
            if (this.esContratistaServicios) return 0;
            return (this.baseVacaciones * this.totalDiasVacacionesLiquidar) / 30;
        },
        sueldoPendiente() {
            if (this.esContratistaServicios) {
                if (this.formHorasExtras && this.formHorasExtras.tipoPagoContratista === 'Horas') {
                    return parseFloat(this.formHorasExtras.horasTrabajadasContratista || 0) * parseFloat(this.formHorasExtras.valorHoraContratista || 0);
                } else {
                    return (parseFloat(this.formCalculo.baseSalarial || 0) / 30) * parseFloat(this.formCalculo.diasSueldoPendiente || 0);
                }
            }
            return (parseFloat(this.formCalculo.baseSalarial || 0) / 30) * parseFloat(this.formCalculo.diasSueldoPendiente || 0);
        },
        auxilioTransportePendiente() {
            if (this.esContratistaServicios) return 0;
            if (!this.formCalculo.aplicaAuxTransp) return 0;
            return (parseFloat(this.auxilioTransporte || 0) / 30) * parseFloat(this.formCalculo.diasSueldoPendiente || 0);
        },
        liquidacionIndemnizacion() {
            if (this.esContratistaServicios) return 0;
            if (this.formCalculo.motivoRetiro !== 'Despido sin Justa Causa') return 0;

            let base = parseFloat(this.formCalculo.baseSalarial || 0);
            if (this.formCalculo.tipoContrato === 'Término Fijo') {
                if (!this.formCalculo.fechaFinalizacion) return 0;
                let diasFaltantes = this.calcularDiasComerciales(this.formCalculo.fechaRetiro, this.formCalculo.fechaFinalizacion) - 1;
                return diasFaltantes > 0 ? (base / 30) * diasFaltantes : 0;
            } else {
                // Término Indefinido
                let totalDiasAnt = this.diasTotalesAntiguedad;
                let years = totalDiasAnt / 360;
                
                if (base < 10 * this.salarioMinimo) {
                    if (years <= 1) {
                        return base;
                    } else {
                        let additionalYears = years - 1;
                        let days = 30 + (additionalYears * 20);
                        return (base / 30) * days;
                    }
                } else {
                    if (years <= 1) {
                        return base * (20 / 30);
                    } else {
                        let additionalYears = years - 1;
                        let days = 20 + (additionalYears * 15);
                        return (base / 30) * days;
                    }
                }
            }
        },
        totalDevengado() {
            let total = this.liquidacionPrima 
                      + this.liquidacionCesantias 
                      + this.liquidacionIntereses 
                      + this.liquidacionVacaciones 
                      + this.sueldoPendiente 
                      + this.auxilioTransportePendiente 
                      + parseFloat(this.formCalculo.horasExtrasPromedio || 0);
            if (this.formCalculo.motivoRetiro === 'Despido sin Justa Causa') {
                total += this.liquidacionIndemnizacion;
            }
            return total;
        },
        saludDeduccion() {
            if (this.esContratistaServicios) return 0;
            let base = this.sueldoPendiente + parseFloat(this.formCalculo.horasExtrasPromedio || 0);
            return base * 0.04;
        },
        pensionDeduccion() {
            if (this.esContratistaServicios) return 0;
            let base = this.sueldoPendiente + parseFloat(this.formCalculo.horasExtrasPromedio || 0);
            return base * 0.04;
        },
        totalDeducciones() {
            return this.saludDeduccion 
                 + this.pensionDeduccion 
                 + parseFloat(this.formCalculo.otrosDescuentos || 0) 
                 + parseFloat(this.formCalculo.fondoSolidaridad || 0) 
                 + parseFloat(this.formCalculo.retencionFuente || 0);
        },
        liquidacionTotal() {
            let total = this.totalDevengado - this.totalDeducciones;
            return total > 0 ? total : 0;
        },
        valorEnLetras() {
            return this.numeroALetras(this.liquidacionTotal);
        },

        // --- FORMULAS Y LIQUIDACIÓN DE HORAS EXTRAS ---
        valorHoraOrdinaria() {
            let base = parseFloat(this.formHorasExtras.salarioBaseOverride || this.empleadoSeleccionado.salario || 0);
            return base / 240;
        },
        montoHED() {
            return (this.formHorasExtras.horasHED || 0) * this.valorHoraOrdinaria * this.configHoras.factorHED;
        },
        montoHEN() {
            return (this.formHorasExtras.horasHEN || 0) * this.valorHoraOrdinaria * this.configHoras.factorHEN;
        },
        montoHEDD() {
            return (this.formHorasExtras.horasHEDD || 0) * this.valorHoraOrdinaria * this.configHoras.factorHEDD;
        },
        montoHEND() {
            return (this.formHorasExtras.horasHEND || 0) * this.valorHoraOrdinaria * this.configHoras.factorHEND;
        },
        montoRNO() {
            return (this.formHorasExtras.horasRNO || 0) * this.valorHoraOrdinaria * this.configHoras.factorRNO;
        },
        montoRDD() {
            return (this.formHorasExtras.horasRDD || 0) * this.valorHoraOrdinaria * this.configHoras.factorRDD;
        },
        montoRND() {
            return (this.formHorasExtras.horasRND || 0) * this.valorHoraOrdinaria * this.configHoras.factorRND;
        },
        totalHorasExtras() {
            return this.montoHED 
                 + this.montoHEN 
                 + this.montoHEDD 
                 + this.montoHEND 
                 + this.montoRNO 
                 + this.montoRDD 
                 + this.montoRND;
        },
        totalHorasExtrasLetras() {
            return this.numeroALetras(this.totalHorasExtras);
        },
        sueldoQuincenaNeto() {
            if (this.empleadoSeleccionado.tipo_contrato === 'Prestación de servicios') {
                if (this.formHorasExtras.tipoPagoContratista === 'Horas') {
                    return parseFloat(this.formHorasExtras.horasTrabajadasContratista || 0) * parseFloat(this.formHorasExtras.valorHoraContratista || 0);
                } else {
                    let base = parseFloat(this.formHorasExtras.salarioBaseOverride || this.empleadoSeleccionado.salario || 0);
                    let dias = parseFloat(this.formHorasExtras.diasTrabajados || 15);
                    return (base / 30) * dias;
                }
            }
            let base = parseFloat(this.formHorasExtras.salarioBaseOverride || this.empleadoSeleccionado.salario || 0);
            let dias = parseFloat(this.formHorasExtras.diasTrabajados || 15);
            return (base / 30) * dias;
        },
        auxTranspQuincena() {
            if (this.empleadoSeleccionado.tipo_contrato === 'Prestación de servicios') return 0;
            if (this.formHorasExtras.auxTranspOverride !== undefined) {
                return parseFloat(this.formHorasExtras.auxTranspOverride);
            }
            if (!this.formHorasExtras.aplicaAuxTransp) return 0;
            let base = parseFloat(this.auxilioTransporte || 0);
            let dias = parseFloat(this.formHorasExtras.diasTrabajados || 15);
            return (base / 30) * dias;
        },
        saludDeduccionQuincena() {
            if (this.empleadoSeleccionado.tipo_contrato === 'Prestación de servicios') return 0;
            if (this.formHorasExtras.saludDeduccionOverride !== undefined) {
                return parseFloat(this.formHorasExtras.saludDeduccionOverride);
            }
            let base = this.sueldoQuincenaNeto + this.totalHorasExtras;
            return base * 0.04;
        },
        pensionDeduccionQuincena() {
            if (this.empleadoSeleccionado.tipo_contrato === 'Prestación de servicios') return 0;
            if (this.formHorasExtras.pensionDeduccionOverride !== undefined) {
                return parseFloat(this.formHorasExtras.pensionDeduccionOverride);
            }
            let base = this.sueldoQuincenaNeto + this.totalHorasExtras;
            return base * 0.04;
        },
        netoQuincenaAPagar() {
            if (this.formHorasExtras.netoOverride !== undefined) {
                return parseFloat(this.formHorasExtras.netoOverride);
            }
            let total = this.sueldoQuincenaNeto 
                      + this.auxTranspQuincena 
                      + this.totalHorasExtras 
                      - this.saludDeduccionQuincena 
                      - this.pensionDeduccionQuincena 
                      - parseFloat(this.formHorasExtras.otrasDeducciones || 0);
            return total > 0 ? total : 0;
        },
        netoQuincenaAPagarLetras() {
            return this.numeroALetras(this.netoQuincenaAPagar);
        },
        totalPrimasSeleccionadasCount() {
            return this.arrayCalculoPrimas.filter(p => p.seleccionado).length;
        },
        totalPrimasSeleccionadasMonto() {
            return this.arrayCalculoPrimas.filter(p => p.seleccionado).reduce((acc, p) => acc + parseFloat(p.valor_prima), 0);
        }
    },
    watch: {
        // Auto check Auxilio de Transporte if Salary is less than 2 SMMLV
        // Auto check Auxilio de Transporte if Salary is less than 2 SMMLV or employee has custom transport allowance
        'formCalculo.baseSalarial'(newVal) {
            if (this.empleadoSeleccionado && parseFloat(this.empleadoSeleccionado.auxilio_transporte) > 0) {
                this.formCalculo.aplicaAuxTransp = true;
            } else {
                this.formCalculo.aplicaAuxTransp = newVal <= (this.salarioMinimo * 2);
            }
        },
        'formHorasExtras.salarioBaseOverride'(newVal) {
            if (newVal !== undefined) {
                if (this.empleadoSeleccionado && parseFloat(this.empleadoSeleccionado.auxilio_transporte) > 0) {
                    this.formHorasExtras.aplicaAuxTransp = true;
                } else {
                    this.formHorasExtras.aplicaAuxTransp = newVal <= (this.salarioMinimo * 2);
                }
            }
        },
        salarioMinimo(newVal) {
            if (this.empleadoSeleccionado && parseFloat(this.empleadoSeleccionado.auxilio_transporte) > 0) {
                this.formCalculo.aplicaAuxTransp = true;
            } else {
                this.formCalculo.aplicaAuxTransp = this.formCalculo.baseSalarial <= (newVal * 2);
            }
        },
        // Auto set default corte dates when retirement date changes
        'formCalculo.fechaRetiro'(newVal) {
            if (newVal) {
                let dateParts = newVal.split('-');
                let year = dateParts[0];
                let month = parseInt(dateParts[1]);

                // Prima de Servicios: defaults to start of current semester
                let primaStart = (month >= 7) ? `${year}-07-01` : `${year}-01-01`;
                // If fechaIngreso is inside the current semester, use fechaIngreso instead
                if (this.formCalculo.fechaIngreso && this.formCalculo.fechaIngreso > primaStart) {
                    this.formCalculo.fechaOriginalPrima = this.formCalculo.fechaIngreso;
                } else {
                    this.formCalculo.fechaOriginalPrima = primaStart;
                }
                this.formCalculo.fechaDesdePrima = this.formCalculo.fechaOriginalPrima;

                // Cesantías: defaults to start of current year
                let cesantiasStart = `${year}-01-01`;
                if (this.formCalculo.fechaIngreso && this.formCalculo.fechaIngreso > cesantiasStart) {
                    this.formCalculo.fechaDesdeCesantias = this.formCalculo.fechaIngreso;
                } else {
                    this.formCalculo.fechaDesdeCesantias = cesantiasStart;
                }

                if (this.empleadoSeleccionado && this.empleadoSeleccionado.id) {
                    this.obtenerAveragesBaseDatos(this.empleadoSeleccionado.id);
                }
            }
        },
        'formHorasExtras.fechaInicio'(newVal) {
            if (newVal && this.formHorasExtras.fechaFin) {
                let dias = this.calcularDiasComerciales(newVal, this.formHorasExtras.fechaFin);
                if (dias > 0) {
                    this.formHorasExtras.diasTrabajados = dias;
                }
            }
        },
        'formHorasExtras.fechaFin'(newVal) {
            if (newVal && this.formHorasExtras.fechaInicio) {
                let dias = this.calcularDiasComerciales(this.formHorasExtras.fechaInicio, newVal);
                if (dias > 0) {
                    this.formHorasExtras.diasTrabajados = dias;
                }
            }
        }
    },
    methods: {
        formatMonto(val) {
            if (val === undefined || val === null) return '0.00';
            return parseFloat(val).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        listarEmpleados(page) {
            let me = this;
            let url = `/empleado?page=${page}&per_page=15&buscar=${me.buscar}&criterio=nombre`;
            axios.get(url).then(response => {
                let r = response.data;
                me.arrayEmpleados = r.empleados.data;
                me.pagination = r.pagination;
            }).catch(error => {
                console.error(error);
            });
        },
        cambiarPagina(page) {
            this.pagination.current_page = page;
            this.listarEmpleados(page);
        },
        calcularDiasComerciales(fechaIni, fechaFin) {
            if (!fechaIni || !fechaFin) return 0;
            let parts1 = fechaIni.split('-');
            let parts2 = fechaFin.split('-');
            if (parts1.length !== 3 || parts2.length !== 3) return 0;

            let y1 = parseInt(parts1[0], 10);
            let m1 = parseInt(parts1[1], 10);
            let d1 = parseInt(parts1[2], 10);

            let y2 = parseInt(parts2[0], 10);
            let m2 = parseInt(parts2[1], 10);
            let d2 = parseInt(parts2[2], 10);

            if (y2 < y1 || (y2 === y1 && m2 < m1) || (y2 === y1 && m2 === m1 && d2 < d1)) return 0;

            if (d1 === 31) d1 = 30;
            if (d2 === 31) d2 = 30;

            let isEndFebLast = (m2 === 2 && ((y2 % 4 === 0 && d2 === 29) || (y2 % 4 !== 0 && d2 === 28)));
            if (isEndFebLast) d2 = 30;
            let isStartFebLast = (m1 === 2 && ((y1 % 4 === 0 && d1 === 29) || (y1 % 4 !== 0 && d1 === 28)));
            if (isStartFebLast) d1 = 30;

            let days = (y2 - y1) * 360 + (m2 - m1) * 30 + (d2 - d1);
            return days + 1;
        },
        abrirCalculadora(emp) {
            this.empleadoSeleccionado = emp;
            this.auxilioTransporte = emp.auxilio_transporte > 0 ? parseFloat(emp.auxilio_transporte) : this.auxilioTransporteGlobal;
            this.vista = 'calculadora';

            let today = new Date().toISOString().split('T')[0];
            let year = today.split('-')[0];
            let month = parseInt(today.split('-')[1]);

            // Set calculation initial form values
            this.formCalculo = {
                tipoContrato: emp.tipo_contrato || 'Término Indefinido',
                fechaIngreso: emp.fecha_ingreso || today,
                fechaRetiro: today,
                fechaFinalizacion: emp.fecha_finalizacion || '',
                baseSalarial: parseFloat(emp.salario) - parseFloat(emp.auxilio_transporte || 0),
                aplicaAuxTransp: emp.auxilio_transporte > 0 || (parseFloat(emp.salario) - parseFloat(emp.auxilio_transporte || 0)) <= (this.salarioMinimo * 2),
                motivoRetiro: 'Renuncia',
                causaRetiro: 'Terminación de contrato por renuncia del empleado',
                fechaDesdePrima: (month >= 7) ? `${year}-07-01` : `${year}-01-01`,
                fechaDesdeCesantias: `${year}-01-01`,
                fechaDesdeVacaciones: emp.fecha_ingreso || today,
                diasVacacionesTomados: 0,
                diasVacacionesAnticipadas: 0,
                diasVacacionesManual: 0,
                descuentoAusencias: 0,
                diasSueldoPendiente: 0,
                horasExtrasPromedio: 0,
                promedioExtrasPrima: 0,
                promedioExtrasCesantias: 0,
                promedioExtrasVacaciones: 0,
                fondoSolidaridad: 0,
                retencionFuente: 0,
                otrosDescuentos: 0,
                primaPagada: false
            };

            // Adjust cutting dates to not start before hire date
            if (emp.fecha_ingreso) {
                if (this.formCalculo.fechaDesdePrima < emp.fecha_ingreso) {
                    this.formCalculo.fechaDesdePrima = emp.fecha_ingreso;
                }
                if (this.formCalculo.fechaDesdeCesantias < emp.fecha_ingreso) {
                    this.formCalculo.fechaDesdeCesantias = emp.fecha_ingreso;
                }
            }
            this.obtenerAveragesBaseDatos(emp.id);
        },
        volverAlListado() {
            this.vista = 'listado';
            this.empleadoSeleccionado = {};
        },
        imprimirLiquidacion() {
            document.body.classList.add('print-liquidacion');
            document.body.classList.remove('print-horas-extras');
            this.$nextTick(() => {
                window.print();
                document.body.classList.remove('print-liquidacion');
            });
        },
        abrirCalculadoraHoras(emp) {
            this.editandoQuincenaId = null;
            this.empleadoSeleccionado = emp;
            this.auxilioTransporte = emp.auxilio_transporte > 0 ? parseFloat(emp.auxilio_transporte) : this.auxilioTransporteGlobal;
            this.modalHorasExtras = 1;
            this.arrayTurnos = [];

            let today = new Date();
            let y = today.getFullYear();
            let m = String(today.getMonth() + 1).padStart(2, '0');
            let d = today.getDate();
            
            let start = '';
            let end = '';
            if (d <= 15) {
                start = `${y}-${m}-01`;
                end = `${y}-${m}-15`;
            } else {
                start = `${y}-${m}-16`;
                let lastDay = new Date(y, today.getMonth() + 1, 0).getDate();
                end = `${y}-${m}-${lastDay}`;
            }
            let todayStr = today.toISOString().split('T')[0];

            this.formHorasExtras = {
                horasHED: 0,
                horasHEN: 0,
                horasHEDD: 0,
                horasHEND: 0,
                horasRNO: 0,
                horasRDD: 0,
                horasRND: 0,
                fechaInicio: start,
                fechaFin: end,
                fechaPago: todayStr,
                diasTrabajados: 15,
                metodoPago: 'Banco',
                otrasDeducciones: 0,
                aplicaAuxTransp: emp.auxilio_transporte > 0 || (parseFloat(emp.salario) - parseFloat(emp.auxilio_transporte || 0)) <= (this.salarioMinimo * 2),
                salarioBaseOverride: parseFloat(emp.salario) - parseFloat(emp.auxilio_transporte || 0),
                auxTranspOverride: undefined,
                saludDeduccionOverride: undefined,
                pensionDeduccionOverride: undefined,
                netoOverride: undefined,
                tipoPagoContratista: 'Fijo',
                horasTrabajadasContratista: 0,
                valorHoraContratista: parseFloat(emp.salario) || 0
            };
        },
        esFestivo(dateStr) {
            const holidays = [
                // 2025
                "2025-01-01", "2025-01-06", "2025-03-24", "2025-04-17", "2025-04-18",
                "2025-05-01", "2025-05-26", "2025-06-16", "2025-06-23", "2025-06-30",
                "2025-07-20", "2025-08-07", "2025-08-18", "2025-10-13", "2025-11-03",
                "2025-11-16", "2025-12-08", "2025-12-25",
                // 2026
                "2026-01-01", "2026-01-12", "2026-03-23", "2026-04-02", "2026-04-03",
                "2026-05-01", "2026-05-18", "2026-06-08", "2026-06-15", "2026-06-29",
                "2026-07-20", "2026-08-07", "2026-08-17", "2026-10-12", "2026-11-02",
                "2026-11-16", "2026-12-08", "2026-12-25"
            ];
            const parts = dateStr.split('-');
            const mmdd = parts[1] + '-' + parts[2];
            const fixedHolidays = ["01-01", "05-01", "07-20", "08-07", "12-08", "12-25"];
            return holidays.includes(dateStr) || fixedHolidays.includes(mmdd);
        },
         calcularHorasTurno(fecha, horaEntrada, horaSalida, esFestivoManual) {
            if (!fecha || !horaEntrada || !horaSalida) return null;
            
            let [h1, m1] = horaEntrada.split(':').map(Number);
            let [h2, m2] = horaSalida.split(':').map(Number);
            
            let totalMinutes = 0;
            let startMinutes = h1 * 60 + m1;
            let endMinutes = h2 * 60 + m2;
            if (endMinutes >= startMinutes) {
                totalMinutes = endMinutes - startMinutes;
            } else {
                totalMinutes = (24 * 60 - startMinutes) + endMinutes;
            }
            let duracion = totalMinutes / 60;
            
            let counts = {
                HED: 0,
                HEN: 0,
                HEDD: 0,
                HEND: 0,
                RNO: 0,
                RDD: 0,
                RND: 0
            };
            
            let [dStartH, dStartM] = (this.configHoras.inicioDiurna || '06:00').split(':').map(Number);
            let [dEndH, dEndM] = (this.configHoras.finDiurna || '21:00').split(':').map(Number);
            let startDiurnaDecimal = dStartH + (dStartM || 0) / 60;
            let endDiurnaDecimal = dEndH + (dEndM || 0) / 60;

            let [joStartH, joStartM] = (this.configHoras.inicioJornadaOrdinaria || '07:00').split(':').map(Number);
            let [joEndH, joEndM] = (this.configHoras.finJornadaOrdinaria || '17:00').split(':').map(Number);
            let startJODecimal = joStartH + (joStartM || 0) / 60;
            let endJODecimal = joEndH + (joEndM || 0) / 60;

            for (let i = 0; i < totalMinutes; i++) {
                let currentTotalMinutes = startMinutes + i;
                let timeOfDayMinutes = currentTotalMinutes % 1440;
                let timeOfDay = timeOfDayMinutes / 60;
                
                let daysOffset = Math.floor(currentTotalMinutes / 1440);
                let blockDate = new Date(fecha + 'T12:00:00');
                if (daysOffset > 0) {
                    blockDate.setDate(blockDate.getDate() + daysOffset);
                }
                
                let blockDateStr = blockDate.toISOString().split('T')[0];
                let dayOfWeek = blockDate.getDay();
                let esFestivoDia = (dayOfWeek === 0) || this.esFestivo(blockDateStr) || (blockDateStr === fecha && esFestivoManual);
                
                let esDiurno = false;
                if (startDiurnaDecimal <= endDiurnaDecimal) {
                    esDiurno = (timeOfDay >= startDiurnaDecimal && timeOfDay < endDiurnaDecimal);
                } else {
                    esDiurno = (timeOfDay >= startDiurnaDecimal || timeOfDay < endDiurnaDecimal);
                }
                
                let esJornadaOrdinaria = false;
                if (!esFestivoDia) {
                    if (dayOfWeek === 6) {
                        // Sábado
                        if (this.configHoras.sabadoLaboral) {
                            esJornadaOrdinaria = (timeOfDay >= startJODecimal && timeOfDay < endJODecimal);
                        } else {
                            esJornadaOrdinaria = false;
                        }
                    } else if (dayOfWeek >= 1 && dayOfWeek <= 5) {
                        // Lunes a Viernes
                        esJornadaOrdinaria = (timeOfDay >= startJODecimal && timeOfDay < endJODecimal);
                    }
                }
                
                if (esJornadaOrdinaria) {
                    // Hora ordinaria (dentro del horario laboral)
                    if (!esDiurno) {
                        // Recargo Nocturno Ordinario si se extiende a horario nocturno
                        counts.RNO += 1 / 60;
                    }
                } else {
                    // Hora extra (fuera del horario laboral ordinario, o en sábados/domingos/festivos)
                    if (esFestivoDia) {
                        if (esDiurno) {
                            counts.HEDD += 1 / 60;
                        } else {
                            counts.HEND += 1 / 60;
                        }
                    } else {
                        if (esDiurno) {
                            counts.HED += 1 / 60;
                        } else {
                            counts.HEN += 1 / 60;
                        }
                    }
                }
            }
            
            return {
                duracion,
                counts
            };
        },
        recalcularTurnos() {
            this.formHorasExtras.horasHED = 0;
            this.formHorasExtras.horasHEN = 0;
            this.formHorasExtras.horasHEDD = 0;
            this.formHorasExtras.horasHEND = 0;
            this.formHorasExtras.horasRNO = 0;
            this.formHorasExtras.horasRDD = 0;
            this.formHorasExtras.horasRND = 0;
            
            this.arrayTurnos.forEach(turno => {
                let res = this.calcularHorasTurno(turno.fecha, turno.horaEntrada, turno.horaSalida, turno.esFestivoManual);
                if (res) {
                    turno.duracion = res.duracion;
                    this.formHorasExtras.horasHED += res.counts.HED;
                    this.formHorasExtras.horasHEN += res.counts.HEN;
                    this.formHorasExtras.horasHEDD += res.counts.HEDD;
                    this.formHorasExtras.horasHEND += res.counts.HEND;
                    this.formHorasExtras.horasRNO += res.counts.RNO;
                    this.formHorasExtras.horasRDD += res.counts.RDD;
                    this.formHorasExtras.horasRND += res.counts.RND;
                }
            });

            this.formHorasExtras.horasHED = parseFloat(this.formHorasExtras.horasHED.toFixed(2));
            this.formHorasExtras.horasHEN = parseFloat(this.formHorasExtras.horasHEN.toFixed(2));
            this.formHorasExtras.horasHEDD = parseFloat(this.formHorasExtras.horasHEDD.toFixed(2));
            this.formHorasExtras.horasHEND = parseFloat(this.formHorasExtras.horasHEND.toFixed(2));
            this.formHorasExtras.horasRNO = parseFloat(this.formHorasExtras.horasRNO.toFixed(2));
            this.formHorasExtras.horasRDD = parseFloat(this.formHorasExtras.horasRDD.toFixed(2));
            this.formHorasExtras.horasRND = parseFloat(this.formHorasExtras.horasRND.toFixed(2));
        },
        agregarTurno() {
            let today = new Date().toISOString().split('T')[0];
            let dayOfWeek = new Date(today + 'T12:00:00').getDay();
            let isFest = (dayOfWeek === 0) || this.esFestivo(today);
            this.arrayTurnos.push({
                fecha: today,
                esFestivoManual: isFest,
                horaEntrada: '08:00',
                horaSalida: '17:00',
                duracion: 9.0
            });
            this.recalcularTurnos();
        },
        onTurnoDateChange(turno) {
            if (turno.fecha) {
                let dayOfWeek = new Date(turno.fecha + 'T12:00:00').getDay();
                turno.esFestivoManual = (dayOfWeek === 0) || this.esFestivo(turno.fecha);
            }
            this.recalcularTurnos();
        },
        eliminarTurno(index) {
            this.arrayTurnos.splice(index, 1);
            this.recalcularTurnos();
        },
        cerrarCalculadoraHoras() {
            this.modalHorasExtras = 0;
        },
        cargarExtrasALiquidacion() {
            let total = this.totalHorasExtras;
            this.cerrarCalculadoraHoras();
            // Open contract liquidation for this employee
            this.abrirCalculadora(this.empleadoSeleccionado);
            // Pre-fill the overtime average
            this.formCalculo.horasExtrasPromedio = parseFloat(total.toFixed(2));
        },
        registrarEgresoExtras() {
            let total = this.totalHorasExtras;
            this.cerrarCalculadoraHoras();
            
            // Set up Egreso modal for monthly overtime payment
            this.modalEgreso = 1;
            let cat = 'Nómina';
            let match = this.arrayClasificaciones.find(c => c.nombre.toLowerCase() === 'nómina' || c.nombre.toLowerCase() === 'nomina');
            if (match) {
                cat = match.nombre;
            } else if (this.arrayClasificaciones.length > 0) {
                cat = this.arrayClasificaciones[0].nombre;
            }

            let today = new Date().toISOString().split('T')[0];
            this.formEgreso = {
                fecha: today,
                tipo_egreso: cat,
                concepto: `Pago de Horas Extras y Recargos - ${this.empleadoSeleccionado.nombre} ${this.empleadoSeleccionado.apellido}`,
                valor: parseFloat(total.toFixed(2)),
                metodo_pago: 'Banco',
                beneficiario: `${this.empleadoSeleccionado.nombre} ${this.empleadoSeleccionado.apellido}`
            };
        },
        imprimirHorasExtras() {
            document.body.classList.add('print-horas-extras');
            document.body.classList.remove('print-liquidacion');
            this.$nextTick(() => {
                window.print();
                document.body.classList.remove('print-horas-extras');
            });
        },
        numeroALetras(num) {
            let cant_cero_a_veinte = [
                "cero", "uno", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho", "nueve", 
                "diez", "once", "doce", "trece", "catorce", "quince", "dieciseis", "diecisiete", "dieciocho", "diecinueve", "veinte"
            ];
            let cant_decenas = [
                "", "", "veinte", "treinta", "cuarenta", "cincuenta", "sesenta", "setenta", "ochenta", "noventa"
            ];
            let cant_centenas = [
                "", "cien", "doscientos", "trescientos", "cuatrocientos", "quinientos", "seiscientos", "setecientos", "ochocientos", "novecientos"
            ];

            function unidad(n) {
                return cant_cero_a_veinte[n];
            }

            function decena(n) {
                if (n <= 20) return cant_cero_a_veinte[n];
                let u = n % 10;
                let d = Math.floor(n / 10);
                if (d === 2) {
                    return u === 0 ? "veinte" : "veinti" + unidad(u);
                }
                return u === 0 ? cant_decenas[d] : cant_decenas[d] + " y " + unidad(u);
            }

            function centena(n) {
                if (n === 100) return "cien";
                if (n < 100) return decena(n);
                let d = n % 100;
                let c = Math.floor(n / 100);
                if (c === 1) {
                    return "ciento " + decena(d);
                }
                return cant_centenas[c] + " " + decena(d);
            }

            function miles(n) {
                if (n < 1000) return centena(n);
                let c = n % 1000;
                let m = Math.floor(n / 1000);
                if (m === 1) {
                    return "mil " + (c > 0 ? centena(c) : "");
                }
                return centena(m) + " mil " + (c > 0 ? centena(c) : "");
            }

            function millones(n) {
                if (n < 1000000) return miles(n);
                let m = n % 1000000;
                let mill = Math.floor(n / 1000000);
                let txt = "";
                if (mill === 1) {
                    txt = "un millón";
                } else {
                    txt = miles(mill) + " millones";
                }
                return txt + (m > 0 ? " " + miles(m) : "");
            }

            let entero = Math.floor(num);
            let centavos = Math.round((num - entero) * 100);
            let centavosTxt = centavos > 0 ? ` con ${centavos}/100 centavos` : "";

            if (entero === 0) return "Cero pesos m/cte" + centavosTxt;
            
            let letras = millones(entero);
            letras = letras.replace(/\s+/g, ' ').trim();
            letras = letras.charAt(0).toUpperCase() + letras.slice(1);
            return letras + " pesos m/cte" + centavosTxt;
        },

        // --- REGISTRO DE EGRESO ---
        abrirModalEgreso() {
            this.modalEgreso = 1;
            
            let cat = 'Nómina';
            let match = this.arrayClasificaciones.find(c => c.nombre.toLowerCase() === 'nómina' || c.nombre.toLowerCase() === 'nomina');
            if (match) {
                cat = match.nombre;
            } else if (this.arrayClasificaciones.length > 0) {
                cat = this.arrayClasificaciones[0].nombre;
            }

            this.formEgreso = {
                fecha: this.formCalculo.fechaRetiro,
                tipo_egreso: cat,
                concepto: `Liquidación definitiva de contrato - ${this.empleadoSeleccionado.nombre} ${this.empleadoSeleccionado.apellido} (Período: ${this.formCalculo.fechaIngreso} a ${this.formCalculo.fechaRetiro})`,
                valor: parseFloat(this.liquidacionTotal.toFixed(2)),
                metodo_pago: 'Banco',
                beneficiario: `${this.empleadoSeleccionado.nombre} ${this.empleadoSeleccionado.apellido}`
            };
        },
        cerrarModalEgreso() {
            this.modalEgreso = 0;
        },
        guardarEgresoLiquidacion() {
            let me = this;
            if (!me.formEgreso.fecha || !me.formEgreso.concepto || !me.formEgreso.valor) {
                Swal.fire('Error', 'Complete los campos obligatorios del egreso.', 'error');
                return;
            }

            axios.post('/egresos/registrar', me.formEgreso).then(response => {
                me.cerrarModalEgreso();
                Swal.fire('Registrado', 'La liquidación ha sido registrada como un Egreso en el sistema.', 'success');
            }).catch(error => {
                console.error(error);
                if (error.response && error.response.data && error.response.data.error) {
                    Swal.fire('Error', error.response.data.error, 'error');
                } else {
                    Swal.fire('Error', 'No se pudo guardar el egreso de la liquidación.', 'error');
                }
            });
        },
        cargarClasificaciones() {
            let me = this;
            axios.get('/clasificaciones-egresos').then(response => {
                me.arrayClasificaciones = response.data;
            }).catch(error => {
                console.error(error);
            });
        },
        cambiarVistaTab(vista) {
            this.vista = vista;
            if (vista === 'historial_quincenas') {
                this.cargarTodosEmpleados();
                this.listarQuincenas(1);
            } else if (vista === 'seguridad_social') {
                this.generarReporteSeguridadSocial();
            } else if (vista === 'primas_semestrales') {
                this.cargarTodosEmpleados();
            } else if (vista === 'historial_primas') {
                this.cargarTodosEmpleados();
                this.listarPrimas(1);
            }
        },
        cargarTodosEmpleados() {
            let me = this;
            axios.get('/empleado?per_page=100').then(response => {
                me.arrayTodosEmpleados = response.data.empleados.data || [];
            }).catch(error => {
                console.error(error);
            });
        },
        listarQuincenas(page) {
            let me = this;
            let url = `/nomina/quincenas?page=${page}&per_page=15` 
                    + `&empleado_id=${me.filtroQuincenaEmpleadoId}`
                    + `&fecha_desde=${me.filtroQuincenaFechaDesde}`
                    + `&fecha_hasta=${me.filtroQuincenaFechaHasta}`;
            axios.get(url).then(response => {
                let r = response.data;
                me.arrayQuincenas = r.quincenas.data;
                me.paginacionQuincenas = r.pagination;
            }).catch(error => {
                console.error(error);
            });
        },
        eliminarQuincena(id) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar esta liquidación de quincena?',
                text: "Se eliminará el registro y también su Egreso contable asociado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    axios.delete(`/nomina/quincenas/eliminar/${id}`).then(response => {
                        Swal.fire('Eliminado', 'Liquidación eliminada correctamente.', 'success');
                        me.listarQuincenas(me.paginacionQuincenas.current_page);
                    }).catch(error => {
                        console.error(error);
                        Swal.fire('Error', 'No se pudo eliminar la liquidación.', 'error');
                    });
                }
            });
        },
        abrirEditarQuincena(q) {
            this.editandoQuincenaId = q.id;
            this.empleadoSeleccionado = q.empleado || {};
            this.auxilioTransporte = (q.empleado && q.empleado.auxilio_transporte > 0) ? parseFloat(q.empleado.auxilio_transporte) : this.auxilioTransporteGlobal;
            this.modalHorasExtras = 1;

            let extras = {
                horasHED: 0,
                horasHEN: 0,
                horasHEDD: 0,
                horasHEND: 0,
                horasRNO: 0,
                horasRDD: 0,
                horasRND: 0,
                tipoPagoContratista: 'Fijo',
                horasTrabajadasContratista: 0,
                valorHoraContratista: 0
            };
            let turnos = [];
            if (q.horas_extras_json) {
                try {
                    let parsed = JSON.parse(q.horas_extras_json);
                    if (parsed && typeof parsed === 'object') {
                        if (parsed.horasHED !== undefined) {
                            extras = Object.assign({}, extras, parsed);
                        }
                        if (parsed.turnos !== undefined) {
                            turnos = parsed.turnos;
                        }
                    }
                } catch(e) {
                    console.error("Error parsing horas_extras_json", e);
                }
            }

            this.arrayTurnos = turnos;

            this.formHorasExtras = {
                horasHED: extras.horasHED || 0,
                horasHEN: extras.horasHEN || 0,
                horasHEDD: extras.horasHEDD || 0,
                horasHEND: extras.horasHEND || 0,
                horasRNO: extras.horasRNO || 0,
                horasRDD: extras.horasRDD || 0,
                horasRND: extras.horasRND || 0,
                fechaInicio: q.fecha_inicio,
                fechaFin: q.fecha_fin,
                fechaPago: q.fecha_pago,
                diasTrabajados: q.dias_trabajados,
                metodoPago: q.egreso ? q.egreso.metodo_pago : 'Banco',
                otrasDeducciones: q.otras_deducciones,
                aplicaAuxTransp: parseFloat(q.auxilio_transporte) > 0,
                salarioBaseOverride: q.salario_base,
                auxTranspOverride: undefined,
                saludDeduccionOverride: undefined,
                pensionDeduccionOverride: undefined,
                netoOverride: undefined,
                tipoPagoContratista: extras.tipoPagoContratista || 'Fijo',
                horasTrabajadasContratista: extras.horasTrabajadasContratista || 0,
                valorHoraContratista: extras.valorHoraContratista || 0
            };
        },
        guardarPagoQuincena() {
            let me = this;
            if (!me.formHorasExtras.fechaPago || !me.formHorasExtras.fechaInicio || !me.formHorasExtras.fechaFin) {
                Swal.fire('Error', 'Complete las fechas de pago y periodo.', 'error');
                return;
            }

            let payload = {
                empleado_id: me.empleadoSeleccionado.id,
                fecha_pago: me.formHorasExtras.fechaPago,
                fecha_inicio: me.formHorasExtras.fechaInicio,
                fecha_fin: me.formHorasExtras.fechaFin,
                dias_trabajados: me.formHorasExtras.diasTrabajados,
                salario_base: parseFloat(me.formHorasExtras.salarioBaseOverride !== undefined ? me.formHorasExtras.salarioBaseOverride : (me.empleadoSeleccionado.salario - (me.empleadoSeleccionado.auxilio_transporte || 0))),
                sueldo_neto: parseFloat(me.sueldoQuincenaNeto.toFixed(2)),
                auxilio_transporte: parseFloat(me.auxTranspQuincena.toFixed(2)),
                monto_extras: parseFloat(me.totalHorasExtras.toFixed(2)),
                salud_deduccion: parseFloat(me.saludDeduccionQuincena.toFixed(2)),
                pension_deduccion: parseFloat(me.pensionDeduccionQuincena.toFixed(2)),
                otras_deducciones: parseFloat(me.formHorasExtras.otrasDeducciones || 0),
                neto_pagado: parseFloat(me.netoQuincenaAPagar.toFixed(2)),
                metodo_pago: me.formHorasExtras.metodoPago,
                horas_extras_json: JSON.stringify({
                    horasHED: me.formHorasExtras.horasHED,
                    horasHEN: me.formHorasExtras.horasHEN,
                    horasHEDD: me.formHorasExtras.horasHEDD,
                    horasHEND: me.formHorasExtras.horasHEND,
                    horasRNO: me.formHorasExtras.horasRNO,
                    horasRDD: me.formHorasExtras.horasRDD,
                    horasRND: me.formHorasExtras.horasRND,
                    turnos: me.arrayTurnos,
                    tipoPagoContratista: me.formHorasExtras.tipoPagoContratista,
                    horasTrabajadasContratista: me.formHorasExtras.horasTrabajadasContratista,
                    valorHoraContratista: me.formHorasExtras.valorHoraContratista
                })
            };

            if (me.editandoQuincenaId) {
                axios.put(`/nomina/quincenas/actualizar/${me.editandoQuincenaId}`, payload).then(response => {
                    me.cerrarCalculadoraHoras();
                    Swal.fire('Modificado', 'La quincena y el egreso se han actualizado con éxito.', 'success');
                    me.listarQuincenas(me.paginacionQuincenas.current_page);
                }).catch(error => {
                    console.error(error);
                    if (error.response && error.response.data && error.response.data.error) {
                        Swal.fire('Error', error.response.data.error, 'error');
                    } else {
                        Swal.fire('Error', 'No se pudo actualizar la liquidación de la quincena.', 'error');
                    }
                });
            } else {
                axios.post('/nomina/quincenas/registrar', payload).then(response => {
                    me.cerrarCalculadoraHoras();
                    Swal.fire('Pago Registrado', 'La quincena y el egreso se han guardado con éxito.', 'success');
                    me.listarEmpleados(me.pagination.current_page);
                }).catch(error => {
                    console.error(error);
                    if (error.response && error.response.data && error.response.data.error) {
                        Swal.fire('Error', error.response.data.error, 'error');
                    } else {
                        Swal.fire('Error', 'No se pudo guardar la liquidación de la quincena.', 'error');
                    }
                });
            }
        },
        obtenerAveragesBaseDatos(empleadoId) {
            let me = this;
            let params = `?empleado_id=${empleadoId}`
                       + `&fecha_desde_prima=${me.formCalculo.fechaDesdePrima}`
                       + `&fecha_desde_cesantias=${me.formCalculo.fechaDesdeCesantias}`
                       + `&fecha_desde_vacaciones=${me.formCalculo.fechaDesdeVacaciones}`
                       + `&fecha_retiro=${me.formCalculo.fechaRetiro}`;
            axios.get(`/nomina/promedio-extras${params}`).then(response => {
                let r = response.data;
                if (r.tiene_registros) {
                    me.formCalculo.horasExtrasPromedio = 0;
                    me.formCalculo.promedioExtrasPrima = r.promedio_extras_prima;
                    me.formCalculo.promedioExtrasCesantias = r.promedio_extras_cesantias;
                    me.formCalculo.promedioExtrasVacaciones = r.promedio_extras_vacaciones;
                    me.formCalculo.baseSalarial = r.salario_base_promedio;
                    Swal.fire({
                        title: 'Promedios Cargados',
                        text: `Se detectaron quincenas guardadas. Se discriminaron los promedios de extras (Prima: $${me.formatMonto(r.promedio_extras_prima)}, Cesantías: $${me.formatMonto(r.promedio_extras_cesantias)}, Vacaciones: $${me.formatMonto(r.promedio_extras_vacaciones)}).`,
                        icon: 'info',
                        timer: 4000
                    });
                }
            }).catch(error => {
                console.error("Error al obtener promedios reales", error);
            });
        },
        generarReporteSeguridadSocial() {
            let me = this;
            me.cargandoReporteSeg = true;
            axios.get(`/nomina/seguridad-social?anio=${me.reporteSegAnio}&mes=${me.reporteSegMes}`).then(response => {
                me.arrayReporteSeguridadSocial = response.data;
                me.cargandoReporteSeg = false;
            }).catch(error => {
                console.error(error);
                me.cargandoReporteSeg = false;
                Swal.fire('Error', 'No se pudo obtener el reporte de seguridad social.', 'error');
            });
        },
        reimprimirQuincena(q) {
            this.empleadoSeleccionado = q.empleado;
            
            let extras = {
                horasHED: 0,
                horasHEN: 0,
                horasHEDD: 0,
                horasHEND: 0,
                horasRNO: 0,
                horasRDD: 0,
                horasRND: 0
            };
            if (q.horas_extras_json) {
                try {
                    let parsed = JSON.parse(q.horas_extras_json);
                    if (parsed && typeof parsed === 'object') {
                        if (parsed.horasHED !== undefined) {
                            extras = parsed;
                        }
                    }
                } catch(e) {
                    console.error("Error parsing horas_extras_json", e);
                }
            }

            this.formHorasExtras = {
                horasHED: extras.horasHED || 0,
                horasHEN: extras.horasHEN || 0,
                horasHEDD: extras.horasHEDD || 0,
                horasHEND: extras.horasHEND || 0,
                horasRNO: extras.horasRNO || 0,
                horasRDD: extras.horasRDD || 0,
                horasRND: extras.horasRND || 0,
                fechaInicio: q.fecha_inicio,
                fechaFin: q.fecha_fin,
                fechaPago: q.fecha_pago,
                diasTrabajados: q.dias_trabajados,
                metodoPago: q.egreso ? q.egreso.metodo_pago : 'Banco',
                otrasDeducciones: q.otras_deducciones,
                aplicaAuxTransp: parseFloat(q.auxilio_transporte) > 0,
                salarioBaseOverride: q.salario_base,
                auxTranspOverride: q.auxilio_transporte,
                saludDeduccionOverride: q.salud_deduccion,
                pensionDeduccionOverride: q.pension_deduccion,
                netoOverride: q.neto_pagado
            };

            this.imprimirHorasExtras();
        },
        calcularPrimasSemestre() {
            let me = this;
            me.cargandoPrimas = true;
            axios.get(`/nomina/primas/calcular?anio=${me.primaFiltroAnio}&periodo=${me.primaFiltroPeriodo}`)
                .then(response => {
                    me.arrayCalculoPrimas = response.data.map(p => {
                        p.seleccionado = !p.ya_pagado;
                        return p;
                    });
                    me.cargandoPrimas = false;
                })
                .catch(error => {
                    console.error(error);
                    me.cargandoPrimas = false;
                    Swal.fire('Error', 'No se pudo calcular las primas semestrales.', 'error');
                });
        },
        toggleSeleccionarTodasPrimas() {
            let me = this;
            me.arrayCalculoPrimas.forEach(p => {
                if (!p.ya_pagado) {
                    p.seleccionado = me.seleccionarTodasPrimas;
                }
            });
        },
        registrarPagoPrimas() {
            let me = this;
            let pagosSeleccionados = me.arrayCalculoPrimas.filter(p => p.seleccionado && !p.ya_pagado);
            if (pagosSeleccionados.length === 0) {
                Swal.fire('Atención', 'No hay primas seleccionadas para pagar.', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Está seguro de registrar el pago de primas?',
                text: `Se registrarán ${pagosSeleccionados.length} pagos de primas y sus correspondientes egresos contables.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    me.registrandoPrimas = true;
                    let payload = {
                        fecha_pago: me.primaFechaPago,
                        metodo_pago: me.primaMetodoPago,
                        anio: me.primaFiltroAnio,
                        periodo: me.primaFiltroPeriodo,
                        pagos: pagosSeleccionados.map(p => {
                            return {
                                empleado_id: p.empleado_id,
                                dias_trabajados: p.dias_trabajados,
                                salario_base: p.salario_base,
                                promedio_extras: p.promedio_extras,
                                valor_prima: p.valor_prima
                            };
                        })
                    };

                    axios.post('/nomina/primas/registrar', payload)
                        .then(response => {
                            me.registrandoPrimas = false;
                            Swal.fire('Éxito', response.data.message, 'success');
                            me.calcularPrimasSemestre();
                        })
                        .catch(error => {
                            console.error(error);
                            me.registrandoPrimas = false;
                            if (error.response && error.response.data && error.response.data.error) {
                                Swal.fire('Error', error.response.data.error, 'error');
                            } else {
                                Swal.fire('Error', 'No se pudieron registrar los pagos de primas.', 'error');
                            }
                        });
                }
            });
        },
        listarPrimas(page) {
            let me = this;
            let url = `/nomina/primas/historial?page=${page}&per_page=15`
                    + `&empleado_id=${me.filtroPrimaEmpleadoId}`
                    + `&anio=${me.filtroPrimaAnio}`
                    + `&periodo=${me.filtroPrimaPeriodo}`;
            axios.get(url).then(response => {
                let r = response.data;
                me.arrayHistorialPrimas = r.primas.data;
                me.paginacionPrimas = r.pagination;
            }).catch(error => {
                console.error(error);
            });
        },
        eliminarPrima(id) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar esta liquidación de prima?',
                text: "Se eliminará el registro y también su Egreso contable asociado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    axios.delete(`/nomina/primas/eliminar/${id}`).then(response => {
                        Swal.fire('Eliminado', 'Liquidación de prima eliminada correctamente.', 'success');
                        me.listarPrimas(me.paginacionPrimas.current_page);
                    }).catch(error => {
                        console.error(error);
                        Swal.fire('Error', 'No se pudo eliminar la liquidación de prima.', 'error');
                    });
                }
            });
        },
        cargarConfigHoras() {
            let saved = localStorage.getItem('sistema_config_horas');
            if (saved) {
                try {
                    let parsed = JSON.parse(saved);
                    if (parsed && typeof parsed === 'object') {
                        this.configHoras = Object.assign({}, this.configHoras, parsed);
                    }
                } catch(e) {
                    console.error("Error al cargar configHoras", e);
                }
            }
        },
        guardarConfigHoras() {
            try {
                localStorage.setItem('sistema_config_horas', JSON.stringify(this.configHoras));
                Swal.fire('Configuración Guardada', 'La configuración general de horarios y factores se ha guardado exitosamente.', 'success');
            } catch(e) {
                console.error("Error al guardar configHoras", e);
            }
        },
        abrirModalPagarQuincena(q) {
            this.quincenaAPagar = q;
            let todayStr = new Date().toISOString().split('T')[0];
            this.formPagarQuincena = {
                fecha_pago: q.fecha_pago || todayStr,
                metodo_pago: (q.egreso && q.egreso.metodo_pago) ? q.egreso.metodo_pago : 'Banco'
            };
            this.modalPagarQuincena = 1;
        },
        cerrarModalPagarQuincena() {
            this.modalPagarQuincena = 0;
            this.quincenaAPagar = {};
        },
        confirmarPagoQuincena() {
            let me = this;
            if (!me.quincenaAPagar || !me.quincenaAPagar.id) return;
            if (!me.formPagarQuincena.metodo_pago) {
                Swal.fire('Atención', 'Seleccione la forma de pago.', 'warning');
                return;
            }

            axios.post(`/nomina/quincenas/pagar/${me.quincenaAPagar.id}`, me.formPagarQuincena).then(response => {
                me.cerrarModalPagarQuincena();
                Swal.fire('Quincena Pagada', 'El pago ha sido registrado y los movimientos contables han sido generados exitosamente.', 'success');
                me.listarQuincenas(me.paginacionQuincenas.current_page);
            }).catch(error => {
                console.error(error);
                if (error.response && error.response.data && error.response.data.error) {
                    Swal.fire('Error', error.response.data.error, 'error');
                } else {
                    Swal.fire('Error', 'No se pudo procesar el pago de la quincena.', 'error');
                }
            });
        },
        cargarConfigNomina() {
            axios.get('/ajustes/nomina-config').then(response => {
                if (response.data) {
                    this.auxilioTransporteGlobal = parseFloat(response.data.auxilio_transporte) || 200000;
                    this.salarioMinimo = parseFloat(response.data.salario_minimo) || 1400000;
                    this.configNomina.auxilio_transporte = this.auxilioTransporteGlobal;
                    this.configNomina.salario_minimo = this.salarioMinimo;
                    this.auxilioTransporte = this.auxilioTransporteGlobal;
                }
            }).catch(e => {
                console.error("Error al cargar configNomina", e);
            });
        },
        abrirModalConfigNomina() {
            this.configNomina.auxilio_transporte = this.auxilioTransporteGlobal;
            this.configNomina.salario_minimo = this.salarioMinimo;
            this.configNomina.aplicar_a_empleados = true;
            this.modalConfigNomina = true;
        },
        guardarConfigNomina() {
            axios.post('/ajustes/nomina-config', this.configNomina).then(response => {
                this.auxilioTransporteGlobal = parseFloat(this.configNomina.auxilio_transporte);
                this.salarioMinimo = parseFloat(this.configNomina.salario_minimo);
                this.auxilioTransporte = this.auxilioTransporteGlobal;
                this.modalConfigNomina = false;
                let msg = response.data.message || 'Configuración guardada exitosamente.';
                if (response.data.empleados_actualizados > 0) {
                    msg += ` Se actualizó la ficha de ${response.data.empleados_actualizados} empleados.`;
                }
                Swal.fire('¡Éxito!', msg, 'success');
                this.listarEmpleados(1);
            }).catch(error => {
                console.error(error);
                Swal.fire('Error', 'No se pudo guardar la configuración de nómina.', 'error');
            });
        }
    },
    mounted() {
        this.cargarConfigNomina();
        this.cargarConfigHoras();
        this.listarEmpleados(1);
        this.cargarClasificaciones();
    }
}
</script>

<style scoped>
.form-label {
    font-weight: 600;
    font-size: 11px;
    margin-bottom: 2px;
    color: #4a5568;
}
.font-14 {
    font-size: 14px;
}
.modal.mostrar {
    display: block !important;
    opacity: 1 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    overflow-y: auto;
}

/* Screen Styles (Realistic paper sheet preview) */
.print-container {
    background-color: #e4e7ea;
    padding: 2rem 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
}
.print-sheet {
    width: 21.59cm; /* US Letter Width */
    min-height: 27.94cm; /* US Letter Height */
    padding: 1.5cm !important;
    margin: 0 auto;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    border: 1px solid #c8ced3;
    background: #fff;
    box-sizing: border-box;
}
</style>

<style>
/* Global Print Overrides (Non-scoped to match external layout wrappers and widgets) */
@media print {
    /* Set page format to Letter portrait and margin to 0 to strip default browser headers/footers */
    @page {
        size: letter portrait;
        margin: 0;
    }

    /* Hide any system layouts, UI widgets, floating components, or buttons */
    header, footer, nav, aside, .app-header, .sidebar, .breadcrumb, .footer, 
    .app-footer, .no-print-card, .no-print-tabs, .modal, .aside-menu, .btn, button,
    .floating-calculator, chat-component, .chat-widget, .chat-widget-container, 
    .swal2-container, .swal2-backdrop {
        display: none !important;
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
    }

    /* Reset core elements layout to white background, full width, static position */
    html, #app, .app, .app-body, .main, .container-fluid, .card, .card-body, .animated, [class*="animated"] {
        background: #fff !important;
        color: #000 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
        width: 100% !important;
        max-width: 100% !important;
        position: static !important;
        overflow: visible !important;
        height: auto !important;
        min-height: auto !important;
    }

    /* Reset body element explicitly to mimic margins and force auto height */
    body {
        background: #fff !important;
        color: #000 !important;
        margin: 0 !important;
        padding: 1.5cm !important; /* this acts as our page margins now */
        border: none !important;
        box-shadow: none !important;
        width: 100% !important;
        max-width: 100% !important;
        position: static !important;
        overflow: visible !important;
        height: auto !important;
        min-height: auto !important;
        box-sizing: border-box !important;
    }

    /* Hide print templates by default */
    #seccion-liquidacion, #seccion-horas-extras {
        display: none !important;
    }

    /* Selectively show based on print action class on body */
    body.print-liquidacion #seccion-liquidacion {
        display: block !important;
    }

    body.print-horas-extras #seccion-horas-extras {
        display: block !important;
    }

    /* Prevent page breaks before or inside the print container */
    .print-container, .print-sheet, #seccion-liquidacion, #seccion-horas-extras {
        page-break-before: avoid !important;
        break-before: avoid !important;
    }

    /* Reset page containers for full width print */
    .print-container {
        background-color: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
        width: 100% !important;
    }

    .print-sheet {
        width: 100% !important;
        min-height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        background: #fff !important;
    }

    /* Optimize margins and sizes inside the sheet to fit strictly on 1 page */
    #seccion-liquidacion {
        font-family: 'Arial', 'Helvetica', sans-serif !important;
        font-size: 10.5px !important;
        line-height: 1.25 !important;
    }

    #seccion-liquidacion .mb-4,
    #seccion-liquidacion .my-4 {
        margin-bottom: 10px !important;
    }

    #seccion-liquidacion .mb-3 {
        margin-bottom: 8px !important;
    }

    #seccion-liquidacion .mb-5 {
        margin-bottom: 12px !important;
    }

    #seccion-liquidacion .mt-4 {
        margin-top: 10px !important;
    }

    #seccion-liquidacion .pt-4 {
        padding-top: 8px !important;
    }

    #seccion-liquidacion h4 {
        font-size: 15px !important;
        margin-bottom: 2px !important;
    }

    #seccion-liquidacion h5 {
        font-size: 12px !important;
        margin-top: 5px !important;
        margin-bottom: 5px !important;
        padding-bottom: 2px !important;
    }

    #seccion-liquidacion h6 {
        font-size: 10.5px !important;
        margin-bottom: 4px !important;
    }

    #seccion-liquidacion p {
        margin-bottom: 2px !important;
    }

    #seccion-liquidacion .mx-auto {
        margin-top: 25px !important;
    }

    /* Clean corporate borders for tables */
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-bottom: 8px !important;
    }
    
    .table td, .table th {
        background-color: #fff !important;
        color: #000 !important;
        border: 1px solid #000 !important; /* solid black borders for print sharpness */
        padding: 4px 6px !important; /* compact cell padding */
        font-size: 10px !important;
    }
    
    .table th {
        font-weight: bold !important;
        background-color: #f2f2f2 !important;
    }

    tr {
        page-break-inside: avoid !important;
    }

    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
}
</style>
