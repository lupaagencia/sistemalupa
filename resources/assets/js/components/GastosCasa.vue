<template>
    <div class="gastos-casa-wrapper">
        <!-- Header Banner -->
        <div class="card border-0 shadow-sm rounded-lg mb-3 bg-gradient-purple text-white">
            <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="font-weight-bold mb-1 responsive-title">
                        <i class="fa fa-home mr-2"></i>Control de Gastos e Ingresos - Casa
                    </h3>
                    <p class="mb-0 text-white-50 small responsive-subtitle">
                        Registro de arreglos, servicios, cuentas compartidas y distribución de ingresos/donaciones.
                    </p>
                </div>
                <div class="mt-2 mt-md-0 d-flex flex-wrap gap-2 header-banner-actions">
                    <button class="btn btn-light text-primary font-weight-bold shadow-sm rounded-pill px-3 header-banner-btn" @click="abrirModalGasto()">
                        <i class="fa fa-plus-circle mr-1"></i> Registrar Gasto
                    </button>
                    <button class="btn btn-outline-light font-weight-bold rounded-pill px-3 header-banner-btn" @click="abrirModalIngreso('arriendo')">
                        <i class="fa fa-money mr-1"></i> Registrar Arriendo
                    </button>
                    <button class="btn btn-warning text-dark font-weight-bold shadow-sm rounded-pill px-3 header-banner-btn" @click="abrirModalIngreso('donacion_aporte')">
                        <i class="fa fa-gift mr-1"></i> Donación / Aporte
                    </button>
                </div>
            </div>
        </div>

        <!-- Global Person Selector / Filter Bar -->
        <div class="bg-white p-3 rounded-lg shadow-sm border mb-3 mb-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-2 w-100 w-md-auto">
                <label class="font-weight-bold mb-0 text-dark mr-2" style="font-size: 13px;">
                    <i class="fa fa-filter text-primary mr-1"></i> Filtrar por Integrante:
                </label>
                <select class="form-control form-control-sm font-weight-bold text-primary shadow-xs filter-persona-select" v-model="filtroPersona">
                    <option value="">-- Todos los Integrantes (General) --</option>
                    <option v-for="p in personas" :key="p.id" :value="p.id">👤 {{ p.nombre }}</option>
                </select>
            </div>
            <div v-if="personaSeleccionadaObj" class="mt-1 mt-sm-0 w-100 w-sm-auto text-right text-sm-left">
                <span class="badge badge-pill badge-primary px-3 py-2" style="font-size: 12px;">
                    <i class="fa fa-user mr-1"></i> {{ personaSeleccionadaObj.nombre }}
                </span>
                <button class="btn btn-xs btn-outline-secondary font-weight-bold ml-2 rounded-pill" @click="filtroPersona = ''">
                    <i class="fa fa-times"></i> Ver General
                </button>
            </div>
            <div v-else class="mt-1 mt-sm-0 text-muted small">
                <i class="fa fa-globe mr-1"></i> Métricas consolidadas
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-3 mb-md-4">
            <!-- Card 1: Total Gastos -->
            <div class="col-12 col-sm-6 col-xl-3 mb-2 mb-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-lg border-left-purple" :class="{'bg-light-purple': personaSeleccionadaObj}">
                    <div class="card-body py-3 px-3">
                        <div class="text-muted small font-weight-bold text-uppercase">
                            {{ personaSeleccionadaObj ? 'Total Gastos (' + personaSeleccionadaObj.nombre + ')' : 'Total Gastos Aportados' }}
                        </div>
                        <div class="h4 font-weight-bold text-dark my-1">${{ formatMoney(kpiTotalGastos) }}</div>
                        <div class="small text-muted" style="font-size: 11px;">
                            <i class="fa fa-info-circle"></i>
                            {{ personaSeleccionadaObj ? 'Comprobantes de ' + personaSeleccionadaObj.nombre : 'Suma de comprobantes' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Saldo Pendiente -->
            <div class="col-12 col-sm-6 col-xl-3 mb-2 mb-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-lg border-left-warning" :class="{'bg-light-warning': personaSeleccionadaObj}">
                    <div class="card-body py-3 px-3">
                        <div class="text-muted small font-weight-bold text-uppercase">
                            {{ personaSeleccionadaObj ? 'Saldo Pendiente (' + personaSeleccionadaObj.nombre + ')' : 'Saldo Por Reembolsar' }}
                        </div>
                        <div class="h4 font-weight-bold text-warning my-1">${{ formatMoney(kpiSaldoPendiente) }}</div>
                        <div class="small text-muted" style="font-size: 11px;">
                            <i class="fa fa-clock-o"></i>
                            {{ personaSeleccionadaObj ? 'Pendiente favor ' + personaSeleccionadaObj.nombre : 'Por saldar con ingresos' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Ingresos & Donaciones -->
            <div class="col-12 col-sm-4 col-xl-2 mb-2 mb-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-lg border-left-primary">
                    <div class="card-body py-3 px-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Ingresos Casa</div>
                        <div class="h5 font-weight-bold text-primary my-1">${{ formatMoney(kpis.total_ingresos_generales) }}</div>
                        <div class="small text-muted" style="font-size: 11px;">
                            <i class="fa fa-university"></i> Arriendos + Aportes
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Fondo Predial -->
            <div class="col-12 col-sm-4 col-xl-2 mb-2 mb-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-lg border-left-info">
                    <div class="card-body py-3 px-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Fondo Predial</div>
                        <div class="h5 font-weight-bold text-info my-1">${{ formatMoney(kpis.fondo_predial) }}</div>
                        <div class="small text-muted" style="font-size: 11px;">
                            <i class="fa fa-building-o"></i> Reservado predial
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 5: Fondo Arreglos -->
            <div class="col-12 col-sm-4 col-xl-2 mb-2 mb-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-lg border-left-success">
                    <div class="card-body py-3 px-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Fondo Arreglos</div>
                        <div class="h5 font-weight-bold text-success my-1">${{ formatMoney(kpis.fondo_arreglos) }}</div>
                        <div class="small text-muted" style="font-size: 11px;">
                            <i class="fa fa-wrench"></i> Reserva arreglos
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (Mobile Scrollable) -->
        <ul class="nav nav-pills mobile-scroll-tabs mb-3 mb-md-4 bg-white p-2 rounded shadow-sm border">
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-3 px-md-4" :class="{ active: tabActiva === 'gastos' }" href="#" @click.prevent="tabActiva = 'gastos'">
                    <i class="fa fa-list mr-1"></i> Gastos Registrados
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-3 px-md-4" :class="{ active: tabActiva === 'personas' }" href="#" @click.prevent="tabActiva = 'personas'">
                    <i class="fa fa-users mr-1"></i> Balance Integrantes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-3 px-md-4" :class="{ active: tabActiva === 'arriendos' }" href="#" @click.prevent="tabActiva = 'arriendos'">
                    <i class="fa fa-building mr-1"></i> Ingresos & Donaciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-3 px-md-4" :class="{ active: tabActiva === 'reservas' }" href="#" @click.prevent="tabActiva = 'reservas'">
                    <i class="fa fa-university mr-1"></i> Fondos Reserva
                </a>
            </li>
        </ul>

        <!-- TAB 1: GASTOS -->
        <div v-if="tabActiva === 'gastos'">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-stretch flex-column flex-md-row gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <h5 class="font-weight-bold mb-1 mb-md-0 text-dark mr-2 responsive-tab-title">
                            <i class="fa fa-file-text-o text-primary mr-1"></i>Historial de Gastos
                        </h5>
                        <button class="btn btn-sm btn-success font-weight-bold rounded-pill shadow-sm" @click="activarFilaRapida()">
                            <i class="fa fa-plus-circle mr-1"></i> + Anexar Renglón Rápido
                        </button>
                        <button class="btn btn-sm btn-outline-info font-weight-bold rounded-pill shadow-sm" @click="abrirModalImportar()">
                            <i class="fa fa-file-excel-o mr-1"></i> Importar Excel / CSV
                        </button>
                        <button v-if="gastosSeleccionados.length > 0" class="btn btn-sm btn-danger font-weight-bold rounded-pill shadow-sm animate__animated animate__fadeIn" @click="eliminarGastosMasivo()">
                            <i class="fa fa-trash mr-1"></i> Eliminar ({{ gastosSeleccionados.length }})
                        </button>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-md-0">
                        <select class="form-control form-control-sm flex-grow-1 flex-md-grow-0" v-model="filtroPersona" style="max-width: 100%; min-width: 140px;">
                            <option value="">Todas las Personas</option>
                            <option v-for="p in personas" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                        <input type="text" class="form-control form-control-sm flex-grow-1 flex-md-grow-0" placeholder="Buscar descripción..." v-model="filtroTexto" style="max-width: 100%; min-width: 150px;">
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- MODO TARJETAS PARA CELULARES (< 768px) -->
                    <div class="d-block d-md-none p-2 bg-light">
                        <div v-if="gastosFiltrados.length === 0 && !mostrandoFilaRapida" class="text-center py-4 text-muted bg-white rounded shadow-sm">
                            <i class="fa fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            No hay gastos registrados que coincidan con los filtros.
                        </div>

                        <!-- RENGLÓN RÁPIDO EN MODO TARJETA MÓVIL -->
                        <div v-if="mostrandoFilaRapida" class="card border border-warning shadow-sm rounded-lg mb-3 p-3 bg-yellow-light">
                            <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-bolt text-warning mr-1"></i> Anexar Gasto Rápido</h6>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Fecha</label>
                                <input type="date" class="form-control form-control-sm font-weight-bold" v-model="gastoRapidoForm.fecha">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Origen / Persona</label>
                                <select class="form-control form-control-sm font-weight-bold" v-model="gastoRapidoForm.origen_pago_select">
                                    <optgroup label="Integrantes (Con Reembolso)">
                                        <option v-for="p in personas" :key="'pm_'+p.id" :value="'persona_' + p.id">👤 {{ p.nombre }}</option>
                                    </optgroup>
                                    <optgroup label="Fondos Casa (Sin Reembolso)">
                                        <option value="fondo_arreglos">🔧 Fondo Arreglos</option>
                                        <option value="fondo_predial">🏛️ Fondo Predial</option>
                                        <option value="caja_arriendo">🏠 Caja Casa / Arriendo</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Categoría</label>
                                <select class="form-control form-control-sm" v-model="gastoRapidoForm.categoria_id">
                                    <option value="">General</option>
                                    <option v-for="c in categorias" :key="'cm_'+c.id" :value="c.id">{{ c.nombre }}</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Descripción</label>
                                <input type="text" class="form-control form-control-sm" v-model="gastoRapidoForm.descripcion" placeholder="Descripción del gasto...">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Valor ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm font-weight-bold" v-model="gastoRapidoForm.valor" placeholder="0.00">
                            </div>
                            <div class="d-flex justify-content-end gap-2 pt-2">
                                <button class="btn btn-sm btn-outline-secondary" @click="mostrandoFilaRapida = false">Cancelar</button>
                                <button class="btn btn-sm btn-success font-weight-bold" @click="guardarGastoRapido()"><i class="fa fa-save"></i> Guardar</button>
                            </div>
                        </div>

                        <!-- TARJETAS DE GASTOS MÓVILES -->
                        <div v-for="g in gastosFiltrados" :key="'mob_'+g.id" class="card border-0 shadow-sm rounded-lg mb-2 p-3 bg-white" :class="{'border-left-purple': gastosSeleccionados.includes(g.id)}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox" :value="g.id" v-model="gastosSeleccionados" class="mr-2" style="transform: scale(1.2);">
                                    <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ g.fecha }}</span>
                                </div>
                                <div>
                                    <span v-if="g.origen_pago === 'fondo_arreglos'" class="badge badge-success px-2 py-1">
                                        <i class="fa fa-wrench"></i> Fondo Arreglos
                                    </span>
                                    <span v-else-if="g.origen_pago === 'fondo_predial'" class="badge badge-info px-2 py-1">
                                        <i class="fa fa-building-o"></i> Fondo Predial
                                    </span>
                                    <span v-else-if="g.origen_pago === 'caja_arriendo'" class="badge badge-dark px-2 py-1">
                                        <i class="fa fa-home"></i> Caja Casa
                                    </span>
                                    <span v-else class="badge badge-pill badge-primary px-2 py-1">
                                        👤 {{ g.persona ? g.persona.nombre : 'S/I' }}
                                    </span>
                                </div>
                            </div>

                            <div class="font-weight-bold text-dark mb-1" style="font-size: 15px; word-break: break-word;">{{ g.descripcion }}</div>
                            <div class="text-muted small mb-2" v-if="g.notas"><i class="fa fa-comment-o"></i> {{ g.notas }}</div>

                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded mb-2" style="font-size: 12px;">
                                <div>
                                    <span class="text-muted d-block small">Categoría</span>
                                    <span class="font-weight-bold text-dark">{{ g.categoria ? g.categoria.nombre : 'General' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-muted d-block small">Valor Gasto</span>
                                    <span class="font-weight-bold text-dark" style="font-size: 14px;">${{ formatMoney(g.valor) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-muted d-block small">Saldo Pendiente</span>
                                    <span class="font-weight-bold" :class="g.saldo_pendiente > 0 ? 'text-danger' : 'text-success'" style="font-size: 14px;">${{ formatMoney(g.saldo_pendiente) }}</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-1 border-top flex-wrap gap-1">
                                <div class="d-flex align-items-center flex-wrap gap-1">
                                    <a v-if="g.soporte_path" :href="getStorageUrl(g.soporte_path)" target="_blank" class="btn btn-xs btn-outline-info rounded-pill">
                                        <i class="fa fa-paperclip"></i> Soporte
                                    </a>
                                    <a v-if="g.comprobante_path" :href="getStorageUrl(g.comprobante_path)" target="_blank" class="btn btn-xs btn-outline-success rounded-pill">
                                        <i class="fa fa-check-circle-o"></i> Pago
                                    </a>
                                    <span v-if="g.origen_pago && g.origen_pago !== 'persona'" class="badge badge-light border border-success text-success">
                                        <i class="fa fa-check-circle"></i> Casa
                                    </span>
                                    <span v-else class="badge" :class="badgeEstado(g.estado)">
                                        {{ textoEstado(g.estado) }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <template v-if="puedeEditarGasto(g)">
                                        <button class="btn btn-xs btn-outline-primary px-2" @click="editarGasto(g)">
                                            <i class="fa fa-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-xs btn-outline-danger px-2" @click="eliminarGasto(g.id)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </template>
                                    <span v-else class="badge badge-light text-muted border px-2 py-1" style="font-size: 11px;" title="Protegido: Registrado por otro usuario">
                                        <i class="fa fa-lock text-warning mr-1"></i> Protegido
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODO TABLA PARA DESKTOPS (>= 768px) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0" style="min-width: 1050px;">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="text-center" style="width: 40px;">
                                        <input type="checkbox" :checked="todosSeleccionados" @change="toggleSeleccionarTodos($event.target.checked)">
                                    </th>
                                    <th class="text-nowrap">Fecha</th>
                                    <th class="text-nowrap">Persona / Origen</th>
                                    <th class="text-nowrap">Categoría</th>
                                    <th>Descripción</th>
                                    <th class="text-right text-nowrap">Valor</th>
                                    <th class="text-right text-nowrap">Saldo Pendiente</th>
                                    <th class="text-center text-nowrap">Soporte</th>
                                    <th class="text-center text-nowrap">Comprobante Pago</th>
                                    <th class="text-center text-nowrap">Estado</th>
                                    <th class="text-center text-nowrap" style="min-width: 95px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- RENGLÓN RÁPIDO PARA AÑADIR DIRECTO EN LA TABLA -->
                                <tr v-if="mostrandoFilaRapida" class="bg-yellow-light border-warning">
                                    <td></td>
                                    <td style="min-width: 135px;">
                                        <input type="date" class="form-control form-control-sm font-weight-bold" v-model="gastoRapidoForm.fecha">
                                    </td>
                                    <td style="min-width: 160px;">
                                        <select class="form-control form-control-sm font-weight-bold" v-model="gastoRapidoForm.origen_pago_select">
                                            <optgroup label="Integrantes (Con Reembolso)">
                                                <option v-for="p in personas" :key="'p_'+p.id" :value="'persona_' + p.id">👤 {{ p.nombre }}</option>
                                            </optgroup>
                                            <optgroup label="Fondos Casa (Sin Reembolso)">
                                                <option value="fondo_arreglos">🔧 Fondo Arreglos</option>
                                                <option value="fondo_predial">🏛️ Fondo Predial</option>
                                                <option value="caja_arriendo">🏠 Caja Casa / Arriendo</option>
                                            </optgroup>
                                        </select>
                                    </td>
                                    <td style="min-width: 140px;">
                                        <select class="form-control form-control-sm" v-model="gastoRapidoForm.categoria_id">
                                            <option value="">General</option>
                                            <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                                        </select>
                                    </td>
                                    <td style="min-width: 210px;">
                                        <input type="text" class="form-control form-control-sm" v-model="gastoRapidoForm.descripcion" placeholder="Escriba descripción aquí...">
                                    </td>
                                    <td style="min-width: 120px;">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right font-weight-bold text-dark" v-model="gastoRapidoForm.valor" placeholder="0.00">
                                    </td>
                                    <td class="text-right font-weight-bold text-muted small" style="min-width: 90px;">-</td>
                                    <td class="text-center" style="min-width: 110px;">
                                        <input type="file" class="form-control-file form-control-sm" ref="soporteRapidoFile" accept="image/*,application/pdf" style="font-size: 11px;">
                                    </td>
                                    <td class="text-center" style="min-width: 110px;">
                                        <input type="file" class="form-control-file form-control-sm" ref="comprobanteRapidoFile" accept="image/*,application/pdf" style="font-size: 11px;">
                                    </td>
                                    <td class="text-center"><span class="badge badge-warning">Pendiente</span></td>
                                    <td class="text-center" style="min-width: 130px;">
                                        <button class="btn btn-sm btn-success mr-1 font-weight-bold" @click="guardarGastoRapido()" title="Guardar Renglón">
                                            <i class="fa fa-save"></i> Guardar
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" @click="mostrandoFilaRapida = false" title="Cancelar">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="gastosFiltrados.length === 0 && !mostrandoFilaRapida">
                                    <td colspan="11" class="text-center py-4 text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                        No hay gastos registrados que coincidan con los filtros.
                                    </td>
                                </tr>
                                <tr v-for="g in gastosFiltrados" :key="g.id" :class="{'table-active': gastosSeleccionados.includes(g.id)}">
                                    <td class="text-center">
                                        <input type="checkbox" :value="g.id" v-model="gastosSeleccionados">
                                    </td>
                                    <td class="font-weight-bold text-nowrap">{{ g.fecha }}</td>
                                    <td>
                                        <span v-if="g.origen_pago === 'fondo_arreglos'" class="badge badge-success px-2 py-1">
                                            <i class="fa fa-wrench"></i> Fondo Arreglos
                                        </span>
                                        <span v-else-if="g.origen_pago === 'fondo_predial'" class="badge badge-info px-2 py-1">
                                            <i class="fa fa-building-o"></i> Fondo Predial
                                        </span>
                                        <span v-else-if="g.origen_pago === 'caja_arriendo'" class="badge badge-dark px-2 py-1">
                                            <i class="fa fa-home"></i> Caja Casa / Arriendo
                                        </span>
                                        <span v-else class="badge badge-pill badge-primary px-3 py-1 font-weight-normal">
                                            {{ g.persona ? g.persona.nombre : 'S/I' }}
                                        </span>
                                    </td>
                                    <td class="small">{{ g.categoria ? g.categoria.nombre : 'General' }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ g.descripcion }}</div>
                                        <small class="text-muted" v-if="g.notas"><i class="fa fa-comment-o"></i> {{ g.notas }}</small>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark text-nowrap">${{ formatMoney(g.valor) }}</td>
                                    <td class="text-right font-weight-bold text-nowrap" :class="g.saldo_pendiente > 0 ? 'text-danger' : 'text-success'">
                                        ${{ formatMoney(g.saldo_pendiente) }}
                                    </td>
                                    <td class="text-center">
                                        <a v-if="g.soporte_path" :href="getStorageUrl(g.soporte_path)" target="_blank" class="btn btn-xs btn-outline-info rounded-pill text-nowrap" title="Ver Factura/Soporte">
                                            <i class="fa fa-paperclip"></i> Soporte
                                        </a>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                                    <td class="text-center">
                                        <a v-if="g.comprobante_path" :href="getStorageUrl(g.comprobante_path)" target="_blank" class="btn btn-xs btn-outline-success rounded-pill text-nowrap" title="Ver Comprobante de Pago">
                                            <i class="fa fa-check-circle-o"></i> Pago
                                        </a>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                                    <td class="text-center">
                                        <span v-if="g.origen_pago && g.origen_pago !== 'persona'" class="badge badge-light border border-success text-success">
                                            <i class="fa fa-check-circle"></i> Casa
                                        </span>
                                        <span v-else class="badge" :class="badgeEstado(g.estado)">
                                            {{ textoEstado(g.estado) }}
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap" style="min-width: 95px;">
                                        <template v-if="puedeEditarGasto(g)">
                                            <button class="btn btn-xs btn-outline-primary px-2 mr-1" @click="editarGasto(g)" title="Editar Gasto">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button class="btn btn-xs btn-outline-danger px-2" @click="eliminarGasto(g.id)" title="Eliminar Gasto">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </template>
                                        <span v-else class="badge badge-light text-muted border px-2 py-1" style="font-size: 11px;" title="Protegido: Registrado por otro usuario">
                                            <i class="fa fa-lock text-warning mr-1"></i> Protegido
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: BALANCE POR PERSONA -->
        <div v-if="tabActiva === 'personas'">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="font-weight-bold mb-0 responsive-tab-title">Resumen de Cuentas por Integrante</h5>
                <button class="btn btn-sm btn-outline-primary font-weight-bold rounded-pill" @click="abrirModalPersona()">
                    <i class="fa fa-user-plus mr-1"></i> Agregar Integrante
                </button>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6 col-md-4 mb-3 mb-md-4" v-for="p in personas" :key="p.id">
                    <div class="card border-0 shadow-sm rounded-lg h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 1.05rem;">
                                <i class="fa fa-user-circle text-primary mr-2"></i>{{ p.nombre }}
                            </h5>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-light border text-muted mr-2">Aporte {{ p.porcentaje }}%</span>
                                <button class="btn btn-xs btn-outline-danger" @click="eliminarPersona(p)" title="Eliminar Integrante">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Aportado:</span>
                                <span class="font-weight-bold text-dark">${{ formatMoney(p.total_aportado) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Reembolsado:</span>
                                <span class="font-weight-bold text-success">${{ formatMoney(p.total_reembolsado) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <span class="font-weight-bold text-dark">Saldo Pendiente:</span>
                                <span class="h5 font-weight-bold mb-0 text-danger">${{ formatMoney(p.saldo_pendiente) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: INGRESOS POR ARRIENDO Y DONACIONES -->
        <div v-if="tabActiva === 'arriendos'">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="font-weight-bold mb-0 text-dark responsive-tab-title">
                        <i class="fa fa-money text-success mr-2"></i>Historial de Ingresos y Donaciones
                    </h5>
                    <div class="mt-2 mt-sm-0 d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-success font-weight-bold rounded-pill shadow-sm" @click="abrirModalIngreso('arriendo')">
                            <i class="fa fa-plus-circle mr-1"></i> Registrar Arriendo
                        </button>
                        <button class="btn btn-sm btn-warning text-dark font-weight-bold rounded-pill shadow-sm" @click="abrirModalIngreso('donacion_aporte')">
                            <i class="fa fa-gift mr-1"></i> Registrar Donación / Aporte
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- MODO TARJETAS PARA CELULARES (< 768px) -->
                    <div class="d-block d-md-none p-2 bg-light">
                        <div v-if="ingresos.length === 0" class="text-center py-4 text-muted bg-white rounded shadow-sm">
                            <i class="fa fa-info-circle mb-1 d-block opacity-50"></i>
                            Aún no se han registrado ingresos por arriendo ni donaciones.
                        </div>
                        <div v-for="ing in ingresos" :key="'mob_ing_'+ing.id" class="card border-0 shadow-sm rounded-lg mb-2 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span v-if="ing.tipo_ingreso === 'donacion_aporte'" class="badge badge-warning text-dark px-2 py-1">
                                    <i class="fa fa-gift"></i> Donación / Aporte
                                </span>
                                <span v-else class="badge badge-success px-2 py-1">
                                    <i class="fa fa-home"></i> Arriendo
                                </span>
                                <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ ing.fecha }}</span>
                            </div>
                            <div class="font-weight-bold text-dark mb-1" style="font-size: 15px;">{{ ing.descripcion }}</div>
                            <div class="text-muted small mb-2"><i class="fa fa-user mr-1"></i> Inquilino/Donante: <strong>{{ ing.inquilino_nombre || 'N/A' }}</strong></div>
                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded mb-2">
                                <div>
                                    <span class="text-muted small d-block">Periodo / Ref</span>
                                    <span class="badge badge-secondary">{{ ing.periodo_mes || 'S/R' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-muted small d-block">Valor Total Ingresado</span>
                                    <span class="h6 font-weight-bold text-success mb-0">${{ formatMoney(ing.valor_total) }}</span>
                                </div>
                            </div>
                            <div class="border-top pt-2" v-if="ing.distribuciones && ing.distribuciones.length > 0">
                                <span class="text-muted small font-weight-bold d-block mb-1">Distribución Realizada:</span>
                                <ul class="list-unstyled mb-0 small pl-2">
                                    <li v-for="d in ing.distribuciones" :key="'mob_d_'+d.id" class="text-muted mb-1">
                                        <i class="fa fa-caret-right text-primary"></i>
                                        <strong v-if="d.tipo_destino === 'reembolso_persona'">Reembolso {{ d.persona ? d.persona.nombre : '' }}:</strong>
                                        <strong v-else-if="d.tipo_destino === 'reserva_predial'">Fondo Predial:</strong>
                                        <strong v-else-if="d.tipo_destino === 'reserva_arreglos'">Fondo Arreglos:</strong>
                                        <strong v-else>Reserva Otra:</strong>
                                        ${{ formatMoney(d.valor) }}
                                    </li>
                                </ul>
                            </div>
                            <div class="pt-2 text-right border-top mt-2" v-if="ing.comprobante_path">
                                <a :href="getStorageUrl(ing.comprobante_path)" target="_blank" class="btn btn-xs btn-outline-info rounded-pill">
                                    <i class="fa fa-file-text-o"></i> Recibo / Comprobante
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- MODO TABLA PARA DESKTOPS (>= 768px) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0" style="min-width: 850px;">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="text-nowrap">Tipo</th>
                                    <th class="text-nowrap">Fecha</th>
                                    <th class="text-nowrap">Periodo / Ref</th>
                                    <th>Inquilino / Donante</th>
                                    <th>Descripción</th>
                                    <th class="text-right text-nowrap">Valor Total</th>
                                    <th class="text-center text-nowrap">Comprobante</th>
                                    <th>Distribución Realizada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="ingresos.length === 0">
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fa fa-info-circle mb-1 d-block opacity-50"></i>
                                        Aún no se han registrado ingresos por arriendo ni donaciones.
                                    </td>
                                </tr>
                                <tr v-for="ing in ingresos" :key="ing.id">
                                    <td>
                                        <span v-if="ing.tipo_ingreso === 'donacion_aporte'" class="badge badge-warning text-dark px-2 py-1">
                                            <i class="fa fa-gift"></i> Donación
                                        </span>
                                        <span v-else class="badge badge-success px-2 py-1">
                                            <i class="fa fa-home"></i> Arriendo
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-nowrap">{{ ing.fecha }}</td>
                                    <td><span class="badge badge-secondary">{{ ing.periodo_mes || 'S/R' }}</span></td>
                                    <td class="font-weight-bold text-dark">{{ ing.inquilino_nombre || 'N/A' }}</td>
                                    <td>{{ ing.descripcion }}</td>
                                    <td class="text-right font-weight-bold text-success text-nowrap">${{ formatMoney(ing.valor_total) }}</td>
                                    <td class="text-center">
                                        <a v-if="ing.comprobante_path" :href="getStorageUrl(ing.comprobante_path)" target="_blank" class="btn btn-xs btn-outline-info rounded-pill text-nowrap">
                                            <i class="fa fa-file-text-o"></i> Recibo
                                        </a>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                                    <td>
                                        <ul class="list-unstyled mb-0 small">
                                            <li v-for="d in ing.distribuciones" :key="d.id" class="text-muted">
                                                <i class="fa fa-caret-right text-primary"></i>
                                                <strong v-if="d.tipo_destino === 'reembolso_persona'">Reembolso {{ d.persona ? d.persona.nombre : '' }}:</strong>
                                                <strong v-else-if="d.tipo_destino === 'reserva_predial'">Fondo Predial:</strong>
                                                <strong v-else-if="d.tipo_destino === 'reserva_arreglos'">Fondo Arreglos:</strong>
                                                <strong v-else>Reserva Otra:</strong>
                                                ${{ formatMoney(d.valor) }}
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: FONDOS DE RESERVA -->
        <div v-if="tabActiva === 'reservas'">
            <div class="row mb-3 mb-md-4">
                <div class="col-12 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg bg-light-blue border-left-info">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 11px;">Acumulado Fondo Predial</h6>
                                <h3 class="font-weight-bold text-info mb-0">${{ formatMoney(kpis.fondo_predial) }}</h3>
                            </div>
                            <button class="btn btn-info btn-sm rounded-pill font-weight-bold text-white mt-1 mt-sm-0" @click="abrirModalReservaMovimiento('predial')">
                                <i class="fa fa-minus-circle mr-1"></i> Registrar Pago Predial
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm rounded-lg bg-light-green border-left-success">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 11px;">Acumulado Fondo Arreglos</h6>
                                <h3 class="font-weight-bold text-success mb-0">${{ formatMoney(kpis.fondo_arreglos) }}</h3>
                            </div>
                            <button class="btn btn-success btn-sm rounded-pill font-weight-bold text-white mt-1 mt-sm-0" @click="abrirModalReservaMovimiento('arreglos')">
                                <i class="fa fa-minus-circle mr-1"></i> Usar Fondo Arreglos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3">
                    <h5 class="font-weight-bold mb-0 text-dark">
                        <i class="fa fa-history text-info mr-2"></i>Movimientos de las Reservas (Ingresos y Egresos)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <!-- MODO TARJETAS PARA CELULARES (< 768px) -->
                    <div class="d-block d-md-none p-2 bg-light">
                        <div v-if="reservas.length === 0" class="text-center py-4 text-muted bg-white rounded shadow-sm">
                            No hay movimientos registrados en las reservas.
                        </div>
                        <div v-for="r in reservas" :key="'mob_res_'+r.id" class="card border-0 shadow-sm rounded-lg mb-2 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge-pill" :class="r.tipo_reserva === 'predial' ? 'badge-info' : 'badge-success'">
                                    Fondo {{ r.tipo_reserva | capitalize }}
                                </span>
                                <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ r.fecha }}</span>
                            </div>
                            <div class="font-weight-bold text-dark mb-2" style="font-size: 14px;">{{ r.concepto }}</div>
                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                                <div>
                                    <span class="badge" :class="r.tipo_movimiento === 'ingreso' ? 'badge-light text-success border border-success' : 'badge-light text-danger border border-danger'">
                                        <i class="fa" :class="r.tipo_movimiento === 'ingreso' ? 'fa-arrow-down' : 'fa-arrow-up'"></i>
                                        {{ r.tipo_movimiento === 'ingreso' ? 'Aporte (Ingreso)' : 'Gasto (Egreso)' }}
                                    </span>
                                </div>
                                <div class="text-right font-weight-bold h6 mb-0" :class="r.tipo_movimiento === 'ingreso' ? 'text-success' : 'text-danger'">
                                    {{ r.tipo_movimiento === 'ingreso' ? '+' : '-' }}${{ formatMoney(r.valor) }}
                                </div>
                            </div>
                            <div class="pt-2 text-right border-top mt-2" v-if="r.soporte_path">
                                <a :href="getStorageUrl(r.soporte_path)" target="_blank" class="btn btn-xs btn-outline-info rounded-pill">
                                    <i class="fa fa-file"></i> Ver Soporte
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- MODO TABLA PARA DESKTOPS (>= 768px) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0" style="min-width: 750px;">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="text-nowrap">Fecha</th>
                                    <th class="text-nowrap">Reserva</th>
                                    <th class="text-nowrap">Tipo Movimiento</th>
                                    <th>Concepto</th>
                                    <th class="text-right text-nowrap">Monto</th>
                                    <th class="text-center text-nowrap">Soporte</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="reservas.length === 0">
                                    <td colspan="6" class="text-center py-4 text-muted">No hay movimientos registrados en las reservas.</td>
                                </tr>
                                <tr v-for="r in reservas" :key="r.id">
                                    <td class="font-weight-bold text-nowrap">{{ r.fecha }}</td>
                                    <td>
                                        <span class="badge badge-pill" :class="r.tipo_reserva === 'predial' ? 'badge-info' : 'badge-success'">
                                            Fondo {{ r.tipo_reserva | capitalize }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="r.tipo_movimiento === 'ingreso' ? 'badge-light text-success border border-success' : 'badge-light text-danger border border-danger'">
                                            <i class="fa" :class="r.tipo_movimiento === 'ingreso' ? 'fa-arrow-down' : 'fa-arrow-up'"></i>
                                            {{ r.tipo_movimiento === 'ingreso' ? 'Aporte (Ingreso)' : 'Gasto (Egreso)' }}
                                        </span>
                                    </td>
                                    <td>{{ r.concepto }}</td>
                                    <td class="text-right font-weight-bold text-nowrap" :class="r.tipo_movimiento === 'ingreso' ? 'text-success' : 'text-danger'">
                                        {{ r.tipo_movimiento === 'ingreso' ? '+' : '-' }}${{ formatMoney(r.valor) }}
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a v-if="r.soporte_path" :href="getStorageUrl(r.soporte_path)" target="_blank" class="btn btn-xs btn-outline-info rounded-pill">
                                            <i class="fa fa-file"></i> Soporte
                                        </a>
                                        <span v-else class="text-muted small">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: REGISTRAR / EDITAR GASTO -->
        <div class="modal fade" id="modalGasto" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg rounded-lg">
                    <div class="modal-header bg-gradient-purple text-white">
                        <h5 class="modal-header-title font-weight-bold mb-0">
                            <i class="fa fa-plus-circle mr-2"></i>{{ gastoForm.id ? 'Editar Gasto' : 'Registrar Nuevo Gasto' }}
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalGasto()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <form @submit.prevent="guardarGasto()">
                            <div class="form-group">
                                <label class="font-weight-bold">¿De dónde salió el dinero para pagar este gasto? <span class="text-danger">*</span></label>
                                <select class="form-control font-weight-bold" v-model="gastoForm.origen_pago" required>
                                    <option value="persona">👤 Bolsillo de un integrante (Genera reembolso pendiente)</option>
                                    <option value="fondo_arreglos">🔧 Fondo de Arreglos / Mantenimiento (Sin reembolso)</option>
                                    <option value="fondo_predial">🏛️ Fondo Predial (Sin reembolso)</option>
                                    <option value="caja_arriendo">🏠 Dinero de Arriendo / Caja Casa (Sin reembolso)</option>
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6" v-if="gastoForm.origen_pago === 'persona'">
                                    <label class="font-weight-bold">¿Quién realizó el gasto? <span class="text-danger">*</span></label>
                                    <select class="form-control" v-model="gastoForm.persona_id" :required="gastoForm.origen_pago === 'persona'" :disabled="!userInfo.is_admin && userInfo.persona_id">
                                        <option value="">Seleccione Persona...</option>
                                        <option v-for="p in personas" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                                    </select>
                                    <small class="text-info font-weight-bold d-block mt-1" v-if="!userInfo.is_admin && userInfo.persona_id">
                                        <i class="fa fa-lock mr-1"></i> Asignado automáticamente a su usuario
                                    </small>
                                </div>
                                <div class="form-group col-md-6" v-else>
                                    <label class="font-weight-bold">Pago realizado por</label>
                                    <div class="form-control bg-light text-success font-weight-bold">
                                        <i class="fa fa-university mr-1"></i> Cubierto con Dinero / Fondo Casa
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold">Fecha del Gasto <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" v-model="gastoForm.fecha" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold">Categoría / Tipo de Gasto</label>
                                    <select class="form-control" v-model="gastoForm.categoria_id">
                                        <option value="">Sin Categoría Especificada</option>
                                        <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold">Valor (Monto COP) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" step="0.01" min="0" class="form-control font-weight-bold text-dark" v-model="gastoForm.valor" required placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Descripción del Gasto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="gastoForm.descripcion" placeholder="Ej: Compra de pintura, pago de recibo de agua, arreglo de tuberia..." required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold">Documento Soporte (Factura / Cotización)</label>
                                    <input type="file" class="form-control-file" ref="soporteFile" accept="image/*,application/pdf">
                                    <small class="text-muted" v-if="gastoForm.soporte_path">Archivo actual cargado: <a :href="getStorageUrl(gastoForm.soporte_path)" target="_blank">Ver Archivo</a></small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold">Comprobante de Pago (Transferencia / Recibo)</label>
                                    <input type="file" class="form-control-file" ref="comprobanteFile" accept="image/*,application/pdf">
                                    <small class="text-muted" v-if="gastoForm.comprobante_path">Archivo actual cargado: <a :href="getStorageUrl(gastoForm.comprobante_path)" target="_blank">Ver Archivo</a></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Notas Adicionales</label>
                                <textarea class="form-control" rows="2" v-model="gastoForm.notas" placeholder="Observaciones extras sobre el pago o arreglo..."></textarea>
                            </div>

                            <div class="text-right pt-2">
                                <button type="button" class="btn btn-secondary mr-2" @click="cerrarModalGasto()">Cancelar</button>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4" :disabled="loading">
                                    <i class="fa fa-save mr-1"></i> Guardar Gasto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: REGISTRAR INGRESO ARRIENDO & DISTRIBUCIÓN -->
        <div class="modal fade" id="modalIngreso" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg rounded-lg">
                    <div class="modal-header text-white" :class="ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'bg-warning text-dark' : 'bg-success'">
                        <h5 class="modal-header-title font-weight-bold mb-0">
                            <i class="fa" :class="ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'fa-gift' : 'fa-money'"></i>
                            {{ ingresoForm.tipo_ingreso === 'donacion_aporte' ? ' Registrar Donación / Aporte Casa' : ' Registrar Pago de Arriendo' }}
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalIngreso()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <form @submit.prevent="guardarIngreso()">
                            <!-- Selector Tipo de Ingreso -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">Origen o Tipo de Dinero Recibido <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <div class="p-3 border rounded-lg cursor-pointer d-flex align-items-center"
                                             :class="ingresoForm.tipo_ingreso === 'arriendo' ? 'border-success bg-light-green' : 'bg-light'"
                                             @click="ingresoForm.tipo_ingreso = 'arriendo'">
                                            <input type="radio" name="tipo_ingreso" value="arriendo" v-model="ingresoForm.tipo_ingreso" class="mr-2">
                                            <div>
                                                <strong class="d-block text-success"><i class="fa fa-home mr-1"></i> Canon de Arriendo</strong>
                                                <small class="text-muted">Pago mensual por arriendo de inquilino</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="p-3 border rounded-lg cursor-pointer d-flex align-items-center"
                                             :class="ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'border-warning bg-yellow-light' : 'bg-light'"
                                             @click="ingresoForm.tipo_ingreso = 'donacion_aporte'">
                                            <input type="radio" name="tipo_ingreso" value="donacion_aporte" v-model="ingresoForm.tipo_ingreso" class="mr-2">
                                            <div>
                                                <strong class="d-block text-dark"><i class="fa fa-gift text-warning mr-1"></i> Donación / Aporte Casa (Papá)</strong>
                                                <small class="text-muted">Dinero regalado o aportado sin reembolso</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label class="font-weight-bold">Fecha de Recepción <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" v-model="ingresoForm.fecha" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="font-weight-bold">{{ ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'Referencia / Concepto' : 'Periodo / Mes' }}</label>
                                    <input type="text" class="form-control" v-model="ingresoForm.periodo_mes" :placeholder="ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'Ej: Apoyo Arreglos 2026' : 'Ej: Octubre 2026'">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="font-weight-bold">Valor Recibido ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control font-weight-bold text-success" v-model="ingresoForm.valor_total" required placeholder="0.00">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold">{{ ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'Donante / Origen' : 'Nombre Inquilino' }}</label>
                                    <input type="text" class="form-control" v-model="ingresoForm.inquilino_nombre" :placeholder="ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'Ej: Donación Papá' : 'Nombre de quien paga el arriendo'">
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold">Comprobante o Recibo</label>
                                    <input type="file" class="form-control-file" ref="comprobanteIngresoFile" accept="image/*,application/pdf">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Descripción del Ingreso <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="ingresoForm.descripcion" :placeholder="ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'Ej: Dinero regalado por el papá para saldar arreglos o cuentas...' : 'Ej: Pago mensualidad canon de arrendamiento casa...'" required>
                            </div>

                            <hr class="my-4">
                            <h6 class="font-weight-bold text-dark mb-2">
                                <i class="fa fa-calculator text-primary mr-1"></i>Distribución del Dinero Recibido
                            </h6>
                            <p class="small text-muted mb-3">
                                {{ ingresoForm.tipo_ingreso === 'donacion_aporte' ? 'Asigne este dinero de la donación para abonar/reembolsar gastos acumulados a Julián, Óscar o Diego, o guardarlo en fondos de reserva.' : 'Asigne este ingreso del arriendo para saldar reembolsos o fondos de reserva.' }}
                            </p>

                            <div class="card bg-light p-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold">Monto Total Ingresado:</span>
                                    <span class="h6 font-weight-bold text-success mb-0">${{ formatMoney(ingresoForm.valor_total || 0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Total Asignado en Distribución:</span>
                                    <span class="h6 font-weight-bold mb-0" :class="totalDistribuidoCalculado > ingresoForm.valor_total ? 'text-danger' : 'text-primary'">
                                        ${{ formatMoney(totalDistribuidoCalculado) }}
                                    </span>
                                </div>
                            </div>

                            <div v-for="(dist, index) in distribucionesList" :key="index" class="form-row align-items-center bg-white p-2 border rounded mb-2">
                                <div class="col-12 col-md-4 mb-2 mb-md-0">
                                    <label class="small font-weight-bold mb-1">Destino</label>
                                    <select class="form-control form-control-sm" v-model="dist.tipo_destino">
                                        <option value="reembolso_persona">Reembolso a Persona</option>
                                        <option value="reserva_predial">Ahorro Fondo Predial</option>
                                        <option value="reserva_arreglos">Ahorro Fondo Arreglos</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 mb-2 mb-md-0" v-if="dist.tipo_destino === 'reembolso_persona'">
                                    <label class="small font-weight-bold mb-1">Persona a Reembolsar</label>
                                    <select class="form-control form-control-sm" v-model="dist.persona_id">
                                        <option value="">Seleccione Persona...</option>
                                        <option v-for="p in personas" :key="p.id" :value="p.id">{{ p.nombre }} (Sald: ${{ formatMoney(p.saldo_pendiente) }})</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 mb-2 mb-md-0" v-else>
                                    <label class="small font-weight-bold mb-1">Observación</label>
                                    <input type="text" class="form-control form-control-sm" v-model="dist.observaciones" placeholder="Detalle reserva...">
                                </div>
                                <div class="col-8 col-md-3 mb-2 mb-md-0">
                                    <label class="small font-weight-bold mb-1">Monto Asignado ($)</label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm font-weight-bold" v-model="dist.valor">
                                </div>
                                <div class="col-4 col-md-2 text-right pt-md-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" @click="eliminarFilaDistribucion(index)">
                                        <i class="fa fa-times"></i> <span class="d-inline d-md-none"> Quitar</span>
                                    </button>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" @click="agregarFilaDistribucion()">
                                <i class="fa fa-plus-circle"></i> Agregar Destino / Reserva
                            </button>

                            <div class="text-right pt-3 border-top mt-4">
                                <button type="button" class="btn btn-secondary mr-2" @click="cerrarModalIngreso()">Cancelar</button>
                                <button type="submit" class="btn btn-success font-weight-bold px-4" :disabled="loading">
                                    <i class="fa fa-save mr-1"></i> Guardar e Ingresar Arriendo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: INTEGRANTE / PERSONA -->
        <div class="modal fade" id="modalPersona" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-header-title font-weight-bold mb-0"><i class="fa fa-user-plus mr-2"></i>Agregar Integrante de la Casa</h5>
                        <button type="button" class="close text-white" @click="cerrarModalPersona()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <form @submit.prevent="guardarPersona()">
                            <div class="form-group">
                                <label class="font-weight-bold">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="personaForm.nombre" required placeholder="Ej: Julián, Óscar, Diego...">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Porcentaje de Participación / Aporte (%)</label>
                                <input type="number" step="0.01" class="form-control" v-model="personaForm.porcentaje_participacion" placeholder="33.33">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Teléfono / Celular</label>
                                <input type="text" class="form-control" v-model="personaForm.telefono">
                            </div>
                            <div class="form-group" v-if="userInfo.is_admin">
                                <label class="font-weight-bold">Usuario del Sistema Vinculado (Seguridad)</label>
                                <select class="form-control" v-model="personaForm.user_id">
                                    <option value="">-- Sin usuario vinculado --</option>
                                    <option v-for="u in systemUsers" :key="u.id" :value="u.id">👤 {{ u.usuario }} (ID: {{ u.id }})</option>
                                </select>
                                <small class="text-muted">Vincule un usuario para restringir las modificaciones únicamente a los datos propios de esa persona.</small>
                            </div>
                            <div class="text-right pt-2">
                                <button type="button" class="btn btn-secondary mr-2" @click="cerrarModalPersona()">Cancelar</button>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4">Guardar Persona</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: MOVIMIENTO DE RESERVA (EGRESO / USO DE FONDOS) -->
        <div class="modal fade" id="modalReserva" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg rounded-lg">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-header-title font-weight-bold mb-0">
                            <i class="fa fa-university mr-2"></i>Registrar Uso de Fondo ({{ reservaForm.tipo_reserva | capitalize }})
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalReserva()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <form @submit.prevent="guardarMovimientoReserva()">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha del Pago / Movimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" v-model="reservaForm.fecha" required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Concepto / Descripción del Pago <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="reservaForm.concepto" placeholder="Ej: Pago de impuesto predial año 2026..." required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Monto a Retirar / Descontar ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control font-weight-bold text-danger" v-model="reservaForm.valor" required placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Soporte o Recibo de Pago</label>
                                <input type="file" class="form-control-file" ref="soporteReservaFile" accept="image/*,application/pdf">
                            </div>
                            <div class="text-right pt-2">
                                <button type="button" class="btn btn-secondary mr-2" @click="cerrarModalReserva()">Cancelar</button>
                                <button type="submit" class="btn btn-info font-weight-bold text-white px-4">Guardar Registro</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: IMPORTAR LISTADO DE GASTOS DESDE EXCEL / CSV -->
        <div class="modal fade" id="modalImportar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg rounded-lg">
                    <div class="modal-header bg-gradient-purple text-white">
                        <h5 class="modal-header-title font-weight-bold mb-0">
                            <i class="fa fa-file-excel-o mr-2"></i>Importar Listado de Gastos (Excel / CSV)
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalImportar()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 shadow-sm rounded-lg mb-3">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fa fa-info-circle fa-lg mr-2"></i>
                                <strong>Plantilla de Importación recomendada</strong>
                            </div>
                            <p class="small mb-2">Para una importación correcta, descargue la plantilla o use las columnas en este orden: <code>Fecha</code>, <code>Persona</code>, <code>Categoria</code>, <code>Descripcion</code>, <code>Valor</code>, <code>Notas</code>.</p>
                            <a href="/gastos/importar/plantilla" target="_blank" class="btn btn-sm btn-outline-info font-weight-bold bg-white text-info rounded-pill">
                                <i class="fa fa-download mr-1"></i> Descargar Plantilla Modelo (CSV)
                            </a>
                        </div>

                        <form @submit.prevent="procesarImportacion()">
                            <div class="form-group">
                                <label class="font-weight-bold">Seleccionar Archivo (.csv, .xlsx, .xls) <span class="text-danger">*</span></label>
                                <input type="file" class="form-control-file" ref="archivoImportacion" accept=".csv, .xlsx, .xls" required>
                            </div>

                            <div class="text-right pt-3 border-top">
                                <button type="button" class="btn btn-secondary mr-2" @click="cerrarModalImportar()">Cancelar</button>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4" :disabled="loading">
                                    <i class="fa fa-upload mr-1"></i> Procesar e Importar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['user'],
    data() {
        return {
            loading: false,
            tabActiva: 'gastos',
            personas: [],
            systemUsers: [],
            userInfo: {
                logged_in: false,
                username: null,
                user_id: null,
                persona_id: null,
                is_admin: false
            },
            categorias: [],
            gastos: [],
            gastosSeleccionados: [],
            ingresos: [],
            reservas: [],
            kpis: {
                total_gastos: 0,
                total_pendiente: 0,
                total_arriendos: 0,
                total_donaciones: 0,
                total_ingresos_generales: 0,
                fondo_predial: 0,
                fondo_arreglos: 0
            },
            filtroPersona: '',
            filtroTexto: '',
            mostrandoFilaRapida: false,
            gastoRapidoForm: {
                origen_pago_select: '',
                categoria_id: '',
                fecha: new Date().toISOString().slice(0, 10),
                descripcion: '',
                valor: ''
            },
            gastoForm: {
                id: null,
                origen_pago: 'persona',
                persona_id: '',
                categoria_id: '',
                fecha: new Date().toISOString().slice(0, 10),
                descripcion: '',
                valor: '',
                notas: '',
                soporte_path: null,
                comprobante_path: null
            },
            ingresoForm: {
                tipo_ingreso: 'arriendo',
                fecha: new Date().toISOString().slice(0, 10),
                periodo_mes: '',
                inquilino_nombre: '',
                descripcion: '',
                valor_total: '',
                notas: ''
            },
            distribucionesList: [],
            personaForm: {
                nombre: '',
                porcentaje_participacion: 33.33,
                telefono: ''
            },
            reservaForm: {
                tipo_reserva: 'predial',
                tipo_movimiento: 'egreso',
                fecha: new Date().toISOString().slice(0, 10),
                concepto: '',
                valor: ''
            }
        };
    },
    filters: {
        capitalize(value) {
            if (!value) return '';
            return value.toString().charAt(0).toUpperCase() + value.slice(1);
        }
    },
    computed: {
        gastosFiltrados() {
            return this.gastos.filter(g => {
                let matchPersona = !this.filtroPersona || g.persona_id == this.filtroPersona;
                let matchTexto = !this.filtroTexto || g.descripcion.toLowerCase().includes(this.filtroTexto.toLowerCase());
                return matchPersona && matchTexto;
            });
        },
        todosSeleccionados() {
            if (!this.gastosFiltrados || this.gastosFiltrados.length === 0) return false;
            return this.gastosFiltrados.every(g => this.gastosSeleccionados.includes(g.id));
        },
        totalDistribuidoCalculado() {
            return this.distribucionesList.reduce((acc, item) => acc + (parseFloat(item.valor) || 0), 0);
        },
        kpiTotalGastos() {
            if (this.filtroPersona) {
                return this.gastos
                    .filter(g => g.persona_id == this.filtroPersona && g.estado !== 'anulado')
                    .reduce((sum, g) => sum + parseFloat(g.valor || 0), 0);
            }
            return this.kpis.total_gastos || 0;
        },
        kpiSaldoPendiente() {
            if (this.filtroPersona) {
                return this.gastos
                    .filter(g => g.persona_id == this.filtroPersona && g.estado !== 'anulado')
                    .reduce((sum, g) => sum + parseFloat(g.saldo_pendiente || 0), 0);
            }
            return this.kpis.total_pendiente || 0;
        },
        personaSeleccionadaObj() {
            if (!this.filtroPersona) return null;
            return this.personas.find(p => p.id == this.filtroPersona);
        }
    },
    mounted() {
        this.cargarDatos();
    },
    methods: {
        cargarDatos() {
            this.loading = true;
            axios.get('/gastos/data')
                .then(res => {
                    this.personas = res.data.personas;
                    this.categorias = res.data.categorias;
                    this.gastos = res.data.gastos;
                    this.ingresos = res.data.ingresos;
                    this.reservas = res.data.reservas;
                    this.kpis = res.data.kpis;
                    if (res.data.user_info) this.userInfo = res.data.user_info;
                    if (res.data.system_users) this.systemUsers = res.data.system_users;
                    let idsExistentes = this.gastos.map(g => g.id);
                    this.gastosSeleccionados = this.gastosSeleccionados.filter(id => idsExistentes.includes(id));
                    this.loading = false;
                })
                .catch(err => {
                    this.loading = false;
                    console.error('Error al cargar datos de gastos casa:', err);
                });
        },
        formatMoney(val) {
            if (val === null || val === undefined) return '0';
            return parseFloat(val).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },
        getStorageUrl(path) {
            if (!path) return '#';
            return window.APP_URL ? window.APP_URL + '/' + path : '/' + path;
        },
        badgeEstado(estado) {
            switch(estado) {
                case 'pendiente': return 'badge-warning text-dark';
                case 'reembolsado_parcial': return 'badge-info';
                case 'reembolsado_total': return 'badge-success';
                case 'anulado': return 'badge-secondary';
                default: return 'badge-light';
            }
        },
        textoEstado(estado) {
            switch(estado) {
                case 'pendiente': return 'Pendiente';
                case 'reembolsado_parcial': return 'Reembolsado Parcial';
                case 'reembolsado_total': return 'Reembolsado Total';
                case 'anulado': return 'Anulado';
                default: return estado;
            }
        },

        // INLINE RENGLÓN RÁPIDO METHODS
        activarFilaRapida() {
            this.gastoRapidoForm = {
                origen_pago_select: this.personas.length > 0 ? 'persona_' + this.personas[0].id : 'caja_arriendo',
                categoria_id: '',
                fecha: new Date().toISOString().slice(0, 10),
                descripcion: '',
                valor: ''
            };
            this.mostrandoFilaRapida = true;
        },
        guardarGastoRapido() {
            if (!this.gastoRapidoForm.origen_pago_select) {
                if (window.swal) window.swal.fire('Atención', 'Seleccione el integrante o fondo de donde proviene el dinero', 'warning');
                return;
            }
            if (!this.gastoRapidoForm.descripcion || !this.gastoRapidoForm.valor) {
                if (window.swal) window.swal.fire('Atención', 'Complete la descripción y el valor del gasto', 'warning');
                return;
            }

            let formData = new FormData();
            let selectVal = this.gastoRapidoForm.origen_pago_select;
            if (selectVal.startsWith('persona_')) {
                formData.append('origen_pago', 'persona');
                formData.append('persona_id', selectVal.replace('persona_', ''));
            } else {
                formData.append('origen_pago', selectVal);
            }

            if (this.gastoRapidoForm.categoria_id) formData.append('categoria_id', this.gastoRapidoForm.categoria_id);
            formData.append('fecha', this.gastoRapidoForm.fecha);
            formData.append('descripcion', this.gastoRapidoForm.descripcion);
            formData.append('valor', this.gastoRapidoForm.valor);

            if (this.$refs.soporteRapidoFile && this.$refs.soporteRapidoFile.files[0]) {
                formData.append('soporte', this.$refs.soporteRapidoFile.files[0]);
            }
            if (this.$refs.comprobanteRapidoFile && this.$refs.comprobanteRapidoFile.files[0]) {
                formData.append('comprobante', this.$refs.comprobanteRapidoFile.files[0]);
            }

            this.loading = true;
            axios.post('/gastos/gasto/store', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(res => {
                this.loading = false;
                if (res.data.success) {
                    this.mostrandoFilaRapida = false;
                    this.cargarDatos();
                    if (window.swal) window.swal.fire('Éxito', 'Gasto registrado rápidamente en la tabla', 'success');
                }
            }).catch(err => {
                this.loading = false;
                if (window.swal) window.swal.fire('Error', 'Hubo un error al guardar el gasto', 'error');
            });
        },

        // GASTO MODAL & METHODS
        puedeEditarGasto(gasto) {
            if (!this.userInfo || !this.userInfo.logged_in) return true;
            if (this.userInfo.is_admin) return true;
            if (!this.userInfo.persona_id) return false;
            if (!gasto || !gasto.persona_id) return false;
            return String(gasto.persona_id) === String(this.userInfo.persona_id);
        },
        abrirModalGasto() {
            let defaultPersonaId = '';
            if (!this.userInfo.is_admin && this.userInfo.persona_id) {
                defaultPersonaId = this.userInfo.persona_id;
            } else if (this.personas.length > 0) {
                defaultPersonaId = this.personas[0].id;
            }
            this.gastoForm = {
                id: null,
                origen_pago: 'persona',
                persona_id: defaultPersonaId,
                categoria_id: '',
                fecha: new Date().toISOString().slice(0, 10),
                descripcion: '',
                valor: '',
                notas: '',
                soporte_path: null,
                comprobante_path: null
            };
            if (this.$refs.soporteFile) this.$refs.soporteFile.value = '';
            if (this.$refs.comprobanteFile) this.$refs.comprobanteFile.value = '';
            $('#modalGasto').modal('show');
        },
        editarGasto(g) {
            this.gastoForm = {
                id: g.id,
                origen_pago: g.origen_pago || 'persona',
                persona_id: g.persona_id || (this.personas.length > 0 ? this.personas[0].id : ''),
                categoria_id: g.categoria_id || '',
                fecha: g.fecha,
                descripcion: g.descripcion,
                valor: g.valor,
                notas: g.notas || '',
                soporte_path: g.soporte_path,
                comprobante_path: g.comprobante_path
            };
            $('#modalGasto').modal('show');
        },
        cerrarModalGasto() {
            $('#modalGasto').modal('hide');
        },
        guardarGasto() {
            let formData = new FormData();
            if (this.gastoForm.id) formData.append('id', this.gastoForm.id);
            formData.append('origen_pago', this.gastoForm.origen_pago || 'persona');
            if (this.gastoForm.origen_pago === 'persona') {
                formData.append('persona_id', this.gastoForm.persona_id);
            } else if (this.gastoForm.persona_id) {
                formData.append('persona_id', this.gastoForm.persona_id);
            }
            if (this.gastoForm.categoria_id) formData.append('categoria_id', this.gastoForm.categoria_id);
            formData.append('fecha', this.gastoForm.fecha);
            formData.append('descripcion', this.gastoForm.descripcion);
            formData.append('valor', this.gastoForm.valor);
            formData.append('notas', this.gastoForm.notas);

            if (this.$refs.soporteFile && this.$refs.soporteFile.files[0]) {
                formData.append('soporte', this.$refs.soporteFile.files[0]);
            }
            if (this.$refs.comprobanteFile && this.$refs.comprobanteFile.files[0]) {
                formData.append('comprobante', this.$refs.comprobanteFile.files[0]);
            }

            this.loading = true;
            axios.post('/gastos/gasto/store', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(res => {
                this.loading = false;
                if (res.data.success) {
                    this.cerrarModalGasto();
                    this.cargarDatos();
                    if (window.swal) window.swal.fire('Éxito', 'Gasto guardado correctamente', 'success');
                }
            }).catch(err => {
                this.loading = false;
                if (window.swal) window.swal.fire('Error', 'Hubo un error al guardar el gasto', 'error');
            });
        },
        eliminarGasto(id) {
            if (window.swal) {
                window.swal.fire({
                    title: '¿Eliminar gasto?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        axios.delete('/gastos/gasto/delete/' + id).then(res => {
                            this.cargarDatos();
                            window.swal.fire('Eliminado', 'El gasto ha sido eliminado', 'success');
                        });
                    }
                });
            }
        },
        toggleSeleccionarTodos(checked) {
            if (checked) {
                let currentIds = this.gastosFiltrados.map(g => g.id);
                this.gastosSeleccionados = Array.from(new Set([...this.gastosSeleccionados, ...currentIds]));
            } else {
                let currentIds = this.gastosFiltrados.map(g => g.id);
                this.gastosSeleccionados = this.gastosSeleccionados.filter(id => !currentIds.includes(id));
            }
        },
        eliminarGastosMasivo() {
            if (this.gastosSeleccionados.length === 0) return;

            let total = this.gastosSeleccionados.length;
            if (window.swal) {
                window.swal.fire({
                    title: `¿Eliminar ${total} gasto(s) seleccionado(s)?`,
                    text: 'Esta acción eliminará los registros de forma permanente.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar seleccionados',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33'
                }).then(result => {
                    if (result.isConfirmed) {
                        this.loading = true;
                        axios.post('/gastos/gasto/delete-masivo', { ids: this.gastosSeleccionados }).then(res => {
                            this.loading = false;
                            if (res.data && res.data.success) {
                                this.gastosSeleccionados = [];
                                this.cargarDatos();
                                window.swal.fire('Eliminados', `Se eliminaron ${res.data.count || total} gastos correctamente.`, 'success');
                            } else {
                                let msg = (res.data && res.data.error) ? res.data.error : 'No se pudieron eliminar los gastos.';
                                window.swal.fire('Error', msg, 'error');
                            }
                        }).catch(err => {
                            this.loading = false;
                            let msg = 'Error al comunicarse con el servidor.';
                            if (err.response && err.response.data && err.response.data.error) {
                                msg = err.response.data.error;
                            }
                            window.swal.fire('Error', msg, 'error');
                        });
                    }
                });
            }
        },

        // INGRESO MODAL & METHODS
        abrirModalIngreso(tipo = 'arriendo') {
            this.ingresoForm = {
                tipo_ingreso: tipo,
                fecha: new Date().toISOString().slice(0, 10),
                periodo_mes: tipo === 'donacion_aporte' ? 'Aporte Especial' : '',
                inquilino_nombre: tipo === 'donacion_aporte' ? 'Donación Papá / Casa' : '',
                descripcion: tipo === 'donacion_aporte' ? 'Donación o aporte extraordinario a la casa (sin reembolso)' : 'Pago mensualidad canon de arrendamiento',
                valor_total: '',
                notas: ''
            };
            this.distribucionesList = [
                { tipo_destino: 'reembolso_persona', persona_id: this.personas.length > 0 ? this.personas[0].id : '', observaciones: '', valor: '' }
            ];
            if (this.$refs.comprobanteIngresoFile) this.$refs.comprobanteIngresoFile.value = '';
            $('#modalIngreso').modal('show');
        },
        cerrarModalIngreso() {
            $('#modalIngreso').modal('hide');
        },
        agregarFilaDistribucion() {
            this.distribucionesList.push({
                tipo_destino: 'reserva_predial',
                persona_id: '',
                observaciones: '',
                valor: ''
            });
        },
        eliminarFilaDistribucion(idx) {
            this.distribucionesList.splice(idx, 1);
        },
        guardarIngreso() {
            let formData = new FormData();
            formData.append('tipo_ingreso', this.ingresoForm.tipo_ingreso || 'arriendo');
            formData.append('fecha', this.ingresoForm.fecha);
            formData.append('periodo_mes', this.ingresoForm.periodo_mes);
            formData.append('inquilino_nombre', this.ingresoForm.inquilino_nombre);
            formData.append('descripcion', this.ingresoForm.descripcion);
            formData.append('valor_total', this.ingresoForm.valor_total);
            formData.append('notas', this.ingresoForm.notas);
            formData.append('distribuciones', JSON.stringify(this.distribucionesList));

            if (this.$refs.comprobanteIngresoFile && this.$refs.comprobanteIngresoFile.files[0]) {
                formData.append('comprobante', this.$refs.comprobanteIngresoFile.files[0]);
            }

            this.loading = true;
            axios.post('/gastos/ingreso/store', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(res => {
                this.loading = false;
                if (res.data.success) {
                    this.cerrarModalIngreso();
                    this.cargarDatos();
                    if (window.swal) window.swal.fire('Éxito', 'Pago de arriendo e ingreso registrado exitosamente', 'success');
                }
            }).catch(err => {
                this.loading = false;
                if (window.swal) window.swal.fire('Error', 'No se pudo guardar el ingreso de arriendo', 'error');
            });
        },

        // PERSONA MODAL & METHODS
        abrirModalPersona() {
            this.personaForm = { nombre: '', porcentaje_participacion: 33.33, telefono: '' };
            $('#modalPersona').modal('show');
        },
        cerrarModalPersona() {
            $('#modalPersona').modal('hide');
        },
        guardarPersona() {
            if (!this.personaForm.nombre) {
                if (window.swal) window.swal.fire('Atención', 'Escriba el nombre de la persona', 'warning');
                return;
            }
            axios.post('/gastos/persona/store', this.personaForm).then(res => {
                if (res.data && res.data.success) {
                    this.cerrarModalPersona();
                    this.cargarDatos();
                    if (window.swal) window.swal.fire('Éxito', 'Persona registrada correctamente', 'success');
                } else {
                    let msg = (res.data && res.data.error) ? res.data.error : 'No se pudo guardar la persona';
                    if (window.swal) window.swal.fire('Error', msg, 'error');
                }
            }).catch(err => {
                let errorMsg = 'Error al comunicarse con el servidor';
                if (err.response && err.response.data && err.response.data.error) {
                    errorMsg = err.response.data.error;
                } else if (err.response && err.response.data && err.response.data.message) {
                    errorMsg = err.response.data.message;
                }
                if (window.swal) window.swal.fire('Error', errorMsg, 'error');
            });
        },
        eliminarPersona(p) {
            if (window.swal) {
                window.swal.fire({
                    title: '¿Eliminar a ' + p.nombre + '?',
                    text: 'Esta acción solo es posible si la persona no tiene gastos registrados.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        axios.delete('/gastos/persona/delete/' + p.id).then(res => {
                            if (res.data && res.data.success) {
                                this.cargarDatos();
                                window.swal.fire('Eliminado', 'Persona eliminada correctamente', 'success');
                            } else {
                                let msg = (res.data && res.data.error) ? res.data.error : 'No se pudo eliminar la persona';
                                window.swal.fire('Atención', msg, 'warning');
                            }
                        }).catch(err => {
                            let msg = 'No se pudo eliminar la persona';
                            if (err.response && err.response.data && err.response.data.error) {
                                msg = err.response.data.error;
                            }
                            window.swal.fire('Atención', msg, 'warning');
                        });
                    }
                });
            }
        },

        // RESERVA MODAL & METHODS
        abrirModalReservaMovimiento(tipo) {
            this.reservaForm = {
                tipo_reserva: tipo,
                tipo_movimiento: 'egreso',
                fecha: new Date().toISOString().slice(0, 10),
                concepto: tipo === 'predial' ? 'Pago Impuesto Predial' : 'Pago obra/arreglo con reserva',
                valor: ''
            };
            if (this.$refs.soporteReservaFile) this.$refs.soporteReservaFile.value = '';
            $('#modalReserva').modal('show');
        },
        cerrarModalReserva() {
            $('#modalReserva').modal('hide');
        },
        guardarMovimientoReserva() {
            let formData = new FormData();
            formData.append('tipo_reserva', this.reservaForm.tipo_reserva);
            formData.append('tipo_movimiento', this.reservaForm.tipo_movimiento);
            formData.append('fecha', this.reservaForm.fecha);
            formData.append('concepto', this.reservaForm.concepto);
            formData.append('valor', this.reservaForm.valor);

            if (this.$refs.soporteReservaFile && this.$refs.soporteReservaFile.files[0]) {
                formData.append('soporte', this.$refs.soporteReservaFile.files[0]);
            }

            axios.post('/gastos/reserva/movimiento', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(res => {
                this.cerrarModalReserva();
                this.cargarDatos();
                if (window.swal) window.swal.fire('Éxito', 'Movimiento de reserva registrado', 'success');
            });
        },

        // IMPORTAR GASTOS METHODS
        abrirModalImportar() {
            if (this.$refs.archivoImportacion) this.$refs.archivoImportacion.value = '';
            $('#modalImportar').modal('show');
        },
        cerrarModalImportar() {
            $('#modalImportar').modal('hide');
        },
        procesarImportacion() {
            if (!this.$refs.archivoImportacion || !this.$refs.archivoImportacion.files[0]) {
                if (window.swal) window.swal.fire('Atención', 'Seleccione un archivo Excel o CSV para importar', 'warning');
                return;
            }

            let formData = new FormData();
            formData.append('archivo', this.$refs.archivoImportacion.files[0]);

            this.loading = true;
            axios.post('/gastos/importar', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(res => {
                this.loading = false;
                if (res.data && res.data.success) {
                    this.cerrarModalImportar();
                    this.cargarDatos();
                    if (window.swal) window.swal.fire('Éxito', res.data.message || 'Gastos importados correctamente', 'success');
                } else {
                    let msg = (res.data && res.data.error) ? res.data.error : 'Ocurrió un error al importar el archivo';
                    if (window.swal) window.swal.fire('Error', msg, 'error');
                }
            }).catch(err => {
                this.loading = false;
                let msg = 'Ocurrió un error al procesar el archivo';
                if (err.response && err.response.data && err.response.data.error) {
                    msg = err.response.data.error;
                }
                if (window.swal) window.swal.fire('Error', msg, 'error');
            });
        }
    }
};
</script>

<style scoped>
.bg-gradient-purple {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}
.border-left-purple {
    border-left: 4px solid #7c3aed !important;
}
.border-left-warning {
    border-left: 4px solid #f59e0b !important;
}
.border-left-info {
    border-left: 4px solid #06b6d4 !important;
}
.border-left-success {
    border-left: 4px solid #10b981 !important;
}
.bg-light-blue {
    background-color: #ecfeff;
}
.bg-light-green {
    background-color: #ecfdf5;
}
.bg-yellow-light {
    background-color: #fffbeb !important;
}
.gap-2 {
    gap: 0.5rem !important;
}
.nav-pills .nav-link {
    color: #64748b;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.nav-pills .nav-link.active {
    background-color: #6366f1;
    color: white;
}
.cursor-pointer {
    cursor: pointer;
}

/* RESPONSIVE MOBILE STYLES */
@media (min-width: 768px) {
    .header-banner-actions {
        width: auto !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }
    .header-banner-btn {
        width: auto !important;
        flex: 0 0 auto !important;
        margin-bottom: 0 !important;
    }
    .filter-persona-select {
        width: 280px !important;
        max-width: 100% !important;
        display: inline-block !important;
        border-color: #6366f1;
        font-size: 13px;
    }
}

@media (max-width: 767.98px) {
    .header-banner-actions {
        width: 100% !important;
        flex-direction: column !important;
    }
    .header-banner-btn {
        width: 100% !important;
        display: block !important;
        margin-bottom: 6px !important;
    }
    .filter-persona-select {
        width: 100% !important;
        border-color: #6366f1;
        font-size: 13px;
    }
    .responsive-title {
        font-size: 1.25rem !important;
    }
    .responsive-subtitle {
        font-size: 0.8rem !important;
    }
    .responsive-tab-title {
        font-size: 1rem !important;
    }
    .mobile-scroll-tabs {
        display: flex !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        white-space: nowrap !important;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 4px !important;
    }
    .mobile-scroll-tabs .nav-item {
        flex: 0 0 auto !important;
    }
    .mobile-scroll-tabs .nav-link {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }
    .table-responsive {
        border: 0;
        margin-bottom: 0;
    }
    .table td, .table th {
        padding: 0.5rem 0.4rem !important;
        font-size: 12px !important;
    }
    .btn-xs {
        padding: 4px 8px !important;
        font-size: 11px !important;
    }
}

@media (max-width: 575.98px) {
    .gastos-casa-wrapper {
        font-size: 13px;
    }
    .modal-dialog {
        margin: 0.5rem !important;
        max-width: calc(100% - 1rem) !important;
    }
    .modal-body {
        padding: 1rem !important;
    }
    .modal-header {
        padding: 0.75rem 1rem !important;
    }
    .modal-header-title {
        font-size: 1.05rem !important;
    }
    .card-body {
        padding: 0.85rem !important;
    }
}
</style>
