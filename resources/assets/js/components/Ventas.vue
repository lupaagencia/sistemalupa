<template>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb bg-white border-0 shadow-sm mb-4">
            <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            <li class="breadcrumb-item active">Ventas</li>
        </ol>

        <div class="container-fluid">
            <!-- Glassmorphism Section Container -->
            <div class="contenedor-seccion shadow-lg rounded-xl overflow-hidden bg-white">
                <div class="seccion-header-premium py-4 px-4 d-flex justify-content-between align-items-center bg-dark text-white">
                    <div class="d-flex align-items-center">
                        <div class="header-icon-box mr-3">
                            <i class="fa fa-line-chart text-primary"></i>
                        </div>
                        <h3 class="mb-0 font-weight-bold">GESTIÓN DE VENTAS</h3>
                    </div>
                    <div class="header-summary d-none d-md-flex">
                        <div class="summary-item mr-4">
                            <span class="text-muted small d-block">VENTA TOTAL</span>
                            <span class="font-weight-bold h5 mb-0">${{ forNum(totalVenta) }}</span>
                        </div>
                        <div class="summary-item mr-4">
                            <span class="text-muted small d-block">CARTERA TOTAL</span>
                            <span class="font-weight-bold h5 mb-0 text-danger">${{ forNum(totalCartera) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="text-muted small d-block">ABONOS</span>
                            <span class="font-weight-bold h5 mb-0 text-success">${{ forNum(totalAbonos) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Filters & Toolbar -->
                <div class="filters-bar p-3 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-xl-6 col-lg-8">
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" v-model="buscare" @keyup="listarVentas(1,`${buscare}`,'like','cliente_id')" class="form-control border-left-0" placeholder="Buscar por cliente o empresa...">
                                <input type="text" v-model="buscarv" @keyup="listarVentas(1,`${buscarv}`,'like','total')" class="form-control" style="max-width: 150px;" placeholder="Valor orden">
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-4 text-right mt-3 mt-lg-0">
                            <div class="btn-group">
                                <button class="btn btn-outline-primary" @click="asiganarIntervalo()">
                                    <i class="fa fa-calendar mr-1"></i> Intervalo
                                </button>
                                <select class="custom-select" style="width: auto;" @change="filtrarFechaVentas()" v-model="filtroFecha">
                                    <option value="1">Filtrar por fecha</option>   
                                    <option value="hoy">Hoy</option>   
                                    <option value="ayer">Ayer</option>  
                                    <option value="ultimos7">Últimos 7 días</option>  
                                    <option value="ultimos30">Últimos 30 días</option>  
                                    <option value="semana">Esta semana</option>  
                                    <option value="mes">Este mes</option>  
                                </select>
                                <button class="btn btn-warning" @click="imprimirOrden">
                                    <i class="fa fa-print mr-1"></i> Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listado Grouped by Client -->
                <template v-if="listado===1">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Acción</th>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Trabajo</th>
                                        <th>Estado DIAN</th>
                                        <th>Abono</th>
                                        <th>Saldo</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <!-- Groups -->
                                <tbody v-for="(grupo, gIdx) in pedidosAgrupados" :key="'g'+gIdx">
                                    <!-- Header del Grupo -->
                                    <tr class="group-row-premium">
                                        <td>
                                            <button class="btn btn-sm btn-dark" @click="alternarGrupo(grupo.cliente ? grupo.cliente.id : 0)">
                                                <i :class="estaExpandido(grupo.cliente ? grupo.cliente.id : 0) ? 'fa fa-minus' : 'fa fa-plus'"></i>
                                            </button>
                                        </td>
                                        <td colspan="2" class="text-center text-muted small italic">--- Agrupado ---</td>
                                        <td class="font-premium"><strong>{{ grupo.cliente ? grupo.cliente.razonsocial : 'Cliente Desconocido' }}</strong></td>
                                        <td></td>
                                        <td class="text-center">
                                            <span class="badge badge-pill badge-info px-3 py-1">
                                                {{ grupo.pedidos.length }} órdenes
                                            </span>
                                        </td>
                                        <td class="font-weight-bold text-success">${{ forNum(grupo.total_abono) }}</td>
                                        <td class="font-weight-bold text-danger">${{ forNum(grupo.total_saldo) }}</td>
                                        <td class="font-weight-bold">${{ forNum(grupo.total_venta) }}</td>
                                    </tr>

                                    <!-- Filas de Órdenes (Solo si está expandido) -->
                                    <tr v-if="estaExpandido(grupo.cliente ? grupo.cliente.id : 0)" v-for="(pedido, index) in grupo.pedidos" :key="'p'+pedido.id" class="row-detail-glass">
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <button type="button" @click="verPedido(pedido)" class="btn btn-outline-primary btn-sm" title="Ver Detalles">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <!-- Transmitir DIAN -->
                                                <button v-if="!pedido.factura_electronica || pedido.factura_electronica.estado_dian === 'Rechazado'" 
                                                        type="button" 
                                                        @click="transmitirFactura(pedido)" 
                                                        class="btn btn-outline-warning btn-sm" 
                                                        title="Transmitir a DIAN"
                                                        :disabled="transmitiendoId === pedido.id">
                                                    <i class="fa" :class="transmitiendoId === pedido.id ? 'fa-spinner fa-spin' : 'fa-cloud-upload'"></i>
                                                </button>
                                                <!-- Descargar XML -->
                                                <button v-if="pedido.factura_electronica && pedido.factura_electronica.estado_dian === 'Aceptado'" 
                                                        type="button" 
                                                        @click="descargarArchivo('xml', pedido.factura_electronica.id)" 
                                                        class="btn btn-outline-info btn-sm" 
                                                        title="Descargar XML">
                                                    <i class="fa fa-file-code-o"></i>
                                                </button>
                                                <!-- Descargar PDF -->
                                                <button v-if="pedido.factura_electronica && pedido.factura_electronica.estado_dian === 'Aceptado'" 
                                                        type="button" 
                                                        @click="descargarArchivo('pdf', pedido.factura_electronica.id)" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        title="Descargar PDF">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>{{ pedido.id }}</td>
                                        <td>{{ pedido.fecha }}</td>
                                        <td class="text-muted small">{{ pedido.cliente ? pedido.cliente.razonsocial : '---' }}</td>
                                        <td>{{ pedido.articulo }}</td>
                                        <td>
                                            <!-- DIAN Status Badge -->
                                            <span v-if="!pedido.factura_electronica" class="badge badge-secondary px-2 py-1" style="font-size: 0.85em;">
                                                <i class="fa fa-cloud"></i> No Emitido
                                            </span>
                                            <span v-else-if="pedido.factura_electronica.estado_dian === 'Aceptado'" class="badge badge-success px-2 py-1" style="font-size: 0.85em; cursor: pointer;" @click="verDetallesDIAN(pedido)">
                                                <i class="fa fa-check-circle"></i> Aceptado
                                            </span>
                                            <span v-else-if="pedido.factura_electronica.estado_dian === 'Pendiente'" class="badge badge-warning px-2 py-1 text-white" style="font-size: 0.85em;">
                                                <i class="fa fa-refresh fa-spin"></i> Pendiente
                                            </span>
                                            <span v-else-if="pedido.factura_electronica.estado_dian === 'Rechazado'" class="badge badge-danger px-2 py-1" style="font-size: 0.85em; cursor: pointer;" @click="verDetallesDIAN(pedido)">
                                                <i class="fa fa-times-circle"></i> Rechazado
                                            </span>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" class="form-control" @change="cambiarAbono(pedido)" v-model="pedido.abono">
                                            </div>
                                        </td>
                                        <td class="font-weight-bold">${{ forNum(pedido.saldo) }}</td>
                                        <td class="font-weight-bold">${{ forNum(pedido.total) }}</td>
                                    </tr>
                                </tbody>
                                
                                <!-- No results -->
                                <tbody v-if="pedidosAgrupados.length === 0">
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="empty-state text-muted">
                                                <i class="fa fa-line-chart fa-4x mb-3" style="opacity: 0.2"></i>
                                                <h5>No hay registros de ventas para mostrar</h5>
                                                <p>Ajuste los filtros o realice una búsqueda diferente.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <div class="p-4 bg-light border-top">
                            <nav class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info text-muted small">
                                    Mostrando página {{ pagination.current_page }} de {{ pagination.last_page }}
                                </div>
                                <ul class="pagination pagination-modern mb-0">
                                    <li class="page-item" :class="{disabled: pagination.current_page <= 1}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1, buscar, criterio)"><i class="fa fa-chevron-left"></i></a>
                                    </li>
                                    <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{active: page == isActived}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(page, buscar, criterio)" v-text="page"></a>
                                    </li>
                                    <li class="page-item" :class="{disabled: pagination.current_page >= pagination.last_page}">
                                        <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1, buscar, criterio)"><i class="fa fa-chevron-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </template>

                <!-- Vista Detalle de Orden -->
                <template v-else-if="listado===2">
                    <div class="p-4 bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-outline-dark mr-2" @click="ocultarDetalle">
                                <i class="fa fa-arrow-left mr-1"></i> Volver al listado
                            </button>
                            <!-- DIAN Status Badge -->
                            <span v-if="pedidoActivo" class="d-inline-block align-middle ml-2">
                                <span v-if="!pedidoActivo.factura_electronica" class="badge badge-secondary px-3 py-2" style="font-size: 0.9em;">
                                    <i class="fa fa-cloud mr-1"></i> Estado DIAN: No Emitido
                                </span>
                                <span v-else-if="pedidoActivo.factura_electronica.estado_dian === 'Aceptado'" class="badge badge-success px-3 py-2" style="font-size: 0.9em; cursor: pointer;" @click="verDetallesDIAN(pedidoActivo)">
                                    <i class="fa fa-check-circle mr-1"></i> Estado DIAN: Aceptado
                                </span>
                                <span v-else-if="pedidoActivo.factura_electronica.estado_dian === 'Pendiente'" class="badge badge-warning px-3 py-2 text-white" style="font-size: 0.9em;">
                                    <i class="fa fa-refresh fa-spin mr-1"></i> Estado DIAN: Pendiente
                                </span>
                                <span v-else-if="pedidoActivo.factura_electronica.estado_dian === 'Rechazado'" class="badge badge-danger px-3 py-2" style="font-size: 0.9em; cursor: pointer;" @click="verDetallesDIAN(pedidoActivo)">
                                    <i class="fa fa-times-circle mr-1"></i> Estado DIAN: Rechazado
                                </span>
                            </span>
                        </div>
                        <div class="d-flex align-items-center mt-2 mt-sm-0">
                            <!-- DIAN Transmission Buttons -->
                            <template v-if="pedidoActivo">
                                <button v-if="!pedidoActivo.factura_electronica || pedidoActivo.factura_electronica.estado_dian === 'Rechazado'" 
                                        type="button" 
                                        @click="transmitirFactura(pedidoActivo)" 
                                        class="btn btn-warning shadow-sm mr-2" 
                                        :disabled="transmitiendoId === pedidoActivo.id">
                                    <i class="fa mr-1" :class="transmitiendoId === pedidoActivo.id ? 'fa-spinner fa-spin' : 'fa-cloud-upload'"></i>
                                    Transmitir a DIAN
                                </button>
                                <template v-if="pedidoActivo.factura_electronica && pedidoActivo.factura_electronica.estado_dian === 'Aceptado'">
                                    <button type="button" 
                                            @click="descargarArchivo('xml', pedidoActivo.factura_electronica.id)" 
                                            class="btn btn-info text-white shadow-sm mr-2">
                                        <i class="fa fa-file-code-o mr-1"></i> Descargar XML
                                    </button>
                                    <button type="button" 
                                            @click="descargarArchivo('pdf', pedidoActivo.factura_electronica.id)" 
                                            class="btn btn-danger shadow-sm mr-2">
                                        <i class="fa fa-file-pdf-o mr-1"></i> Descargar PDF
                                    </button>
                                </template>
                            </template>
                            <button class="btn btn-warning shadow-sm" @click="imprimirOrden">
                                <i class="fa fa-print mr-1"></i> {{ (pedidoActivo && pedidoActivo.factura_electronica && pedidoActivo.factura_electronica.estado_dian === 'Aceptado') ? 'Imprimir Factura' : 'Imprimir Orden' }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-5 bg-white doc-print-container">
                        <!-- Invoice Header -->
                        <div class="d-flex justify-content-between align-items-start mb-5">
                            <div class="invoice-logo">
                                <img src="img/LOGO-LUPA.jpg" style="height: 80px;" alt="Logo">
                            </div>
                            <div class="text-right">
                                <h1 class="h2 font-weight-bold text-primary mb-1">
                                    {{ (pedidoActivo && pedidoActivo.factura_electronica && pedidoActivo.factura_electronica.estado_dian === 'Aceptado') ? 'FACTURA ELECTRÓNICA' : 'ORDEN DE TRABAJO' }}
                                </h1>
                                <h4 class="text-muted">No. {{ idorden }}</h4>
                                <div class="mt-3">
                                    <span class="d-block text-muted small">FECHA DE EMISIÓN</span>
                                    <span class="font-weight-bold">{{ fecha }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Información del Cliente</h6>
                                <div class="p-3 rounded-lg border bg-light">
                                    <h5 class="font-weight-bold mb-2">{{ nombre }}</h5>
                                    <p class="mb-1 text-muted">{{ tipo_documento }} {{ num_documento }}</p>
                                    <p class="mb-1 text-muted"><i class="fa fa-map-marker mr-2"></i>{{ direccion }}</p>
                                    <p class="mb-1 text-muted"><i class="fa fa-phone mr-2"></i>{{ telefono_contacto }}</p>
                                    <p class="mb-0 text-muted"><i class="fa fa-envelope mr-2"></i>{{ email_contacto }}</p>
                                </div>
                            </div>
                            <div class="col-md-5 offset-md-1">
                                <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Detalles de la Orden</h6>
                                <div class="p-3 rounded-lg border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Producto:</span> <span class="font-weight-bold">{{ articulo }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Cantidad:</span> <span class="font-weight-bold">{{ cantidad }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Medidas:</span> <span class="font-weight-bold">{{ tamano }} x {{ medida_material }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-0">
                                        <span class="text-muted">Entrega:</span> <span class="font-weight-bold text-primary">{{ fecha_entrega }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive mb-5">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3">
                                            {{ (pedidoActivo && pedidoActivo.factura_electronica && pedidoActivo.factura_electronica.estado_dian === 'Aceptado') ? 'SUBTOTAL' : 'VALOR ORDEN' }}
                                        </th>
                                        <th class="py-3">IVA</th>
                                        <th class="py-3">DESCUENTO</th>
                                        <th class="py-3">ABONO</th>
                                        <th class="py-3 text-right">TOTAL A PAGAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="h5">
                                        <td class="py-4 font-weight-bold">${{ forNum(valor) }}</td>
                                        <td class="py-4 text-muted">${{ forNum(totalImpuesto) }}</td>
                                        <td class="py-4 text-muted">${{ forNum(descuento) }}</td>
                                        <td class="py-4 text-success font-weight-bold">${{ forNum(abono) }}</td>
                                        <td class="py-4 text-right font-weight-bold text-primary">${{ forNum(total) }}</td>
                                    </tr>
                                    <tr class="bg-dark text-white text-right">
                                        <td colspan="4" class="py-4">SALDO PENDIENTE</td>
                                        <td class="py-4 h3 font-weight-bold text-warning">${{ forNum(saldo) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalles DIAN en la representación impresa -->
                        <div v-if="pedidoActivo && pedidoActivo.factura_electronica && pedidoActivo.factura_electronica.estado_dian === 'Aceptado'" class="mt-5 p-3 border rounded bg-light invoice-dian-info">
                            <div class="row align-items-center">
                                <div class="col-md-9">
                                    <h6 class="text-uppercase text-muted small font-weight-bold mb-2">Información de Facturación Electrónica (DIAN)</h6>
                                    <p class="mb-1 small text-muted"><strong>CUFE:</strong> <span class="text-monospace text-break" style="word-break: break-all;">{{ pedidoActivo.factura_electronica.cufe }}</span></p>
                                    <p class="mb-1 small text-muted"><strong>UUID Proveedor:</strong> <span class="text-monospace">{{ pedidoActivo.factura_electronica.uuid_proveedor }}</span></p>
                                    <p class="mb-0 small text-muted"><strong>Fecha Autorización:</strong> {{ formatDate(pedidoActivo.factura_electronica.fecha_transmision) }}</p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div v-if="pedidoActivo.factura_electronica.qr_code" class="p-2 bg-white rounded border d-inline-block">
                                        <div class="d-flex align-items-center justify-content-center bg-light font-weight-bold" style="width: 90px; height: 90px; border: 1px dashed #ccc; font-size: 8px; color: #666; flex-direction: column;">
                                            <i class="fa fa-qrcode fa-2x mb-1 text-dark"></i>
                                            <span>MOCK DIAN QR</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>
            </div>
        </div>

        <!-- Modal Intervalo -->
        <div class="modal fade" :class="{'mostrar' : modalIntervalo}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-xl overflow-hidden">
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title font-weight-bold"><i class="fa fa-calendar mr-2"></i> Seleccionar Intervalo</h5>
                        <button @click="cerrarModalIntervalo" type="button" class="close text-white"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">FECHA INICIAL</label>
                                    <input type="date" class="form-control" v-model="fechaI">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">FECHA FINAL</label>
                                    <input type="date" class="form-control" v-model="fechaF">
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3 text-muted small">
                            Rango: <span class="font-weight-bold text-primary">{{ fechaI }}</span> a <span class="font-weight-bold text-primary">{{ fechaF }}</span>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button @click="cerrarModalIntervalo" type="button" class="btn btn-secondary px-4">Cancelar</button>
                        <button @click="filtrarFechaVentas()" type="button" class="btn btn-primary px-4 shadow-sm">Aplicar Filtro</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Auditoría DIAN -->
        <div class="modal fade" :class="{'mostrar' : modalDIAN}" tabindex="-1" style="overflow-y: auto;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0 rounded-xl overflow-hidden">
                    <div class="modal-header bg-dark text-white py-3">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fa fa-shield mr-2 text-primary"></i> Detalles de Facturación Electrónica DIAN
                        </h5>
                        <button @click="cerrarModalDIAN" type="button" class="close text-white"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-4 bg-light" v-if="dianDetalle">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card border-0 shadow-sm rounded-lg mb-4">
                                    <div class="card-body">
                                        <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Información General</h6>
                                        <div class="row mb-3">
                                            <div class="col-sm-4 text-muted">ID Comprobante:</div>
                                            <div class="col-sm-8 font-weight-bold">#{{ dianDetalle.comprobante_id }}</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4 text-muted">Estado DIAN:</div>
                                            <div class="col-sm-8">
                                                <span class="badge" :class="dianDetalle.estado_dian === 'Aceptado' ? 'badge-success' : 'badge-danger'">
                                                    {{ dianDetalle.estado_dian }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row mb-3" v-if="dianDetalle.fecha_transmision">
                                            <div class="col-sm-4 text-muted">Transmisión:</div>
                                            <div class="col-sm-8">{{ formatDate(dianDetalle.fecha_transmision) }}</div>
                                        </div>
                                        <div class="row mb-3" v-if="dianDetalle.uuid_proveedor">
                                            <div class="col-sm-4 text-muted">UUID Proveedor:</div>
                                            <div class="col-sm-8"><code class="small">{{ dianDetalle.uuid_proveedor }}</code></div>
                                        </div>
                                        <div class="row" v-if="dianDetalle.cufe">
                                            <div class="col-12 text-muted mb-1">CUFE (Código Único de Facturación Electrónica):</div>
                                            <div class="col-12">
                                                <div class="p-3 bg-dark text-success rounded-lg font-weight-bold small text-monospace" style="word-break: break-all;">
                                                    {{ dianDetalle.cufe }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="card border-0 shadow-sm rounded-lg mb-4 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Código QR de Control</h6>
                                    <div v-if="dianDetalle.qr_code" class="p-2 bg-white rounded border shadow-xs mb-3">
                                        <div class="d-flex align-items-center justify-content-center bg-light font-weight-bold" style="width: 120px; height: 120px; border: 2px dashed #ccc; font-size: 10px; color: #666; flex-direction: column;">
                                            <i class="fa fa-qrcode fa-3x mb-2 text-dark"></i>
                                            <span>MOCK DIAN QR</span>
                                        </div>
                                    </div>
                                    <a v-if="dianDetalle.qr_code" :href="dianDetalle.qr_code" target="_blank" class="btn btn-outline-primary btn-sm btn-block">
                                        <i class="fa fa-external-link"></i> Consultar DIAN
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Respuesta DIAN -->
                        <div class="card border-0 shadow-sm rounded-lg" v-if="dianDetalle.dian_response">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Respuesta Oficial DIAN / PTA</h6>
                                <pre class="p-3 bg-dark text-white rounded-lg small mb-0" style="max-height: 200px; overflow-y: auto;">{{ formatJSON(dianDetalle.dian_response) }}</pre>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button @click="cerrarModalDIAN" type="button" class="btn btn-secondary px-4">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
    var meses=['01','02','03','04','05','06','07','08','09','10','11','12']
    export default {
        data (){
            return {
                buscare:'',
                buscarc:'',
                buscarv:'',
                buscar:'todos',
                fechaI:'',
                fechaF:'',
                filtroFecha:'1',
                buscarFechai:'',
                buscarFechaf:`${new Date().getFullYear()}-${meses[new Date().getMonth()]}-${new Date().getDate()}`,
                idorden:0,
                arrayPedidos:[],
                descuento:0,
                total:0,
                abono:0,
                saldo:0,
                totalImpuesto:0,
                idcliente:0,
                nombre:'',
                tipo_documento:'',
                num_documento:'',
                direccion:'',
                telefono_contacto:'',
                email_contacto:'',
                articulo:'',
                cantidad:1000,
                tamano:0,
                medida_material:0,
                valor:0,
                fecha_entrega: '',
                fecha: '',
                listado:1,
                modalIntervalo:0,
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : 'cliente_id',
                expandedGroups: [],
                ordenarFlecha:false,
                dominio: '',
                modalDIAN: 0,
                dianDetalle: null,
                transmitiendoId: null,
                pedidoActivo: null
            }
        },
        computed:{
            pedidosAgrupados() {
                let grupos = {};
                if (!Array.isArray(this.arrayPedidos)) return [];
                this.arrayPedidos.forEach(p => {
                    if (!p) return;
                    let cid = (p.cliente && p.cliente.id) ? p.cliente.id : 0;
                    if (!grupos[cid]) {
                        grupos[cid] = {
                            cliente: p.cliente || { id: 0, razonsocial: 'Sin Cliente' },
                            total_venta: 0,
                            total_abono: 0,
                            total_saldo: 0,
                            pedidos: []
                        };
                    }
                    grupos[cid].total_venta += parseFloat(p.total || 0);
                    grupos[cid].total_abono += parseFloat(p.abono || 0);
                    grupos[cid].total_saldo += parseFloat(p.saldo || 0);
                    grupos[cid].pedidos.push(p);
                });
                return Object.values(grupos);
            },
            isActived: function(){
                return this.pagination.current_page;
            },
            pagesNumber: function() {
                if(!this.pagination.to) return [];
                var from = this.pagination.current_page - this.offset; 
                if(from < 1) from = 1;
                var to = from + (this.offset * 2); 
                if(to >= this.pagination.last_page) to = this.pagination.last_page;
                var pagesArray = [];
                while(from <= to) { pagesArray.push(from); from++; }
                return pagesArray;             
            },
            totalCartera(){
                let res = 0;
                this.arrayPedidos.forEach(p => res += parseFloat(p.saldo || 0));
                return res;
            },
            totalAbonos(){
                let res = 0;
                this.arrayPedidos.forEach(p => res += parseFloat(p.abono || 0));
                return res;
            },
            totalVenta(){
                let res = 0;
                this.arrayPedidos.forEach(p => res += parseFloat(p.total || 0));
                return res;
            }
        },
        methods : {
            forNum(num){
                return new Intl.NumberFormat("es-CO").format(parseFloat(num || 0));
            },
            asiganarIntervalo(){ this.modalIntervalo=1; },
            cerrarModalIntervalo(){ this.modalIntervalo=0; },
            listarVentas (page,buscar,operador,criterio){
                let me=this;
                var url= me.dominio+'/orden/ventas?page='+page+'&criterio='+ criterio+'&operador='+operador+'&buscar='+buscar;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayPedidos = respuesta.pedidos.data;
                    me.pagination= respuesta.pagination;
                }).catch(function (error) { console.log(error); });
            },
            cambiarPagina(page,buscar,criterio){
                this.pagination.current_page = page;
                this.listarVentas(page,buscar,'like',criterio);
            },
            cambiarAbono(pedido){
                var me=this;
                var saldo=parseFloat(pedido.total)-parseFloat(pedido.abono);
                axios.put(me.dominio+'/orden/cambiarAbono',{
                    'id':pedido.id,
                    'abono':pedido.abono,
                    'saldo':saldo
                }).then(function (response) {
                    me.listarVentas(me.pagination.current_page, me.buscare, 'like', 'cliente_id');
                }).catch(function (error) { console.log(error); });
            },
            imprimirOrden(){ window.print(); },
            alternarGrupo(grupoId) {
                const index = this.expandedGroups.indexOf(grupoId);
                if (index > -1) this.expandedGroups.splice(index, 1);
                else this.expandedGroups.push(grupoId);
            },
            estaExpandido(grupoId) { return this.expandedGroups.indexOf(grupoId) > -1; },
            filtrarFechaVentas(){
                var me=this;
                if(this.modalIntervalo) me.filtroFecha=`${this.fechaI},${this.fechaF}`;
                var url= me.dominio+'/orden/filtrarFechaVentas?filtroFecha='+ me.filtroFecha;
                axios.get(url).then(function (response) {
                    var respuesta= response.data;
                    me.arrayPedidos = respuesta.pedidos.data;
                    me.pagination= respuesta.pagination;
                    me.modalIntervalo=0;
                }).catch(function (error) { console.log(error); });
            },
            verPedido(pedido){
                this.listado = 2;
                this.pedidoActivo = pedido;
                this.idorden = pedido.id;
                this.nombre = pedido.cliente.razonsocial;
                this.tipo_documento = pedido.cliente.tipo_documento;
                this.num_documento = pedido.cliente.num_documento;
                this.direccion = pedido.cliente.direccion;
                this.telefono_contacto = pedido.cliente.telefono_contacto;
                this.email_contacto = pedido.cliente.email_contacto;
                this.articulo = pedido.articulo;
                this.cantidad = pedido.cantidad;
                this.tamano = pedido.tamano;
                this.medida_material = pedido.medida_material;
                this.valor = pedido.valor;
                this.totalImpuesto = pedido.impuesto;
                this.descuento = pedido.descuento;
                this.total = pedido.total;
                this.abono = pedido.abono;
                this.saldo = pedido.saldo;
                this.fecha = pedido.fecha;
                this.fecha_entrega = pedido.fecha_entrega;
            },
            ocultarDetalle(){ this.listado=1; },
            verDetallesDIAN(pedido) {
                if (pedido.factura_electronica) {
                    this.dianDetalle = pedido.factura_electronica;
                    this.modalDIAN = 1;
                }
            },
            cerrarModalDIAN() {
                this.modalDIAN = 0;
                this.dianDetalle = null;
            },
            formatJSON(jsonStr) {
                try {
                    return JSON.stringify(JSON.parse(jsonStr), null, 2);
                } catch(e) {
                    return jsonStr;
                }
            },
            formatDate(dateStr) {
                if (!dateStr) return '';
                return new Date(dateStr).toLocaleString('es-CO');
            },
            descargarArchivo(tipo, id) {
                window.open(this.dominio + '/factura-electronica/descargar/' + tipo + '/' + id, '_blank');
            },
            transmitirFactura(pedido) {
                let me = this;
                me.transmitiendoId = pedido.id;
                
                axios.post(me.dominio + '/factura-electronica/transmitir', {
                    'comprobante_id': pedido.id
                })
                .then(function (response) {
                    me.transmitiendoId = null;
                    if (response.data.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'La factura ha sido transmitida y aprobada por la DIAN.',
                            icon: 'success',
                            confirmButtonClass: 'btn btn-success'
                        });
                        // Update the invoice in local state
                        pedido.factura_electronica = response.data.factura_electronica;
                        me.listarVentas(me.pagination.current_page, me.buscare, 'like', me.criterio);
                    } else {
                        Swal.fire({
                            title: 'Error de validación',
                            text: response.data.error || 'Ocurrió un error en la transmisión.',
                            icon: 'error',
                            confirmButtonClass: 'btn btn-danger'
                        });
                    }
                })
                .catch(function (error) {
                    me.transmitiendoId = null;
                    let errorMsg = 'Error en el servidor al transmitir a la DIAN.';
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMsg = error.response.data.error;
                    }
                    Swal.fire({
                        title: 'Error DIAN',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonClass: 'btn btn-danger'
                    });
                });
            }
        },
        created() {
             this.listarVentas(1,this.buscar,'like',this.criterio);
        }
    }
</script>

<style scoped>  
    .rounded-xl { border-radius: 16px !important; }
    .font-premium { font-family: 'Inter', sans-serif; }
    .seccion-header-premium {
        border-bottom: 3px solid #3b82f6;
    }
    .header-icon-box {
        background: rgba(59, 130, 246, 0.1);
        padding: 12px;
        border-radius: 14px;
    }
    .table-modern td, .table-modern th {
        padding: 15px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
    }
    .table-modern thead th {
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #718096;
    }
    .group-row-premium {
        background: #f1f5f9 !important;
        border-left: 5px solid #3b82f6;
    }
    .row-detail-glass {
        background: rgba(255, 255, 255, 0.4) !important;
        backdrop-filter: blur(5px);
    }
    .pagination-modern .page-link {
        border-radius: 8px;
        margin: 0 3px;
        border: 1px solid #e2e8f0;
        color: #4a5568;
    }
    .pagination-modern .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    .mostrar {
        display: flex !important;
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1060;
    }
    .doc-print-container {
        font-family: 'Helvetica', sans-serif;
    }
</style>


