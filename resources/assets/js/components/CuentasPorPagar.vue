<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb shadow-sm border-0">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Cuentas por Pagar</li>
        </ol>

        <div class="container-fluid">
            <!-- Premium Summary KPI Cards -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-primary text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ totalGeneral.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Total Registrado</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-calculator fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-danger text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ totalPendiente.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Total Pendiente</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-clock-o fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-warning text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ totalAbonado.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Total Abonado</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-pie-chart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-success text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ totalPagado.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Total Pagado</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-mora text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ totalVencidas.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Mora Vencida</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="kpi-card shadow-sm border-0 gradient-vence text-white p-3 rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-1">${{ totalPorVencer.toLocaleString() }}</h3>
                                <p class="mb-0 text-white-50 small font-weight-bold">Vence en 7 días</p>
                            </div>
                            <div class="icon-circle bg-white-20 text-white">
                                <i class="fa fa-calendar-check-o fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Panel -->
            <div class="card border-0 shadow-sm-premium rounded">
                <div class="card-header bg-white border-bottom-light py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-align-justify text-primary mr-2"></i>
                        <h5 class="mb-0 font-weight-bold">Control de Cuentas por Pagar</h5>
                    </div>
                    <button class="btn btn-primary btn-sm-premium mt-2 mt-sm-0" @click="abrirModalCrear()">
                        <i class="fa fa-plus mr-1"></i> Nueva Cuenta por Pagar
                    </button>
                </div>
                <div class="card-body">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs mb-4" id="cuentasTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link font-weight-bold" 
                                    :class="{'active': tabActiva === 'proveedores'}" 
                                    @click="tabActiva = 'proveedores'" 
                                    type="button">
                                <i class="fa fa-users mr-1 text-primary"></i> Resumen de Deudas (Proveedores / Operarias)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link font-weight-bold" 
                                    :class="{'active': tabActiva === 'general'}" 
                                    @click="tabActiva = 'general'" 
                                    type="button">
                                <i class="fa fa-list mr-1 text-secondary"></i> Listado General de Cuentas
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab 1: Resumen de Proveedores -->
                        <div v-if="tabActiva === 'proveedores'">
                            <!-- Filters for provider debts -->
                            <div class="row mb-4 align-items-center bg-light p-3 rounded border border-light-2 select-container">
                                <div class="col-md-5 col-sm-12 form-group mb-md-0">
                                    <label class="small font-weight-bold text-muted">Buscar Beneficiario</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" 
                                               class="form-control border-light-2" 
                                               placeholder="Buscar por nombre o documento..."
                                               v-model="buscarProveedor">
                                        <div class="input-group-append" v-if="buscarProveedor">
                                            <button class="btn btn-outline-secondary" type="button" @click="buscarProveedor = ''">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 form-group mb-md-0 d-flex align-items-center justify-content-start pt-3 pt-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="checkSoloDeuda" v-model="mostrarSoloConDeuda">
                                        <label class="custom-control-label font-weight-bold text-muted cursor-pointer" for="checkSoloDeuda">
                                            Mostrar sólo beneficiarios con deuda activa
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 form-group mb-md-0 text-right pt-3 pt-md-4">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn font-weight-bold" 
                                                :class="vistaProveedores === 'cuadros' ? 'btn-primary' : 'btn-outline-primary'" 
                                                @click="vistaProveedores = 'cuadros'">
                                            <i class="fa fa-th-large mr-1"></i> Cuadros
                                        </button>
                                        <button class="btn font-weight-bold" 
                                                :class="vistaProveedores === 'columnas' ? 'btn-primary' : 'btn-outline-primary'" 
                                                @click="vistaProveedores = 'columnas'">
                                            <i class="fa fa-bars mr-1"></i> Columnas
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Providers Empty State -->
                            <div v-if="proveedoresFiltrados.length === 0" class="text-center py-5 text-muted">
                                <i class="fa fa-info-circle fa-3x mb-3 text-secondary"></i>
                                <h5>No se encontraron beneficiarios</h5>
                                <p class="mb-0">Prueba cambiando los filtros o la búsqueda.</p>
                            </div>
                            
                            <!-- Providers Grid (Cuadros) -->
                            <div v-else-if="vistaProveedores === 'cuadros'" class="row">
                                <div class="col-xl-4 col-md-6 mb-4" v-for="prov in proveedoresFiltrados" :key="prov.tipo + '-' + prov.id">
                                    <div class="card h-100 shadow-sm border border-light-2 provider-card rounded">
                                        <div class="card-header bg-white border-bottom-light py-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 font-weight-bold text-dark">{{ prov.nombre }}</h6>
                                                <small class="text-muted" v-if="prov.tipo === 'proveedor'">NIT/Doc: {{ prov.num_documento || 'N/A' }}</small>
                                                <small class="text-muted" v-else>Operaria de Terminado</small>
                                            </div>
                                            <span v-if="prov.tipo === 'proveedor'" class="badge badge-light p-2 font-weight-bold text-primary">
                                                <i class="fa fa-truck mr-1"></i> Proveedor
                                            </span>
                                            <span v-else class="badge badge-light p-2 font-weight-bold text-secondary">
                                                <i class="fa fa-user-circle mr-1"></i> Operaria
                                            </span>
                                        </div>
                                        <div class="card-body py-3">
                                            <!-- Credit Limits row -->
                                            <div class="row mb-3" v-if="prov.tipo === 'proveedor'">
                                                <div class="col-6">
                                                    <span class="text-muted small d-block font-weight-bold">Cupo Total:</span>
                                                    <span class="font-weight-bold text-dark">${{ prov.cupo_credito.toLocaleString() }}</span>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <span class="text-muted small d-block font-weight-bold">Cupo Disponible:</span>
                                                    <span class="font-weight-bold" :class="prov.cupo_credito - prov.total_pendiente >= 0 ? 'text-success' : 'text-danger'">
                                                        ${{ Math.max(0, prov.cupo_credito - prov.total_pendiente).toLocaleString() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row mb-3" v-else>
                                                <div class="col-12">
                                                    <span class="text-muted small d-block font-weight-bold">Límite de Crédito:</span>
                                                    <span class="text-muted font-italic small">No aplica (Operaria interna)</span>
                                                </div>
                                            </div>

                                            <h6 class="font-weight-bold border-bottom pb-1 mb-2 small text-secondary">
                                                <i class="fa fa-list mr-1"></i> Facturas Pendientes
                                            </h6>
                                            
                                            <!-- Pending Invoices list -->
                                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                                                    <thead>
                                                        <tr class="text-muted border-bottom" style="font-size: 0.75rem;">
                                                            <th>Vencimiento</th>
                                                            <th>Referencia / Detalle</th>
                                                            <th class="text-right">Abono</th>
                                                            <th class="text-right">Saldo</th>
                                                            <th class="text-center">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="c in prov.cuentas_detalle" :key="c.id" class="align-middle border-bottom-light">
                                                            <td>
                                                                <span v-if="c.fecha_vencimiento">{{ c.fecha_vencimiento }}</span>
                                                                <span v-else class="text-muted small">Sin venc.</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div>
                                                                        <strong v-if="c.numero_factura" class="text-dark d-block">
                                                                            Factura #{{ c.numero_factura }}
                                                                        </strong>
                                                                        <span class="text-muted d-block" style="font-size: 0.75rem;">{{ c.descripcion }}</span>
                                                                        <small v-if="c.ordentrabajo_id" class="d-block text-muted">
                                                                            Orden #{{ c.ordentrabajo_id }}
                                                                        </small>
                                                                        <!-- If it is an operaria account and has a linked order with customer/product info -->
                                                                        <div v-if="prov.tipo === 'operaria' && c.orden" class="mt-1">
                                                                            <span v-if="c.orden.cliente" class="badge badge-light border text-dark mr-1" style="font-weight: 500; font-size: 0.7rem; padding: 2px 4px;">
                                                                                <i class="fa fa-user text-muted mr-1"></i> {{ c.orden.cliente.razonsocial }}
                                                                            </span>
                                                                            <span v-if="c.orden.articulo" class="badge badge-light border text-dark" style="font-weight: 500; font-size: 0.7rem; padding: 2px 4px;">
                                                                                <i class="fa fa-tag text-muted mr-1"></i> {{ c.orden.articulo.nombre }}
                                                                            </span>
                                                                        </div>
                                                                        <div v-if="prov.tipo === 'operaria' && c.cantidad > 0" class="mt-1">
                                                                            <span class="badge badge-info-light mr-1" style="font-weight: 500; font-size: 0.7rem; padding: 2px 4px; background-color: #e8f4fd; color: #007aff; border: 1px solid rgba(0, 122, 255, 0.15);">
                                                                                Entregado: {{ c.cantidad_entregada }} de {{ c.cantidad }} uds
                                                                            </span>
                                                                            <span class="badge badge-success-light" style="font-weight: 500; font-size: 0.7rem; padding: 2px 4px; background-color: #e3f9eb; color: #24b057; border: 1px solid rgba(36, 176, 87, 0.15);">
                                                                                Para Pago: ${{ (c.cantidad_entregada * c.valor_unitario).toLocaleString() }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div v-if="c.soporte">
                                                                        <a :href="'/uploads/cuentas_por_pagar/' + c.soporte" 
                                                                           target="_blank" 
                                                                           class="btn btn-outline-info btn-xs-premium ml-2" 
                                                                           style="padding: 1px 4px; font-size: 0.7rem;"
                                                                           title="Ver Soporte Digital">
                                                                            <i class="fa fa-file-pdf-o text-danger"></i> Soporte
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-right text-success">${{ (c.monto - c.saldo).toLocaleString() }}</td>
                                                            <td class="text-right font-weight-bold" :class="c.saldo < 0 ? 'text-success' : 'text-danger'">
                                                                {{ c.saldo < 0 ? 'A favor: ' : '' }}${{ Math.abs(c.saldo).toLocaleString() }}
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <button class="btn btn-success btn-action" 
                                                                            style="padding: 1px 4px; font-size: 0.75rem;"
                                                                            :title="c.estado === 'En Espera' ? (c.cantidad_entregada > 0 ? 'Hacer Abono (Parcial Entregado)' : 'En Espera (Definir cantidad en Producción)') : 'Hacer Abono'" 
                                                                            :disabled="c.estado === 'En Espera' && (!c.cantidad_entregada || c.cantidad_entregada <= 0)"
                                                                            @click="abrirModalAbono(c)">
                                                                        <i class="fa fa-money"></i>
                                                                    </button>
                                                                    <a v-if="c.soporte" 
                                                                       :href="'/uploads/cuentas_por_pagar/' + c.soporte" 
                                                                       target="_blank" 
                                                                       class="btn btn-info btn-action text-white ml-1"
                                                                       style="padding: 1px 4px; font-size: 0.75rem;"
                                                                       title="Ver Factura / Soporte">
                                                                        <i class="fa fa-eye"></i>
                                                                    </a>
                                                                    <button v-else 
                                                                            class="btn btn-secondary btn-action ml-1" 
                                                                            style="padding: 1px 4px; font-size: 0.75rem;" 
                                                                            disabled 
                                                                            title="Sin soporte">
                                                                        <i class="fa fa-eye-slash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr v-if="prov.cuentas_detalle.length === 0">
                                                            <td colspan="5" class="text-center text-muted py-2 small">
                                                                Sin facturas pendientes.
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div v-if="prov.cuentas_vencidas > 0" class="alert alert-danger-light mt-2 py-1 px-2 mb-0 small text-center rounded">
                                                <i class="fa fa-exclamation-triangle mr-1"></i> Tiene <strong>{{ prov.cuentas_vencidas }}</strong> cuentas vencidas
                                            </div>

                                            <!-- Valor total de las facturas -->
                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light-2">
                                                <span class="font-weight-bold text-muted small">{{ prov.total_pendiente < 0 ? 'Saldo a Favor:' : 'Valor Total Facturas:' }}</span>
                                                <span class="font-weight-bold h5 mb-0" :class="prov.total_pendiente < 0 ? 'text-success' : 'text-danger'">
                                                    {{ prov.total_pendiente < 0 ? 'A favor: ' : '' }}${{ Math.abs(prov.total_pendiente).toLocaleString() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light border-0 py-3 d-flex justify-content-between">
                                            <button class="btn btn-outline-dark btn-sm-premium flex-grow-1 mr-2" @click="abrirEstadoCuenta(prov.tipo, prov.id)">
                                                <i class="fa fa-file-text-o mr-1"></i> Estado / Ficha
                                            </button>
                                            <button class="btn btn-success btn-sm-premium flex-grow-1" :disabled="prov.total_pendiente <= 0" @click="abrirAbonoGeneralDesdeResumen(prov)">
                                                <i class="fa fa-money mr-1"></i> Abonar / Pagar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Providers Columns (Table) -->
                            <div v-else-if="vistaProveedores === 'columnas'" class="table-responsive">
                                <table class="table table-hover table-custom border-light-2">
                                    <thead>
                                        <tr>
                                            <th>Beneficiario</th>
                                            <th>Tipo</th>
                                            <th>Documento / NIT</th>
                                            <th class="text-right">Total Registrado</th>
                                            <th class="text-right">Total Abonado</th>
                                            <th class="text-right">Total Adeudado</th>
                                            <th class="text-right">Cupo Crédito</th>
                                            <th class="text-right">Cupo Disponible</th>
                                            <th class="text-center">Cuentas</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="prov in proveedoresFiltrados" :key="prov.tipo + '-' + prov.id" class="align-middle">
                                            <td>
                                                <strong class="text-dark">{{ prov.nombre }}</strong>
                                                <div v-if="prov.cuentas_vencidas > 0" class="text-danger small mt-1">
                                                    <i class="fa fa-exclamation-triangle mr-1"></i> {{ prov.cuentas_vencidas }} vencidas
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge font-weight-bold" :class="prov.tipo === 'proveedor' ? 'badge-primary' : 'badge-secondary'">
                                                    {{ prov.tipo === 'proveedor' ? 'Proveedor' : 'Operaria' }}
                                                </span>
                                            </td>
                                            <td>{{ prov.num_documento || 'N/A' }}</td>
                                            <td class="text-right font-weight-bold">${{ prov.total_registrado.toLocaleString() }}</td>
                                            <td class="text-right font-weight-bold text-success">${{ prov.total_pagado.toLocaleString() }}</td>
                                            <td class="text-right font-weight-bold h6 mb-0" :class="prov.total_pendiente < 0 ? 'text-success' : 'text-danger'">
                                                {{ prov.total_pendiente < 0 ? 'A favor: ' : '' }}${{ Math.abs(prov.total_pendiente).toLocaleString() }}
                                            </td>
                                            <td class="text-right text-muted">
                                                <span v-if="prov.tipo === 'proveedor'">${{ prov.cupo_credito.toLocaleString() }}</span>
                                                <span v-else class="text-muted small">-</span>
                                            </td>
                                            <td class="text-right font-weight-bold" :class="prov.tipo === 'proveedor' ? (prov.cupo_credito - prov.total_pendiente >= 0 ? 'text-success' : 'text-danger') : 'text-muted'">
                                                <span v-if="prov.tipo === 'proveedor'">${{ Math.max(0, prov.cupo_credito - prov.total_pendiente).toLocaleString() }}</span>
                                                <span v-else class="text-muted small">-</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-warning font-weight-bold text-dark px-2">{{ prov.cantidad_cuentas }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-dark btn-action mr-1" title="Ver Estado de Cuenta / Ficha" @click="abrirEstadoCuenta(prov.tipo, prov.id)">
                                                        <i class="fa fa-file-text-o"></i>
                                                    </button>
                                                    <button class="btn btn-success btn-action" title="Abonar / Pagar Deuda" :disabled="prov.total_pendiente <= 0" @click="abrirAbonoGeneralDesdeResumen(prov)">
                                                        <i class="fa fa-money"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Listado General de Cuentas -->
                        <div v-else-if="tabActiva === 'general'">
                            <!-- Filters Grid -->
                    <div class="row mb-3 bg-light p-3 rounded border border-light-2 select-container">
                        <div class="col-md-3 col-sm-6 form-group">
                            <label class="small font-weight-bold text-muted">Filtrar por Proveedor / Operaria</label>
                            <select class="form-control form-control-sm border-light-2" v-model="filtroBeneficiario" @change="listarCuentas(1)">
                                <option value="">Todos</option>
                                <optgroup label="Proveedores Externos">
                                    <option v-for="prov in proveedores" :key="'prov-' + prov.id" :value="'prov-' + prov.id">{{ prov.nombre }}</option>
                                </optgroup>
                                <optgroup label="Operarias de Terminado">
                                    <option v-for="op in operarias" :key="'op-' + op.id" :value="'op-' + op.id">{{ op.activo }}</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label class="small font-weight-bold text-muted">Filtrar por Estado</label>
                            <select class="form-control form-control-sm border-light-2" v-model="filtroEstado" @change="listarCuentas(1)">
                                <option value="">Todos los estados</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Abonado">Abonado</option>
                                <option value="Pagado">Pagado</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12 form-group">
                            <label class="small font-weight-bold text-muted">Buscar por Descripción</label>
                            <div class="input-group input-group-sm">
                                <input type="text" 
                                       class="form-control border-light-2" 
                                       placeholder="Buscar por descripción o nombre..."
                                       v-model="buscar" 
                                       @keyup.enter="listarCuentas(1)">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" @click="listarCuentas(1)">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12 form-group d-flex align-items-end justify-content-end">
                            <button class="btn btn-outline-secondary btn-sm-premium w-100" @click="limpiarFiltros()">
                                <i class="fa fa-refresh mr-1"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Accounts Payable Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-custom border-light-2">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Vencimiento</th>
                                    <th>Proveedor / Beneficiario</th>
                                    <th>Referencia / Detalle</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Saldo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cuenta in arrayCuentas" :key="cuenta.id" class="align-middle">
                                    <td>{{ cuenta.fecha }}</td>
                                    <td class="text-center">
                                        <span v-if="!cuenta.fecha_vencimiento" class="text-muted small">Sin vencimiento</span>
                                        <span v-else-if="cuenta.estado === 'Pagado'" class="badge badge-success-light">
                                            {{ cuenta.fecha_vencimiento }} <small class="d-block text-muted">Pagado</small>
                                        </span>
                                        <span v-else :class="['badge-vencimiento', obtenerClaseVencimiento(cuenta.fecha_vencimiento)]">
                                            {{ cuenta.fecha_vencimiento }}
                                            <small class="d-block font-weight-bold">{{ obtenerTextoVencimiento(cuenta.fecha_vencimiento) }}</small>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light p-2 font-weight-bold cursor-pointer text-hover-underline" v-if="cuenta.activo" @click="abrirEstadoCuenta('operaria', cuenta.activo.id)" title="Ver Estado de Cuenta">
                                            <i class="fa fa-user-circle mr-1 text-secondary"></i> {{ cuenta.activo.activo }} (Operaria)
                                        </span>
                                        <span class="badge badge-light p-2 font-weight-bold cursor-pointer text-hover-underline" v-else-if="cuenta.proveedor" @click="abrirEstadoCuenta('proveedor', cuenta.proveedor.id)" title="Ver Estado de Cuenta">
                                            <i class="fa fa-truck mr-1 text-primary"></i> {{ cuenta.proveedor.nombre }} (Proveedor)
                                        </span>
                                        <span class="badge badge-light p-2 font-weight-bold text-muted" v-else>
                                            Sin definir
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <strong v-if="cuenta.numero_factura" class="text-dark d-block">
                                                    Factura #{{ cuenta.numero_factura }}
                                                </strong>
                                                <span class="text-muted small">{{ cuenta.descripcion }}</span>
                                                <div v-if="cuenta.cuenta" class="mt-1">
                                                    <span class="badge badge-light border text-muted" style="font-weight: 500; font-size: 0.72rem; padding: 2px 4px;">
                                                        <i class="fa fa-book mr-1"></i> {{ cuenta.cuenta.codigo }} - {{ cuenta.cuenta.nombre }}
                                                    </span>
                                                </div>
                                                <small v-if="cuenta.ordentrabajo_id" class="d-block text-muted">
                                                    Orden #{{ cuenta.ordentrabajo_id }}
                                                </small>
                                                <!-- If it is an operaria account and has a linked order with customer/product info -->
                                                <div v-if="cuenta.activo && cuenta.orden" class="mt-1">
                                                    <span v-if="cuenta.orden.cliente" class="badge badge-light border text-dark mr-1" style="font-weight: 500;">
                                                        <i class="fa fa-user text-muted mr-1"></i> {{ cuenta.orden.cliente.razonsocial }}
                                                    </span>
                                                    <span v-if="cuenta.orden.articulo" class="badge badge-light border text-dark" style="font-weight: 500;">
                                                        <i class="fa fa-tag text-muted mr-1"></i> {{ cuenta.orden.articulo.nombre }}
                                                    </span>
                                                </div>
                                                <div v-if="cuenta.activo && cuenta.cantidad > 0" class="mt-1">
                                                    <span class="badge badge-info-light mr-1" style="font-weight: 500; font-size: 0.75rem; padding: 2px 5px; background-color: #e8f4fd; color: #007aff; border: 1px solid rgba(0, 122, 255, 0.15);">
                                                        Entregado: {{ cuenta.cantidad_entregada }} de {{ cuenta.cantidad }} uds
                                                    </span>
                                                    <span class="badge badge-success-light" style="font-weight: 500; font-size: 0.75rem; padding: 2px 5px; background-color: #e3f9eb; color: #24b057; border: 1px solid rgba(36, 176, 87, 0.15);">
                                                        Para Pago: ${{ (cuenta.cantidad_entregada * cuenta.valor_unitario).toLocaleString() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div v-if="cuenta.soporte">
                                                <a :href="'/uploads/cuentas_por_pagar/' + cuenta.soporte" 
                                                   target="_blank" 
                                                   class="btn btn-outline-info btn-xs-premium ml-2" 
                                                   title="Ver Soporte Digital">
                                                    <i class="fa fa-file-pdf-o text-danger mr-1"></i> Soporte
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right font-weight-bold">${{ parseFloat(cuenta.monto).toLocaleString() }}</td>
                                    <td class="text-right font-weight-bold text-danger">${{ parseFloat(cuenta.saldo).toLocaleString() }}</td>
                                    <td class="text-center">
                                        <span :class="['badge-status', cuenta.estado.toLowerCase()]">
                                            {{ cuenta.estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-success btn-action" 
                                                    :title="cuenta.estado === 'En Espera' ? (cuenta.cantidad_entregada > 0 ? 'Registrar Abono / Pago (Parcial Entregado)' : 'En Espera (Definir cantidad en Producción)') : 'Registrar Abono / Pago'" 
                                                    :disabled="cuenta.estado === 'En Espera' && (!cuenta.cantidad_entregada || cuenta.cantidad_entregada <= 0)"
                                                    @click="abrirModalAbono(cuenta)">
                                                <i class="fa fa-money"></i>
                                            </button>
                                            <button class="btn btn-info btn-action text-white" 
                                                    title="Editar" 
                                                    @click="abrirModalEditar(cuenta)">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-action" 
                                                    title="Eliminar" 
                                                    :disabled="cuenta.abonos.length > 0"
                                                    @click="eliminarCuenta(cuenta)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="arrayCuentas.length === 0">
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        No se encontraron cuentas por pagar registradas.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                        <span class="small text-muted">Mostrando del {{ pagination.from || 0 }} al {{ pagination.to || 0 }} de {{ pagination.total || 0 }} registros</span>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">
                                    <i class="fa fa-angle-left"></i>
                                </a>
                            </li>
                            <li class="page-item" 
                                v-for="page in paginasCalculadas" 
                                :key="page" 
                                :class="{ active: page === pagination.current_page }">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                            </li>
                            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                        </div> <!-- End Tab General -->
                    </div> <!-- End Tab Content -->
                </div>
            </div>
        </div>

        <!-- Modal Crear/Editar Cuenta por Pagar -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalForm}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 3000 !important;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header" :class="tipoAccion === 1 ? 'bg-primary' : 'bg-info'">
                        <h5 class="modal-title text-white font-weight-bold">
                            {{ tipoAccion === 1 ? 'Crear Cuenta por Pagar' : 'Editar Cuenta por Pagar' }}
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarModalForm()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <form @submit.prevent="guardarCuenta()">
                            <div class="form-group">
                                <label class="font-weight-bold small">Tipo de Beneficiario <span class="text-danger">*</span></label>
                                <div class="d-flex mb-2 select-container">
                                    <div class="custom-control custom-radio custom-control-inline mr-4">
                                        <input type="radio" id="tipoBeneficiarioOperaria" name="tipoBeneficiario" class="custom-control-input" value="operaria" v-model="tipoBeneficiarioForm" @change="onTipoBeneficiarioChange">
                                        <label class="custom-control-label font-weight-bold text-muted cursor-pointer" for="tipoBeneficiarioOperaria">Operaria (Terminado)</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="tipoBeneficiarioProveedor" name="tipoBeneficiario" class="custom-control-input" value="proveedor" v-model="tipoBeneficiarioForm" @change="onTipoBeneficiarioChange">
                                        <label class="custom-control-label font-weight-bold text-muted cursor-pointer" for="tipoBeneficiarioProveedor">Proveedor Externo</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" v-if="tipoBeneficiarioForm === 'operaria'">
                                <label class="font-weight-bold small">Operaria / Beneficiaria <span class="text-danger">*</span></label>
                                <select class="form-control" v-model="cuentaForm.activo_id" required>
                                    <option value="" disabled>Seleccione una operaria</option>
                                    <option v-for="op in operarias" :key="op.id" :value="op.id">{{ op.activo }}</option>
                                </select>

                                <!-- Operator Deliveries and Payable metrics helper block -->
                                <div v-if="cuentaForm.cantidad > 0" class="mt-3 p-3 bg-light rounded border border-light-2 small">
                                    <h6 class="font-weight-bold text-info mb-2"><i class="fa fa-info-circle mr-1"></i>Detalles de Entrega (Operaria)</h6>
                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <span class="text-muted d-block font-weight-bold">Cantidad Total (Orden):</span>
                                            <span class="font-weight-bold text-dark">{{ cuentaForm.cantidad }} uds</span>
                                        </div>
                                        <div class="col-6 mb-2 text-right">
                                            <span class="text-muted d-block font-weight-bold">Cantidad Entregada:</span>
                                            <span class="font-weight-bold text-success">{{ cuentaForm.cantidad_entregada }} uds</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block font-weight-bold">Valor Unitario:</span>
                                            <span class="font-weight-bold text-dark">${{ parseFloat(cuentaForm.valor_unitario).toLocaleString() }}</span>
                                        </div>
                                        <div class="col-6 text-right">
                                            <span class="text-muted d-block font-weight-bold">Total Esperado:</span>
                                            <span class="font-weight-bold text-dark">${{ (cuentaForm.cantidad * cuentaForm.valor_unitario).toLocaleString() }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-top border-light-2 d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold text-muted">Monto Habilitado para Pago:</span>
                                        <span class="font-weight-bold text-primary h6 mb-0">${{ (cuentaForm.cantidad_entregada * cuentaForm.valor_unitario).toLocaleString() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" v-if="tipoBeneficiarioForm === 'proveedor'">
                                <label class="font-weight-bold small">Proveedor <span class="text-danger">*</span></label>
                                <select class="form-control" v-model="cuentaForm.proveedor_id" @change="obtenerCreditoProveedor" required>
                                    <option value="" disabled>Seleccione un proveedor</option>
                                    <option v-for="prov in proveedores" :key="prov.id" :value="prov.id">{{ prov.nombre }}</option>
                                </select>
                                
                                <!-- Credit Limit Status helper block -->
                                <div v-if="cuentaForm.proveedor_id && cupo_credito > 0" class="mt-2 p-3 bg-light rounded border border-light-2 small">
                                    <div class="d-flex flex-wrap justify-content-between mb-1">
                                        <span class="badge bg-secondary text-white mr-1 mb-1">Cupo Autorizado: ${{ parseFloat(cupo_credito).toLocaleString() }}</span>
                                        <span class="badge bg-danger text-white mr-1 mb-1">Saldo Pendiente: ${{ parseFloat(saldo_pendiente).toLocaleString() }}</span>
                                        <span class="badge mb-1" :class="cupoDisponibleCalculado >= cuentaForm.monto ? 'bg-success text-white' : 'bg-warning text-dark'">
                                            Cupo Disponible: ${{ parseFloat(cupoDisponibleCalculado).toLocaleString() }}
                                        </span>
                                    </div>
                                    <div v-if="cuentaForm.monto > cupoDisponibleCalculado" class="alert alert-danger mt-2 py-2 px-3 mb-2" role="alert">
                                        <i class="fa fa-warning mr-1"></i> <strong>¡Cupo Excedido!</strong> Esta cuenta supera el cupo de crédito disponible del proveedor.
                                    </div>
                                    <div v-if="cuentaForm.monto > cupoDisponibleCalculado" class="custom-control custom-checkbox mt-1">
                                        <input type="checkbox" class="custom-control-input" id="aceptarSobrecupo" v-model="aceptarSobrecupo">
                                        <label class="custom-control-label font-weight-bold text-danger cursor-pointer" for="aceptarSobrecupo">Aceptar sobrecupo (Permitir registrar superando el límite de crédito)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Número de Factura / Cuenta</label>
                                    <input type="text" class="form-control" v-model="cuentaForm.numero_factura" placeholder="Ej. FAC-0001">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Monto Total a Pagar <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" class="form-control text-right font-weight-bold text-primary" v-model.number="cuentaForm.monto" min="0.01" step="any" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label class="font-weight-bold small">Cuenta Contable (Debe / Gasto o Materia Prima)</label>
                                    <select class="form-control" v-model="cuentaForm.cuenta_id">
                                        <option value="">-- Selección Automática (Por Defecto) --</option>
                                        <option v-for="cta in arrayCuentasContables" :key="cta.id" :value="cta.id">
                                            {{ cta.codigo }} - {{ cta.nombre }}
                                        </option>
                                    </select>
                                    <small class="text-muted">Deje vacío para aplicar la cuenta automática por defecto.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold small">Fecha Registro <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" v-model="cuentaForm.fecha" required>
                                </div>
                                <div class="col-md-6 form-group" v-if="tipoAccion === 2">
                                    <label class="font-weight-bold small">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control" v-model="cuentaForm.estado" required>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Abonado">Abonado</option>
                                        <option value="Pagado">Pagado</option>
                                        <option value="En Espera">En Espera</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group" v-else>
                                    <label class="font-weight-bold small">Fecha Vencimiento</label>
                                    <input type="date" class="form-control" v-model="cuentaForm.fecha_vencimiento">
                                </div>
                            </div>

                            <div class="row" v-if="tipoAccion === 2">
                                <div class="col-md-12 form-group">
                                    <label class="font-weight-bold small">Fecha Vencimiento</label>
                                    <input type="date" class="form-control" v-model="cuentaForm.fecha_vencimiento">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold small">Soporte Digital (PDF, JPG, PNG)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="soporteFile" ref="soporteFile" accept=".pdf,image/*" @change="onFileSelected">
                                    <label class="custom-file-label" for="soporteFile">{{ fileSelectedName }}</label>
                                </div>
                                <small class="text-muted d-block mt-1" v-if="cuentaForm.soporte">
                                    Soporte actual: <a :href="'/uploads/cuentas_por_pagar/' + cuentaForm.soporte" target="_blank">{{ cuentaForm.soporte }}</a>
                                </small>
                            </div>

                            <div class="text-right mt-3">
                                <button type="button" class="btn btn-secondary btn-sm-premium mr-2" @click="cerrarModalForm()">Cancelar</button>
                                <button type="submit" class="btn btn-success btn-sm-premium" :disabled="loadingForm || (tipoBeneficiarioForm === 'proveedor' && cupo_credito > 0 && cuentaForm.monto > cupoDisponibleCalculado && !aceptarSobrecupo)">
                                    <span v-if="loadingForm"><i class="fa fa-spinner fa-spin mr-1"></i> Guardando...</span>
                                    <span v-else>Guardar</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Registrar Abonos / Detalle Cuenta -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalAbono}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 2500 !important;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">Abonos y Detalles - Cuenta #{{ cuentaSeleccionada.id }}</h5>
                        <button type="button" class="close text-white" @click="cerrarModalAbono()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded border border-light-2">
                                    <h6 class="font-weight-bold text-primary mb-2">Resumen de la Cuenta</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-1">
                                            <strong>Proveedor / Beneficiario:</strong> 
                                            <span v-if="cuentaSeleccionada.activo">{{ cuentaSeleccionada.activo.activo }} (Operaria)</span>
                                            <span v-else-if="cuentaSeleccionada.proveedor">{{ cuentaSeleccionada.proveedor.nombre }} (Proveedor)</span>
                                            <span v-else class="text-muted">Sin definir</span>
                                        </li>
                                        <li class="mb-1"><strong>Descripción:</strong> {{ cuentaSeleccionada.descripcion }}</li>
                                        <li class="mb-1"><strong>Fecha:</strong> {{ cuentaSeleccionada.fecha }}</li>
                                        <li class="mb-1"><strong>Total Cuenta:</strong> ${{ parseFloat(cuentaSeleccionada.monto || 0).toLocaleString() }}</li>
                                        <li><strong>Saldo Pendiente:</strong> <span class="text-danger font-weight-bold">${{ parseFloat(cuentaSeleccionada.saldo || 0).toLocaleString() }}</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6" v-if="cuentaSeleccionada.saldo > 0">
                                <div class="p-3 bg-light rounded border border-light-2">
                                    <h6 class="font-weight-bold text-success mb-2">Registrar Nuevo Abono</h6>
                                    <form @submit.prevent="guardarAbono()">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Monto del Abono</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" 
                                                       class="form-control text-right" 
                                                       v-model.number="abonoForm.monto" 
                                                       :max="cuentaSeleccionada.saldo" 
                                                       min="0.01" 
                                                       step="any" 
                                                       required>
                                            </div>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Fecha de Pago</label>
                                            <input type="date" class="form-control form-control-sm" v-model="abonoForm.fecha" required>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Observaciones</label>
                                            <input type="text" class="form-control form-control-sm" v-model="abonoForm.observaciones" placeholder="Ej. Pago efectivo, transferencia Bancolombia">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Método de Pago <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm" v-model="abonoForm.metodo_pago" required>
                                                <option value="Banco">Banco</option>
                                                <option value="Caja Menor">Caja Menor</option>
                                                <option value="Caja Mayor">Caja Mayor</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold">Soporte de Pago (PDF, JPG, PNG)</label>
                                            <div class="custom-file custom-file-sm">
                                                <input type="file" class="custom-file-input form-control-sm" id="abonoSoporteFile" ref="abonoSoporteFile" accept=".pdf,image/*" @change="onAbonoFileSelected">
                                                <label class="custom-file-label col-form-label-sm" for="abonoSoporteFile" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 80px;">{{ abonoFileSelectedName }}</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm btn-block" :disabled="loadingAbono">
                                            <span v-if="loadingAbono"><i class="fa fa-spinner fa-spin mr-1"></i> Registrando...</span>
                                            <span v-else>Confirmar Abono</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-center justify-content-center text-center" v-else>
                                <div class="alert alert-success w-100 py-4 mb-0">
                                    <i class="fa fa-check-circle fa-3x mb-2 text-success"></i>
                                    <h5>Cuenta Totalmente Pagada</h5>
                                    <p class="mb-0 text-muted small">No hay saldos pendientes para esta cuenta.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Abonos History List -->
                        <h6 class="font-weight-bold border-bottom pb-2">Historial de Abonos / Pagos Realizados</h6>
                        <div class="table-responsive" style="max-height: 250px;">
                            <table class="table table-sm table-hover border-light-2">
                                <thead>
                                    <tr>
                                        <th>Fecha Pago</th>
                                        <th class="text-right">Monto</th>
                                        <th>Observaciones</th>
                                        <th class="text-center">Soporte</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ab in cuentaSeleccionada.abonos" :key="ab.id">
                                        <td>{{ ab.fecha }}</td>
                                        <td class="text-right font-weight-bold text-success">${{ parseFloat(ab.monto).toLocaleString() }}</td>
                                        <td>{{ ab.observaciones || 'Sin observaciones' }}</td>
                                        <td class="text-center">
                                            <a v-if="ab.soporte" 
                                               :href="'/uploads/abonos_cuentas_por_pagar/' + ab.soporte" 
                                               target="_blank" 
                                               class="btn btn-outline-info btn-xs-premium" 
                                               title="Ver Soporte Digital">
                                                <i class="fa fa-file-pdf-o text-danger"></i>
                                            </a>
                                            <span v-else class="text-muted small">Sin soporte</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-primary btn-xs-premium mr-1" title="Descargar Comprobante PDF" @click="descargarPDFAbono(ab.id)">
                                                    <i class="fa fa-print"></i>
                                                </button>
                                                <button class="btn btn-outline-info btn-xs-premium mr-1" title="Editar Abono" @click="abrirEditarAbono(ab)">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-xs-premium" @click="eliminarAbono(ab)">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!cuentaSeleccionada.abonos || cuentaSeleccionada.abonos.length === 0">
                                        <td colspan="5" class="text-center text-muted py-3">
                                            No se han registrado abonos para esta cuenta.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Estado de Cuenta Individual -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalEstado}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 1050 !important;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white d-flex align-items-center justify-content-between">
                        <h5 class="modal-title font-weight-bold my-0">
                            <i class="fa fa-file-text-o mr-1"></i> Estado de Cuenta Individual
                        </h5>
                        <button type="button" class="close text-white" @click="cerrarEstadoCuenta()">&times;</button>
                    </div>
                    <div class="modal-body p-4" id="print-area">
                        <!-- Date range search form inside modal -->
                        <div class="row mb-4 bg-light p-3 rounded border border-light-2 select-container no-print">
                            <div class="col-md-5 col-sm-6 form-group mb-2 mb-sm-0">
                                <label class="small font-weight-bold text-muted">Fecha Inicio</label>
                                <input type="date" class="form-control form-control-sm border-light-2" v-model="fechaInicioEstado">
                            </div>
                            <div class="col-md-5 col-sm-6 form-group mb-2 mb-sm-0">
                                <label class="small font-weight-bold text-muted">Fecha Fin</label>
                                <input type="date" class="form-control form-control-sm border-light-2" v-model="fechaFinEstado">
                            </div>
                            <div class="col-md-2 col-sm-12 form-group mb-0 d-flex align-items-end justify-content-between">
                                <button class="btn btn-primary btn-sm-premium mr-1 w-50" @click="consultarEstadoCuentaConFiltro()" title="Buscar por rango de fecha">
                                    <i class="fa fa-search"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm-premium w-50" @click="limpiarFiltroEstadoCuenta()" title="Limpiar filtro">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Botones de Acción dentro de Estado de Cuenta (no-print) -->
                        <div class="d-flex justify-content-center mb-4 no-print">
                            <button type="button" class="btn btn-success btn-sm-premium mr-2" @click="toggleFormNuevoItem()">
                                <i class="fa fa-plus mr-1"></i> {{ mostrarFormNuevoItem ? 'Ocultar Formulario' : 'Crear Cuenta / Item' }}
                            </button>
                            <button type="button" class="btn btn-info btn-sm-premium" @click="toggleFormAbonoGeneral()">
                                <i class="fa fa-money mr-1"></i> {{ mostrarFormAbonoGeneral ? 'Ocultar Formulario' : 'Registrar Abono General' }}
                            </button>
                        </div>

                        <!-- Formulario Crear Cuenta/Item desde Estado de Cuenta (no-print) -->
                        <div v-if="mostrarFormNuevoItem" class="bg-light p-3 rounded border border-success mb-4 no-print">
                            <h6 class="font-weight-bold text-success mb-3"><i class="fa fa-plus"></i> Agregar Nueva Cuenta / Item</h6>
                            
                            <!-- Información de crédito para proveedores -->
                            <div v-if="estadoCuentaData.tipo === 'proveedor' && estadoCuentaData.cupo_credito > 0" class="mb-3 p-2 bg-white rounded border border-light-2 small">
                                <div class="d-flex flex-wrap justify-content-between">
                                    <span class="badge bg-secondary text-white mr-1 mb-1">Cupo Autorizado: ${{ parseFloat(estadoCuentaData.cupo_credito).toLocaleString() }}</span>
                                    <span class="badge bg-danger text-white mr-1 mb-1">Saldo Pendiente: ${{ parseFloat(estadoCuentaData.total_pendiente).toLocaleString() }}</span>
                                    <span class="badge mb-1" :class="(estadoCuentaData.cupo_credito - estadoCuentaData.total_pendiente) >= nuevoItemForm.monto ? 'bg-success text-white' : 'bg-warning text-dark'">
                                        Cupo Disponible: ${{ parseFloat(Math.max(0, estadoCuentaData.cupo_credito - estadoCuentaData.total_pendiente)).toLocaleString() }}
                                    </span>
                                </div>
                                <div v-if="nuevoItemForm.monto > (estadoCuentaData.cupo_credito - estadoCuentaData.total_pendiente)" class="alert alert-danger mt-2 py-1 px-3 mb-0" role="alert">
                                    <i class="fa fa-warning mr-1"></i> <strong>¡Cupo Excedido!</strong> Esta cuenta supera el cupo de crédito disponible del proveedor.
                                </div>
                            </div>

                            <form @submit.prevent="guardarNuevoItemDesdeEstado()">
                                <div class="row">
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold">Número de Factura / Cuenta</label>
                                        <input type="text" class="form-control form-control-sm" v-model="nuevoItemForm.numero_factura" placeholder="Ej. FAC-0001">
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold">Monto Total <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm" v-model.number="nuevoItemForm.monto" min="0.01" step="any" required>
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="small font-weight-bold">Fecha <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" v-model="nuevoItemForm.fecha" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group mb-2">
                                        <label class="small font-weight-bold">Descripción / Detalles</label>
                                        <input type="text" class="form-control form-control-sm" v-model="nuevoItemForm.descripcion" placeholder="Ej. Registro directo desde estado de cuenta">
                                    </div>
                                    <div class="col-md-6 form-group mb-2">
                                        <label class="small font-weight-bold">Fecha Vencimiento</label>
                                        <input type="date" class="form-control form-control-sm" v-model="nuevoItemForm.fecha_vencimiento">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group mb-2">
                                        <label class="small font-weight-bold">Cuenta Contable (Debe)</label>
                                        <select class="form-control form-control-sm" v-model="nuevoItemForm.cuenta_id">
                                            <option value="">-- Selección Automática (Por Defecto) --</option>
                                            <option v-for="cta in arrayCuentasContables" :key="cta.id" :value="cta.id">
                                                {{ cta.codigo }} - {{ cta.nombre }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="estadoCuentaData.tipo === 'proveedor' && estadoCuentaData.cupo_credito > 0 && nuevoItemForm.monto > (estadoCuentaData.cupo_credito - estadoCuentaData.total_pendiente)" class="custom-control custom-checkbox mb-2 text-left">
                                    <input type="checkbox" class="custom-control-input" id="aceptarSobrecupoEstado" v-model="aceptarSobrecupoEstado">
                                    <label class="custom-control-label font-weight-bold text-danger cursor-pointer" for="aceptarSobrecupoEstado">Aceptar sobrecupo (Permitir registrar superando el límite de crédito)</label>
                                </div>

                                <div class="text-right mt-2">
                                    <button type="button" class="btn btn-secondary btn-xs-premium mr-1" @click="mostrarFormNuevoItem = false">Cancelar</button>
                                    <button type="submit" class="btn btn-success btn-xs-premium" :disabled="loadingNuevoItem || (estadoCuentaData.tipo === 'proveedor' && estadoCuentaData.cupo_credito > 0 && nuevoItemForm.monto > (estadoCuentaData.cupo_credito - estadoCuentaData.total_pendiente) && !aceptarSobrecupoEstado)">
                                        <span v-if="loadingNuevoItem"><i class="fa fa-spinner fa-spin mr-1"></i> Guardando...</span>
                                        <span v-else>Guardar Item</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Formulario Registrar Abono General desde Estado de Cuenta (no-print) -->
                        <div v-if="mostrarFormAbonoGeneral" class="bg-light p-3 rounded border border-info mb-4 no-print">
                            <h6 class="font-weight-bold text-info mb-3"><i class="fa fa-money"></i> Registrar Abono General (Abona a las facturas más viejas primero)</h6>
                            <form @submit.prevent="guardarAbonoGeneral()">
                                <div class="row">
                                    <div class="col-md-2 form-group mb-2">
                                        <label class="small font-weight-bold">Monto del Abono <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm" v-model.number="abonoGeneralForm.monto" min="0.01" step="any" required>
                                        <small class="text-muted">Pendiente: ${{ parseFloat(estadoCuentaData.total_pendiente || 0).toLocaleString() }}</small>
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label class="small font-weight-bold">Fecha de Pago <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" v-model="abonoGeneralForm.fecha" required>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold">Observaciones</label>
                                        <input type="text" class="form-control form-control-sm" v-model="abonoGeneralForm.observaciones" placeholder="Ej. Transferencia Bancolombia">
                                    </div>
                                    <div class="col-md-2 form-group mb-2">
                                        <label class="small font-weight-bold">Método de Pago <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" v-model="abonoGeneralForm.metodo_pago" required>
                                            <option value="Banco">Banco</option>
                                            <option value="Caja Menor">Caja Menor</option>
                                            <option value="Caja Mayor">Caja Mayor</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="small font-weight-bold">Soporte de Pago (PDF, Imagen)</label>
                                        <div class="custom-file custom-file-sm">
                                            <input type="file" class="custom-file-input form-control-sm" id="abonoGeneralSoporteFile" ref="abonoGeneralSoporteFile" accept=".pdf,image/*" @change="onAbonoGeneralFileSelected">
                                            <label class="custom-file-label col-form-label-sm" for="abonoGeneralSoporteFile" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 80px;">{{ abonoGeneralFileSelectedName }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right mt-2">
                                    <button type="button" class="btn btn-secondary btn-xs-premium mr-1" @click="mostrarFormAbonoGeneral = false">Cancelar</button>
                                    <button type="submit" class="btn btn-info btn-xs-premium" :disabled="loadingAbonoGeneral">
                                        <span v-if="loadingAbonoGeneral"><i class="fa fa-spinner fa-spin mr-1"></i> Aplicando...</span>
                                        <span v-else>Aplicar Abono</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="text-center mb-4">
                            <h4 class="font-weight-bold text-dark mb-1">{{ estadoCuentaData.nombre }}</h4>
                            <p class="text-muted small mb-0">{{ estadoCuentaData.detalles }}</p>
                            <p class="text-primary font-weight-bold mt-2 mb-0 small" v-if="estadoCuentaData.fecha_inicio && estadoCuentaData.fecha_fin">
                                <i class="fa fa-calendar mr-1"></i> Periodo: {{ estadoCuentaData.fecha_inicio }} al {{ estadoCuentaData.fecha_fin }}
                            </p>
                            <span class="badge badge-dark p-2 mt-2 font-weight-bold text-uppercase">Resumen de Saldos</span>
                        </div>

                        <!-- Quick KPI Summary inside modal -->
                        <div class="row mb-4">
                            <div class="col-md-4 text-center mb-2">
                                <div class="p-3 bg-light rounded border border-light-2">
                                    <span class="text-muted small font-weight-bold d-block mb-1 text-uppercase">Total Contabilizado</span>
                                    <h4 class="font-weight-bold text-secondary mb-0">${{ parseFloat(estadoCuentaData.total_registrado || 0).toLocaleString() }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-2">
                                <div class="p-3 bg-light rounded border border-light-2">
                                    <span class="text-muted small font-weight-bold d-block mb-1 text-uppercase">Total Abonado / Pagado</span>
                                    <h4 class="font-weight-bold text-success mb-0">${{ parseFloat(estadoCuentaData.total_pagado || 0).toLocaleString() }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-2">
                                <div class="p-3 bg-light rounded border border-light-2">
                                    <span class="text-muted small font-weight-bold d-block mb-1 text-uppercase">{{ parseFloat(estadoCuentaData.total_pendiente) < 0 ? 'Saldo a Favor' : 'Total Pendiente (Deuda)' }}</span>
                                    <h4 class="font-weight-bold mb-0" :class="parseFloat(estadoCuentaData.total_pendiente) < 0 ? 'text-success' : 'text-danger'">${{ Math.abs(parseFloat(estadoCuentaData.total_pendiente || 0)).toLocaleString() }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Conditional view if date filter is active -->
                        <template v-if="estadoCuentaData.fecha_inicio && estadoCuentaData.fecha_fin">
                            <!-- Accounts List -->
                            <h6 class="font-weight-bold border-bottom pb-2 mb-3 text-dark">
                                <i class="fa fa-list mr-1 text-primary"></i> Cuentas y Facturas del Periodo
                            </h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-hover table-custom border-light-2">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Factura / Ref</th>
                                            <th>Descripción</th>
                                            <th class="text-right">Monto</th>
                                            <th class="text-right">Saldo</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center no-print">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="c in estadoCuentaData.cuentas">
                                            <tr :key="'cuenta-' + c.id">
                                                <td>{{ c.fecha }}</td>
                                                <td><strong>{{ c.numero_factura || 'N/A' }}</strong></td>
                                                <td>
                                                    {{ c.descripcion }}
                                                    <div v-if="c.cuenta" class="mt-1">
                                                        <span class="badge badge-light border text-muted" style="font-weight: 500; font-size: 0.72rem; padding: 2px 4px;">
                                                            <i class="fa fa-book mr-1"></i> {{ c.cuenta.codigo }} - {{ c.cuenta.nombre }}
                                                        </span>
                                                    </div>
                                                    <small v-if="c.ordentrabajo_id" class="d-block text-muted">Orden #{{ c.ordentrabajo_id }}</small>
                                                    <!-- If it is an operaria account and has a linked order with customer/product info -->
                                                    <div v-if="estadoCuentaData.tipo === 'operaria' && c.orden" class="mt-1">
                                                        <span v-if="c.orden.cliente" class="badge badge-light border text-dark mr-1" style="font-weight: 500;">
                                                            <i class="fa fa-user text-muted mr-1"></i> {{ c.orden.cliente.razonsocial }}
                                                        </span>
                                                        <span v-if="c.orden.articulo" class="badge badge-light border text-dark" style="font-weight: 500;">
                                                            <i class="fa fa-tag text-muted mr-1"></i> {{ c.orden.articulo.nombre }}
                                                        </span>
                                                    </div>
                                                    <div v-if="estadoCuentaData.tipo === 'operaria' && c.cantidad > 0" class="mt-1">
                                                        <span class="badge badge-info-light mr-1" style="font-weight: 500; font-size: 0.75rem; padding: 2px 5px; background-color: #e8f4fd; color: #007aff; border: 1px solid rgba(0, 122, 255, 0.15);">
                                                            Entregado: {{ c.cantidad_entregada }} de {{ c.cantidad }} uds
                                                        </span>
                                                        <span class="badge badge-success-light" style="font-weight: 500; font-size: 0.75rem; padding: 2px 5px; background-color: #e3f9eb; color: #24b057; border: 1px solid rgba(36, 176, 87, 0.15);">
                                                            Para Pago: ${{ (c.cantidad_entregada * c.valor_unitario).toLocaleString() }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="text-right">${{ parseFloat(c.monto).toLocaleString() }}</td>
                                                <td class="text-right font-weight-bold" :class="parseFloat(c.saldo) < 0 ? 'text-success' : 'text-danger'">
                                                    {{ parseFloat(c.saldo) < 0 ? 'A Favor: ' : '' }}${{ Math.abs(parseFloat(c.saldo)).toLocaleString() }}
                                                </td>
                                                <td class="text-center">
                                                    <span :class="['badge-status', c.estado.toLowerCase()]">
                                                        {{ c.estado }}
                                                    </span>
                                                </td>
                                                <td class="text-center no-print">
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-outline-warning btn-xs-premium mr-1" 
                                                                :title="c.abonos && c.abonos.length > 0 ? 'Ver / Eliminar Abonos (' + c.abonos.length + ')' : 'Sin Abonos'" 
                                                                :disabled="!c.abonos || c.abonos.length === 0"
                                                                @click="toggleAbonos(c.id)">
                                                            <i class="fa fa-money"></i> <span v-if="c.abonos && c.abonos.length > 0" class="badge badge-warning text-dark ml-1">{{ c.abonos.length }}</span>
                                                        </button>
                                                        <button class="btn btn-outline-info btn-xs-premium mr-1" 
                                                                title="Editar Cuenta" 
                                                                @click="abrirModalEditar(c)">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger btn-xs-premium" 
                                                                title="Eliminar Cuenta" 
                                                                :disabled="c.abonos && c.abonos.length > 0"
                                                                @click="eliminarCuenta(c)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="expandedAbonos && expandedAbonos[c.id]" :key="'period-abonos-' + c.id">
                                                <td colspan="7" class="bg-light p-3">
                                                    <div class="card border-info shadow-sm mb-0">
                                                        <div class="card-header bg-info text-white py-1 px-3 d-flex justify-content-between align-items-center">
                                                            <small class="font-weight-bold my-0">
                                                                <i class="fa fa-money mr-1"></i> Historial de Abonos - Factura #{{ c.numero_factura || c.id }} ({{ c.descripcion }})
                                                            </small>
                                                            <button type="button" class="close text-white" style="font-size: 1.1rem;" @click="toggleAbonos(c.id)">&times;</button>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                                                                <thead>
                                                                    <tr class="text-muted">
                                                                        <th>Fecha Pago</th>
                                                                        <th>Método de Pago</th>
                                                                        <th>Observaciones</th>
                                                                        <th class="text-right">Monto Abono</th>
                                                                        <th class="text-center">Soporte</th>
                                                                        <th class="text-center">Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr v-for="ab in c.abonos" :key="'p-abono-' + ab.id">
                                                                        <td>{{ ab.fecha }}</td>
                                                                        <td><span class="badge badge-light border text-dark">{{ ab.metodo_pago || 'Banco' }}</span></td>
                                                                        <td>{{ ab.observaciones || 'Sin observaciones' }}</td>
                                                                        <td class="text-right font-weight-bold text-success">${{ parseFloat(ab.monto).toLocaleString() }}</td>
                                                                        <td class="text-center">
                                                                            <a v-if="ab.soporte" :href="'/uploads/abonos_cuentas_por_pagar/' + ab.soporte" target="_blank" class="btn btn-outline-info btn-xs-premium" title="Ver Soporte">
                                                                                <i class="fa fa-file-pdf-o text-danger"></i>
                                                                            </a>
                                                                            <span v-else class="text-muted small">Sin soporte</span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <div class="btn-group" role="group">
                                                                                <button class="btn btn-outline-primary btn-xs-premium mr-1" title="Imprimir PDF" @click="descargarPDFAbono(ab.id)">
                                                                                    <i class="fa fa-print"></i>
                                                                                </button>
                                                                                <button class="btn btn-outline-info btn-xs-premium mr-1" title="Editar Abono" @click="abrirEditarAbonoDesdeEstado(ab)">
                                                                                    <i class="fa fa-edit"></i>
                                                                                </button>
                                                                                <button class="btn btn-danger btn-xs-premium" title="Eliminar este abono" @click="eliminarAbonoDesdeEstado(ab)">
                                                                                    <i class="fa fa-trash"></i> Eliminar
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr v-if="!c.abonos || c.abonos.length === 0">
                                                                        <td colspan="6" class="text-center text-muted py-2">No hay abonos registrados para esta cuenta.</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr v-if="!estadoCuentaData.cuentas || estadoCuentaData.cuentas.length === 0">
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No se encontraron cuentas o facturas registradas en este periodo.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Abonos/Payments List -->
                            <h6 class="font-weight-bold border-bottom pb-2 mb-3 text-dark">
                                <i class="fa fa-money mr-1 text-success"></i> Pagos y Abonos del Periodo
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-custom border-light-2">
                                    <thead>
                                        <tr>
                                            <th>Fecha Pago</th>
                                            <th>Factura / Ref</th>
                                            <th>Observaciones</th>
                                            <th class="text-center">Soporte</th>
                                            <th class="text-right">Monto Pagado</th>
                                            <th class="text-center no-print">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="ab in estadoCuentaData.abonos" :key="'abono-' + ab.id">
                                            <td>{{ ab.fecha }}</td>
                                            <td>
                                                <strong v-if="ab.cuenta_por_pagar">{{ ab.cuenta_por_pagar.numero_factura || 'N/A' }}</strong>
                                                <strong v-else-if="ab.cuentaPorPagar">{{ ab.cuentaPorPagar.numero_factura || 'N/A' }}</strong>
                                                <span v-else>N/A</span>
                                            </td>
                                            <td>{{ ab.observaciones || 'Sin observaciones' }}</td>
                                            <td class="text-center">
                                                <a v-if="ab.soporte" 
                                                   :href="'/uploads/abonos_cuentas_por_pagar/' + ab.soporte" 
                                                   target="_blank" 
                                                   class="btn btn-outline-info btn-xs-premium" 
                                                   title="Ver Soporte Digital">
                                                    <i class="fa fa-file-pdf-o text-danger"></i>
                                                </a>
                                                <span v-else class="text-muted small">Sin soporte</span>
                                            </td>
                                            <td class="text-right font-weight-bold text-success">${{ parseFloat(ab.monto).toLocaleString() }}</td>
                                            <td class="text-center no-print">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-primary btn-xs-premium mr-1" title="Descargar Comprobante PDF" @click="descargarPDFAbono(ab.id)">
                                                        <i class="fa fa-print"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info btn-xs-premium mr-1" title="Editar Abono" @click="abrirEditarAbonoDesdeEstado(ab)">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-xs-premium" title="Eliminar Abono" @click="eliminarAbonoDesdeEstado(ab)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="!estadoCuentaData.abonos || estadoCuentaData.abonos.length === 0">
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No se registraron abonos o pagos en este periodo.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <!-- Else (Default Pending Invoices View) -->
                        <template v-else>
                            <!-- Details list of outstanding balances -->
                            <h6 class="font-weight-bold border-bottom pb-2 mb-3 text-dark">
                                <i class="fa fa-list mr-1 text-primary"></i> Detalle de Cuentas Pendientes
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-custom border-light-2">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Factura / Ref</th>
                                            <th>Descripción</th>
                                            <th class="text-right">Monto</th>
                                            <th class="text-right">Saldo</th>
                                            <th class="text-center">Vencimiento</th>
                                            <th class="text-center no-print">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="c in estadoCuentaData.cuentas">
                                            <tr :key="'pend-' + c.id">
                                                <td>{{ c.fecha }}</td>
                                                <td><strong>{{ c.numero_factura || 'N/A' }}</strong></td>
                                                <td>
                                                    {{ c.descripcion }}
                                                    <div v-if="c.cuenta" class="mt-1">
                                                        <span class="badge badge-light border text-muted" style="font-weight: 500; font-size: 0.72rem; padding: 2px 4px;">
                                                            <i class="fa fa-book mr-1"></i> {{ c.cuenta.codigo }} - {{ c.cuenta.nombre }}
                                                        </span>
                                                    </div>
                                                    <small v-if="c.ordentrabajo_id" class="d-block text-muted">Orden #{{ c.ordentrabajo_id }}</small>
                                                    <!-- If it is an operaria account and has a linked order with customer/product info -->
                                                    <div v-if="estadoCuentaData.tipo === 'operaria' && c.orden" class="mt-1">
                                                        <span v-if="c.orden.cliente" class="badge badge-light border text-dark mr-1" style="font-weight: 500;">
                                                            <i class="fa fa-user text-muted mr-1"></i> {{ c.orden.cliente.razonsocial }}
                                                        </span>
                                                        <span v-if="c.orden.articulo" class="badge badge-light border text-dark" style="font-weight: 500;">
                                                            <i class="fa fa-tag text-muted mr-1"></i> {{ c.orden.articulo.nombre }}
                                                        </span>
                                                    </div>
                                                    <div v-if="estadoCuentaData.tipo === 'operaria' && c.cantidad > 0" class="mt-1">
                                                        <span class="badge badge-info-light mr-1" style="font-weight: 500; font-size: 0.75rem; padding: 2px 5px; background-color: #e8f4fd; color: #007aff; border: 1px solid rgba(0, 122, 255, 0.15);">
                                                            Entregado: {{ c.cantidad_entregada }} de {{ c.cantidad }} uds
                                                        </span>
                                                        <span class="badge badge-success-light" style="font-weight: 500; font-size: 0.75rem; padding: 2px 5px; background-color: #e3f9eb; color: #24b057; border: 1px solid rgba(36, 176, 87, 0.15);">
                                                            Para Pago: ${{ (c.cantidad_entregada * c.valor_unitario).toLocaleString() }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="text-right">${{ parseFloat(c.monto).toLocaleString() }}</td>
                                                <td class="text-right font-weight-bold" :class="parseFloat(c.saldo) < 0 ? 'text-success' : 'text-danger'">
                                                    {{ parseFloat(c.saldo) < 0 ? 'A Favor: ' : '' }}${{ Math.abs(parseFloat(c.saldo)).toLocaleString() }}
                                                </td>
                                                <td class="text-center">
                                                    <span :class="['badge-vencimiento', obtenerClaseVencimiento(c.fecha_vencimiento)]">
                                                        {{ c.fecha_vencimiento || 'Sin vencimiento' }}
                                                        <small v-if="c.fecha_vencimiento" class="d-block font-weight-bold">{{ obtenerTextoVencimiento(c.fecha_vencimiento) }}</small>
                                                    </span>
                                                </td>
                                                <td class="text-center no-print">
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-outline-warning btn-xs-premium mr-1" 
                                                                :title="c.abonos && c.abonos.length > 0 ? 'Ver / Eliminar Abonos (' + c.abonos.length + ')' : 'Sin Abonos'" 
                                                                :disabled="!c.abonos || c.abonos.length === 0"
                                                                @click="toggleAbonos(c.id)">
                                                            <i class="fa fa-money"></i> <span v-if="c.abonos && c.abonos.length > 0" class="badge badge-warning text-dark ml-1">{{ c.abonos.length }}</span>
                                                        </button>
                                                        <button class="btn btn-outline-info btn-xs-premium mr-1" 
                                                                title="Editar Cuenta" 
                                                                @click="abrirModalEditar(c)">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger btn-xs-premium" 
                                                                title="Eliminar Cuenta" 
                                                                :disabled="c.abonos && c.abonos.length > 0"
                                                                @click="eliminarCuenta(c)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="expandedAbonos && expandedAbonos[c.id]" :key="'pend-abonos-' + c.id">
                                                <td colspan="7" class="bg-light p-3">
                                                    <div class="card border-info shadow-sm mb-0">
                                                        <div class="card-header bg-info text-white py-1 px-3 d-flex justify-content-between align-items-center">
                                                            <small class="font-weight-bold my-0">
                                                                <i class="fa fa-money mr-1"></i> Historial de Abonos - Factura #{{ c.numero_factura || c.id }} ({{ c.descripcion }})
                                                            </small>
                                                            <button type="button" class="close text-white" style="font-size: 1.1rem;" @click="toggleAbonos(c.id)">&times;</button>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                                                                <thead>
                                                                    <tr class="text-muted">
                                                                        <th>Fecha Pago</th>
                                                                        <th>Método de Pago</th>
                                                                        <th>Observaciones</th>
                                                                        <th class="text-right">Monto Abono</th>
                                                                        <th class="text-center">Soporte</th>
                                                                        <th class="text-center">Acciones</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr v-for="ab in c.abonos" :key="'sub-abono-' + ab.id">
                                                                        <td>{{ ab.fecha }}</td>
                                                                        <td><span class="badge badge-light border text-dark">{{ ab.metodo_pago || 'Banco' }}</span></td>
                                                                        <td>{{ ab.observaciones || 'Sin observaciones' }}</td>
                                                                        <td class="text-right font-weight-bold text-success">${{ parseFloat(ab.monto).toLocaleString() }}</td>
                                                                        <td class="text-center">
                                                                            <a v-if="ab.soporte" :href="'/uploads/abonos_cuentas_por_pagar/' + ab.soporte" target="_blank" class="btn btn-outline-info btn-xs-premium" title="Ver Soporte">
                                                                                <i class="fa fa-file-pdf-o text-danger"></i>
                                                                            </a>
                                                                            <span v-else class="text-muted small">Sin soporte</span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <div class="btn-group" role="group">
                                                                                <button class="btn btn-outline-primary btn-xs-premium mr-1" title="Imprimir PDF" @click="descargarPDFAbono(ab.id)">
                                                                                    <i class="fa fa-print"></i>
                                                                                </button>
                                                                                <button class="btn btn-outline-info btn-xs-premium mr-1" title="Editar Abono" @click="abrirEditarAbonoDesdeEstado(ab)">
                                                                                    <i class="fa fa-edit"></i>
                                                                                </button>
                                                                                <button class="btn btn-danger btn-xs-premium" title="Eliminar este abono" @click="eliminarAbonoDesdeEstado(ab)">
                                                                                    <i class="fa fa-trash"></i> Eliminar
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr v-if="!c.abonos || c.abonos.length === 0">
                                                                        <td colspan="6" class="text-center text-muted py-2">No hay abonos registrados para esta cuenta.</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr v-if="!estadoCuentaData.cuentas || estadoCuentaData.cuentas.length === 0">
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No hay saldos pendientes registrados para este beneficiario.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer border-top-light justify-content-between">
                        <button type="button" class="btn btn-outline-dark btn-sm-premium" @click="imprimirEstadoCuenta()">
                            <i class="fa fa-print mr-1"></i> Imprimir Estado de Cuenta
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm-premium" @click="cerrarEstadoCuenta()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Abono -->
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalEditarAbono}" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 3100 !important;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title font-weight-bold">Editar Abono #{{ abonoEditarForm.id }}</h5>
                        <button type="button" class="close text-white" @click="cerrarModalEditarAbono()">&times;</button>
                    </div>
                    <div class="modal-body p-4">
                        <form @submit.prevent="actualizarAbono()">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Monto del Abono <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" class="form-control text-right font-weight-bold text-primary" v-model.number="abonoEditarForm.monto" min="0.01" step="any" required>
                                </div>
                                <small class="text-muted">Monto original: ${{ parseFloat(abonoEditarForm.montoOriginal || 0).toLocaleString() }} | Saldo disponible cuenta: ${{ parseFloat(abonoEditarForm.saldoDisponibleCuenta || 0).toLocaleString() }}</small>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Fecha de Pago <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" v-model="abonoEditarForm.fecha" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold">Observaciones</label>
                                <input type="text" class="form-control" v-model="abonoEditarForm.observaciones" placeholder="Ej. Pago efectivo, transferencia Bancolombia">
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold">Soporte de Pago (PDF, JPG, PNG)</label>
                                <div class="custom-file custom-file-sm">
                                    <input type="file" class="custom-file-input" id="abonoEditarSoporteFile" ref="abonoEditarSoporteFile" accept=".pdf,image/*" @change="onAbonoEditarFileSelected">
                                    <label class="custom-file-label" for="abonoEditarSoporteFile" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 80px;">{{ abonoEditarFileSelectedName }}</label>
                                </div>
                                <small class="text-muted d-block mt-1" v-if="abonoEditarForm.soporte">
                                    Soporte actual: <a :href="'/uploads/abonos_cuentas_por_pagar/' + abonoEditarForm.soporte" target="_blank">{{ abonoEditarForm.soporte }}</a>
                                </small>
                            </div>
                            <div class="text-right mt-3">
                                <button type="button" class="btn btn-secondary btn-sm-premium mr-2" @click="cerrarModalEditarAbono()">Cancelar</button>
                                <button type="submit" class="btn btn-info btn-sm-premium" :disabled="loadingEditarAbono">
                                    <span v-if="loadingEditarAbono"><i class="fa fa-spinner fa-spin mr-1"></i> Guardando...</span>
                                    <span v-else>Guardar Cambios</span>
                                </button>
                            </div>
                        </form>
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
            // Arrays
            arrayCuentas: [],
            operarias: [],
            proveedores: [],
            arrayDeudasProveedores: [],
            arrayCuentasContables: [],

            // Tab configuration & filters
            tabActiva: 'proveedores',
            vistaProveedores: 'cuadros',
            buscarProveedor: '',
            mostrarSoloConDeuda: true,

            // Filter attributes
            filtroBeneficiario: '',
            filtroEstado: '',
            buscar: '',
            fechaInicio: '',
            fechaFin: '',

            // Totals Metrics
            totalGeneral: 0,
            totalPendiente: 0,
            totalAbonado: 0,
            totalPagado: 0,

            // Pagination metadata
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 15,
                last_page: 1,
                from: 0,
                to: 0
            },
            offset: 3,

            // Modals & Forms flags
            modalForm: 0,
            modalAbono: 0,
            tipoAccion: 1, // 1: Crear, 2: Editar
            loadingForm: false,
            loadingAbono: false,

            // Selected model & forms payload
            cuentaSeleccionada: {},
            totalVencidas: 0,
            totalPorVencer: 0,
            originalSaldo: 0,
            originalProveedorId: 0,
            cupo_credito: 0.00,
            saldo_pendiente: 0.00,
            cupo_disponible: 0.00,
            tipoBeneficiarioForm: 'operaria', // 'operaria' or 'proveedor'
            soporte_file: null,
            fileSelectedName: 'Seleccionar archivo...',
            abonoFileSelectedName: 'Seleccionar archivo...',
            abonoGeneralFileSelectedName: 'Seleccionar archivo...',
            fechaInicioEstado: '',
            fechaFinEstado: '',
            modalEstado: 0,
            expandedAbonos: {},
            estadoCuentaData: {
                nombre: '',
                detalles: '',
                total_registrado: 0,
                total_pendiente: 0,
                total_pagado: 0,
                cuentas: []
            },
            cuentaForm: {
                id: 0,
                activo_id: '',
                proveedor_id: '',
                numero_factura: '',
                monto: 0,
                soporte: '',
                fecha: new Date().toISOString().slice(0, 10),
                fecha_vencimiento: '',
                estado: 'Pendiente',
                cantidad: 0,
                cantidad_entregada: 0,
                valor_unitario: 0,
                cuenta_id: ''
            },
            abonoForm: {
                cuenta_por_pagar_id: 0,
                monto: 0,
                fecha: new Date().toISOString().slice(0, 10),
                observaciones: '',
                soporte_file: null,
                metodo_pago: 'Banco'
            },
            mostrarFormNuevoItem: false,
            mostrarFormAbonoGeneral: false,
            loadingNuevoItem: false,
            loadingAbonoGeneral: false,
            nuevoItemForm: {
                numero_factura: '',
                monto: 0,
                fecha: new Date().toISOString().slice(0, 10),
                fecha_vencimiento: '',
                descripcion: '',
                cuenta_id: ''
            },
            abonoGeneralForm: {
                monto: 0,
                fecha: new Date().toISOString().slice(0, 10),
                observaciones: '',
                soporte_file: null,
                metodo_pago: 'Banco'
            },
            aceptarSobrecupo: false,
            aceptarSobrecupoEstado: false,
            modalEditarAbono: 0,
            loadingEditarAbono: false,
            abonoEditarFileSelectedName: 'Seleccionar archivo...',
            abonoEditarForm: {
                id: 0,
                monto: 0,
                montoOriginal: 0,
                saldoDisponibleCuenta: 0,
                fecha: '',
                observaciones: '',
                soporte: '',
                soporte_file: null,
                desdeEstado: false,
                tipoEstado: '',
                idEstado: 0
            }
        }
    },
    computed: {
        paginasCalculadas() {
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
        },
        cupoDisponibleCalculado() {
            if (this.tipoAccion === 2 && this.cuentaForm.proveedor_id === this.originalProveedorId) {
                return Math.max(0, this.cupo_credito - (this.saldo_pendiente - this.originalSaldo));
            }
            return this.cupo_disponible;
        },
        proveedoresFiltrados() {
            let me = this;
            return me.arrayDeudasProveedores.filter(prov => {
                let matchesSearch = true;
                if (me.buscarProveedor) {
                    let query = me.buscarProveedor.toLowerCase();
                    matchesSearch = (prov.nombre && prov.nombre.toLowerCase().includes(query)) ||
                                    (prov.num_documento && prov.num_documento.toLowerCase().includes(query));
                }
                let matchesDebt = true;
                if (me.mostrarSoloConDeuda) {
                    matchesDebt = prov.total_pendiente !== 0 || (prov.cuentas_detalle && prov.cuentas_detalle.length > 0);
                }
                return matchesSearch && matchesDebt;
            });
        }
    },
    methods: {
        listarCuentas(page) {
            let me = this;
            let activo_id = '';
            let proveedor_id = '';
            if (me.filtroBeneficiario) {
                if (me.filtroBeneficiario.startsWith('op-')) {
                    activo_id = me.filtroBeneficiario.substring(3);
                } else if (me.filtroBeneficiario.startsWith('prov-')) {
                    proveedor_id = me.filtroBeneficiario.substring(5);
                }
            }
            let url = `/cuentas-pagar?page=${page}&activo_id=${activo_id}&proveedor_id=${proveedor_id}&estado=${me.filtroEstado}&buscar=${me.buscar}&fecha_inicio=${me.fechaInicio}&fecha_fin=${me.fechaFin}`;
            
            axios.get(url).then(response => {
                let respuesta = response.data;
                me.arrayCuentas = respuesta.cuentas.data;
                me.pagination = respuesta.pagination;
                
                // Recalculate summary metrics based on paginated view (or sum them)
                me.calcularResumenGlobal();
            }).catch(error => {
                console.error(error);
                Swal.fire('Error', 'No se pudieron cargar las cuentas por pagar.', 'error');
            });
        },
        calcularResumenGlobal() {
            let me = this;
            // Let's fetch all records for calculating totals dynamically to ensure correct metrics
            let url = `/cuentas-pagar?per_page=10000`;
            axios.get(url).then(response => {
                let cuentas = response.data.cuentas.data || [];
                me.totalGeneral = 0;
                me.totalPendiente = 0;
                me.totalAbonado = 0;
                me.totalPagado = 0;
                me.totalVencidas = 0;
                me.totalPorVencer = 0;

                const hoy = new Date();
                hoy.setHours(0,0,0,0);

                const sieteDias = new Date();
                sieteDias.setDate(hoy.getDate() + 7);
                sieteDias.setHours(23,59,59,999);

                // Group by provider and operaria (beneficiaries) for summary tab
                let deudasBeneficiarios = {};
                if (me.proveedores && me.proveedores.length > 0) {
                    me.proveedores.forEach(prov => {
                        deudasBeneficiarios['prov-' + prov.id] = {
                            id: prov.id,
                            tipo: 'proveedor',
                            nombre: prov.nombre,
                            num_documento: prov.num_documento,
                            telefono: prov.telefono || 'Sin teléfono',
                            email: prov.email || 'Sin correo',
                            cupo_credito: parseFloat(prov.cupo_credito) || 0,
                            total_registrado: 0,
                            total_pendiente: 0,
                            total_pagado: 0,
                            cantidad_cuentas: 0,
                            cuentas_vencidas: 0,
                            cuentas_detalle: []
                        };
                    });
                }
                if (me.operarias && me.operarias.length > 0) {
                    me.operarias.forEach(op => {
                        deudasBeneficiarios['op-' + op.id] = {
                            id: op.id,
                            tipo: 'operaria',
                            nombre: op.activo,
                            num_documento: '',
                            telefono: 'Sin teléfono',
                            email: 'Sin correo',
                            cupo_credito: 0,
                            total_registrado: 0,
                            total_pendiente: 0,
                            total_pagado: 0,
                            cantidad_cuentas: 0,
                            cuentas_vencidas: 0,
                            cuentas_detalle: []
                        };
                    });
                }

                cuentas.forEach(c => {
                    let cantEnt = parseInt(c.cantidad_entregada) || 0;
                    let cantTot = parseInt(c.cantidad) || 0;
                    let valUnit = parseFloat(c.valor_unitario) || 0;
                    let isPartialDelivery = (cantEnt > 0 && cantEnt < cantTot);

                    let monto = parseFloat(c.monto) || 0;
                    let totalAbonado = parseFloat(c.total_abonado);
                    if (isNaN(totalAbonado)) {
                        if (c.abonos && Array.isArray(c.abonos)) {
                            totalAbonado = c.abonos.reduce((acc, a) => acc + (parseFloat(a.monto) || 0), 0);
                        } else {
                            totalAbonado = monto - (parseFloat(c.saldo) || 0);
                        }
                    }

                    let saldo = parseFloat(c.saldo) || 0;

                    if (isPartialDelivery || c.estado === 'En Espera') {
                        if (cantEnt > 0) {
                            let valorEntregado = cantEnt * valUnit;
                            let saldoEntregado = Math.max(0, valorEntregado - totalAbonado);
                            me.totalGeneral += valorEntregado;
                            me.totalAbonado += totalAbonado;
                            me.totalPendiente += saldoEntregado;

                            monto = valorEntregado;
                            saldo = saldoEntregado;
                        }
                    } else {
                        me.totalGeneral += monto;
                        me.totalPendiente += saldo;
                        me.totalAbonado += totalAbonado;
                        if (c.estado === 'Pagado') {
                            me.totalPagado += monto;
                        }

                        if (c.estado !== 'Pagado' && c.fecha_vencimiento) {
                            const venc = new Date(c.fecha_vencimiento + 'T00:00:00');
                            if (venc < hoy) {
                                me.totalVencidas += saldo;
                            } else if (venc >= hoy && venc <= sieteDias) {
                                me.totalPorVencer += saldo;
                            }
                        }
                    }

                    // Grouping for beneficiaries (providers or operarias)
                    let key = null;
                    if (c.proveedor_id) {
                        key = 'prov-' + c.proveedor_id;
                        if (!deudasBeneficiarios[key]) {
                            deudasBeneficiarios[key] = {
                                id: c.proveedor_id,
                                tipo: 'proveedor',
                                nombre: c.proveedor ? c.proveedor.nombre : 'Proveedor Desconocido',
                                num_documento: c.proveedor ? c.proveedor.num_documento : '',
                                telefono: (c.proveedor && c.proveedor.telefono) || 'Sin teléfono',
                                email: (c.proveedor && c.proveedor.email) || 'Sin correo',
                                cupo_credito: (c.proveedor && parseFloat(c.proveedor.cupo_credito)) || 0,
                                total_registrado: 0,
                                total_pendiente: 0,
                                total_pagado: 0,
                                cantidad_cuentas: 0,
                                cuentas_vencidas: 0,
                                cuentas_detalle: []
                            };
                        } else if (c.proveedor) {
                            deudasBeneficiarios[key].telefono = c.proveedor.telefono || deudasBeneficiarios[key].telefono;
                            deudasBeneficiarios[key].email = c.proveedor.email || deudasBeneficiarios[key].email;
                            deudasBeneficiarios[key].cupo_credito = parseFloat(c.proveedor.cupo_credito) || deudasBeneficiarios[key].cupo_credito;
                        }
                    } else if (c.activo_id) {
                        key = 'op-' + c.activo_id;
                        if (!deudasBeneficiarios[key]) {
                            deudasBeneficiarios[key] = {
                                id: c.activo_id,
                                tipo: 'operaria',
                                nombre: c.activo ? c.activo.activo : 'Operaria Desconocida',
                                num_documento: '',
                                telefono: 'Sin teléfono',
                                email: 'Sin correo',
                                cupo_credito: 0,
                                total_registrado: 0,
                                total_pendiente: 0,
                                total_pagado: 0,
                                cantidad_cuentas: 0,
                                cuentas_vencidas: 0,
                                cuentas_detalle: []
                            };
                        } else if (c.activo) {
                            deudasBeneficiarios[key].nombre = c.activo.activo || deudasBeneficiarios[key].nombre;
                        }
                    }

                    if (key) {
                        if (isPartialDelivery || c.estado === 'En Espera') {
                            if (cantEnt > 0) {
                                let valorEntregado = cantEnt * valUnit;
                                let saldoEntregado = Math.max(0, valorEntregado - totalAbonado);
                                deudasBeneficiarios[key].total_registrado += valorEntregado;
                                deudasBeneficiarios[key].total_pagado += totalAbonado;
                                deudasBeneficiarios[key].total_pendiente += saldoEntregado;
                            }
                        } else {
                            deudasBeneficiarios[key].total_registrado += monto;
                            deudasBeneficiarios[key].total_pendiente += saldo;
                            deudasBeneficiarios[key].total_pagado += totalAbonado;
                        }

                        if (saldo !== 0) {
                            if (saldo > 0) {
                                deudasBeneficiarios[key].cantidad_cuentas += 1;
                            }
                            deudasBeneficiarios[key].cuentas_detalle.push({
                                id: c.id,
                                fecha: c.fecha,
                                fecha_vencimiento: c.fecha_vencimiento,
                                numero_factura: c.numero_factura,
                                ordentrabajo_id: c.ordentrabajo_id,
                                orden: c.orden,
                                monto: monto,
                                saldo: saldo,
                                cantidad: c.cantidad,
                                cantidad_entregada: c.cantidad_entregada,
                                valor_unitario: c.valor_unitario,
                                total_abonado: totalAbonado,
                                estado: c.estado,
                                descripcion: c.descripcion,
                                proveedor_id: c.proveedor_id,
                                proveedor: c.proveedor,
                                activo_id: c.activo_id,
                                activo: c.activo,
                                abonos: c.abonos || [],
                                soporte: c.soporte
                            });
                            if (c.estado !== 'En Espera' && c.fecha_vencimiento && saldo > 0) {
                                const venc = new Date(c.fecha_vencimiento + 'T00:00:00');
                                if (venc < hoy) {
                                    deudasBeneficiarios[key].cuentas_vencidas += 1;
                                }
                            }
                        }
                    }
                });

                me.arrayDeudasProveedores = Object.values(deudasBeneficiarios);
            }).catch(err => console.error(err));
        },
        getOperarias() {
            let me = this;
            axios.get('/activo/portipo?tipo=Terminado').then(response => {
                me.operarias = response.data;
            }).catch(error => console.error(error));
        },
        getProveedores() {
            let me = this;
            axios.get('/proveedor/selectProveedor').then(response => {
                me.proveedores = response.data.proveedores;
                if (me.arrayCuentas.length > 0 || me.totalGeneral > 0) {
                    me.calcularResumenGlobal();
                }
            }).catch(error => console.error(error));
        },
        cambiarPagina(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.pagination.current_page = page;
                this.listarCuentas(page);
            }
        },
        limpiarFiltros() {
            this.filtroBeneficiario = '';
            this.filtroEstado = '';
            this.buscar = '';
            this.fechaInicio = '';
            this.fechaFin = '';
            this.listarCuentas(1);
        },
        abrirModalCrear() {
            this.tipoAccion = 1;
            this.tipoBeneficiarioForm = 'operaria';
            this.cuentaForm = {
                id: 0,
                activo_id: '',
                proveedor_id: '',
                numero_factura: '',
                monto: 0,
                soporte: '',
                fecha: new Date().toISOString().slice(0, 10),
                fecha_vencimiento: '',
                estado: 'Pendiente',
                cantidad: 0,
                cantidad_entregada: 0,
                valor_unitario: 0,
                cuenta_id: ''
            };
            this.soporte_file = null;
            this.fileSelectedName = 'Seleccionar archivo...';
            if (this.$refs.soporteFile) {
                this.$refs.soporteFile.value = '';
            }
            this.originalSaldo = 0;
            this.originalProveedorId = 0;
            this.cupo_credito = 0;
            this.saldo_pendiente = 0;
            this.cupo_disponible = 0;
            this.aceptarSobrecupo = false;
            this.modalForm = 1;
        },
        abrirModalEditar(cuenta) {
            this.tipoAccion = 2;
            if (cuenta.proveedor_id) {
                this.tipoBeneficiarioForm = 'proveedor';
            } else {
                this.tipoBeneficiarioForm = 'operaria';
            }
            this.cuentaForm = {
                id: cuenta.id,
                activo_id: cuenta.activo_id || '',
                proveedor_id: cuenta.proveedor_id || '',
                numero_factura: cuenta.numero_factura || '',
                monto: parseFloat(cuenta.monto) || 0,
                soporte: cuenta.soporte || '',
                fecha: cuenta.fecha,
                fecha_vencimiento: cuenta.fecha_vencimiento || '',
                estado: cuenta.estado,
                cantidad: cuenta.cantidad || 0,
                cantidad_entregada: cuenta.cantidad_entregada || 0,
                valor_unitario: parseFloat(cuenta.valor_unitario) || 0,
                cuenta_id: cuenta.cuenta_id || ''
            };
            this.soporte_file = null;
            this.fileSelectedName = cuenta.soporte ? 'Cambiar soporte...' : 'Seleccionar archivo...';
            if (this.$refs.soporteFile) {
                this.$refs.soporteFile.value = '';
            }
            this.originalSaldo = parseFloat(cuenta.saldo) || 0;
            this.originalProveedorId = cuenta.proveedor_id || 0;
            this.cupo_credito = 0;
            this.saldo_pendiente = 0;
            this.cupo_disponible = 0;
            this.aceptarSobrecupo = false;
            if (cuenta.proveedor_id) {
                this.obtenerCreditoProveedor();
            }
            this.modalForm = 1;
        },
        cerrarModalForm() {
            this.modalForm = 0;
            this.soporte_file = null;
            this.fileSelectedName = 'Seleccionar archivo...';
            if (this.$refs.soporteFile) {
                this.$refs.soporteFile.value = '';
            }
            this.aceptarSobrecupo = false;
        },
        onTipoBeneficiarioChange() {
            this.cuentaForm.activo_id = '';
            this.cuentaForm.proveedor_id = '';
            this.cupo_credito = 0;
            this.saldo_pendiente = 0;
            this.cupo_disponible = 0;
        },
        obtenerCreditoProveedor() {
            let me = this;
            let provId = me.cuentaForm.proveedor_id;
            if (!provId) {
                me.cupo_credito = 0;
                me.saldo_pendiente = 0;
                me.cupo_disponible = 0;
                return;
            }
            axios.get('/proveedor/obtenerEstadoCredito?id=' + provId)
                .then(response => {
                    let data = response.data;
                    me.cupo_credito = parseFloat(data.cupo_credito);
                    me.saldo_pendiente = parseFloat(data.saldo_pendiente);
                    me.cupo_disponible = parseFloat(data.cupo_disponible);
                })
                .catch(error => {
                    console.error(error);
                });
        },
        obtenerClaseVencimiento(fechaVencimiento) {
            if (!fechaVencimiento) return '';
            const hoy = new Date();
            hoy.setHours(0,0,0,0);
            const venc = new Date(fechaVencimiento + 'T00:00:00');
            const diffTime = venc - hoy;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 0) {
                return 'vencido';
            } else if (diffDays === 0) {
                return 'vence-hoy';
            } else if (diffDays <= 5) {
                return 'vence-pronto';
            } else {
                return 'vence-ok';
            }
        },
        obtenerTextoVencimiento(fechaVencimiento) {
            if (!fechaVencimiento) return '';
            const hoy = new Date();
            hoy.setHours(0,0,0,0);
            const venc = new Date(fechaVencimiento + 'T00:00:00');
            const diffTime = venc - hoy;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 0) {
                const absDays = Math.abs(diffDays);
                return `Vencido hace ${absDays} ${absDays === 1 ? 'día' : 'días'}`;
            } else if (diffDays === 0) {
                return 'Vence hoy';
            } else if (diffDays === 1) {
                return 'Vence mañana';
            } else {
                return `Vence en ${diffDays} días`;
            }
        },
        calcularTotalForm() {
            // Deprecated
        },
        onFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                this.soporte_file = file;
                this.fileSelectedName = file.name;
            } else {
                this.soporte_file = null;
                this.fileSelectedName = 'Seleccionar archivo...';
            }
        },
        onAbonoFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                this.abonoForm.soporte_file = file;
                this.abonoFileSelectedName = file.name;
            } else {
                this.abonoForm.soporte_file = null;
                this.abonoFileSelectedName = 'Seleccionar archivo...';
            }
        },
        onAbonoGeneralFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                this.abonoGeneralForm.soporte_file = file;
                this.abonoGeneralFileSelectedName = file.name;
            } else {
                this.abonoGeneralForm.soporte_file = null;
                this.abonoGeneralFileSelectedName = 'Seleccionar archivo...';
            }
        },
        abrirEstadoCuenta(tipo, id, usarFechas = false) {
            let me = this;
            let url = `/cuentas-pagar/estado-cuenta?tipo=${tipo}&id=${id}`;
            if (usarFechas && me.fechaInicioEstado && me.fechaFinEstado) {
                url += `&fecha_inicio=${me.fechaInicioEstado}&fecha_fin=${me.fechaFinEstado}`;
            }
            axios.get(url)
                .then(response => {
                    me.estadoCuentaData = response.data;
                    me.estadoCuentaData.tipo = tipo;
                    me.estadoCuentaData.id = id;
                    me.modalEstado = 1;
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo obtener el estado de cuenta.', 'error');
                });
        },
        abrirAbonoGeneralDesdeResumen(benef) {
            let me = this;
            axios.get(`/cuentas-pagar/estado-cuenta?tipo=${benef.tipo}&id=${benef.id}`)
                .then(response => {
                    me.estadoCuentaData = response.data;
                    me.estadoCuentaData.tipo = benef.tipo;
                    me.estadoCuentaData.id = benef.id;
                    me.mostrarFormAbonoGeneral = true;
                    me.mostrarFormNuevoItem = false;
                    me.abonoGeneralForm = {
                        monto: benef.total_pendiente,
                        fecha: new Date().toISOString().slice(0, 10),
                        observaciones: 'Pago general desde resumen',
                        soporte_file: null,
                        metodo_pago: 'Banco'
                    };
                    me.abonoGeneralFileSelectedName = 'Seleccionar archivo...';
                    me.modalEstado = 1;
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo abrir el formulario de pago.', 'error');
                });
        },
        cerrarEstadoCuenta() {
            this.modalEstado = 0;
            this.fechaInicioEstado = '';
            this.fechaFinEstado = '';
            this.mostrarFormNuevoItem = false;
            this.mostrarFormAbonoGeneral = false;
            this.aceptarSobrecupoEstado = false;
        },
        toggleFormNuevoItem() {
            this.mostrarFormNuevoItem = !this.mostrarFormNuevoItem;
            this.mostrarFormAbonoGeneral = false;
            this.aceptarSobrecupoEstado = false;
            if (this.mostrarFormNuevoItem) {
                this.nuevoItemForm = {
                    numero_factura: '',
                    monto: 0,
                    fecha: new Date().toISOString().slice(0, 10),
                    fecha_vencimiento: '',
                    descripcion: ''
                };
            }
        },
        toggleFormAbonoGeneral() {
            this.mostrarFormAbonoGeneral = !this.mostrarFormAbonoGeneral;
            this.mostrarFormNuevoItem = false;
            if (this.mostrarFormAbonoGeneral) {
                this.abonoGeneralForm = {
                    monto: 0,
                    fecha: new Date().toISOString().slice(0, 10),
                    observaciones: '',
                    soporte_file: null,
                    metodo_pago: 'Banco'
                };
                this.abonoGeneralFileSelectedName = 'Seleccionar archivo...';
                this.$nextTick(() => {
                    if (this.$refs.abonoGeneralSoporteFile) {
                        this.$refs.abonoGeneralSoporteFile.value = '';
                    }
                });
            }
        },
        guardarNuevoItemDesdeEstado() {
            let me = this;
            if (me.nuevoItemForm.monto <= 0) {
                Swal.fire('Error', 'El monto debe ser mayor a 0.', 'error');
                return;
            }

            // Client-side credit validation
            if (me.estadoCuentaData.tipo === 'proveedor' && me.estadoCuentaData.cupo_credito > 0) {
                let cupoDisponibleEstado = me.estadoCuentaData.cupo_credito - me.estadoCuentaData.total_pendiente;
                if (me.nuevoItemForm.monto > cupoDisponibleEstado && !me.aceptarSobrecupoEstado) {
                    Swal.fire('Error', 'No se puede guardar este ítem porque supera el cupo de crédito disponible del proveedor. Si desea permitirlo, marque la casilla "Aceptar sobrecupo".', 'error');
                    return;
                }
            }

            me.loadingNuevoItem = true;

            let activo_id = me.estadoCuentaData.tipo === 'operaria' ? me.estadoCuentaData.id : '';
            let proveedor_id = me.estadoCuentaData.tipo === 'proveedor' ? me.estadoCuentaData.id : '';

            let formData = new FormData();
            formData.append('id', 0);
            formData.append('activo_id', activo_id);
            formData.append('proveedor_id', proveedor_id);
            formData.append('numero_factura', me.nuevoItemForm.numero_factura || '');
            formData.append('monto', me.nuevoItemForm.monto);
            formData.append('fecha', me.nuevoItemForm.fecha);
            formData.append('fecha_vencimiento', me.nuevoItemForm.fecha_vencimiento || '');
            formData.append('descripcion', me.nuevoItemForm.descripcion || '');
            formData.append('cuenta_id', me.nuevoItemForm.cuenta_id || '');
            formData.append('aceptar_sobrecupo', me.aceptarSobrecupoEstado ? 1 : 0);

            axios.post('/cuentas-pagar/registrar', formData)
                .then(response => {
                    me.loadingNuevoItem = false;
                    Swal.fire('Guardado', 'El ítem se ha registrado correctamente.', 'success');
                    me.mostrarFormNuevoItem = false;
                    me.abrirEstadoCuenta(me.estadoCuentaData.tipo, me.estadoCuentaData.id, !!(me.fechaInicioEstado && me.fechaFinEstado));
                    me.listarCuentas(me.pagination.current_page);
                })
                .catch(error => {
                    me.loadingNuevoItem = false;
                    console.error(error);
                    let errorMsg = 'Error al guardar el ítem.';
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMsg = error.response.data.error;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                });
        },
        guardarAbonoGeneral() {
            let me = this;
            if (me.abonoGeneralForm.monto <= 0) {
                Swal.fire('Error', 'El monto debe ser mayor a 0.', 'error');
                return;
            }

            me.loadingAbonoGeneral = true;

            let formData = new FormData();
            formData.append('tipo', me.estadoCuentaData.tipo);
            formData.append('id', me.estadoCuentaData.id);
            formData.append('monto', me.abonoGeneralForm.monto);
            formData.append('fecha', me.abonoGeneralForm.fecha);
            formData.append('observaciones', me.abonoGeneralForm.observaciones || '');
            formData.append('metodo_pago', me.abonoGeneralForm.metodo_pago || 'Banco');
            if (me.abonoGeneralForm.soporte_file) {
                formData.append('soporte_file', me.abonoGeneralForm.soporte_file);
            }

            axios.post('/cuentas-pagar/abono-general', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    me.loadingAbonoGeneral = false;
                    Swal.fire('Abono Aplicado', 'El abono general se ha registrado y distribuido correctamente.', 'success');
                    me.mostrarFormAbonoGeneral = false;
                    me.abonoGeneralForm.soporte_file = null;
                    me.abonoGeneralFileSelectedName = 'Seleccionar archivo...';
                    
                    me.abrirEstadoCuenta(me.estadoCuentaData.tipo, me.estadoCuentaData.id, !!(me.fechaInicioEstado && me.fechaFinEstado));
                    me.listarCuentas(me.pagination.current_page);
                })
                .catch(error => {
                    me.loadingAbonoGeneral = false;
                    console.error(error);
                    let errorMsg = 'Error al registrar el abono general.';
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMsg = error.response.data.error;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                });
        },
        consultarEstadoCuentaConFiltro() {
            if (!this.fechaInicioEstado || !this.fechaFinEstado) {
                Swal.fire('Atención', 'Debe seleccionar Fecha Inicio y Fecha Fin para buscar.', 'warning');
                return;
            }
            this.abrirEstadoCuenta(this.estadoCuentaData.tipo, this.estadoCuentaData.id, true);
        },
        limpiarFiltroEstadoCuenta() {
            this.fechaInicioEstado = '';
            this.fechaFinEstado = '';
            this.abrirEstadoCuenta(this.estadoCuentaData.tipo, this.estadoCuentaData.id, false);
        },
        imprimirEstadoCuenta() {
            const printContent = document.getElementById('print-area').innerHTML;
            const printWindow = window.open('', '_blank', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Estado de Cuenta</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
            printWindow.document.write('<style>body { padding: 20px; font-family: sans-serif; } .badge-vencimiento { font-size: 0.7rem; font-weight: bold; } .no-print { display: none !important; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        },
        guardarCuenta() {
            let me = this;

            // Client-side credit validation
            if (me.tipoBeneficiarioForm === 'proveedor' && me.cupo_credito > 0) {
                let totalCuenta = me.cuentaForm.monto;
                if (totalCuenta > me.cupoDisponibleCalculado && !me.aceptarSobrecupo) {
                    Swal.fire('Error', 'No se puede guardar esta cuenta porque supera el cupo de crédito disponible del proveedor. Si desea permitirlo, marque la casilla "Aceptar sobrecupo".', 'error');
                    return;
                }
            }

            me.loadingForm = true;
            
            if (me.tipoBeneficiarioForm === 'operaria') {
                me.cuentaForm.proveedor_id = '';
            } else {
                me.cuentaForm.activo_id = '';
            }
            
            // Build FormData
            let formData = new FormData();
            formData.append('id', me.cuentaForm.id);
            formData.append('activo_id', me.cuentaForm.activo_id || '');
            formData.append('proveedor_id', me.cuentaForm.proveedor_id || '');
            formData.append('numero_factura', me.cuentaForm.numero_factura || '');
            formData.append('monto', me.cuentaForm.monto || 0);
            formData.append('fecha', me.cuentaForm.fecha);
            formData.append('fecha_vencimiento', me.cuentaForm.fecha_vencimiento || '');
            formData.append('cuenta_id', me.cuentaForm.cuenta_id || '');
            formData.append('aceptar_sobrecupo', me.aceptarSobrecupo ? 1 : 0);
            formData.append('cantidad', me.cuentaForm.cantidad || 1);
            formData.append('valor_unitario', me.cuentaForm.valor_unitario || 0);
            
            if (me.soporte_file) {
                formData.append('soporte_file', me.soporte_file);
            }

            if (me.tipoAccion === 2) {
                formData.append('_method', 'PUT');
                formData.append('estado', me.cuentaForm.estado || 'Pendiente');
            }

            let url = me.tipoAccion === 1 ? '/cuentas-pagar/registrar' : `/cuentas-pagar/actualizar/${me.cuentaForm.id}`;
            
            axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                me.loadingForm = false;
                me.modalForm = 0;
                me.soporte_file = null;
                me.fileSelectedName = 'Seleccionar archivo...';
                if (me.$refs.soporteFile) {
                    me.$refs.soporteFile.value = '';
                }
                Swal.fire({
                    title: '¡Operación Exitosa!',
                    text: me.tipoAccion === 1 ? 'Cuenta por pagar registrada correctamente.' : 'Cuenta por pagar actualizada.',
                    icon: 'success',
                    timer: 2000
                });
                me.listarCuentas(me.pagination.current_page);
                if (me.modalEstado) {
                    me.abrirEstadoCuenta(me.estadoCuentaData.tipo, me.estadoCuentaData.id, !!(me.fechaInicioEstado && me.fechaFinEstado));
                }
            }).catch(error => {
                me.loadingForm = false;
                console.error(error);
                let targetEl = document.querySelector('.mostrar') || 'body';
                let msg = (error.response && error.response.data && (error.response.data.error || error.response.data.message)) || 'Ocurrió un error al guardar.';
                Swal.fire({ title: 'Error', text: msg, icon: 'error', target: targetEl });
            });
        },
        eliminarCuenta(cuenta) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar esta cuenta?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.value) {
                    axios.delete(`/cuentas-pagar/eliminar/${cuenta.id}`).then(response => {
                        Swal.fire('¡Eliminado!', 'La cuenta por pagar ha sido eliminada.', 'success');
                        me.listarCuentas(me.pagination.current_page);
                        if (me.modalEstado) {
                            me.abrirEstadoCuenta(me.estadoCuentaData.tipo, me.estadoCuentaData.id, !!(me.fechaInicioEstado && me.fechaFinEstado));
                        }
                    }).catch(error => {
                        console.error(error);
                        let msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo eliminar.';
                        Swal.fire('Error', msg, 'error');
                    });
                }
            });
        },
        abrirModalAbono(cuenta) {
            this.cuentaSeleccionada = cuenta;
            let defaultMonto = cuenta.saldo;
            if (cuenta.estado === 'En Espera') {
                let valorEntregado = (cuenta.cantidad_entregada || 0) * (cuenta.valor_unitario || 0);
                let pagado = (cuenta.monto || 0) - (cuenta.saldo || 0);
                defaultMonto = Math.max(0, valorEntregado - pagado);
            }
            this.abonoForm = {
                cuenta_por_pagar_id: cuenta.id,
                monto: defaultMonto,
                fecha: new Date().toISOString().slice(0, 10),
                observaciones: '',
                soporte_file: null,
                metodo_pago: 'Banco'
            };
            this.abonoFileSelectedName = 'Seleccionar archivo...';
            if (this.$refs.abonoSoporteFile) {
                this.$refs.abonoSoporteFile.value = '';
            }
            this.modalAbono = 1;
        },
        cerrarModalAbono() {
            this.modalAbono = 0;
            this.abonoForm.soporte_file = null;
            this.abonoFileSelectedName = 'Seleccionar archivo...';
            if (this.$refs.abonoSoporteFile) {
                this.$refs.abonoSoporteFile.value = '';
            }
        },
        guardarAbono() {
            let me = this;
            me.loadingAbono = true;

            let formData = new FormData();
            formData.append('cuenta_por_pagar_id', me.abonoForm.cuenta_por_pagar_id);
            formData.append('monto', me.abonoForm.monto);
            formData.append('fecha', me.abonoForm.fecha);
            formData.append('observaciones', me.abonoForm.observaciones || '');
            formData.append('metodo_pago', me.abonoForm.metodo_pago || 'Banco');
            if (me.abonoForm.soporte_file) {
                formData.append('soporte_file', me.abonoForm.soporte_file);
            }

            axios.post('/cuentas-pagar/abono', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                me.loadingAbono = false;
                
                // Refresh local fields
                let updatedCuenta = response.data.cuenta;
                me.cuentaSeleccionada.saldo = updatedCuenta.saldo;
                me.cuentaSeleccionada.estado = updatedCuenta.estado;
                
                // Clear file input
                me.abonoForm.soporte_file = null;
                me.abonoFileSelectedName = 'Seleccionar archivo...';
                if (me.$refs.abonoSoporteFile) {
                    me.$refs.abonoSoporteFile.value = '';
                }

                // Re-fetch abonos list
                axios.get(`/cuentas-pagar?buscar=${me.cuentaSeleccionada.id}`).then(res => {
                    let matching = res.data.cuentas.data.find(item => item.id === me.cuentaSeleccionada.id);
                    if (matching) {
                        me.cuentaSeleccionada.abonos = matching.abonos;
                    }
                });

                Swal.fire({
                    title: '¡Abono Registrado!',
                    text: 'El pago ha sido acreditado exitosamente.',
                    icon: 'success',
                    timer: 2000
                });
                
                me.abonoForm.monto = me.cuentaSeleccionada.saldo;
                me.abonoForm.observaciones = '';
                
                me.listarCuentas(me.pagination.current_page);
            }).catch(error => {
                me.loadingAbono = false;
                console.error(error);
                let msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo registrar el abono.';
                Swal.fire('Error', msg, 'error');
            });
        },
        descargarPDFAbono(id) {
            window.open(`/cuentas-pagar/abono/pdf/${id}`, '_blank');
        },
        eliminarAbono(abono) {
            let me = this;
            let targetEl = document.querySelector('.mostrar') || 'body';
            Swal.fire({
                title: '¿Está seguro de eliminar este abono?',
                text: "El monto correspondiente se sumará nuevamente al saldo pendiente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar abono',
                target: targetEl
            }).then((result) => {
                if (result.value) {
                    axios.delete(`/cuentas-pagar/abono/${abono.id}`).then(response => {
                        let updatedCuenta = response.data.cuenta;
                        me.cuentaSeleccionada.saldo = updatedCuenta.saldo;
                        me.cuentaSeleccionada.estado = updatedCuenta.estado;
                        
                        // Remove abono locally
                        me.cuentaSeleccionada.abonos = me.cuentaSeleccionada.abonos.filter(item => item.id !== abono.id);

                        Swal.fire({ title: '¡Eliminado!', text: 'El abono ha sido reversado.', icon: 'success', target: targetEl });
                        me.abonoForm.monto = me.cuentaSeleccionada.saldo;
                        
                        me.listarCuentas(me.pagination.current_page);
                    }).catch(error => {
                        console.error(error);
                        let msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo reversar el abono.';
                        Swal.fire({ title: 'Error', text: msg, icon: 'error', target: targetEl });
                    });
                }
            });
        },
        onAbonoEditarFileSelected(event) {
            const file = event.target.files[0];
            if (file) {
                this.abonoEditarForm.soporte_file = file;
                this.abonoEditarFileSelectedName = file.name;
            } else {
                this.abonoEditarForm.soporte_file = null;
                this.abonoEditarFileSelectedName = 'Seleccionar archivo...';
            }
        },
        abrirEditarAbono(ab) {
            let maxMonto = parseFloat(ab.monto) + parseFloat(this.cuentaSeleccionada.saldo || 0);
            
            this.abonoEditarForm = {
                id: ab.id,
                monto: parseFloat(ab.monto),
                montoOriginal: parseFloat(ab.monto),
                saldoDisponibleCuenta: maxMonto,
                fecha: ab.fecha,
                observaciones: ab.observaciones || '',
                soporte: ab.soporte || '',
                soporte_file: null,
                desdeEstado: false
            };
            this.abonoEditarFileSelectedName = ab.soporte ? 'Cambiar soporte...' : 'Seleccionar archivo...';
            if (this.$refs.abonoEditarSoporteFile) {
                this.$refs.abonoEditarSoporteFile.value = '';
            }
            this.modalEditarAbono = 1;
        },
        cerrarModalEditarAbono() {
            this.modalEditarAbono = 0;
            this.abonoEditarForm = {
                id: 0,
                monto: 0,
                montoOriginal: 0,
                saldoDisponibleCuenta: 0,
                fecha: '',
                observaciones: '',
                soporte: '',
                soporte_file: null,
                desdeEstado: false,
                tipoEstado: '',
                idEstado: 0
            };
            this.abonoEditarFileSelectedName = 'Seleccionar archivo...';
            if (this.$refs.abonoEditarSoporteFile) {
                this.$refs.abonoEditarSoporteFile.value = '';
            }
        },
        abrirEditarAbonoDesdeEstado(ab) {
            let cuenta = ab.cuenta_por_pagar || ab.cuentaPorPagar || {};
            let saldo = parseFloat(cuenta.saldo || 0);
            let maxMonto = parseFloat(ab.monto) + saldo;

            this.abonoEditarForm = {
                id: ab.id,
                monto: parseFloat(ab.monto),
                montoOriginal: parseFloat(ab.monto),
                saldoDisponibleCuenta: maxMonto,
                fecha: ab.fecha,
                observaciones: ab.observaciones || '',
                soporte: ab.soporte || '',
                soporte_file: null,
                desdeEstado: true,
                tipoEstado: this.estadoCuentaData.tipo,
                idEstado: this.estadoCuentaData.id
            };
            this.abonoEditarFileSelectedName = ab.soporte ? 'Cambiar soporte...' : 'Seleccionar archivo...';
            if (this.$refs.abonoEditarSoporteFile) {
                this.$refs.abonoEditarSoporteFile.value = '';
            }
            this.modalEditarAbono = 1;
        },
        actualizarAbono() {
            let me = this;
            let form = me.abonoEditarForm;
            if (form.monto <= 0) {
                Swal.fire('Error', 'El monto debe ser mayor a 0.', 'error');
                return;
            }

            me.loadingEditarAbono = true;

            let formData = new FormData();
            formData.append('monto', form.monto);
            formData.append('fecha', form.fecha);
            formData.append('observaciones', form.observaciones || '');
            if (form.soporte_file) {
                formData.append('soporte_file', form.soporte_file);
            }

            axios.post(`/cuentas-pagar/abono/actualizar/${form.id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    me.loadingEditarAbono = false;
                    me.modalEditarAbono = 0;
                    Swal.fire('Abono Actualizado', 'El abono se ha modificado correctamente.', 'success');
                    
                    if (form.desdeEstado) {
                        me.abrirEstadoCuenta(form.tipoEstado, form.idEstado, !!(me.fechaInicioEstado && me.fechaFinEstado));
                    } else {
                        let updatedCuenta = response.data.cuenta;
                        me.cuentaSeleccionada.saldo = updatedCuenta.saldo;
                        me.cuentaSeleccionada.estado = updatedCuenta.estado;
                        me.abonoForm.monto = updatedCuenta.saldo;

                        axios.get(`/cuentas-pagar?buscar=${me.cuentaSeleccionada.id}`).then(res => {
                            let matching = res.data.cuentas.data.find(item => item.id === me.cuentaSeleccionada.id);
                            if (matching) {
                                me.cuentaSeleccionada.abonos = matching.abonos;
                            }
                        });
                    }

                    me.listarCuentas(me.pagination.current_page);
                })
                .catch(error => {
                    me.loadingEditarAbono = false;
                    console.error(error);
                    let errorMsg = 'Error al actualizar el abono.';
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMsg = error.response.data.error;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                });
        },
        eliminarAbonoDesdeEstado(ab) {
            let me = this;
            Swal.fire({
                title: '¿Está seguro de eliminar este abono?',
                text: "El monto correspondiente se sumará nuevamente al saldo pendiente de la cuenta.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar abono'
            }).then((result) => {
                if (result.value) {
                    axios.delete(`/cuentas-pagar/abono/${ab.id}`).then(response => {
                        Swal.fire('¡Eliminado!', 'El abono ha sido reversado.', 'success');
                        me.abrirEstadoCuenta(me.estadoCuentaData.tipo, me.estadoCuentaData.id, !!(me.fechaInicioEstado && me.fechaFinEstado));
                        me.listarCuentas(me.pagination.current_page);
                    }).catch(error => {
                        console.error(error);
                        let msg = (error.response && error.response.data && error.response.data.error) || 'No se pudo reversar el abono.';
                        Swal.fire('Error', msg, 'error');
                    });
                }
            });
        },
        toggleAbonos(cuentaId) {
            if (!this.expandedAbonos) {
                this.$set(this, 'expandedAbonos', {});
            }
            this.$set(this.expandedAbonos, cuentaId, !this.expandedAbonos[cuentaId]);
        },
        listarCuentasContables() {
            let me = this;
            axios.get('/cuentas-contables?es_detalle=1').then(response => {
                me.arrayCuentasContables = response.data.cuentas || [];
            }).catch(error => console.error(error));
        }
    },
    mounted() {
        this.listarCuentas(1);
        this.getOperarias();
        this.getProveedores();
        this.listarCuentasContables();
    }
}
</script>

<style scoped>
/* Glassmorphism & Custom Layout Styles */
.kpi-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 12px !important;
}
.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
}
.gradient-primary {
    background: linear-gradient(135deg, #1d976c 0%, #93f9b9 100%);
}
.gradient-danger {
    background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
}
.gradient-warning {
    background: linear-gradient(135deg, #f857a6 0%, #ff5858 100%);
}
.gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}
.gradient-mora {
    background: linear-gradient(135deg, #890596 0%, #f41b63 100%);
}
.gradient-vence {
    background: linear-gradient(135deg, #e65c00 0%, #f9d423 100%);
}
.badge-success-light {
    background-color: #e3f9eb;
    color: #24b057;
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: bold;
    display: inline-block;
}
.badge-vencimiento {
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 0.75rem;
    display: inline-block;
    text-align: center;
    line-height: 1.2;
}
.badge-vencimiento.vencido {
    background-color: #ffe8e6;
    color: #ff3b30;
    border: 1px solid rgba(255, 59, 48, 0.2);
}
.badge-vencimiento.vence-hoy {
    background-color: #fff4e5;
    color: #ff9500;
    border: 1px solid rgba(255, 149, 0, 0.2);
}
.badge-vencimiento.vence-pronto {
    background-color: #fef9e7;
    color: #d4ac0d;
    border: 1px solid rgba(212, 172, 13, 0.2);
}
.badge-vencimiento.vence-ok {
    background-color: #e8f4fd;
    color: #007aff;
    border: 1px solid rgba(0, 122, 255, 0.2);
}
.text-hover-underline:hover {
    text-decoration: underline !important;
}
.cursor-pointer {
    cursor: pointer !important;
}
.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-white-20 {
    background-color: rgba(255, 255, 255, 0.25);
}
.btn-sm-premium {
    padding: 6px 16px;
    font-size: 0.82rem;
    font-weight: bold;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.btn-sm-premium:hover {
    transform: translateY(-1px);
}
.btn-action {
    padding: 3px 8px;
    font-size: 0.8rem;
    border-radius: 4px;
}
.btn-xs-premium {
    padding: 1px 5px;
    font-size: 0.72rem;
    border-radius: 4px;
}
.border-bottom-light {
    border-bottom: 1px solid #f2f4f7;
}
.border-light-2 {
    border-color: #e9ecef !important;
}
.shadow-sm-premium {
    box-shadow: 0 4px 18px rgba(0,0,0,0.04) !important;
    border-radius: 12px !important;
}
.table-custom th {
    font-weight: 800;
    color: #495057;
    background-color: #fafbfc;
    border-bottom-width: 1px !important;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.table-custom td {
    font-size: 0.88rem;
    color: #495057;
    padding: 12px 8px !important;
}
.badge-status {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.72rem;
    font-weight: bold;
    text-transform: uppercase;
    display: inline-block;
}
.badge-status.pendiente {
    background-color: #ffe8e6;
    color: #ff3b30;
}
.badge-status.abonado {
    background-color: #fff4e5;
    color: #ff9500;
}
.badge-status.pagado {
    background-color: #e3f9eb;
    color: #24b057;
}
.badge-status.en.espera {
    background-color: #e2e3e5;
    color: #383d41;
}

/* Ensure SweetAlert modals appear ABOVE all Bootstrap modals and backdrops */
.swal2-container {
    z-index: 999999 !important;
}

/* Modal configuration override */
.mostrar {
    display: flex !important;
    align-items: flex-start !important;
    justify-content: center !important;
    opacity: 1 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0,0,0,0.6) !important;
    overflow-x: hidden !important;
    overflow-y: hidden !important;
    pointer-events: auto !important;
}
.mostrar .modal-dialog {
    margin: 10px auto !important;
    pointer-events: auto !important;
    max-height: calc(100vh - 20px) !important;
    height: calc(100vh - 20px) !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    min-height: auto !important;
}
.mostrar .modal-dialog::before {
    display: none !important;
    content: none !important;
}
.mostrar .modal-content {
    max-height: calc(100vh - 20px) !important;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}
.mostrar .modal-body {
    overflow-y: auto !important;
    flex: 1 1 auto !important;
}
@media (max-width: 576px) {
    .mostrar .modal-dialog {
        margin: 15px auto !important;
        max-height: calc(100vh - 30px);
    }
    .mostrar .modal-content {
        max-height: calc(100vh - 30px);
    }
}

/* Provider Debt Summary Styles */
.provider-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px !important;
}
.provider-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08) !important;
}
.alert-danger-light {
    background-color: #ffe8e6;
    color: #ff3b30;
    border: 1px solid rgba(255, 59, 48, 0.15);
}
</style>
