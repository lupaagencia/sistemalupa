<template>
    <div class="cartera-wrapper">
        <main class="main">
        <!-- Breadcrumb Moderno -->
        <div class="breadcrumb-container shadow-sm px-4 py-3 bg-white mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="/" class="text-muted"><i class="fa fa-home"></i> Escritorio</a></li>
                    <li class="breadcrumb-item active font-weight-bold" aria-current="page">Cartera</li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <!-- Main Card Container -->
            <div class="card border-0 shadow-lg rounded-xl overflow-hidden mb-5">
                    <div class="card-header bg-premium-header py-4 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="header-icon-box mr-3">
                                <i class="fa fa-briefcase text-white"></i>
                            </div>
                            <h3 class="text-white mb-0 font-weight-bold">Gestión de Cartera</h3>
                        </div>
                        <div class="header-actions">
                            <button class="btn btn-glass-light btn-sm" @click="imprimirOrden">
                                <i class="fa fa-print mr-1"></i> Imprimir Reporte
                            </button>
                        </div>
                    </div>

                    <!-- Modern Tabs Navigation -->
                    <div class="tabs-container bg-white border-bottom">
                        <ul class="nav nav-pills custom-pills px-4 py-2" id="carteraTab" role="tablist">
                             <li class="nav-item mr-2">
                                 <a class="nav-link py-2 px-4" :class="{'active': tab==5}" href="#" @click.prevent="cambiarTab(5)">
                                     <i class="fa fa-list-alt mr-2"></i> Estado de Cuenta
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a class="nav-link py-2 px-4" :class="{'active': tab==3}" href="#" @click.prevent="cambiarTab(3)">
                                     <i class="fa fa-history mr-2"></i> Historial de Pagos
                                 </a>
                             </li>
                        </ul>
                    </div>

                    <!-- Alertas y Filtros de Cartera (Completados / Entregados / Remisionado / No Recogidos / En Producción / Total) -->
                    <div v-if="tab != 3" class="bg-light px-3 pt-3 pb-2 border-bottom d-flex justify-content-center">
                        <div class="row w-100 justify-content-center" style="max-width: 1350px;">
                            <!-- Completados -->
                            <div v-if="alertaCompletadosCount > 0" class="col-md-2 col-6 mb-2 px-1">
                                <div class="alert shadow-sm mb-0 p-2 d-flex align-items-center" 
                                    :style="filtroEstadoCartera === 'Completados' ? 'background-color: #fff3cd; color: #856404; border-left: 5px solid #ffc107 !important; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(255, 193, 7, 0.35) !important; transform: translateY(-2px);' : 'background-color: #ffffff; color: #856404; border-left: 4px solid #ffe082 !important; cursor: pointer; transition: 0.2s; opacity: 0.9;'" 
                                    @click="seleccionarFiltroEstado('Completados')">
                                    <i class="fa fa-flag-checkered fa-2x mr-2 text-warning"></i>
                                    <div class="overflow-hidden">
                                        <h6 class="font-weight-bold mb-0 text-truncate" style="font-size: 0.85rem;">Completados ({{ alertaCompletadosCount }})</h6>
                                        <span class="font-weight-bold" style="font-size: 0.95rem;">${{ forNum(alertaCompletadosTotal) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Entregados -->
                            <div v-if="alertaEntregadosCount > 0" class="col-md-2 col-6 mb-2 px-1">
                                <div class="alert shadow-sm mb-0 p-2 d-flex align-items-center" 
                                    :style="filtroEstadoCartera === 'Entregados' ? 'background-color: #cce5ff; color: #004085; border-left: 5px solid #007bff !important; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(0, 123, 255, 0.35) !important; transform: translateY(-2px);' : 'background-color: #ffffff; color: #004085; border-left: 4px solid #90caf9 !important; cursor: pointer; transition: 0.2s; opacity: 0.9;'" 
                                    @click="seleccionarFiltroEstado('Entregados')">
                                    <i class="fa fa-check-circle fa-2x mr-2 text-primary"></i>
                                    <div class="overflow-hidden">
                                        <h6 class="font-weight-bold mb-0 text-truncate" style="font-size: 0.85rem;">Entregados ({{ alertaEntregadosCount }})</h6>
                                        <span class="font-weight-bold" style="font-size: 0.95rem;">${{ forNum(alertaEntregadosTotal) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cuentas de Cobro -->
                            <div v-if="alertaCuentasCobroCount > 0" class="col-md-2 col-6 mb-2 px-1">
                                <div class="alert shadow-sm mb-0 p-2 d-flex align-items-center" 
                                    :style="filtroEstadoCartera === 'Cuentas de Cobro' ? 'background-color: #e0f7fa; color: #006064; border-left: 5px solid #00acc1 !important; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(0, 172, 193, 0.35) !important; transform: translateY(-2px);' : 'background-color: #ffffff; color: #006064; border-left: 4px solid #80cbd4 !important; cursor: pointer; transition: 0.2s; opacity: 0.9;'" 
                                    @click="seleccionarFiltroCuentasCobro()">
                                    <i class="fa fa-file-text-o fa-2x mr-2 text-info"></i>
                                    <div class="overflow-hidden">
                                        <h6 class="font-weight-bold mb-0 text-truncate" style="font-size: 0.85rem;">Cuentas de Cobro ({{ alertaCuentasCobroCount }})</h6>
                                        <span class="font-weight-bold" style="font-size: 0.95rem;">${{ forNum(alertaCuentasCobroTotal) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- En Producción -->
                            <div v-if="alertaEnProduccionCount > 0" class="col-md-2 col-6 mb-2 px-1">
                                <div class="alert shadow-sm mb-0 p-2 d-flex align-items-center" 
                                    :style="filtroEstadoCartera === 'En Producción' ? 'background-color: #f3e5f5; color: #4a148c; border-left: 5px solid #ab47bc !important; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(171, 71, 188, 0.35) !important; transform: translateY(-2px);' : 'background-color: #ffffff; color: #4a148c; border-left: 4px solid #ce93d8 !important; cursor: pointer; transition: 0.2s; opacity: 0.9;'" 
                                    @click="seleccionarFiltroEstado('En Producción')">
                                    <i class="fa fa-cogs fa-2x mr-2 text-secondary"></i>
                                    <div class="overflow-hidden">
                                        <h6 class="font-weight-bold mb-0 text-truncate" style="font-size: 0.85rem;">Producción ({{ alertaEnProduccionCount }})</h6>
                                        <span class="font-weight-bold" style="font-size: 0.95rem;">${{ forNum(alertaEnProduccionTotal) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- No Recogidos -->
                            <div v-if="alertaNoRecogidosCount > 0" class="col-md-2 col-6 mb-2 px-1">
                                <div class="alert shadow-sm mb-0 p-2 d-flex align-items-center" 
                                    :style="filtroEstadoCartera === 'No Recogidos' ? 'background-color: #f8d7da; color: #721c24; border-left: 5px solid #dc3545 !important; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(220, 53, 69, 0.35) !important; transform: translateY(-2px);' : 'background-color: #ffffff; color: #721c24; border-left: 4px solid #ef9a9a !important; cursor: pointer; transition: 0.2s; opacity: 0.9;'" 
                                    @click="seleccionarFiltroEstado('No Recogidos')">
                                    <i class="fa fa-exclamation-triangle fa-2x mr-2 text-danger"></i>
                                    <div class="overflow-hidden">
                                        <h6 class="font-weight-bold mb-0 text-truncate" style="font-size: 0.85rem;">No Recogidos ({{ alertaNoRecogidosCount }})</h6>
                                        <span class="font-weight-bold" style="font-size: 0.95rem;">${{ forNum(alertaNoRecogidosTotal) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Cartera -->
                            <div class="col-md-2 col-6 mb-2 px-1">
                                <div class="alert shadow-sm mb-0 p-2 d-flex align-items-center" 
                                    :style="filtroEstadoCartera === 'Total' ? 'background-color: #e8eaf6; color: #1a237e; border-left: 5px solid #3f51b5 !important; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(63, 81, 181, 0.35) !important; transform: translateY(-2px);' : 'background-color: #ffffff; color: #1a237e; border-left: 4px solid #9fa8da !important; cursor: pointer; transition: 0.2s; opacity: 0.9;'" 
                                    @click="seleccionarFiltroEstado('Total')">
                                    <i class="fa fa-globe fa-2x mr-2 text-dark"></i>
                                    <div class="overflow-hidden">
                                        <h6 class="font-weight-bold mb-0 text-truncate" style="font-size: 0.85rem;">Total Cartera ({{ alertaTotalCarteraCount }})</h6>
                                        <span class="font-weight-bold" style="font-size: 0.95rem;">${{ forNum(alertaTotalCarteraTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="tab == 3" class="card-body bg-light border-bottom">
                        <div class="row">
                            <div v-for="resumen in arrayResumenPagos" :key="resumen.forma_pago" class="col-sm-6 col-lg-3">
                                <div class="card text-white" :class="resumen.forma_pago == 'Efectivo' ? 'bg-success' : 'bg-info'">
                                    <div class="card-body pb-0">
                                        <div class="text-value">{{ resumen.total | currency }}</div>
                                        <div>{{ resumen.forma_pago }}</div>
                                    </div>
                                    <div class="chart-wrapper mt-3" style="height:40px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Listado-->

                    <template v-if="tab===5">
                        <div class="card-body">
                            <!-- Buscador para Estado de Cuenta -->
                            <div class="search-modern-container p-4 mb-4 shadow-sm">
                                <div class="row align-items-end">
                                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
                                        <label class="small font-weight-bold text-muted mb-1 text-uppercase">Filtrar por Cliente</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-input-modern w-100" v-model="buscarEC" @keyup="cargarEstadoCuenta(false)" placeholder="Escriba para buscar...">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-4 mb-3 mb-md-0">
                                        <label class="small font-weight-bold text-muted mb-1 text-uppercase">Valor</label>
                                        <input type="text" class="form-control form-input-modern w-100" v-model="buscarv" @keyup="cargarEstadoCuenta(false)" placeholder="Monto...">
                                    </div>
                                    <div class="col-lg-2 col-md-4 mb-3 mb-md-0">
                                        <label class="small font-weight-bold text-muted mb-1 text-uppercase">Filtro Rápido</label>
                                        <select class="form-control form-input-modern w-100" v-model="filtroFecha" @change="cargarEstadoCuenta(true)">
                                            <option value="1">Cualquier fecha</option>   
                                            <option value="hoy">Hoy</option>   
                                            <option value="ayer">Ayer</option>  
                                            <option value="ultimos7">Ultimos 7 días</option>  
                                            <option value="ultimos30">Ultimos 30 días</option>  
                                            <option value="semana">Esta semana</option>  
                                            <option value="mes">Este mes</option>  
                                        </select>
                                    </div>
                                    <div class="col-lg-5 col-md-12 d-flex align-items-center justify-content-md-end flex-wrap">
                                        <button class="btn btn-primary btn-round mr-2 mb-2 mb-lg-0" @click="asiganarIntervalo()">
                                            <i class="fa fa-calendar mr-1"></i> Intervalo
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="loadingEC" class="text-center py-5 w-100">
                                <i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                                <p class="text-muted font-weight-bold">Generando reporte de estados de cuenta...</p>
                            </div>

                            <div v-else-if="estadoCuentaFiltrado.length === 0" class="alert alert-warning py-5 px-4 text-center border-0 shadow-sm rounded-xl w-100 mb-4">
                                <i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                <h4 class="font-weight-bold">Sin registros</h4>
                                <p class="mb-0 text-muted">No se encontraron pedidos con saldo pendiente para los criterios seleccionados.</p>
                            </div>

                            <template v-else>
                                <!-- Encabezado Global de Totales -->
                                <div class="bg-dark text-white shadow-sm rounded-xl px-4 py-3 mb-4">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col-lg-3">
                                            <h5 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1px;"><i class="fa fa-bar-chart mr-2"></i>Total General</h5>
                                        </div>
                                        <div class="col-lg-3 text-center border-left border-white-50">
                                            <span class="d-block text-white-50 text-uppercase font-weight-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Pedidos</span>
                                            <span class="font-weight-bold h5 mb-0">${{ forNum(totalECValor) }}</span>
                                        </div>
                                        <div class="col-lg-3 text-center border-left border-white-50">
                                            <span class="d-block text-white-50 text-uppercase font-weight-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Abonos</span>
                                            <span class="font-weight-bold h5 mb-0" style="color: #a3ffc0;">${{ forNum(totalECAbono) }}</span>
                                        </div>
                                        <div class="col-lg-3 text-center border-left border-white-50">
                                            <span class="d-block text-white-50 text-uppercase font-weight-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Cartera Real (Saldo)</span>
                                            <span class="font-weight-bold h5 mb-0" style="color: #ffd875;">${{ forNum(totalECSaldo) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-for="cl in estadoCuentaFiltrado" :key="cl.cliente.id" class="cliente-ec-container mb-3 shadow-sm rounded overflow-hidden bg-white border-0">
                                    <!-- Encabezado del cliente -->
                                    <div class="bg-premium-header text-white px-4 py-3 d-flex align-items-center" style="cursor: pointer;" @click="alternarGrupoEC(cl.cliente.id)">
                                        <div class="row w-100 align-items-center no-gutters">
                                            <div class="col-lg-4">
                                                <h5 class="mb-0 font-weight-bold text-truncate">
                                                    <i class="fa fa-user-circle mr-2"></i>
                                                    {{ cl.cliente.razonsocial || 'Cliente #' + cl.cliente.id }}
                                                </h5>
                                            </div>
                                            
                                            <div class="col-lg-6 d-none d-lg-flex align-items-center" v-if="!estaExpandidoEC(cl.cliente.id)">
                                                <div class="col-4 text-center border-left border-white-50 px-2">
                                                    <span class="d-block text-white-50 text-uppercase font-weight-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Pedidos</span>
                                                    <span class="font-weight-bold h6 mb-0">${{ forNum(cl.totales.valor) }}</span>
                                                </div>
                                                <div class="col-4 text-center border-left border-white-50 px-2">
                                                    <span class="d-block text-white-50 text-uppercase font-weight-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Abonos</span>
                                                    <span class="font-weight-bold h6 mb-0" style="color: #a3ffc0;">${{ forNum(cl.totales.abono) }}</span>
                                                </div>
                                                <div class="col-4 text-center border-left border-white-50 px-2">
                                                    <span class="d-block text-white-50 text-uppercase font-weight-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Saldo</span>
                                                    <span class="font-weight-bold h6 mb-0" style="color: #ffd875;">${{ forNum(cl.totales.saldo) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-6" v-else></div>

                                            <div class="col-lg-2 text-right px-0" @click.stop>
                                                <button @click="pagarClienteDesdeEC(cl)" class="btn btn-light text-primary btn-sm mr-2 px-3 font-weight-bold shadow-sm" style="border-radius: 20px;" title="Registrar Pago">
                                                    <i class="fa fa-money text-success mr-1"></i> REGISTRAR PAGO
                                                </button>
                                                <button @click="alternarGrupoEC(cl.cliente.id)" class="btn btn-link text-white p-0">
                                                    <i :class="estaExpandidoEC(cl.cliente.id) ? 'fa fa-chevron-up h5 mb-0' : 'fa fa-chevron-down h5 mb-0'"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                <div class="p-4" v-show="estaExpandidoEC(cl.cliente.id)">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover account-statement-table mb-0">
                                            <thead class="bg-light text-uppercase small font-weight-bold letter-spacing-1">
                                                <tr>
                                                    <th style="width:38%">Documento</th>
                                                    <th class="text-right">Valor Pedido</th>
                                                    <th class="text-right">Abonos</th>
                                                    <th class="text-right">Cartera Real (Saldo)</th>
                                                    <th class="text-center" style="width:120px;">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="d in cl.documentos" :key="d.id"
                                                    :class="{
                                                        'table-primary font-weight-bold': d.clase==='pedido' || d.clase==='remision',
                                                        'table-success-light':            d.clase==='recibo'
                                                    }">
                                                    <!-- Columna Documento -->
                                                    <td class="py-2 align-middle">
                                                        <div class="d-flex align-items-center">
                                                            <i v-if="d.clase==='pedido'" class="fa fa-file-text-o mr-2 text-primary"></i>
                                                            <i v-if="d.clase==='cuentacobro'" class="fa fa-file-text-o mr-2 text-info"></i>
                                                            <i v-if="d.clase==='remision'" class="fa fa-truck mr-2 text-info"></i>
                                                            <i v-if="d.clase==='recibo'" class="fa fa-check-circle mr-2 text-success"></i>
                                                            <div>
                                                                <div class="d-flex align-items-center flex-wrap">
                                                                    <span class="font-weight-bold mr-1">{{ d.label }}</span>
                                                                    <span v-if="d.clase==='cuentacobro'" class="badge badge-success px-2 py-1 mr-2" style="font-size:0.7rem;">
                                                                        <i class="fa fa-check-circle mr-1"></i>Válida
                                                                    </span>
                                                                    <!-- Botón directo de Abonar a Cuenta de Cobro / Pedido -->
                                                                    <button v-if="(d.clase==='cuentacobro' || d.clase==='pedido') && d.saldo > 0.01" 
                                                                            type="button" 
                                                                            @click="pagarDesdeEC(d, cl.cliente)" 
                                                                            class="btn btn-sm btn-success font-weight-bold px-2 py-0 shadow-sm" 
                                                                            style="font-size:0.72rem; border-radius:12px;" 
                                                                            title="Registrar Abono a este documento">
                                                                        <i class="fa fa-money mr-1"></i>Abonar
                                                                    </button>
                                                                </div>
                                                                <div v-if="d.clase==='recibo'" class="x-small text-muted">
                                                                    <i class="fa fa-link mr-1"></i>APLICADO {{ d.aplicado_a }}
                                                                </div>
                                                                <div v-if="d.pedido_ref" class="x-small font-weight-bold text-primary mt-1">
                                                                    <i class="fa fa-shopping-cart mr-1"></i>Perteneciente a {{ d.pedido_ref }}
                                                                </div>
                                                                <div v-if="d.remisiones_ref" class="x-small font-weight-bold text-info mt-1">
                                                                    <i class="fa fa-truck mr-1"></i>Anexa {{ d.remisiones_ref }}
                                                                </div>
                                                                <div class="x-small text-muted opacity-7">
                                                                    <i class="fa fa-calendar-o mr-1"></i>{{ d.fecha }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <!-- Valor Pedido -->
                                                    <td class="text-right align-middle">
                                                        <span v-if="d.valor_pedido > 0" class="text-primary font-weight-bold">
                                                            ${{ forNum(d.valor_pedido) }}
                                                        </span>
                                                    </td>
                                                    <!-- Abonos -->
                                                    <td class="text-right align-middle">
                                                        <span v-if="d.abono > 0" class="text-success font-weight-bold">
                                                            ${{ forNum(d.abono) }}
                                                        </span>
                                                    </td>
                                                    <!-- Saldo -->
                                                    <td class="text-right align-middle">
                                                        <span v-if="d.clase!=='recibo'"
                                                              :class="d.saldo > 0.01 ? 'text-danger font-weight-bold' : 'text-muted'">
                                                            ${{ forNum(d.saldo) }}
                                                        </span>
                                                    </td>
                                                    <!-- Columna Acción -->
                                                    <td class="text-center align-middle">
                                                        <button v-if="(d.clase==='cuentacobro' || d.clase==='pedido') && d.saldo > 0.01"
                                                                type="button"
                                                                @click="pagarDesdeEC(d, cl.cliente)"
                                                                class="btn btn-success btn-sm font-weight-bold px-3 py-1 shadow-sm"
                                                                style="border-radius:15px;"
                                                                title="Registrar Abono">
                                                            <i class="fa fa-dollar mr-1"></i> Abonar
                                                        </button>
                                                        <span v-else-if="d.clase==='cuentacobro' || d.clase==='pedido'" class="badge badge-secondary px-2 py-1">
                                                            <i class="fa fa-check mr-1"></i>Saldado
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="bg-dark text-white">
                                                <tr class="h5">
                                                    <td class="font-weight-bold text-uppercase py-3">TOTALES</td>
                                                    <td class="text-right py-3">${{ forNum(cl.totales.valor) }}</td>
                                                    <td class="text-right py-3 text-success">${{ forNum(cl.totales.abono) }}</td>
                                                    <td class="text-right py-3 text-warning font-weight-bold">${{ forNum(cl.totales.saldo) }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            </template>
                        </div>
                    </template>
                    <template v-else-if="listado===1">
                        <div class="card-body">
                            <div class="search-modern-container p-4 mb-4 shadow-sm">
                                <div class="row align-items-end">
                                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
                                        <label class="small font-weight-bold text-muted mb-1 text-uppercase">Cliente / Cuenta</label>
                                        <input type="text" class="form-control form-input-modern w-100" v-model="buscare" list="clientesList" @keyup="listarPedidosCartera(1,`${buscare}`,'like','cliente_id')"  placeholder="Buscar cliente...">
                                        <datalist id="clientesList">
                                            <option v-for="c in arrayClientesSug" :key="c.id" :value="c.razonsocial"></option>
                                        </datalist>
                                    </div>
                                    <div class="col-lg-2 col-md-4 mb-3 mb-md-0">
                                        <label class="small font-weight-bold text-muted mb-1 text-uppercase">Valor</label>
                                        <input type="text" class="form-control form-input-modern w-100" v-model="buscarv" @keyup="listarPedidosCartera(1,`${buscarv}`,'like','total')"  placeholder="Monto...">
                                    </div>
                                    <div class="col-lg-2 col-md-4 mb-3 mb-md-0">
                                        <label class="small font-weight-bold text-muted mb-1 text-uppercase">Filtro Rápido</label>
                                        <select class="form-control form-input-modern w-100" @change="filtrarFecha()" v-model="filtroFecha">
                                            <option value="1">Cualquier fecha</option>   
                                            <option value="hoy">Hoy</option>   
                                            <option value="ayer">Ayer</option>  
                                            <option value="ultimos7">Ultimos 7 días</option>  
                                            <option value="ultimos30">Ultimos 30 días</option>  
                                            <option value="semana">Esta semana</option>  
                                            <option value="mes">Este mes</option>  
                                        </select>
                                    </div>
                                    <div class="col-lg-5 col-md-12 d-flex align-items-center justify-content-md-end flex-wrap">
                                        <button class="btn btn-primary btn-round mr-2 mb-2 mb-lg-0" @click="asiganarIntervalo()">
                                            <i class="fa fa-calendar mr-1"></i> Intervalo
                                        </button>
                                        <div class="custom-control custom-switch custom-control-right ml-3">
                                            <input class="custom-control-input" type="checkbox" v-model="agruparPorCliente" id="groupClientSwitch">
                                            <label class="custom-control-label font-weight-bold text-primary" for="groupClientSwitch">Agrupar por Cliente</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Intervalo Incorporado -->
                                <div class="modal fade" tabindex="-1" :class="{'mostrar' : modalIntervalo}">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title font-weight-bold"><i class="fa fa-calendar-plus-o mr-2"></i>Seleccionar Rango de Fechas</h5>
                                                <button @click="cerrarModalIntervalo" type="button" class="close text-white" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <div class="badge badge-light-primary p-2 mb-4 h6 w-100">
                                                    {{fechaI}} <i class="fa fa-long-arrow-right mx-2"></i> {{fechaF}}
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 text-left">
                                                        <label class="small font-weight-bold text-muted">FECHA INICIO</label>
                                                        <input type="date" class="form-control form-input-modern" v-model="fechaI">
                                                    </div>
                                                    <div class="col-6 text-left">
                                                        <label class="small font-weight-bold text-muted">FECHA FIN</label>
                                                        <input type="date" class="form-control form-input-modern" v-model="fechaF">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button @click="cerrarModalIntervalo" type="button" class="btn btn-light btn-round px-4">Cancelar</button>
                                                <button @click="filtrarFecha()" type="button" class="btn btn-primary btn-round px-4 shadow">Aplicar Filtro</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    
                                    <thead v-if="tab != 3">
                                        <tr>
                                            <th>Opciones</th>
                                            <th @click="ordenar('id')">{{ tab == 1 ? 'No. Cuenta cobro' : 'No. Pedido' }} <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th v-if="tab == 1">Pedido(s) Ref.</th>
                                            <th @click="ordenar('fecha')">Fecha <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('razonsocial')">Cliente <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('total')">Valor Pedido <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('abono')">Abono <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            <th @click="ordenar('saldo')">Cartera Real (Saldo) <i v-if="ordenarFlecha" class="fa fa-arrow-up"></i><i v-else class="fa fa-arrow-down"></i></th>
                                            
                                        </tr>
                                    </thead>
                                     <thead v-else-if="tab == 3">
                                         <tr>
                                             <th>Opciones</th>
                                             <th>No. Recibo</th>
                                             <th>Fecha</th>
                                             <th>Cliente</th>
                                             <th>Documento</th>
                                             <th>Monto</th>
                                             <th>Forma de Pago</th>
                                         </tr>
                                     </thead>
                                     <thead v-else-if="tab == 4">
                                         <tr>
                                             <th>Opciones</th>
                                             <th @click="ordenar('num_comprobante')">No. Comprobante</th>
                                             <th @click="ordenar('fecha')">Fecha</th>
                                             <th @click="ordenar('razonsocial')">Cliente</th>
                                             <th @click="ordenar('total')">Valor Total</th>
                                             <th @click="ordenar('abono')">Abono</th>
                                             <th @click="ordenar('saldo')">Saldo</th>
                                         </tr>
                                     </thead>
                                     <!-- Vista Agrupada -->
                                    <template v-if="tab != 3 && agruparPorCliente">
                                        <tbody v-for="(grupo, gIdx) in pedidosAgrupados" :key="'g'+gIdx">
                                            <!-- Fila del Cliente (Grupo) -->
                                            <tr class="table-info">
                                                <td>
                                                    <button class="btn btn-sm btn-dark" @click="alternarGrupo(grupo.cliente.id)">
                                                        <i :class="estaExpandido(grupo.cliente.id) ? 'fa fa-minus' : 'fa fa-plus'"></i>
                                                    </button>
                                                    <template v-if="tab != 4">
                                                        <button v-if="grupo.pedidos.some(p => p.clase_cartera == 'Credito') && grupo.pedidos.some(p => p.clase_cartera == 'Deuda')" @click="cruzarCartera(grupo.cliente.id)" class="btn btn-primary btn-sm" title="Aplicar Saldos a Favor">
                                                            <i class="fa fa-refresh"></i> Aplicar Créditos
                                                        </button>
                                                        <button @click="abrirModalPagoCliente(grupo)" class="btn btn-success btn-sm" title="Crear un recibo de caja consolidado">
                                                            <i class="fa fa-money"></i> Crear Recibo
                                                        </button>
                                                    </template>
                                                </td>
                                                <td :colspan="tab == 1 ? 3 : 2" class="text-center text-muted small">---</td>
                                                <td><strong>{{ grupo.nombre }}</strong></td>
                                                <td><strong>${{ forNum(grupo.total_venta) }}</strong></td>
                                                <td><strong>${{ forNum(grupo.total_abono) }}</strong></td>
                                                 <td class="text-danger"><strong>${{ forNum(grupo.total_saldo < 0 ? 0 : grupo.total_saldo) }}</strong> <small v-if="grupo.total_saldo < 0" class="text-success">(A Favor ${{ forNum(Math.abs(grupo.total_saldo)) }})</small></td>
                                            </tr>
                                            <!-- Filas de Cuentas de Cobro o Créditos (Detalle) -->
                                            <tr v-if="estaExpandido(grupo.cliente.id)" v-for="(pedido, index) in grupo.pedidos" :key="'p'+pedido.id" :class="pedido.clase_cartera == 'Credito' ? 'table-success' : ''">
                                                <td class="text-center">
                                                    <div class="btn-group" v-if="tab != 4">
                                                        <button v-if="pedido.clase_cartera != 'Credito'" type="button" @click="verPedido(pedido)" class="btn btn-primary btn-sm" title="Ver Detalle">
                                                            <i class="icon-eye"></i>
                                                        </button>
                                                        <button v-if="pedido.clase_cartera != 'Credito'" type="button" @click="abrirModalPago(pedido)" class="btn btn-success btn-sm" title="Registrar Pago">
                                                            <i class="fa fa-money"></i>
                                                        </button>
                                                        <button v-if="pedido.clase_cartera != 'Credito'" type="button" @click="imprimirCC(pedido.id)" class="btn btn-warning btn-sm" title="Imprimir Cuenta de Cobro">
                                                            <i class="fa fa-print"></i>
                                                        </button>
                                                        <template v-else>
                                                            <button type="button" @click="imprimirRecibo(pedido.id.substring(1))" class="btn btn-info btn-sm" title="Imprimir Recibo">
                                                                <i class="fa fa-print"></i>
                                                            </button>
                                                            <button type="button" @click="abrirModalEditarRecibo(pedido)" class="btn btn-primary btn-sm" title="Editar Recibo">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button" @click="eliminarRecibo(pedido.id.substring(1))" class="btn btn-danger btn-sm" title="Eliminar Recibo">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span v-if="pedido.clase_cartera == 'Credito'" class="badge badge-success">Ingreso #{{pedido.num_comprobante}}</span>
                                                    <span v-else v-text="pedido.num_comprobante ? pedido.num_comprobante : pedido.id"></span>
                                                </td>
                                                <td v-if="tab == 1">
                                                    <div v-if="pedido.parent_pedido_ids && pedido.parent_pedido_ids.length">
                                                        <span v-for="(pid, pIdx) in pedido.parent_pedido_ids" :key="pIdx" class="badge badge-light-primary mr-1">
                                                            #{{ pid }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td v-text="pedido.fecha"></td>
                                                <td v-text="pedido.cliente.razonsocial" class="text-muted small"></td>
                                                <td v-text="pedido.clase_cartera == 'Credito' ? '-' : `$${forNum(pedido.valor_pedido || pedido.total)}` "></td>
                                                <td v-text="pedido.clase_cartera == 'Credito' ? `$${forNum(pedido.abono)}` : `$${forNum(parseFloat(pedido.abono || 0))}`"></td>
                                                 <td :class="pedido.clase_cartera == 'Credito' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold'">
                                                     <template v-if="pedido.clase_cartera == 'Credito'">
                                                         <span v-if="Math.abs(parseFloat(pedido.saldo || 0)) > 0">-${{ forNum(Math.abs(parseFloat(pedido.saldo || 0))) }} <small>(A Favor)</small></span>
                                                         <span v-else class="text-muted">$0 <small>(Aplicado)</small></span>
                                                     </template>
                                                     <template v-else>${{ forNum(parseFloat(pedido.saldo || 0)) }}</template>
                                                 </td>
                                             </tr>
                                        </tbody>
                                    </template>

                                    <!-- Vista Plana (Sin Agrupar) -->
                                    <template v-else-if="tab != 3 && !agruparPorCliente">
                                        <tbody>
                                            <tr v-for="(pedido, index) in pedidosFiltradosEstado" :key="'f'+pedido.id" :class="pedido.clase_cartera == 'Credito' ? 'table-success' : ''">
                                                <td class="text-center">
                                                    <div class="btn-group" v-if="tab != 4">
                                                        <button v-if="pedido.clase_cartera != 'Credito'" type="button" @click="verPedido(pedido)" class="btn btn-primary btn-sm" title="Ver Detalle">
                                                            <i class="icon-eye"></i>
                                                        </button>
                                                        <button v-if="pedido.clase_cartera != 'Credito'" type="button" @click="abrirModalPago(pedido)" class="btn btn-success btn-sm" title="Registrar Pago">
                                                            <i class="fa fa-money"></i>
                                                        </button>
                                                        <button v-if="pedido.clase_cartera != 'Credito'" type="button" @click="imprimirCC(pedido.id)" class="btn btn-warning btn-sm" title="Imprimir Cuenta de Cobro">
                                                            <i class="fa fa-print"></i>
                                                        </button>
                                                        <template v-else>
                                                            <button type="button" @click="imprimirRecibo(pedido.id.substring(1))" class="btn btn-info btn-sm" title="Imprimir Recibo">
                                                                <i class="fa fa-print"></i>
                                                            </button>
                                                            <button type="button" @click="abrirModalEditarRecibo(pedido)" class="btn btn-primary btn-sm" title="Editar Recibo">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button" @click="eliminarRecibo(pedido.id.substring(1))" class="btn btn-danger btn-sm" title="Eliminar Recibo">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span v-if="pedido.clase_cartera == 'Credito'" class="badge badge-success">Ingreso #{{pedido.num_comprobante}}</span>
                                                    <span v-else v-text="pedido.num_comprobante ? pedido.num_comprobante : pedido.id"></span>
                                                </td>
                                                <td v-if="tab == 1">
                                                    <div v-if="pedido.parent_pedido_ids && pedido.parent_pedido_ids.length">
                                                        <span v-for="(pid, pIdx) in pedido.parent_pedido_ids" :key="pIdx" class="badge badge-light-primary mr-1">
                                                            #{{ pid }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td v-text="pedido.fecha"></td>
                                                <td v-text="pedido.cliente ? pedido.cliente.razonsocial : 'N/A'" class="text-muted small"></td>
                                                <td v-text="pedido.clase_cartera == 'Credito' ? '-' : `$${forNum(pedido.valor_pedido || pedido.total)}` "></td>
                                                <td v-text="pedido.clase_cartera == 'Credito' ? `$${forNum(pedido.abono)}` : `$${forNum(parseFloat(pedido.abono || 0))}`"></td>
                                                 <td :class="pedido.clase_cartera == 'Credito' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold'">
                                                     <template v-if="pedido.clase_cartera == 'Credito'">
                                                         <span v-if="Math.abs(parseFloat(pedido.saldo || 0)) > 0">-${{ forNum(Math.abs(parseFloat(pedido.saldo || 0))) }} <small>(A Favor)</small></span>
                                                         <span v-else class="text-muted">$0 <small>(Aplicado)</small></span>
                                                     </template>
                                                     <template v-else>${{ forNum(parseFloat(pedido.saldo || 0)) }}</template>
                                                 </td>
                                             </tr>
                                        </tbody>
                                    </template>
                                    <tbody v-if="tab != 3 && pedidosAgrupados.length">
                                        <tr class="bg-dark text-white text-right">
                                            <td :colspan="tab == 1 ? 5 : 4"><strong>TOTALES GENERALES</strong></td>
                                            <td><strong>${{forNum(totalVenta)}}</strong></td>
                                            <td><strong>${{forNum(totalAbonos)}}</strong></td>
                                            <td><strong>${{forNum(totalCartera)}}</strong></td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else-if="tab == 3 && arrayPagos.length">
                                        <tr v-for="pago in pagosOrdenados" :key="pago.id">
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" @click="imprimirRecibo(pago.id)" class="btn btn-primary btn-sm" title="Imprimir Recibo">
                                                        <i class="icon-printer"></i>
                                                    </button>
                                                    <button type="button" @click="abrirModalEditarRecibo({id: 'R'+pago.id, fecha: pago.fecha, abono: pago.monto, num_comprobante: pago.num_recibo, forma_pago: pago.forma_pago, observaciones: pago.observaciones})" class="btn btn-warning btn-sm" title="Editar Recibo">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button type="button" @click="abrirModalAsignar(pago)" class="btn btn-success btn-sm" title="Asignar a Cuenta de Cobro / Pedido">
                                                        <i class="fa fa-link"></i>
                                                    </button>
                                                    <button type="button" @click="eliminarRecibo(pago.id)" class="btn btn-danger btn-sm" title="Eliminar Recibo">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td v-text="pago.num_recibo"></td>
                                            <td v-text="pago.fecha"></td>
                                            <td v-text="pago.cliente ? pago.cliente.razonsocial : 'N/A'"></td>
                                            <td v-text="pago.comprobante ? pago.comprobante.num_comprobante : 'N/A'"></td>
                                            <td>
                                                ${{ forNum(pago.monto) }}
                                                <span v-if="pago.saldo_recibo > 0" class="badge badge-warning ml-1" title="Saldo disponible del recibo">${{ forNum(pago.saldo_recibo) }} disponible</span>
                                            </td>
                                            <td v-text="pago.forma_pago"></td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td :colspan="tab == 3 ? 7 : 7">
                                                No hay registros para mostrar.
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
                    </template>
                    <!--Fin Listado-->
                    <template v-else>
                        <pedido :user="user" :pedido="pedido" :dato="1" @ocultarDetalle="ocultarDetalle" :edit="edit"></pedido>
                    </template>
                    <!-- Detalle-->
                  
                    <!-- Fin Detalle-->
                    

                </div>
                <!-- Fin ejemplo de tabla Listado -->
                 
            </div>
            
             
           
        </main>

        <!-- Modal Registrar Pago -->
        <div class="modal fade" :class="{'mostrar' : modalPago}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
                             <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden; max-height: 95vh;">
                    <div class="modal-header header-premium-pago text-white border-0 py-2">
                        <h4 class="modal-title font-weight-bold" style="font-size: 1.1rem;">
                            <i class="fa fa-money mr-2"></i> Registrar Pago - Doc #{{pagoData.num_comprobante}}
                        </h4>
                        <button type="button" class="close text-white opacity-10" @click="cerrarModalPago()" aria-label="Close" style="font-size: 1.5rem; margin-top: -5px;">
                          <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3 bg-glass-modal" style="overflow-y: auto;">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px; height: 100%;">
                                    <div class="card-body p-3">
                                        <h5 class="text-muted small text-uppercase font-weight-bold mb-2">Resumen de Deuda</h5>
                                        <div class="resumen-pago-box rounded p-2 mb-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-secondary">Total Venta:</span>
                                                <strong class="text-dark font-lg">${{forNum(pagoData.total)}}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-secondary">Saldo Pendiente:</span>
                                                <strong class="text-danger font-xl">${{forNum(pagoData.saldo)}}</strong>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-muted uppercase">Aplicar a Pedido</label>
                                            <select class="form-control form-input-modern" v-model="pagoData.pedido_especifico_id" @change="actualizarMontoEspecifico">
                                                <option v-if="!pedidosClienteActivos.length" :value="null">No hay pedidos pendientes</option>
                                                <option v-for="ped in pedidosClienteActivos" :key="ped.id" :value="ped.id">
                                                    Pedido #{{ ped.num_comprobante || ped.id }} - Saldo: ${{ forNum(ped.saldo) }}
                                                </option>
                                            </select>
                                        </div>
                                        
                                         <div class="form-group mb-2">
                                            <label class="small font-weight-bold text-muted uppercase">Fecha de Pago</label>
                                            <input type="date" class="form-control form-input-modern py-1" v-model="pagoData.fecha">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold text-muted uppercase">Monto a Pagar</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0">$</span>
                                                </div>
                                                <input type="number" class="form-control form-input-modern border-left-0 font-weight-bold text-success" v-model="pagoData.monto" style="font-size: 1.1rem;">
                                            </div>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold text-muted uppercase">Forma de Pago</label>
                                            <select class="form-control form-input-modern py-1" v-model="pagoData.forma_pago">
                                                <option value="Efectivo">Caja / Efectivo</option>
                                                <option value="Banco">Bancos / Transferencia</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-muted uppercase">Observaciones</label>
                                            <textarea class="form-control form-input-modern" v-model="pagoData.observaciones" rows="1" placeholder="Notas adicionales..."></textarea>
                                        </div>
                                        
                                        <button class="btn btn-premium-success btn-block py-2 font-weight-bold shadow-sm" @click="registrarPago()" :disabled="loading">
                                            <span v-if="loading"><i class="fa fa-spinner fa-spin mr-2"></i>PROCESANDO...</span>
                                            <span v-else><i class="fa fa-save mr-2"></i>GUARDAR PAGO</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-muted small text-uppercase font-weight-bold mb-2 pl-2">Historial de Pagos</h5>
                                <div class="table-responsive bg-white rounded shadow-sm" style="max-height: 380px; overflow-y: auto;">
                                    <table class="table table-sm table-modern-inner mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                <th>Forma</th>
                                                <th class="text-center">PDF</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="p in arrayPagos" :key="p.id">
                                                <td class="small">{{p.fecha}}</td>
                                                <td class="font-weight-bold text-success">${{forNum(p.monto)}}</td>
                                                <td class="small">{{p.forma_pago}}</td>
                                                <td class="text-center">
                                                    <button class="btn btn-link py-0 text-danger" @click="verReciboPdf(p.id)">
                                                        <i class="fa fa-file-pdf-o"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="!arrayPagos.length">
                                                <td colspan="4" class="text-center py-4 text-muted small">No hay pagos registrados.</td>
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

        <!-- Modal Editar Recibo -->
        <div class="modal fade" :class="{'mostrar' : modalEditarRecibo}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header bg-primary text-white border-0">
                        <h4 class="modal-title font-weight-bold">
                            <i class="fa fa-edit mr-2"></i> Editar Recibo #{{reciboEditData.num_recibo}}
                        </h4>
                        <button type="button" class="close text-white" @click="cerrarModalEditarRecibo()" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted">FECHA</label>
                            <input type="date" class="form-control" v-model="reciboEditData.fecha">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted">MONTO</label>
                            <input type="number" class="form-control font-weight-bold text-primary" v-model="reciboEditData.monto" placeholder="Monto total del recibo">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted">NÚMERO DE RECIBO</label>
                            <input type="text" class="form-control" v-model="reciboEditData.num_recibo">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted">FORMA DE PAGO</label>
                            <select class="form-control" v-model="reciboEditData.forma_pago">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Banco">Banco / Transferencia</option>
                                <option value="Migración">Migración</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-muted">OBSERVACIONES</label>
                            <textarea class="form-control" v-model="reciboEditData.observaciones" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" @click="cerrarModalEditarRecibo()">Cancelar</button>
                        <button type="button" class="btn btn-primary px-4" @click="actualizarRecibo()" :disabled="loading">
                            <span v-if="loading"><i class="fa fa-spinner fa-spin mr-2"></i>GUARDANDO...</span>
                            <span v-else>ACTUALIZAR DATOS</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Asignar Recibo a Comprobante -->
        <div class="modal fade" :class="{'mostrar': modalAsignar}" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                    <div class="modal-header bg-success text-white border-0">
                        <h4 class="modal-title font-weight-bold">
                            <i class="fa fa-link mr-2"></i> Asignar Recibo #{{ reciboAsignar.num_recibo }} a Documento
                        </h4>
                        <button type="button" class="close text-white" @click="cerrarModalAsignar()">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span><strong>Saldo disponible del recibo:</strong></span>
                            <strong class="text-primary font-lg">${{ forNum(reciboAsignar.saldo_recibo || 0) }}</strong>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-muted">MONTO A APLICAR</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input type="number" class="form-control font-weight-bold text-success" v-model="asignarData.monto" :max="reciboAsignar.saldo_recibo">
                            </div>
                        </div>
                        <hr>
                        <h6 class="font-weight-bold text-muted text-uppercase small mb-3">Documentos Pendientes del Cliente</h6>
                        <div v-if="loadingComprobantes" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>
                        <div v-else-if="comprobantesCliente.length === 0" class="alert alert-warning">No hay documentos pendientes para este cliente.</div>
                        <div class="table-responsive" v-else>
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th></th>
                                        <th>No. Doc</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Total</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="comp in comprobantesCliente" :key="comp.id" :class="asignarData.comprobante_id == comp.id ? 'table-success' : ''"
                                        style="cursor:pointer" @click="asignarData.comprobante_id = comp.id">
                                        <td><input type="radio" :value="comp.id" v-model="asignarData.comprobante_id"></td>
                                        <td><strong>{{ comp.num_comprobante || comp.id }}</strong></td>
                                        <td>{{ comp.fecha }}</td>
                                         <td><span class="badge" :class="comp.tipo == 'factura' ? 'badge-primary' : (comp.tipo == 'pedido' ? 'badge-warning' : 'badge-info')">{{ comp.tipo == 'factura' ? 'Factura' : (comp.tipo == 'pedido' ? 'Pedido' : 'Cuenta Cobro') }}</span></td>
                                        <td>${{ forNum(comp.total) }}</td>
                                        <td class="text-danger font-weight-bold">${{ forNum(comp.saldo) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" @click="cerrarModalAsignar()">Cancelar</button>
                        <button type="button" class="btn btn-success px-4" @click="ejecutarAsignacion()" :disabled="loading || !asignarData.comprobante_id">
                            <span v-if="loading"><i class="fa fa-spinner fa-spin mr-2"></i>APLICANDO...</span>
                            <span v-else><i class="fa fa-check mr-1"></i>APLICAR ABONO</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
   
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    import pedido from './partes/Pedido'
    export default {
        props:['user'],
        data (){
            return {
                dato:0,
                tab: 5,
                arrayPagos: [],
                arrayResumenPagos: [],
                buscare:'',
                buscarc:'',
                buscarv:'',
                buscar:'todos',
                fechaI:'',
                fechaF:'',
                filtroFecha:'1',
                buscarFechai:'',
                buscarFechaf:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                action:'',
                idorden:0,
                estadocomercial:{C:'Por Cotizar', PC:'Por Concretar', PA:'Pendiente Abono', VC:'Venta Cerrada', P:'No Comprar', A:'Aplazada'},
                estadoproduccion:{D:'Diseño', A:'Aprobación',EP:'Enviar a producción',ENP:'En Producción', E:'Para Entrega',T:'Terminada'},
                unidad:'',
                fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                fechaorden:`${new Date().getFullYear()}/${meses[new Date().getMonth()]}/${new Date().getDate()}`,
                fecha_entrega:new Date(),
                diasfaltantes:10,
                arrayPedidos:[],
                estadoc:'C',
                estadop:'',
                carpeta_cliente:'',
                detalles_diseno:'',
                observaciones:'',
                medida_material:0,
                tamano:0,
                valor:0,
                idproveedor:0,
                nombre : '',
                tipo_comprobante : 'BOLETA',
                serie_comprobante : '',
                num_comprobante : '',
                impuesto: 0.19,
                descuento:0,
                total:0,
                valor_insumo:0,
                abono:0,
                saldo:0,
                totalImpuesto:0,
                totalParcial:0,
                arrayClientes: [],
                idcliente:0,
                cliente_seleccionado:null,
                cseleccionado:false,
                nombre:'',
                tipo_cliente:'Natural',
                tipo_documento:'',
                num_documento:'',
                direccion:'',
                pais:'Colombia',
                departamento:'Valle del Cauca',
                ciudad:'Cali',
                telefono:'',
                email:'',
                contacto:'',
                telefono_contacto:'',
                email_contacto:'',
                arrayDetalle : [],
                titulo_detalle:'',
                valor_detalle:'',
                descripcion_detalle:'',
                arrayArticulos:[],
                idarticulo:0,
                articulo_seleccionado:null,
                aseleccionado:false,
                nombre_articulo:'',
                precio:0,
                cantidad:1000,
                vieworden:0,
                listado:1,
                idcosto:0,
                nombre_insumo:'',
                valor_insumo:0,
                cabida:'',
                insumo_seleccionado:null,
                iseleccionado:false,
                arrayInsumos:[],
                arrayCostos:[],
                descripcion_costo:'',
                valor_costo:0,
                cantidad_costo:1,
                tipo_costo:'',
                orden_costo:0,
                modala:0,
                modalc:0,
                modali:0,
                modalfecha:0,
                modalIntervalo:0,
                tituloModal : '',
                tipoAccion : 0,
                errorIngreso : 0,
                errorMostrarMsjIngreso : [],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : '',
                buscar_cliente: '',
                criterioA:'nombre',
                buscar_articulo:'',
                buscar_insumo:'',
                topedit:0,
                dominio:'',
                msjCliente:'',
                msjArticulo:'',
                seccion:'',
                ordenarFlecha:false,
                modalPago: 0,
                pagoData: {
                    comprobante_id: 0,
                    cliente_id: 0,
                    es_masivo: false,
                    monto: 0,
                    forma_pago: 'Efectivo',
                    fecha: `${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                    observaciones: '',
                    num_comprobante: '',
                    total: 0,
                    saldo: 0,
                    pedido_especifico_id: null
                },
                pedidosClienteActivos: [],
                grupoActual: null,
                searchTimer: null,
                arrayClientesSug: [],
                recibosSeleccionados: [],
                expandedGroups: [],
                loading: false,
                modalEditarRecibo: 0,
                reciboEditData: {
                    id: 0,
                    fecha: '',
                    monto: 0,
                    num_recibo: '',
                    forma_pago: '',
                    observaciones: ''
                },
                agruparPorCliente: false,
                modalAsignar: 0,
                reciboAsignar: { id: 0, num_recibo: '', saldo_recibo: 0, cliente_id: 0 },
                asignarData: { recibo_id: 0, comprobante_id: null, monto: 0 },
                comprobantesCliente: [],
                loadingComprobantes: false,
                estadoCuentaData: [],
                loadingEC: false,
                buscarEC: '',
                expandedECGroups: [],
                filtroEstadoCartera: 'Total',
                alertaCompletadosCount: 0,
                alertaCompletadosTotal: 0,
                alertaEntregadosCount: 0,
                alertaEntregadosTotal: 0,
                alertaNoRecogidosCount: 0,
                alertaNoRecogidosTotal: 0,
                alertaCuentasCobroCount: 0,
                alertaCuentasCobroTotal: 0,
                alertaEnProduccionCount: 0,
                alertaEnProduccionTotal: 0,
                alertaTotalCarteraCount: 0,
                alertaTotalCarteraTotal: 0
            }
        },
         components: {
            pedido
        },
        computed:{
            pedidosFiltradosEstado() {
                if (this.filtroEstadoCartera === 'Total') {
                    return this.arrayPedidos;
                }
                
                return this.arrayPedidos.filter(p => {
                    if (p.clase_cartera === 'Credito' || p.tipo === 'ingreso') {
                        return true; 
                    }
                    
                    let e = String(p.estado || '');
                    
                    // Si es cuentacobro o factura, buscar el estado de su pedido asociado a través de las líneas
                    if (p.tipo === 'cuentacobro' || p.tipo === 'factura') {
                        if (p.lineas && p.lineas.length > 0 && p.lineas[0].orden) {
                            e = String(p.lineas[0].orden.estado || '');
                        } else if (p.pedido_id) { // Solo por si acaso estuviera el id
                            // No podemos deducirlo sin las lineas cargadas
                        }
                    }
                    
                    if (this.filtroEstadoCartera === 'Entregados') {
                        return e === '5';
                    } else if (this.filtroEstadoCartera === 'Completados') {
                        return e === '3' || e === '4';
                    } else if (this.filtroEstadoCartera === 'No Recogidos') {
                        return e === '6';
                    } else if (this.filtroEstadoCartera === 'En Producción') {
                        return e !== '3' && e !== '4' && e !== '5' && e !== '6'; 
                    } else if (this.filtroEstadoCartera === 'Cuentas de Cobro' || this.filtroEstadoCartera === 'Remisionado') {
                        return (p.tipo === 'cuentacobro' || e === 'Cuentas de Cobro') && p.saldo > 0;
                    }
                    
                    return true;
                });
            },
            estadoCuentaFiltrado() {
                if (this.filtroEstadoCartera === 'Total') {
                    let filtradosTotal = this.estadoCuentaData.map(cl => {
                        let docsFiltrados = cl.documentos.filter(d => d.clase !== 'cuentacobro' && d.clase !== 'remision');
                        let tieneDeudas = docsFiltrados.some(d => d.clase !== 'recibo' && d.saldo > 0);
                        if (!tieneDeudas) return null;
                        
                        let totalValor = docsFiltrados.reduce((sum, d) => sum + (d.clase === 'pedido' ? (parseFloat(d.valor_pedido) || 0) : 0), 0);
                        let totalEntregado = docsFiltrados.reduce((sum, d) => sum + (d.clase === 'pedido' ? (parseFloat(d.valor_entregado) || 0) : 0), 0);
                        let totalAbono = docsFiltrados.reduce((sum, d) => sum + (d.clase === 'pedido' ? (parseFloat(d.abono) || 0) : 0), 0);
                        let totalSaldo = docsFiltrados.reduce((sum, d) => sum + (d.clase === 'pedido' ? (parseFloat(d.saldo) || 0) : 0), 0);
                        
                        return {
                            ...cl,
                            documentos: docsFiltrados,
                            totales: { valor: totalValor, entregado: totalEntregado, abono: totalAbono, saldo: totalSaldo }
                        };
                    }).filter(cl => cl !== null);
                    return filtradosTotal;
                }
                
                let filtrados = this.estadoCuentaData.map(cl => {
                    let docsFiltrados = cl.documentos.filter(d => {
                        if (d.clase === 'recibo') return true;
                        
                        let e = String(d.estado || '');
                        if (this.filtroEstadoCartera === 'Entregados') return e === '5' && d.clase === 'pedido';
                        if (this.filtroEstadoCartera === 'Completados') return (e === '3' || e === '4') && d.clase === 'pedido';
                        if (this.filtroEstadoCartera === 'No Recogidos') return e === '6' && d.clase === 'pedido';
                        if (this.filtroEstadoCartera === 'En Producción') return e !== '3' && e !== '4' && e !== '5' && e !== '6' && d.clase === 'pedido';
                        if (this.filtroEstadoCartera === 'Cuentas de Cobro' || this.filtroEstadoCartera === 'Remisionado') return (d.clase === 'cuentacobro' || e === 'Cuentas de Cobro') && d.saldo > 0;
                        return d.clase !== 'cuentacobro' && d.clase !== 'remision';
                    });
                    
                    let tieneDeudas = docsFiltrados.some(d => d.clase !== 'recibo' && d.saldo > 0);
                    if (!tieneDeudas) return null; 
                    
                    let totalValor = docsFiltrados.reduce((sum, d) => sum + (d.clase !== 'recibo' ? (parseFloat(d.valor_pedido) || 0) : 0), 0);
                    let totalEntregado = docsFiltrados.reduce((sum, d) => sum + (d.clase !== 'recibo' ? (parseFloat(d.valor_entregado) || 0) : 0), 0);
                    let totalAbono = docsFiltrados.reduce((sum, d) => sum + (d.clase !== 'recibo' ? (parseFloat(d.abono) || 0) : 0), 0);
                    let totalSaldo = docsFiltrados.reduce((sum, d) => sum + (d.clase !== 'recibo' ? (parseFloat(d.saldo) || 0) : 0), 0);
                    
                    return {
                        ...cl,
                        documentos: docsFiltrados,
                        totales: {
                            valor: totalValor,
                            entregado: totalEntregado,
                            abono: totalAbono,
                            saldo: totalSaldo
                        }
                    };
                }).filter(cl => cl !== null);
                
                return filtrados;
            },
            pedidosAgrupados() {
                let grupos = {};
                this.pedidosFiltradosEstado.forEach(p => {
                    if (!p.cliente) return;
                    
                    let cid = p.cliente.id;
                    if (!grupos[cid]) {
                        let nombreMostrado = p.cliente.razonsocial || 'Cliente sin nombre';
                        grupos[cid] = {
                            cliente: p.cliente,
                            nombre: nombreMostrado || 'Cliente sin nombre',
                            pedidos: [],
                            total_venta: 0,
                            total_entregado: 0,
                            total_abono: 0,
                            total_saldo: 0,
                            id: cid
                        };
                    }

                    let esCredito = (p.clase_cartera === 'Credito' || p.tipo === 'ingreso');

                    if (!esCredito) {
                        // Deuda: usar saldo real de BD (ya descontados todos los pagos aplicados)
                        grupos[cid].total_venta += parseFloat(p.valor_pedido || p.total || 0);
                        grupos[cid].total_entregado += parseFloat(p.valor_entregado || 0);
                        grupos[cid].total_abono += parseFloat(p.abono || 0);
                        grupos[cid].total_saldo += parseFloat(p.saldo || 0);
                    } else {
                        // Crédito: saldo = -saldo_recibo (negativo). 
                        let creditoDisponible = Math.abs(parseFloat(p.saldo || 0)); // saldo_recibo
                        grupos[cid].total_saldo -= creditoDisponible; 
                    }

                    grupos[cid].pedidos.push(p);
                });
                return Object.values(grupos);
            },
            isActived: function(){
                return this.pagination.current_page;
            },

            //Calcula los elementos de la paginación
            pagesNumber: function() {
                if(!this.pagination.to) {
                    return [];
                }
                
                var from = this.pagination.current_page - this.offset; 
                if(from < 1) {
                    from = 1;
                }

                var to = from + (this.offset * 2); 
                if(to >= this.pagination.last_page){
                    to = this.pagination.last_page;
                }  

                var pagesArray = [];
                while(from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;             

            },
           
            totalCartera(){
                var resultado = 0;
                this.pedidosFiltradosEstado.forEach(e => {
                    let esCredito = (e.clase_cartera === 'Credito' || e.tipo === 'ingreso');
                    if (esCredito) {
                        resultado += parseFloat(e.saldo || 0); 
                    } else {
                        resultado += parseFloat(e.saldo || 0); 
                    }
                });
                return resultado;
            },
            totalAbonos(){
                var resultado = 0;
                this.pedidosFiltradosEstado.forEach(e => {
                    let esCredito = (e.clase_cartera === 'Credito' || e.tipo === 'ingreso');
                    if (!esCredito) {
                        resultado += parseFloat(e.abono || 0);
                    }
                });
                return resultado;
            },
            totalEntregado(){
                var resultado = 0;
                this.pedidosFiltradosEstado.forEach((e)=>{
                    if (e.clase_cartera != 'Credito' && e.tipo != 'ingreso') {
                        resultado += parseFloat(e.valor_entregado || 0);
                    }
                });
                return resultado;
            },
            totalVenta(){
                var resultado=0
                this.pedidosFiltradosEstado.forEach((e)=>{
                    resultado=resultado+parseFloat(e.valor_pedido || e.total || 0)
                })
                return resultado
            },
            totalECValor(){
                let sum = 0;
                this.estadoCuentaFiltrado.forEach(c => {
                    if (c.totales && c.totales.valor) {
                        sum += parseFloat(c.totales.valor);
                    }
                });
                return sum;
            },
            totalECEntregado(){
                let sum = 0;
                this.estadoCuentaFiltrado.forEach(c => {
                    if (c.totales && c.totales.entregado) {
                        sum += parseFloat(c.totales.entregado);
                    }
                });
                return sum;
            },
            totalECAbono(){
                let sum = 0;
                this.estadoCuentaFiltrado.forEach(c => {
                    if (c.totales && c.totales.abono) {
                        sum += parseFloat(c.totales.abono);
                    }
                });
                return sum;
            },
            totalECSaldo(){
                let sum = 0;
                this.estadoCuentaFiltrado.forEach(c => {
                    if (c.totales && c.totales.saldo) {
                        sum += parseFloat(c.totales.saldo);
                    }
                });
                return sum;
            },
            pagosOrdenados() {
                if (!Array.isArray(this.arrayPagos)) return [];
                return [...this.arrayPagos].sort((a, b) => {
                    // Primary sort: fecha descending
                    if (a.fecha > b.fecha) return -1;
                    if (a.fecha < b.fecha) return 1;
                    // Tiebreaker: id descending (most recently created first)
                    return b.id - a.id;
                });
            }
        },
        methods : {
            verPedido(pedido){
                this.pedido=pedido
                this.listado=0
                this.edit=1
            },
            forNum(num){
                let val = parseFloat(num);
                if (isNaN(val)) val = 0;
                return new Intl.NumberFormat("es-CO").format(val);
            },
            asignarFecha(){
                this.modalfecha=1
            },
            cerrarModalFecha(){
                this.modalfecha=0
            },
           
            asiganarIntervalo(){
                this.modalIntervalo=1
            },
            
            cerrarModalIntervalo(){
                this.modalIntervalo=0
            },
            calcularCosto(){
                let me=this;
                if(me.valor_insumo!=0){
                    me.valor_costo=me.cantidad_costo*me.valor_insumo;
                }else{
                    alert('seleccione primero un Insumo');
                }
            },
            cargarAlertaCartera() {
                let me = this;
                axios.get('/orden/alertaCartera').then(function(response) {
                    me.alertaCompletadosCount = response.data.completados.count;
                    me.alertaCompletadosTotal = response.data.completados.total;
                    me.alertaEntregadosCount = response.data.entregados.count;
                    me.alertaEntregadosTotal = response.data.entregados.total;
                    if (response.data.no_recogidos) {
                        me.alertaNoRecogidosCount = response.data.no_recogidos.count;
                        me.alertaNoRecogidosTotal = response.data.no_recogidos.total;
                    }
                    if (response.data.en_produccion) {
                        me.alertaEnProduccionCount = response.data.en_produccion.count;
                        me.alertaEnProduccionTotal = response.data.en_produccion.total;
                    }
                    if (response.data.cuentas_cobro) {
                        me.alertaCuentasCobroCount = response.data.cuentas_cobro.count;
                        me.alertaCuentasCobroTotal = response.data.cuentas_cobro.total;
                    } else if (response.data.remisionado) {
                        me.alertaCuentasCobroCount = response.data.remisionado.count;
                        me.alertaCuentasCobroTotal = response.data.remisionado.total;
                    }
                    if (response.data.total_cartera) {
                        me.alertaTotalCarteraCount = response.data.total_cartera.count;
                        me.alertaTotalCarteraTotal = response.data.total_cartera.total;
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
             
            listarPedidosCartera (page,buscar,operador,criterio){
                let me=this;
                if (me.tab == 3) {
                    me.listarHistorialPagos(page, buscar, criterio);
                    return;
                }

                 // Pequeño retardo para evitar demasiadas peticiones al servidor si escribe rápido
                 clearTimeout(this.searchTimer);
                 this.searchTimer = setTimeout(() => {
                     let endpoint = '';
                     if (me.tab === 6 || me.filtroEstadoCartera === 'Cuentas de Cobro' || me.filtroEstadoCartera === 'Remisionado') {
                         endpoint = '/orden/carteraRemisiones';
                     } else {
                         switch(me.tab) {
                             case 1: endpoint = '/orden/cartera'; break;
                             case 2: endpoint = '/orden/carteraProyectada'; break;
                             case 4: endpoint = '/orden/carteraRespaldo'; break;
                             default: endpoint = '/orden/cartera'; break;
                         }
                     }
                     var url= me.dominio + endpoint + '?page='+page+'&criterio='+ criterio+'&operador='+operador+'&buscar='+buscar;
                     axios.get(url).then(function (response) {
                         var respuesta= response.data;
                         me.arrayPedidos = respuesta.pedidos.data;
                         me.pagination= respuesta.pagination;
                         if (criterio == 'cliente_id') {
                             me.arrayClientesSug = respuesta.clientes_encontrados || [];
                         }
                     })
                     .catch(function (error) {
                         console.log(error);
                     });
                 }, 300);
            },
            listarHistorialPagos(page, buscar, criterio) {
                let me = this;
                var url = me.dominio + '/orden/historialPagos?page=' + page + '&criterio=' + criterio + '&buscar=' + buscar;
                axios.get(url).then(function (response) {
                    var respuesta = response.data;
                    me.arrayPagos = respuesta.pagos.data;
                    me.pagination = respuesta.pagination;
                    me.listarResumenPagos();
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            listarResumenPagos() {
                let me = this;
                var url = me.dominio + '/orden/resumenPagos';
                axios.get(url).then(function (response) {
                    me.arrayResumenPagos = response.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            cambiarTab(tab) {
                this.tab = tab;
                this.buscare = '';
                this.buscarv = '';
                if (tab === 5) {
                    this.cargarEstadoCuenta();
                } else {
                    this.listarPedidosCartera(1, '', 'like', 'cliente_id');
                }
            },
            seleccionarFiltroEstado(estado) {
                this.filtroEstadoCartera = estado;
                if (this.tab === 5) {
                    this.cargarEstadoCuenta();
                } else {
                    this.listarPedidosCartera(1, '', 'like', 'cliente_id');
                }
            },
            seleccionarFiltroCuentasCobro() {
                this.filtroEstadoCartera = 'Cuentas de Cobro';
                if (this.tab !== 5) {
                    this.tab = 5;
                }
                this.cargarEstadoCuenta();
            },
            seleccionarFiltroRemisionado() {
                this.seleccionarFiltroCuentasCobro();
            },
            cargarEstadoCuenta(immediate = false) {
                let me = this;
                
                // Debounce para evitar peticiones excesivas en keyup
                clearTimeout(this.searchTimer);
                
                const execute = () => {
                    me.loadingEC = true;
                    axios.get(me.dominio + '/orden/estadoCuenta', {
                        params: {
                            buscar: me.buscarEC,
                            valor: me.buscarv,
                            filtroFecha: me.filtroFecha,
                            fechaI: me.fechaI,
                            fechaF: me.fechaF,
                            criterio: 'cliente_id'
                        }
                    }).then(function (response) {
                        me.estadoCuentaData = response.data;
                        me.loadingEC = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        me.loadingEC = false;
                    });
                };

                if (immediate) {
                    execute();
                } else {
                    this.searchTimer = setTimeout(execute, 300);
                }
            },
            dSorted(docs) {
                // Ensure proper display order if needed, but the back-end already sends it ordered.
                // Just as a safety or if you want to apply more filters.
                return docs;
            },
            sumarDocs(docs, prop) {
                let total = 0;
                docs.forEach(doc => {
                    total += parseFloat(doc[prop] || 0);
                });
                return total;
            },
            sumarDocsFiltrado(docs, prop, clase) {
                let total = 0;
                docs.forEach(doc => {
                    if (doc.clase === clase) {
                        total += parseFloat(doc[prop] || 0);
                    }
                });
                return total;
            },
            pagarDesdeEC(doc, cliente) {
                const p = {
                    id: doc.raw_id,
                    total: doc.raw_total,
                    saldo: doc.raw_saldo,
                    num_comprobante: doc.raw_num,
                    cliente: cliente
                };
                this.abrirModalPago(p);
            },
            pagarClienteDesdeEC(cl) {
                const totalVenta = this.sumarDocs(cl.documentos, 'v_pedido');
                const totalAbonos = this.sumarDocs(cl.documentos, 'abono');
                const saldo = totalVenta - totalAbonos;
                
                const grupo = {
                    cliente: cl.cliente,
                    total_venta: totalVenta,
                    total_abono: totalAbonos,
                    pedidos: cl.documentos.map(d => ({
                        id: d.raw_id,
                        num_comprobante: d.num,
                        total: d.v_pedido,
                        saldo: d.saldo,
                        clase_cartera: d.clase,
                        cliente: cl.cliente
                    }))
                };
                this.abrirModalPagoCliente(grupo);
            },
            imprimirRecibo(id) {
                this.cerrarModalPago();
                window.open(this.dominio + '/orden/reciboPdf/' + id, '_blank');
            },
         
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarPedidosCartera(page,buscar,'like',criterio);
            },
           
          
         
            
            cambiarAbono(pedido){
                var me=this
                var saldo=parseFloat(pedido.total)-parseFloat(pedido.abono)
                 axios.put(me.dominio+'/orden/cambiarAbono',{
                     'id':pedido.id,
                     'abono':pedido.abono,
                     'saldo':saldo
                 })
                .then(function (response) {
                   
                }).catch(function (error) {
                    console.log(error);
                });
                this.listarPedidosCartera(1,this.buscar,'like',this.criterio);
            },
           
           
           
            imprimirOrden(){
                window.print()
            },
         
            cerrarModal(){
                this.modal=0;
            },
            abrirModal(orden, accion, data = []){
                this.arrayArticulos=[]
                this.modal = 1;
                this.tituloModal = 'Seleccione 1 o varios artículos';
            },
             ordenar(opcion){
                this.ordenarFlecha = !this.ordenarFlecha;
                let direction = this.ordenarFlecha ? 1 : -1;

                this.arrayPedidos.sort((a, b) => {
                    let valA, valB;

                    switch(opcion){
                        case 'id':
                        case 'num_comprobante':
                            valA = a.num_comprobante || a.id || '';
                            valB = b.num_comprobante || b.id || '';
                            break;
                        case 'fecha':
                            valA = a.fecha || '';
                            valB = b.fecha || '';
                            break;
                        case 'razonsocial':
                        case 'cliente':
                            valA = (a.cliente ? a.cliente.razonsocial : '') || '';
                            valB = (b.cliente ? b.cliente.razonsocial : '') || '';
                            break;
                        case 'total':
                            valA = parseFloat(a.total || 0);
                            valB = parseFloat(b.total || 0);
                            return (valA - valB) * direction;
                        case 'abono':
                            valA = parseFloat(a.abono || 0);
                            valB = parseFloat(b.abono || 0);
                            return (valA - valB) * direction;
                        case 'saldo':
                            valA = Math.abs(parseFloat(a.saldo || 0));
                            valB = Math.abs(parseFloat(b.saldo || 0));
                            return (valA - valB) * direction;
                        default:
                            valA = a[opcion] || '';
                            valB = b[opcion] || '';
                    }

                    if (valA < valB) return -1 * direction;
                    if (valA > valB) return 1 * direction;
                    return 0;
                });
            },
            filtrarFecha(){
                var me=this;
                if(this.modalIntervalo){
                    me.filtroFecha=`${this.fechaI},${this.fechaF}`
                }

                if (this.tab === 5) {
                    this.cargarEstadoCuenta(true);
                    this.modalIntervalo = 0;
                    return;
                }

                var url= me.dominio+'/orden/filtrarFecha?filtroFecha='+ me.filtroFecha;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayPedidos = respuesta.ordenes.data;
                    me.pagination= respuesta.pagination
                    me.modalIntervalo=0

                }).catch(function (error) {
                    console.log(error);
                });
            },
            
            filtrarOrdenes(buscar){
                var me=this
                me.buscar=buscar
                var url= me.dominio+'/orden/filtrarOrdenes?buscar='+buscar;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayPedidos = respuesta.ordenes.data;
                    me.pagination= respuesta.pagination
                }).catch(function (error) {
                    console.log(error);
                });
            },
           
            ocultarDetalle(){
                this.listado=1;
            },
            abrirModalPago(pedido) {
                let grupo = this.pedidosAgrupados ? this.pedidosAgrupados.find(g => g.cliente.id === pedido.cliente.id) : null;
                
                if (!grupo && this.tab === 5 && this.estadoCuentaData) {
                    let cl = this.estadoCuentaData.find(c => c.cliente.id === pedido.cliente.id);
                    if (cl) {
                        grupo = {
                            cliente: cl.cliente,
                            total_venta: this.sumarDocs(cl.documentos, 'v_pedido'),
                            total_abono: this.sumarDocs(cl.documentos, 'abono'),
                            pedidos: cl.documentos.map(d => ({
                                id: d.raw_id,
                                num_comprobante: d.raw_num || d.num,
                                total: d.v_pedido,
                                saldo: d.saldo,
                                clase_cartera: d.clase,
                                cliente: cl.cliente
                            }))
                        };
                    }
                }
                
                this.grupoActual = grupo || null;

                this.modalPago = 1;
                this.pagoData.es_masivo = false;
                this.pagoData.comprobante_id = pedido.id;
                this.pagoData.cliente_id = pedido.cliente.id;
                this.pagoData.num_comprobante = pedido.num_comprobante || pedido.id;
                this.pagoData.total = pedido.total;
                this.pagoData.saldo = pedido.saldo;
                this.pagoData.monto = pedido.saldo;
                this.pagoData.pedido_especifico_id = pedido.id;
                
                if (grupo && grupo.pedidos) {
                    let pedFiltered = grupo.pedidos.filter(p => !['Credito', 'recibo'].includes(p.clase_cartera) && parseFloat(p.saldo) > 0);
                    if (!pedFiltered.some(p => p.id === pedido.id)) {
                        pedFiltered.push(pedido);
                    }
                    this.pedidosClienteActivos = pedFiltered;
                } else {
                    this.pedidosClienteActivos = [pedido];
                }

                this.listarPagos(pedido.id);
            },
            abrirModalPagoCliente(grupo) {
                this.grupoActual = grupo;
                this.modalPago = 1;
                this.pagoData.es_masivo = true;
                this.pagoData.cliente_id = grupo.cliente.id;
                this.pagoData.num_comprobante = `TOTAL ${grupo.cliente.razonsocial || grupo.nombre}`;
                this.pagoData.total = grupo.total_venta;
                let saldoCalculado = parseFloat(grupo.total_venta) - parseFloat(grupo.total_abono);
                this.pagoData.saldo = saldoCalculado;
                this.pagoData.monto = saldoCalculado > 0 ? saldoCalculado : 0;
                this.pagoData.pedido_especifico_id = null;
                
                if (grupo.pedidos) {
                    this.pedidosClienteActivos = grupo.pedidos.filter(p => !['Credito', 'recibo'].includes(p.clase_cartera) && parseFloat(p.saldo) > 0);
                } else {
                    this.pedidosClienteActivos = [];
                }

                if (this.pedidosClienteActivos.length > 0) {
                    this.pagoData.pedido_especifico_id = this.pedidosClienteActivos[0].id;
                    this.pagoData.es_masivo = false;
                    this.pagoData.comprobante_id = this.pedidosClienteActivos[0].id;
                } else {
                    this.pagoData.pedido_especifico_id = null;
                }
                
                this.arrayPagos = []; 
            },
            actualizarMontoEspecifico() {
                if (this.pagoData.pedido_especifico_id) {
                    let ped = this.pedidosClienteActivos.find(p => p.id === this.pagoData.pedido_especifico_id);
                    if (ped) {
                        // this.pagoData.monto = ped.saldo; // User requested NOT to change the amount automatically
                        this.pagoData.saldo = ped.saldo;
                        this.pagoData.total = ped.total;
                        this.pagoData.num_comprobante = ped.num_comprobante || ped.id;
                        this.pagoData.comprobante_id = ped.id;
                        this.pagoData.es_masivo = false;
                        this.listarPagos(ped.id);
                    }
                } else if (this.grupoActual) {
                    this.pagoData.es_masivo = true;
                    this.pagoData.comprobante_id = 0;
                    this.pagoData.num_comprobante = `TOTAL ${this.grupoActual.cliente.razonsocial || this.grupoActual.nombre}`;
                    this.pagoData.total = this.grupoActual.total_venta;
                    let saldoCalculado = parseFloat(this.grupoActual.total_venta) - parseFloat(this.grupoActual.total_abono);
                    this.pagoData.saldo = saldoCalculado;
                    this.pagoData.monto = saldoCalculado > 0 ? saldoCalculado : 0;
                    this.arrayPagos = [];
                }
            },
            alternarGrupo(grupoId) {
                const index = this.expandedGroups.indexOf(grupoId);
                if (index > -1) {
                    this.expandedGroups.splice(index, 1);
                } else {
                    this.expandedGroups.push(grupoId);
                }
            },
            estaExpandido(grupoId) {
                return this.expandedGroups.indexOf(grupoId) > -1;
            },
            alternarGrupoEC(grupoId) {
                const index = this.expandedECGroups.indexOf(grupoId);
                if (index > -1) {
                    this.expandedECGroups.splice(index, 1);
                } else {
                    this.expandedECGroups.push(grupoId);
                }
            },
            pagarDesdeEC(doc, cliente) {
                if (doc.clase === 'cuentacobro') {
                    this.modalPago = 1;
                    this.pagoData = {
                        es_cuentacobro: true,
                        es_masivo: false,
                        cuentacobro_id: doc.raw_id,
                        comprobante_id: doc.raw_id,
                        cliente_id: cliente.id,
                        num_comprobante: doc.raw_num ? ('CC #' + doc.raw_num) : doc.label,
                        total: parseFloat(doc.v_pedido || doc.raw_total || 0),
                        saldo: parseFloat(doc.saldo || doc.raw_saldo || 0),
                        monto: parseFloat(doc.saldo || doc.raw_saldo || 0),
                        fecha: new Date().toISOString().substring(0, 10),
                        forma_pago: 'Efectivo',
                        observaciones: '',
                        pedido_especifico_id: null
                    };
                    this.grupoActual = { cliente: cliente };
                    this.pedidosClienteActivos = [];
                } else {
                    const p = {
                        id: doc.raw_id,
                        total: doc.raw_total || doc.v_pedido,
                        saldo: doc.raw_saldo || doc.saldo,
                        num_comprobante: doc.raw_num || doc.label,
                        cliente: cliente
                    };
                    this.abrirModalPago(p);
                }
            },
            pagarClienteDesdeEC(cl) {
                const totalVenta = this.sumarDocs(cl.documentos, 'v_pedido');
                const totalAbonos = this.sumarDocs(cl.documentos, 'abono');
                const saldo = totalVenta - totalAbonos;
                
                const grupo = {
                    cliente: cl.cliente,
                    total_venta: totalVenta,
                    total_abono: totalAbonos,
                    pedidos: cl.documentos.map(d => ({
                        id: d.raw_id,
                        num_comprobante: d.num,
                        total: d.v_pedido,
                        saldo: d.saldo,
                        clase_cartera: d.clase,
                        cliente: cl.cliente
                    }))
                };
                this.abrirModalPagoCliente(grupo);
            },
            imprimirRecibo(id) {
                this.cerrarModalPago();
                window.open(this.dominio + '/orden/reciboPdf/' + id, '_blank');
            },
         
            cambiarPagina(page,buscar,criterio){
                let me = this;
                me.pagination.current_page = page;
                me.listarPedidosCartera(page,buscar,'like',criterio);
            },
            estaExpandidoEC(grupoId) {
                return this.expandedECGroups.indexOf(grupoId) > -1;
            },
            imprimirCC(id) {
                window.open(this.dominio + '/imprimirPedido?id=' + id, '_blank');
            },
            cerrarModalPago() {
                this.modalPago = 0;
                if (this.pagoData) {
                    this.pagoData.es_cuentacobro = false;
                }
            },
            registrarPago() {
                let me = this;
                if (me.pagoData.monto <= 0) {
                    Swal.fire('Error', 'Ingrese un monto válido', 'error');
                    return;
                }

                let url = '';
                let payload = me.pagoData;

                if (me.pagoData.es_cuentacobro) {
                    url = '/comprobante/registrarAbonoCuentaCobro';
                    payload = {
                        cuentacobro_id: me.pagoData.cuentacobro_id,
                        monto: me.pagoData.monto,
                        fecha: me.pagoData.fecha || new Date().toISOString().substring(0, 10),
                        forma_pago: me.pagoData.forma_pago || 'Efectivo',
                        observaciones: me.pagoData.observaciones || ''
                    };
                } else {
                    url = me.pagoData.es_masivo ? '/orden/registrarPagoMasivo' : '/orden/registrarPago';
                }

                me.loading = true;
                axios.post(me.dominio + url, payload)
                    .then(function(response) {
                        me.loading = false;
                        let rid = response.data.recibo_id || (response.data.recibos && response.data.recibos.length ? response.data.recibos[0].id : null);
                        Swal.fire({
                            title: 'Pago Registrado',
                            text: response.data.message || 'El pago se guardó correctamente y se actualizó el saldo.',
                            icon: 'success',
                            showCancelButton: rid ? true : false,
                            confirmButtonText: 'Ver Recibo PDF',
                            cancelButtonText: 'Cerrar'
                        }).then((result) => {
                            if (result.value && rid) {
                                me.verReciboPdf(rid);
                            }
                        });
                        me.cerrarModalPago();
                        me.listarPedidosCartera(me.pagination.current_page, me.buscar, 'like', me.criterio);
                        me.cargarEstadoCuenta(true);
                    })
                    .catch(function(error) {
                        me.loading = false;
                        console.error(error);
                        let msg = error.response && error.response.data && error.response.data.error ? error.response.data.error : (error.response && error.response.data && error.response.data.message ? error.response.data.message : 'No se pudo registrar el pago');
                        Swal.fire('Error', msg, 'error');
                    });
            },
            cruzarCartera(clienteId) {
                let me = this;
                Swal.fire({
                    title: '¿Aplicar saldos a favor?',
                    text: "Se usarán los créditos disponibles para pagar las facturas más antiguas de este cliente.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cruzar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        me.loading = true;
                        axios.post(me.dominio + '/orden/cruzarCarteraCliente', {
                            cliente_id: clienteId
                        }).then(function(response) {
                            me.loading = false;
                            Swal.fire('Proceso Completado', `Se realizaron ${response.data.cruces} cruces de cartera.`, 'success');
                            me.listarPedidosCartera(1, '', 'like', 'cliente_id');
                        }).catch(function(error) {
                            me.loading = false;
                            console.error(error);
                            Swal.fire('Error', 'No se pudo realizar el cruce', 'error');
                        });
                    }
                });
            },
            abrirModalEditarRecibo(recibo) {
                 this.modalEditarRecibo = 1;
                 this.reciboEditData = {
                     id: recibo.id.toString().replace('R', ''),
                     fecha: recibo.fecha,
                     monto: Math.abs(recibo.abono),
                     num_recibo: recibo.num_comprobante,
                     forma_pago: recibo.forma_pago || 'Banco',
                     observaciones: recibo.observaciones || ''
                 };
            },
            cerrarModalEditarRecibo() {
                this.modalEditarRecibo = 0;
            },
            actualizarRecibo() {
                let me = this;
                me.loading = true;
                axios.put(me.dominio + '/orden/actualizarRecibo', me.reciboEditData)
                    .then(function(response) {
                        me.loading = false;
                        Swal.fire('Actualizado', 'Recibo actualizado correctamente', 'success');
                        me.modalEditarRecibo = 0;
                        me.listarPedidosCartera(1, me.buscar, 'like', me.criterio);
                    })
                    .catch(function(error) {
                        me.loading = false;
                        let msg = error.response && error.response.data && error.response.data.error ? error.response.data.error : 'No se pudo actualizar';
                        Swal.fire('Error', msg, 'error');
                    });
            },
            eliminarRecibo(id) {
                let me = this;
                let cleanId = String(id).replace(/[^0-9]/g, '');
                Swal.fire({
                    title: '¿Eliminar Recibo?',
                    text: "Se revertirán todos los cruces y el saldo volverá a los documentos. Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        me.loading = true;
                        axios.delete(me.dominio + '/orden/eliminarRecibo', {
                            data: { id: cleanId }
                        }).then(function(response) {
                            me.loading = false;
                            Swal.fire('Eliminado', 'El recibo ha sido eliminado y los saldos revertidos.', 'success');
                            if (typeof me.listarEstadoCuenta === 'function') {
                                me.listarEstadoCuenta();
                            }
                            if (typeof me.listarPedidosCartera === 'function') {
                                me.listarPedidosCartera(1, me.buscar, 'like', me.criterio);
                            }
                        }).catch(function(error) {
                            me.loading = false;
                            console.error(error);
                            let msg = error.response && error.response.data && error.response.data.error ? error.response.data.error : 'No se pudo eliminar el recibo';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                });
            },
            listarPagos(id) {
                let me = this;
                axios.get(me.dominio + '/orden/listarPagos/' + id).then(function(response) {
                    me.arrayPagos = response.data;
                });
            },
            verReciboPdf(id) {
                this.cerrarModalPago();
                window.open(this.dominio + '/orden/reciboPdf/' + id, '_blank');
            },
            limpiarFormulario() {
                this.listado=1;
                this.articulo_seleccionado=''
                this.insumo_seleccionado=''
                this.cliente_seleccionado=''
                this.abono=0
                this.tamano=''
                this.medida_material=''
                this.detalles_diseno=''
                this.observaciones=''
                this.cantidad=1000
                this.unidad=''
                this.carpeta_cliente=''
                this.descuento=0
                this.saldo=0
                this.precio=0
                this.idarticulo=0
                this.fecha_entrega=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.fechaorden=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.fecha=`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`
                this.estadoc='C'
                this.estadop=''
            },
            verOrden(orden){
                this.listado = 2
                this.idorden=orden.idorden
                this.estadoc=orden.estadoc
                this.estadop=orden.estadop
                this.id_cliente=orden.idcliente
                this.articulo=orden.articulo
                this.fecha_entrega=orden.fecha_entrega
                this.fecha=orden.fecha
                this.carpeta_cliente=orden.carpeta_cliente
                this.detalles_diseno=orden.detalles_diseno
                this.observaciones=orden.observaciones
                this.tamano= orden.tamano
                this.medida_material= orden.medida_material
                this.cantidad= orden.cantidad
                this.subtotal_orden=orden.totalParcial
                this.descuento=orden.descuento
                this.impuesto=orden.impuesto
                this.total=orden.total
                this.abono=orden.abono
                this.saldo=orden.saldo
                this.arrayDetalle=orden.detalles
                this.arrayCostos=orden.costos
            },
            calcularDias(index,orden){
                var fecha=new Date()
                var fechaini = new Date(`${fecha.getFullYear()}-${(fecha.getMonth()+1)}-${fecha.getDate()}`)
                var fechafin = new Date(orden.fecha_entrega)
                var diasdif= fechafin.getTime()-fechaini.getTime()
                var contdias = Math.round(diasdif/(1000*60*60*24))
               return contdias
            },
            cerrarModalo(){
                this.modalo=0
                this.ordenv=[]
            },
            abrirModalAsignar(pago) {
                this.reciboAsignar = {
                    id: pago.id,
                    num_recibo: pago.num_recibo,
                    saldo_recibo: pago.saldo_recibo || 0,
                    cliente_id: pago.cliente_id
                };
                this.asignarData = {
                    recibo_id: pago.id,
                    comprobante_id: null,
                    monto: pago.saldo_recibo || pago.monto
                };
                this.comprobantesCliente = [];
                this.modalAsignar = 1;
                // Cargar comprobantes pendientes del cliente
                this.loadingComprobantes = true;
                axios.get(this.dominio + '/orden/comprobantesClientePendientes?cliente_id=' + pago.cliente_id)
                    .then(response => {
                        this.comprobantesCliente = response.data;
                        this.loadingComprobantes = false;
                    })
                    .catch(() => { this.loadingComprobantes = false; });
            },
            cerrarModalAsignar() {
                this.modalAsignar = 0;
                this.comprobantesCliente = [];
            },
            ejecutarAsignacion() {
                let me = this;
                if (!me.asignarData.comprobante_id) {
                    Swal.fire('Error', 'Seleccione un documento al cual aplicar el abono.', 'error');
                    return;
                }
                if (me.asignarData.monto <= 0) {
                    Swal.fire('Error', 'El monto debe ser mayor a 0.', 'error');
                    return;
                }
                me.loading = true;
                axios.post(me.dominio + '/orden/registrarCruce', me.asignarData)
                    .then(function(response) {
                        me.loading = false;
                        Swal.fire('Éxito', 'El abono fue aplicado correctamente al documento seleccionado.', 'success');
                        me.cerrarModalAsignar();
                        me.listarPedidosCartera(me.pagination.current_page, me.buscar, 'like', me.criterio);
                    })
                    .catch(function(error) {
                        me.loading = false;
                        let msg = error.response && error.response.data && error.response.data.error
                            ? error.response.data.error : 'No se pudo aplicar el abono.';
                        Swal.fire('Error', msg, 'error');
                    });
            }
           
          
        },
        created() {
             this.dominio = window.APP_URL ? window.APP_URL : window.location.pathname.replace('/index.php', '').replace(/\/$/, '');
             this.cargarEstadoCuenta(true);
        },
        mounted() {
            // Verificar si venimos desde los accesos directos del escritorio o campana
            let filtroInicial = localStorage.getItem('filtroCarteraInicial');
            if (filtroInicial) {
                this.filtroEstadoCartera = filtroInicial;
                this.tab = 5; // Cambiamos a Estado de Cuenta para ver los pedidos correctamente
                localStorage.removeItem('filtroCarteraInicial');
            }
            this.cargarAlertaCartera();
            
            if (this.tab === 5) {
                this.cargarEstadoCuenta(true);
            }
        },
    }
</script>

<style scoped>
/* Modern Font Imports */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

.cartera-wrapper {
    font-family: 'Outfit', sans-serif;
    color: #2d3748;
    flex: 1;
    min-width: 0;
}

/* Header & Breadcrumb */
.bg-premium-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    border: none;
}

.header-icon-box {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    backdrop-filter: blur(5px);
}

.rounded-xl {
    border-radius: 18px !important;
}

.breadcrumb-container {
    border-radius: 0 0 15px 15px;
}

/* Tabs */
.custom-pills .nav-link {
    border-radius: 30px;
    color: #64748b;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-pills .nav-link:hover {
    background: #f1f5f9;
}

.custom-pills .nav-link.active {
    background: #3b82f6;
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-round {
    border-radius: 30px;
    padding: 8px 20px;
    font-weight: 600;
}

.badge-light-primary {
    background: #eff6ff;
    color: #3b82f6;
    border-radius: 10px;
}

/* Search Area */
.search-modern-container {
    background: #f8fafc;
    border-radius: 15px;
}

.form-input-modern {
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    padding: 10px 15px;
    transition: all 0.2s;
}

.form-input-modern:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

/* Table Style */
.table-modern thead th {
    background: #f1f5f9;
    border: none;
    padding: 15px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
    font-weight: 700;
}

.row-hover:hover {
    background-color: #f8fafc;
}

.group-row {
    background-color: #f1f5f9 !important;
    border-left: 4px solid #3b82f6;
}

/* Modals */
.header-premium-pago {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
}

.bg-glass-modal {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.resumen-pago-box {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
}

.btn-premium-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    transition: all 0.3s;
}

.btn-premium-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-glass-light {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    backdrop-filter: blur(5px);
}

.btn-glass-light:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
}

/* Pagination */
.pagination .page-link {
    border: none;
    margin: 0 3px;
    border-radius: 8px;
    color: #475569;
    font-weight: 600;
}

.pagination .page-item.active .page-link {
    background: #3b82f6;
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
}

.account-statement-table {
    width: 100%;
    table-layout: fixed;
}
.account-statement-table th, .account-statement-table td {
    border: 1px solid #e2e8f0 !important;
    vertical-align: middle;
    word-wrap: break-word;
    white-space: normal !important;
}
.doc-label {
    font-size: 0.9rem;
    line-height: 1;
}
.lh-1 {
    line-height: 1.1;
}
.btn-xs {
    padding: 0.1rem 0.3rem;
    font-size: 0.75rem;
}
.table-info-light {
    background-color: #f8fbff;
}
.font-weight-600 {
    font-weight: 600;
}
.btn-premium-success {
    background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
    border: none;
    color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.btn-premium-success:hover {
    background: linear-gradient(135deg, #16a34a 0%, #166534 100%);
    color: white;
    transform: translateY(-1px);
}
.btn-premium-success-header {
    background: white;
    color: #22c55e;
    border: none;
    border-radius: 50px;
    transition: all 0.3s ease;
}
.btn-premium-success-header:hover {
    background: #f0fdf4;
    color: #15803d;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.opacity-7 {
    opacity: 0.7;
}

</style>
<style>  
    .insumos, .producto, .cliente{
        position: relative;
    } 
    .modal{
        z-index: 1040;
    }
   
    .modal-content{
        width: 100% !important;
        position: relative !important;
        border-radius: 8px !important;
        box-shadow: 0 5px 25px rgba(0,0,0,0.2) !important;
    }
    .mostrar{
        display: flex !important;
        align-items: flex-start !important;
        justify-content: center !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 1050 !important;
        background-color: rgba(0,0,0,0.6) !important;
        overflow-x: hidden !important;
        overflow-y: hidden !important;
    }
    .modal-dialog {
        width: 100%;
        margin: 1.75rem auto;
        pointer-events: auto !important;
    }
    .mostrar .modal-dialog {
        margin: 10px auto !important;
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
    @media (min-width: 576px) {
        .modal-dialog {
            max-width: 800px;
        }
        /* Special case for the wide payment modal */
        .modal-lg, .modal-dialog-large {
            max-width: 1100px !important;
        }
    }
    .div-error{
        display: flex;
        justify-content: center;
    }
    .text-error{
        color: red !important;
        font-weight: bold;
    }
    @media (min-width: 600px) {
        .btnagregar {
            margin-top: 2rem;
        }
    }

</style>

